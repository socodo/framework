<?php

namespace Socodo\Framework;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Socodo\Framework\Interfaces\ApplicationInterface;
use Socodo\Http\Enums\HttpMethods;
use Socodo\Http\Response;
use Socodo\Http\Stream;
use Socodo\Router\Interfaces\RouteCollectionInterface;
use Socodo\Router\Route;
use Throwable;

class Handler implements RequestHandlerInterface
{
    /** @var ApplicationInterface App instance. */
    protected ApplicationInterface $app;

    /** @var RouteCollectionInterface Route collection instance. */
    protected RouteCollectionInterface $collection;

    /**
     * Constructor.
     *
     * @param ApplicationInterface $app
     * @param RouteCollectionInterface $collection
     */
    public function __construct (ApplicationInterface $app, RouteCollectionInterface $collection)
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

        $stream = null;
        $contentType = null;

        $output = is_resource($output) ? new Stream($output) : $output;
        if ($output instanceof Stream)
        {
            $stream = $output;
            $contentType = mime_content_type($output->getMetadata('uri'));
        }

        if (is_string($output))
        {
            $stream = new Stream($output);
            $contentType = 'text/plain';
        }

        if (is_array($output) || is_object($output))
        {
            $stream = new Stream(json_encode($output, JSON_PRETTY_PRINT));
            $contentType = 'application/json';
        }

        if ($stream !== null)
        {
            return (new Response())->withStatus(200)->withHeader('Content-Type', $contentType)->withBody($stream);
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