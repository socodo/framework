<?php

namespace Socodo\Framework;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Socodo\Framework\Interfaces\ApplicationInterface;
use Socodo\Framework\Interfaces\GuardInterface;
use Socodo\Framework\Interfaces\StructureInterface;
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
                if ($guards = $this->getGuards($className, $methodName))
                {
                    foreach ($guards as $guard)
                    {
                        if ($guard->isGuarded($request))
                        {
                            throw new \Exception('wip: guarded by ' . get_class($guard) . '.');
                        }
                    }
                }

                if ($body = $this->getBodyParameter($className, $methodName))
                {
                    /** @var StructureInterface $bodyClass */
                    $bodyClass = $body->getType()->getName();
                    $matched['params'][$body->getName()] = $bodyClass::from(json_decode($request->getBody()));
                }

                $output = $this->app->call($className, $methodName, $matched['params']);
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
     * Get controller guards.
     *
     * @param string $className
     * @param string $methodName
     * @return array
     */
    protected function getGuards (string $className, string $methodName): array
    {
        try
        {
            $class = new ReflectionClass($className);
            $attrs = $class->getAttributes(GuardInterface::class, ReflectionAttribute::IS_INSTANCEOF);

            $method = new ReflectionMethod($className, $methodName);
            $attrs = array_merge($attrs, $method->getAttributes(GuardInterface::class, ReflectionAttribute::IS_INSTANCEOF));

            return array_map(static function (ReflectionAttribute $attr) {
                return $attr->newInstance();
            }, $attrs);
        }
        catch (Throwable)
        {
            return [];
        }
    }

    /**
     * Get reflector of body parameter from the method.
     *
     * @param string $className
     * @param string $methodName
     * @return ?ReflectionMethod
     */
    protected function getBodyParameter (string $className, string $methodName): ?ReflectionParameter
    {
        try
        {
            $method = new ReflectionMethod($className, $methodName);
            $parameters = $method->getParameters();
            foreach ($parameters as $parameter)
            {
                if (is_subclass_of($parameter->getType()->getName(), StructureInterface::class))
                {
                    return $parameter;
                }
            }

            return null;
        }
        catch (Throwable)
        {
            return null;
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

        if ($output instanceof StructureInterface)
        {
            return $output->buildResponse();
        }

        $stream = null;
        $contentType = null;

        $output = is_resource($output) ? new Stream($output) : $output;
        if ($output instanceof Stream)
        {
            $stream = $output;
            $contentType = mime_content_type($output->getMetadata('uri'));
        }
        elseif (is_string($output) || is_numeric($output) || is_bool($output) || is_array($output) || is_object($output))
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
        return (new Response())->withStatus(500)->withHeader('Content-Type', 'application/json')->withBody(new Stream(json_encode([
            'message' => $e->getMessage(),
            'trace' => $e->getTrace()
        ])));
    }
}