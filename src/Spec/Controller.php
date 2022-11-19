<?php

namespace Socodo\Framework\Spec;

use Psr\Http\Message\ServerRequestInterface;

class Controller
{
    /**
     * Constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct (protected ServerRequestInterface $request)
    {
    }
}