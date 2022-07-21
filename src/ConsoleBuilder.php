<?php

namespace UnknownRori\Console;

/**
 * Builder class for initialize \UnknownRori\Console\Console
 */
class ConsoleBuilder
{
    private Console $console;

    /**
     * Initialize Console object
     * @param  string  $name
     * @param  string  $description
     * 
     * @return void
     */
    public function __construct(string $name, string $description)
    {
        $this->console = new Console();
        $this->console->appName = $name;
        $this->console->appDescription = $description;
    }

    /**
     * Return a console object
     * @return \UnknownRori\Console\Console
     */
    public function getConsole(): Console
    {
        return $this->console;
    }

    /**
     * Add command using CommandBuilder
     * @param  \UnknownRori\Console\CommandBuilder $commandBuilder
     * 
     * @return void
     */
    public function addCommand(CommandBuilder $commandBuilder): void
    {
        $this->console->addCommand(
            $commandBuilder->commandName,
            $commandBuilder->commandDescription,
            $commandBuilder->commandAction,
        );

        array_filter($commandBuilder->flag, function (array $flag, string $key) use (&$commandBuilder) {
            $this->console->addFlag(
                $commandBuilder->commandName,
                $key,
                $flag['description'],
                $flag['type'],
                $flag['action'],
            );
        }, ARRAY_FILTER_USE_BOTH);
    }
}
