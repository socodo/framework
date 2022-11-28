<?php

namespace Socodo\Framework;

use Socodo\CLI\CLI;
use Socodo\Framework\Commands\GenerateCommand;

class Console
{
    /** @var Application Application instance. */
    protected Application $app;

    /** @var CLI CLI instance. */
    protected CLI $cli;

    /**
     * Constructor.
     */
    public function __construct ()
    {
        $this->app = new Application();
        $this->cli = new CLI();
    }

    /**
     * Handle execution.
     *
     * @return never
     */
    public function handle (): never
    {
        $this->registerSocodoOptions();
        $this->registerSocodoCommands();

        $this->cli->registerSupportingCommands();
        $this->cli->handle();
        exit();
    }

    /**
     * Register socodo options.
     *
     * @return void
     */
    protected function registerSocodoOptions (): void
    {
        $this->cli->registerOption('quiet', 'Do not print any message.', 'q');
    }

    /**
     * Register socodo commands.
     *
     * @return void
     */
    protected function registerSocodoCommands (): void
    {
        $this->cli->registerCommand(new GenerateCommand());
    }
}