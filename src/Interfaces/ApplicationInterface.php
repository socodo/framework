<?php

namespace Socodo\Framework\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface ApplicationInterface extends RequestHandlerInterface
{
    /**
     * Handle HTTP request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle (ServerRequestInterface $request): ResponseInterface;

    /**
     * Terminate an app.
     *
     * @param ResponseInterface $response
     * @return never
     */
    public function terminate (ResponseInterface $response): never;
}