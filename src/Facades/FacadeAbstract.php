<?php

namespace Socodo\Framework\Facades;

use Socodo\Framework\Application;
use Socodo\Injection\Exceptions\EntryNotFoundException;

abstract class FacadeAbstract
{
    /** @var ?Application Application. */
    protected static ?Application $app = null;

    /** @var array<FacadeAbstract> Shared facade instances. */
    protected static array $instances = [];

    /**
     * Static call magic method.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws EntryNotFoundException
     */
    public static function __callStatic (string $method, array $arguments)
    {
        if (static::$app === null)
        {
            static::$app = Application::$app;
        }

        if (!isset(static::$instances[static::class]))
        {
            static::$instances[static::class] = static::$app->get(static::class);
        }

        return static::$instances[static::class]->{$method}(...$arguments);
    }
}