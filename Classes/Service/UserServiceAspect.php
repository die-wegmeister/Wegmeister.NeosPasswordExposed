<?php

namespace Wegmeister\NeosPasswordExposed\Service;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Validation\Exception as ValidationException;

#[Flow\Aspect]
class UserServiceAspect
{
    /** @var VariableFrontend */
    #[Flow\Inject]
    protected $cache;

    /**
     * @param JoinPointInterface $joinPoint
     * @return string
     * @throws ValidationException
     */
    #[Flow\Around("method(Neos\Neos\Domain\Service\UserService->addUser()) && setting(Wegmeister.NeosPasswordExposed.checkExposedPasswords)")]
    public function addUserWithExposedPasswordCheck(JoinPointInterface $joinPoint)
    {
        throw new ValidationException('This is working.');

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
        throw new ValidationException('This is working.');

        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }
}
