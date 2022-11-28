<?php

namespace Socodo\Framework\Commands;

use Exception;
use Socodo\CLI\Commands\CommandAbstract;
use Socodo\CLI\Enums\Colors;
use Socodo\CLI\Writer;

class GenerateCommand extends CommandAbstract
{
    /** @var string Command name. */
    protected string $name = 'generate';

    /** @var string Command description. */
    protected string $description = 'Generate a new socodo entity.';

    /**
     * Constructor.
     */
    public function __construct ()
    {
        if (static::class !== self::class)
        {
            return;
        }

        $this->addChildCommand(new GenerateControllerCommand());
        $this->addChildCommand(new GenerateModelCommand());
        $this->addChildCommand(new GenerateStructureCommand());
    }

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
        if (static::class === self::class)
        {
            $items = $this->buildCommandItems($this->childCommands, $this->getName());
            $writer->color(Colors::LIGHT_BROWN, 'Commands:');
            $writer->index($items);
            return;
        }

        $appRoot = $this->getCurrentProjectRoot();
        if ($appRoot === false)
        {
            $writer->color(Colors::RED, 'Could not locate app root path. You need to change the working directory to app root.');
            return;
        }

        $name = trim($arguments[0] ?? '');
        if ($name === '')
        {
            $writer->color(Colors::RED, 'Argument #1 ($name) missing.');
            $writer->color(Colors::LIGHT_BROWN, 'Usage:');
            $writer->color(Colors::GREEN, '  socodo generate:' . $this->name . ' "' . $this->name . 'Name"');
            return;
        }

        $generatedClass = ucfirst($name);
        if ($name === $this->name)
        {
            $generatedClass = 'The' . $generatedClass;
        }

        $generatedRoot = $appRoot . '/src/' . ucfirst($this->name) . 's';
        $generatedPath = $generatedRoot . '/' . $generatedClass . '.php';
        if (file_exists($generatedPath))
        {
            $writer->color(Colors::RED, ucfirst($this->name) . ' "' . $generatedClass . '" already exists.');
            return;
        }

        @mkdir($generatedRoot, 0777, true);
        if (!is_dir($generatedRoot))
        {
            $writer->color(Colors::RED, 'Failed to create directory "' . $generatedRoot . '". Check the permissions or others.');
            return;
        }

        $generateString = $this->getGeneratedClassString($generatedClass);
        file_put_contents($generatedPath, $generateString);
        $writer->color(Colors::BLUE, ucfirst($this->name) . ' "' . $generatedClass . '" generated at "' . $generatedPath . '".');
    }

    /**
     * Build command sheet items from command array.
     *
     * @param array $commands
     * @param string|null $parentName
     * @return array
     */
    protected function buildCommandItems (array $commands, string $parentName = null): array
    {
        $items = [];
        foreach ($commands as $command)
        {
            $name = ($parentName ? $parentName . ':' : '') . $command->getName();
            $item = [
                'name' => $name,
                'description' => $command->getDescription()
            ];

            $children = $command->getChildCommands();
            if (!empty($children))
            {
                $item['children'] = $this->buildCommandItems($children, $name);
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get current project root directory.
     *
     * @param string|null $cwd
     * @return false|string
     */
    protected function getCurrentProjectRoot (string $cwd = null): false|string
    {
        if ($cwd === null)
        {
            $cwd = getcwd();
        }

        try
        {
            if (!file_exists($cwd . '/composer.json'))
            {
                throw new Exception();
            }

            $composerJson = file_get_contents($cwd . '/composer.json');
            $composer = json_decode($composerJson);
            if (!isset($composer->require->{'socodo/framework'}))
            {
                throw new Exception();
            }

            return realpath($cwd);
        }
        catch (Exception)
        {
            $cwd = realpath($cwd . '/../');
            if ($cwd === '/' || str_ends_with($cwd, ':\\'))
            {
                return false;
            }

            return $this->getCurrentProjectRoot($cwd);
        }
    }

    /**
     * Generate a PHP class string.
     *
     * @param string $className
     * @return string
     */
    protected function getGeneratedClassString (string $className): string
    {
        $lines = [];
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = 'namespace App\\' . ucfirst($this->name) . ';';
        $lines[] = '';
        $lines[] = 'class ' . $className;
        $lines[] = '{';
        $lines[] = '    public function __construct ()';
        $lines[] = '    {';
        $lines[] = '        // TODO: Change the autogenerated stub';
        $lines[] = '    }';
        $lines[] = '}';

        return implode("\n", $lines);
    }
}