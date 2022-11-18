<?php

namespace Socodo\Framework;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Socodo\Http\Enums\HttpMethods;
use Socodo\Http\Response;
use Socodo\Http\Stream;
use Socodo\Router\Route;
use Socodo\Router\RouteCollection;
use Throwable;

class Handler implements RequestHandlerInterface
{
    /** @var Application App instance. */
    protected Application $app;

    /** @var RouteCollection Route collection instance. */
    protected RouteCollection $collection;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param RouteCollection $collection
     */
    public function __construct (Application $app, RouteCollection $collection)
    {
        $this->app = $app;
        $this->collection = $collection;
    }

    /**
     * Handle server request.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle (ServerRequestInterface $request): ResponseInterface
    {
        $matched = $this->collection->match($request);
        if ($matched === null)
        {
            return (new Response())->withStatus(404);
        }

        $this->app->set(Route::class, $matched['route']);
        $this->app->set(HttpMethods::class, $matched['method']);

        if (is_string($matched['controller']))
        {
            if (str_contains($matched['controller'], '@'))
            {
                $matched['controller'] = explode('@', $matched['controller'], 2);
            }
            else
            {
                $matched['controller'] = [ $matched['controller'], '__invoke' ];
            }
        }

        try
        {
            if (is_array($matched['controller']))
            {
                [ $className, $methodName ] = $matched['controller'];
                $output = $this->app->call($className, $methodName);
                return $this->buildResponse($output);
            }

            throw new \Exception('wip: cannot determine how to execute controller.'); // TODO
        }
        catch (Throwable $e)
        {
            return $this->buildErrorResponse($e);
        }
    }

    /**
     * Build controller output to ResponseInterface.
     *
     * @param mixed $output
     * @return ResponseInterface
     */
    protected function buildResponse (mixed $output): ResponseInterface
    {
        if ($output instanceof ResponseInterface)
        {
            return $output;
        }

        $response = new Response();

        if (is_string($output) || is_resource($output))
        {
            return $response->withStatus(200)->withBody(new Stream($output));
        }

        if (is_array($output) || is_object($output))
        {
            return $response->withStatus(200)->withBody(new Stream(json_encode($output, JSON_PRETTY_PRINT)));
        }

        return $this->buildErrorResponse(new \Exception('wip: cannot determine how to build response.')); // TODO
    }

    /**
     * Build an error response.
     *
     * @param Throwable $e
     * @return ResponseInterface
     */
    protected function buildErrorResponse (Throwable $e): ResponseInterface
    {
        return (new Response())->withStatus(500)->withBody(new Stream(json_encode($e)));
    }
}