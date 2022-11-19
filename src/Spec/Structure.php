<?php

namespace Socodo\Framework\Spec;

use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionProperty;
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
     * @return self
     */
    public function withStatus (HttpStatuses $status): self
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
     * @throws \Exception
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
                throw new \Exception('wip: property not all set.'); // TODO
            }

            $value = $this->{$name};
            if ($value instanceof Structure)
            {
                $data[$name] = $value->buildResponse();
                continue;
            }

            $data[$name] = $value;
        }

        $stream = new Stream(json_encode($data, JSON_PRETTY_PRINT));
        return (new Response())->withStatus($this->getStatus())->withHeader('Content-Type', 'application/json')->withBody($stream);
    }
}