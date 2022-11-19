<?php

namespace Socodo\Framework\Controller;

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