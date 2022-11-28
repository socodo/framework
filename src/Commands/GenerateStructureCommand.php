<?php

namespace Socodo\Framework\Commands;

class GenerateStructureCommand extends GenerateCommand
{
    /** @var string Command name. */
    protected string $name = 'structure';

    /** @var string Command description. */
    protected string $description = 'Generate a new structure.';

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
        $lines[] = 'namespace App\\Structures;';
        $lines[] = '';
        $lines[] = 'use Socodo\\Framework\\Structure\\Structure;';
        $lines[] = '';
        $lines[] = 'class ' . $className . ' extends Structure';
        $lines[] = '{';
        $lines[] = '';
        $lines[] = '}';

        return implode("\n", $lines);
    }
}