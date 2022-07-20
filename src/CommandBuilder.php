<?php

namespace UnknownRori\Console;

use Closure;

class CommandBuilder
{
    public string $commandName;
    public string $commandDescription;
    public Closure $commandAction;
    public array $flag;

    public function __construct(string $commandName, string $commandDescription)
    {
        $this->commandName = $commandName;
        $this->commandDescription = $commandDescription;
    }

    public function setAction(callable $callback)
    {
        $this->commandAction = $callback;
    }

    public function addFlag(string $key, string $description, int $type, callable $callback)
    {
        $this->flag[$key] = [
            'type' => $type,
            'description' => $description,
            'action' => $callback,
        ];
    }
}
