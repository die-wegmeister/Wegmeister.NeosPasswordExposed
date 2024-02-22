<?php

namespace Wegmeister\NeosPasswordExposed\Security\Controller;

use Neos\Error\Messages\Message;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController;

#[Flow\Aspect]
class AuthenticationControllerAspect
{
    #[Flow\Before("within(Neos\Flow\Security\Authentication\Controller\AbstractAuthenticationController) && method(.*->onAuthenticationSuccess()) && setting(Wegmeister.NeosPasswordExposed.checkExposedPasswords)")]
    public function showFlashMessageIfPasswordIsExposed(JoinPointInterface $joinPoint): void
    {
        /** @var AbstractAuthenticationController $controller */
        $controller = $joinPoint->getProxy();
        $controller->addFlashMessage('This is not working.', '', Message::SEVERITY_ERROR);
    }
}
