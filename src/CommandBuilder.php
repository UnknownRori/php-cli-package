<?php

namespace UnknownRori\Console;

use Closure;

/**
 * A builder class for helping creating command
 */
class CommandBuilder
{
    public string $commandName;
    public string $commandDescription;
    public Closure $commandAction;
    public array $flag;

    /**
     * Initialize CommandBuilder object
     * 
     * @return void
     */
    public function __construct(string $commandName, string $commandDescription)
    {
        $this->commandName = $commandName;
        $this->commandDescription = $commandDescription;
    }

    /**
     * Set the command action
     * @param  callable  $callback
     * 
     * @return void
     */
    public function setAction(callable $callback): void
    {
        $this->commandAction = $callback;
    }

    /**
     * Add flag on the command
     * @param  string   $key
     * @param  string   $description
     * @param  int      $type
     * @param  callable $callback
     * 
     * @return void
     */
    public function addFlag(string $key, string $description, int $type, callable $callback): void
    {
        $this->flag[$key] = [
            'type' => $type,
            'description' => $description,
            'action' => $callback,
        ];
    }
}
