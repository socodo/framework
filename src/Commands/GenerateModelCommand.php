<?php

namespace Socodo\Framework\Commands;

class GenerateModelCommand extends GenerateCommand
{
    /** @var string Command name. */
    protected string $name = 'model';

    /** @var string Command description. */
    protected string $description = 'Generate a new model.';

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
        $lines[] = 'namespace App\\Models;';
        $lines[] = '';
        $lines[] = 'use Socodo\\ORM\\Model;';
        $lines[] = 'use Socodo\\ORM\\Attributes\\Table;';
        $lines[] = 'use Socodo\\ORM\\Attributes\\AutoIncrement;';
        $lines[] = '';
        $lines[] = '#[Table(\'' . strtolower($className) . '\')]';
        $lines[] = 'class ' . $className . ' extends Model';
        $lines[] = '{';
        $lines[] = '    /** @var int Primary id of Model "' . $className . '" */';
        $lines[] = '    #[AutoIncrement()]';
        $lines[] = '    public int $id;';
        $lines[] = '}';

        return implode("\n", $lines);
    }
}