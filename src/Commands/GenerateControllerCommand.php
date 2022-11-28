<?php

namespace Socodo\Framework\Commands;

class GenerateControllerCommand extends GenerateCommand
{
    /** @var string Command name. */
    protected string $name = 'controller';

    /** @var string Command description. */
    protected string $description = 'Generate a new controller.';
}