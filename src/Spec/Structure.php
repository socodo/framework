<?php

namespace Socodo\Framework\Spec;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionProperty;
use Socodo\Framework\Exceptions\StructureResolutionException;
use Socodo\Framework\Interfaces\StructureInterface;
use Socodo\Http\Enums\HttpStatuses;
use Socodo\Http\Response;
use Socodo\Http\Stream;

class Structure implements StructureInterface
{
    /** @var HttpStatuses Http status. */
    private HttpStatuses $status = HttpStatuses::OK;

    /**
     * Get HTTP status.
     *
     * @return HttpStatuses
     */
    public function getStatus (): HttpStatuses
    {
        return $this->status;
    }

    /**
     * Create an instance with a new status.
     *
     * @param HttpStatuses $status
     * @return static
     */
    public function withStatus (HttpStatuses $status): static
    {
        if ($this->status === $status)
        {
            return $this;
        }

        $new = clone $this;
        $new->status = $status;
        return $new;
    }

    /**
     * Build a response.
     *
     * @return ResponseInterface
     */
    public function buildResponse (): ResponseInterface
    {
        $data = [];
        $properties = (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property)
        {
            $name = $property->getName();
            if (!isset($this->{$name}))
            {
                throw new StructureResolutionException(static::class . '::buildResponse() Cannot build response before all public properties are set, but property $' . $name . ' not set.');
            }

            $value = $this->{$name};
            if ($value instanceof StructureInterface)
            {
                $response = $value->buildResponse();
                $data[$name] = json_decode((string) $response->getBody());
                continue;
            }

            $data[$name] = $value;
        }

        $stream = new Stream(json_encode($data, JSON_PRETTY_PRINT));
        return (new Response())->withStatus($this->getStatus())->withHeader('Content-Type', 'application/json')->withBody($stream);
    }

    /**
     * Create an instance from a given data.
     *
     * @param array|object $data
     * @return static
     */
    public static function from (array|object $data): static
    {
        if (is_object($data))
        {
            $data = (array) $data;
        }

        $new = new static();
        $properties = (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property)
        {
            $name = $property->getName();
            if (!isset($data[$name]))
            {
                throw new InvalidArgumentException(static::class . '::from() Argument #1 ($data) must contains key named "' . $name . '".');
            }

            $new->{$name} = $data[$name];
        }

        return $new;
    }
}