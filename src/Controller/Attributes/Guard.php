<?php

namespace Socodo\Framework\Controller\Attributes;

use Attribute;
use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Socodo\Framework\Interfaces\GuardInterface;

#[Attribute(Attribute::IS_REPEATABLE|Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class Guard implements GuardInterface
{
    /** @var Closure<bool> Guard determiner. */
    protected Closure $guard;

    /**
     * Constructor.
     *
     * @param Closure<bool> $guard
     */
    public function __construct (Closure $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Determine if the request should be guarded.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function isGuarded (ServerRequestInterface $request): bool
    {
        $guard = $this->guard;
        return !!$guard($request);
    }
}