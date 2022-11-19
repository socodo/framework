<?php

namespace Socodo\Framework\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Socodo\Http\Enums\HttpStatuses;

interface StructureInterface
{
    /**
     * Get HTTP status.
     *
     * @return HttpStatuses
     */
    public function getStatus (): HttpStatuses;

    /**
     * Create an instance with a new status.
     *
     * @param HttpStatuses $status
     * @return $this
     */
    public function withStatus (HttpStatuses $status): self;

    /**
     * Build a response.
     *
     * @return ResponseInterface
     */
    public function buildResponse (): ResponseInterface;
}