<?php

namespace UnknownRori\Console;

use Closure;
use ReflectionFunction;
use ReflectionParameter;

/**
 * An abstraction layer for developing cli application in php
 */
class Console
{
    public string $appName = "UnknownRori's CLI";
    public string $appDescription = "A very simple abstracted CLI";
    public string $appVersion = "0.0-alpha.1";
    public string $fileName = "Unknown";
    public static bool $isTitleDisplay = false;
    public array $commands = [];

    // Customizable behavior
    protected ?Closure $topHeader = null;
    protected ?Closure $commandDisplay = null;
    protected ?Closure $titleDisplay = null;
    protected ?Closure $confirmationDisplay = null;
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
     * Set the confirmation display, pass the function that accept a single string argumments
     * @param  callable $callback
     * 
     * @return void
     */
    public function setConfirmationDisplay(callable $callback): void
    {
        $this->confirmationDisplay = $callback;
    }

    /**
     * 
     */
    public function addCommand(string $key, string $description, callable $callback): void
    {
        $reflection = new ReflectionFunction($callback);
        $parameters = $reflection->getParameters();

        $this->commands[$key] = [
            'title' => $key,
            'argumments' => count($parameters),
            'namedArgumments' => $this->getFunctionArgumments($parameters),
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
            $argv = $this->parseArgumments($argv);

            if (array_key_exists($command, $this->commands)) {
                return $this->run($command, $argv);
            } else {
                $this->printTitleDisplay();

                $command = $this->predictCommand($command);

                $userInput = $this->handleConfirmationDisplay($command);

                if ($userInput) {
                    echo "\n";
                    return $this->run($command, $argv);
                }
            }
        } else {
            $this->printTopHeader();
            $this->printCommand($this->commands);
        }
    }


    /**----------------
     * Private section
     */


    private function handleConfirmationDisplay($command): bool
    {
        if (!is_null($this->confirmationDisplay))
            return call_user_func($this->confirmationDisplay, $command);

        echo "Did you mean '{$command}' ";
        $userInput = readline("Y/n :");

        if ($userInput == "Y")
            return true;

        return false;
    }

    private function predictCommand(string $inputCommand): string
    {
        $probabilityCommand = [];

        foreach ($this->commands as $command) {
            $percentage = 0;
            $similarity = strlen($command['title']) - similar_text($command['title'], $inputCommand, $percentage);
            $probabilityCommand[] = ['percentage' => $percentage, 'similarity' => $similarity, 'command' => $command['title']];
        }

        $percentage = 0.0;
        $similarity = 0;
        $outCommand = '';

        foreach ($probabilityCommand as $command) {
            if ($command['percentage'] >= $percentage) {
                $percentage = $command['percentage'];
                $similarity = $command['similarity'];
                $outCommand = $command['command'];
            }
        }

        if ($outCommand != '')
            return $outCommand;

        echo "Invalid command!";
        exit(0);
    }

    /**
     * Parse the passed argumments in the console
     * @param  array<string> $argv
     * @return array
     */
    private function parseArgumments(array $argv): array
    {
        // TODO! Refator this
        if (count($argv) <= 1)
            return [];

        $argv = array_slice($argv, 2);

        for ($i = 0; $i < count($argv); $i++) {
            $parse = explode('=', $argv[$i]);
            if (count($parse) > 1) {
                $argv[$parse[0]] = $parse[1];
                unset($argv[$i]);
            }
        }

        return $argv;
    }

    /**
     * Run the passed command
     * @param  string $command
     *
     * @return mixed
     */
    private function run(string $command, array $args)
    {
        // TODO! Refator this
        $this->printTitleDisplay();

        $commandArgummentsCount = $this->commands[$command]['argumments'];

        if (count($args) == $commandArgummentsCount)
            return call_user_func($this->commands[$command]['action'], ...$args);
        else if (count($args) < $commandArgummentsCount)
            echo "Insufficient argumments \n";
        else
            echo "Too many argumments \n";
    }

    /**
     * Get the argumments name and type
     * @param  array<ReflectionParameter> $argumments
     * 
     * @return array
     */
    private function getFunctionArgumments(array $argumments): array
    {
        $argummentsList = [];

        foreach ($argumments as $argumment) {
            $argummentsList[$argumment->getName()] = (string) $argumment->getType();
        }

        return $argummentsList;
    }

    /**
     * The default Title Display
     * 
     * @return void
     */
    private function printTitleDisplay()
    {
        if (self::$isTitleDisplay == true)
            return;

        if (is_null($this->titleDisplay)) {
            echo "{$this->appName} - {$this->appVersion}\n";
            echo "Original Author : UnknownRori\n\n";
        } else {
            call_user_func($this->titleDisplay);
        }

        self::$isTitleDisplay = true;
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
