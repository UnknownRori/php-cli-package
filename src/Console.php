<?php

namespace UnknownRori\Console;

use Closure;

/**
 * An abstraction layer for developing cli application in php
 */
class Console
{
    public string $appName        = "UnknownRori's CLI";
    public string $appDescription = "A very simple abstracted CLI";
    public string $appVersion     = "0.0-alpha.1";
    public string $fileName       = "Unknown";
    public bool $status           = false;
    public array $commands = [];

    // Customizable behavior
    public ?Closure $topHeader    = null;
    public ?Closure $commandDisplay = null;

    /**
     * 
     */
    public function setHeader(callable $callback): void
    {
        $this->topHeader = $callback;
    }

    /**
     * 
     */
    public function addCommand(string $key, string $description, callable $callback): void
    {
        $this->commands[$key] = [
            'title' => $key,
            'description' => $description,
            'action' => $callback
        ];
    }

    /**
     * Serve the cli
     * @param array $argv - arguments passed to file
     * @return mixed
     */
    public function serve($argv)
    {
        if (!is_array($argv))
            explode(' ', $argv);

        $this->fileName = $argv[0];
        $this->printTopHeader();
        if (count($argv) > 1) {
            // Todo Run Code
        } else {
            echo "Global flag : \n";
            echo "--help, -h \t\t - Show Help\n";
            echo "--quiet, -q \t\t - Stop printing the output\n";
            echo "-v \t\t\t - Show Version\n\n";
            $this->printCommand($this->commands);
        }
    }


    /**----------------
     * Private section
     */

    /**
     * This function will print out the top header of the console
     * @return void
     */
    private function printTopHeader(): void
    {
        if (is_null($this->topHeader)) {
            echo "{$this->appName} - {$this->appVersion}\n";
            echo "Original Author : UnknownRori\n\n";
            echo "{$this->appDescription}\n\n";
            echo "php {$this->fileName} <command> <flag|arguments>\n";

            for ($i = 0; $i < 40; $i++) {
                echo "-";
            }
            echo "\n";
        } else {
            call_user_func($this->topHeader);
        }
    }

    // Todo : Improve this code
    private function printCommand(array $commands): void
    {
        echo "Command : \n";

        $longestCommand = 0;
        array_map(function ($command) use (&$longestCommand) {
            if (strlen($command['title']) > $longestCommand)
                $longestCommand = strlen($command['title']);
        }, $commands);

        foreach ($commands as $command) {
            $displayArray = [];
            $displayArray[] = $command['title'];

            if (strlen($command['title']) == $longestCommand) {
                $displayArray[] = "\t";
            } else {
                for ($i = 0; $i < ($longestCommand - strlen($command['title'])); $i++) {
                    $displayArray[] = " ";
                }
                $displayArray[]  = "\t";
            }

            $displayArray[] = "- {$command['description']}\n";

            echo implode('', $displayArray);
        }
    }
}
