<?php

namespace Socodo\Framework;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Socodo\Framework\Interfaces\ApplicationInterface;
use Socodo\Injection\Container;
use Socodo\Injection\Exceptions\EntryNotFoundException;

class Application extends Container implements ApplicationInterface
{
    /** @var Application The application instance. */
    public static Application $app;

    /**
     * Constructor.
     */
    public function __construct ()
    {
        static::$app = $this;

        $this->registerBaseBindings();
    }

    /**
     * Register some basic bindings.
     *
     * @return void
     */
    protected function registerBaseBindings (): void
    {
        $this->set(Application::class, $this);
        $this->set(Container::class, $this);
    }

    /**
     * Handle HTTP request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws EntryNotFoundException
     */
    public function handle (ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this->get(Handler::class);
        return $handler->handle($request);
    }

    /**
     * Terminate an app.
     *
     * @param ResponseInterface $response
     * @return never
     */
    public function terminate (ResponseInterface $response): never
    {
        $statusLine = sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase());
        header($statusLine);

        foreach ($response->getHeaders() as $key => $_)
        {
            $headerLine = sprintf('%s: %s', $key, $response->getHeaderLine($key));
            header($headerLine, false);
        }

        echo $response->getBody()->getContents();
        exit();
    }
}