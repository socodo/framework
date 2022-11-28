<?php

namespace Socodo\Framework\Commands;

use Exception;
use Socodo\CLI\Commands\CommandAbstract;
use Socodo\CLI\Enums\Colors;
use Socodo\CLI\Writer;
use Socodo\Framework\Application;
use Socodo\Router\Interfaces\RouteCollectionInterface;
use Socodo\SDK\SDK;

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
        $path = realpath($path);

        try
        {
            $max = 3;
            $writer->progress(0, $max);

            $collection = Application::$app->get(RouteCollectionInterface::class);
            $writer->progress(1, $max);

            $sdk = new SDK($collection);
            $sdk->compile($path);
            $writer->progress(2, $max);

            chdir($path);
            if (!system('npm install', $code))
            {
                throw new Exception('Failed to execute npm install.', $code);
            }

            chdir($cwd);
            $writer->color(Colors::BLUE, 'SDK built successfully at "' . $path . '".');
        }
        catch (Exception $e)
        {
            chdir($cwd);

            $writer->color(Colors::RED, 'Failed to build a SDK. (' . $e->getCode() . ')');
            $writer->write('');
            $writer->color(Colors::PURPLE, 'Trace:');
            var_dump($e);
        }
    }
}