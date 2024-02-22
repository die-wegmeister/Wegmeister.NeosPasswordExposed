<?php

namespace Wegmeister\NeosPasswordExposed\Service;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Validation\Exception as ValidationException;

#[Flow\Aspect]
class UserServiceAspect
{
    #[Flow\Inject]
    protected VariableFrontend $cacheFrontend;
}
