<?php

namespace Socodo\Framework;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Socodo\Framework\Interfaces\ApplicationInterface;
use Socodo\Injection\Container;
use Socodo\Injection\Exceptions\EntryNotFoundException;
use Socodo\Router\Interfaces\LoaderInterface;
use Socodo\Router\Interfaces\RouteCollectionInterface;
use Socodo\Router\Loaders\AttributeLoader;
use Socodo\Router\Router;

class Application extends Container implements ApplicationInterface
{
    /** @var ApplicationInterface The application instance. */
    public static ApplicationInterface $app;

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
     * Get loaded route collection.
     *
     * @return RouteCollectionInterface
     * @throws EntryNotFoundException
     */
    protected function getLoadedRouteCollection (): RouteCollectionInterface
    {
        $this->set(LoaderInterface::class, new AttributeLoader('App\\'));

        /** @var Router $router */
        $router = $this->get(Router::class);
        return $router->getCollection();
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
        $this->getLoadedRouteCollection();

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