<?php

namespace Socodo\Framework\Interfaces;

use Attribute;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

#[Attribute(Attribute::IS_REPEATABLE|Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
interface GuardInterface
{
    /**
     * Determine if the request should be guarded.
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function isGuarded (ServerRequestInterface $request): bool;
}