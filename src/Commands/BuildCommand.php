<?php

namespace Socodo\Framework\Commands;

use Exception;
use Socodo\CLI\Commands\CommandAbstract;
use Socodo\CLI\Enums\Colors;
use Socodo\CLI\Writer;
use Socodo\Router\Loaders\AttributeLoader;
use Socodo\SDK\SDK;
use Socodo\SDK\SDKConfig;

class BuildCommand extends CommandAbstract
{
    /** @var string Command name. */
    protected string $name = 'build';

    /** @var string Command description. */
    protected string $description = 'Build a socodo SDK.';

    /**
     * Handle execution.
     *
     * @param Writer $writer
     * @param array $arguments
     * @param array $options
     * @return void
     */
    public function handle (Writer $writer, array $arguments = [], array $options = []): void
    {
        $cwd = getcwd();
        $path = trim($arguments[0] ?? '');
        if ($path === '')
        {
            $path = getcwd() . '/dist/';
        }

        try
        {
            $progress = [
                /** Create a path, and use as realpath. */
                static function () use (&$path) {
                    if (!is_dir($path))
                    {
                        mkdir($path, 0777, true);
                    }
                    $path = realpath($path);
                },

                /** Load a RouteCollection. */
                static function () use (&$collection) {
                    $loader = new AttributeLoader('App\\');
                    $collection = $loader->load();
                },

                /** Create a SDKConfig instance. */
                static function () use (&$config) {
                    $config = new SDKConfig();
                },

                /** Compile SDK. */
                static function () use (&$path, &$config, &$collection) {
                    $sdk = new SDK();
                    $sdk->setConfig($config);
                    $sdk->setRouteCollection($collection);
                    $sdk->compile($path);
                },

                /** Install npm packages. */
                static function () use (&$cwd, &$path) {
                    chdir($path);
                    system('npm install');
                    chdir($cwd);
                }
            ];

            $max = count($progress);
            $writer->progress(0, $max);
            foreach ($progress as $i => $closure)
            {
                $closure();
                $writer->progress($i + 1, $max);
            }

            $writer->color(Colors::BLUE, 'SDK built successfully at "' . $path . '".');
        }
        catch (Exception $e)
        {
            chdir($cwd);

            $writer->color(Colors::RED, 'Failed to build a SDK. (' . $e->getCode() . ')');
            $writer->write('');
            $writer->color(Colors::PURPLE, 'Trace:');
            $writer->color(Colors::LIGHT_GRAY, $e->getTraceAsString());
        }
    }
}