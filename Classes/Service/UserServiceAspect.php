<?php

namespace Wegmeister\NeosPasswordExposed\Service;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Validation\Exception as ValidationException;
use Psr\Http\Message\ResponseInterface;

#[Flow\Aspect]
class UserServiceAspect
{
    /** @var VariableFrontend */
    #[Flow\Inject]
    protected $cache;

    #[Flow\InjectConfiguration()]
    protected array $settings;

    /**
     * @param JoinPointInterface $joinPoint
     * @return string
     * @throws ValidationException
     */
    #[Flow\Around("method(Neos\Neos\Domain\Service\UserService->addUser()) && setting(Wegmeister.NeosPasswordExposed.checkExposedPasswords)")]
    public function addUserWithExposedPasswordCheck(JoinPointInterface $joinPoint)
    {
        $this->checkIfExposed($joinPoint->getMethodArgument('password'));


        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }

    /**
     * @param JoinPointInterface $joinPoint
     * @return string
     * @throws ValidationException
     */
    #[Flow\Around("method(Neos\Neos\Domain\Service\UserService->setUserPassword()) && setting(Wegmeister.NeosPasswordExposed.checkExposedPasswords)")]
    public function setUserPasswordWithExposedPasswordCheck(JoinPointInterface $joinPoint)
    {
        $this->checkIfExposed($joinPoint->getMethodArgument('password'));

        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }

    private function checkIfExposed(string $password): void
    {
        $passwordHash = sha1($password);
        $passwordHashPrefix = substr($passwordHash, 0, 5);
        $cacheKey = 'pwned_' . $passwordHashPrefix;

        if ($this->cache->has($cacheKey)) {
            $cachedResult = $this->cache->get($cacheKey);
            $lines = explode("\n", $cachedResult);
        } else {
            $response = $this->request($passwordHashPrefix);

            if ($response instanceof ResponseInterface && $response->getStatusCode() === 200) {
                $body = (string)$response->getBody();
                $this->cache->set($cacheKey, $body, [], 36000); // 10 hours
                $lines = explode("\n", $body);
            } else {
                return;
            }
        }

        foreach ($lines as $line) {
            [$hashSuffix, $count] = explode(':', $line);
            if (strtoupper(substr($passwordHash, 5)) === $hashSuffix) {
                throw new ValidationException('The password is exposed in ' . $count . ' data breaches.');
            }
        }
    }



    private function request(string $passwordHashPrefix): ResponseInterface
    {
        $uri = 'https://api.pwnedpasswords.com/range/' . strtoupper($passwordHashPrefix);
        $browser = new Browser();
        $curlEngine = new CurlEngine();
        $curlEngine->setOption(CURLOPT_CONNECTTIMEOUT, 10);
        $browser->setRequestEngine($curlEngine);

        return $browser->request($uri);
    }
}
