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
}
