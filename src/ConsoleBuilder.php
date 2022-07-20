<?php

namespace UnknownRori\Console;

class ConsoleBuilder
{
    private Console $console;

    public function __construct(string $name, string $description)
    {
        $this->console = new Console();
        $this->console->appName = $name;
        $this->console->appDescription = $description;
    }

    public function getConsole(): Console
    {
        return $this->console;
    }

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
