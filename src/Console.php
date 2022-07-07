<?php

namespace UnknownRori\Console;

use Closure;

/**
 * An abstraction layer for developing cli application in php
 */
class Console
{
    public string $appName = "UnknownRori's CLI";
    public string $appDescription = "A very simple abstracted CLI";
    public string $appVersion = "0.0-alpha.1";
    public string $fileName = "Unknown";
    public array $commands = [];

    // Customizable behavior
    protected ?Closure $topHeader = null;
    protected ?Closure $commandDisplay = null;
    protected ?Closure $titleDisplay = null;
    protected bool $verbose = false;

    /**
     * Set the display header using passed function
     * @param  callable $callback
     * 
     * @return void
     */
    public function setHeader(callable $callback): void
    {
        $this->topHeader = $callback;
    }

    /**
     * Set the display command using passed function, the function should accept the array of command
     * @param  callable $callback
     * 
     * @return void
     */
    public function setCommandDisplay(callable $callback): void
    {
        $this->commandDisplay = $callback;
    }

    /**
     * Set the title display (the thing always displayed when the cli run)
     * @param  callable $callback
     * 
     * @return void
     */
    public function setTitleDisplay(callable $callback): void
    {
        $this->titleDisplay = $callback;
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

    // Todo : Improve the code
    /**
     * Serve the cli
     * @param array $argv - arguments passed to file
     * @return mixed
     */
    public function serve($argv)
    {
        if (!is_array($argv))
            $argv = explode(' ', $argv);

        $this->fileName = $argv[0];

        if (count($argv) > 1) {
            $command = $argv[1];
            $argv = array_slice($argv, 1);

            return $this->run($command, $argv);
        } else {
            $this->printTopHeader();
            $this->printCommand($this->commands);
        }
    }


    /**----------------
     * Private section
     */

    /**
     * Run the passed command
     * @param  string $command
     *
     * @return mixed
     */
    private function run(string $command, array $args): mixed
    {
        $this->printTitleDisplay();

        return call_user_func($this->commands[$command]['action'], $args);
    }

    /**
     * The default Title Display
     * 
     * @return void
     */
    private function printTitleDisplay()
    {
        if (is_null($this->titleDisplay)) {
            echo "{$this->appName} - {$this->appVersion}\n";
            echo "Original Author : UnknownRori\n\n";
        } else {
            call_user_func($this->titleDisplay);
        }
    }

    /**
     * This function will print out the top header of the console
     * @return void
     */
    private function printTopHeader(): void
    {
        $this->printTitleDisplay();
        if (is_null($this->topHeader)) {
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
        if (!is_null($this->commandDisplay)) {
            call_user_func($this->commandDisplay, $this->commands);
            return;
        }

        echo "Global flag : \n";
        echo "--help, -h \t\t - Show Help\n";
        echo "--quiet, -q \t\t - Stop printing the output\n";
        echo "-v \t\t\t - Show Version\n\n";

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
                $displayArray[] = "\t";
            }

            $displayArray[] = "- {$command['description']}\n";

            echo implode('', $displayArray);
        }
    }
}
