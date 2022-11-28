<?php

namespace Socodo\Framework\Commands;

use Socodo\CLI\Commands\CommandAbstract;
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
            $writer->write('Commands:');
            $writer->index($items);
        }
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
}