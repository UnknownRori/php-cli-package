<?php

namespace UnknownRori\Console;

use Closure;
use ReflectionFunction;
use ReflectionParameter;

/**
 * A customizable cli helper class
 */
class Console
{
    public string $appName = "UnknownRori's CLI"; //  App name
    public string $appDescription = "A very simple abstracted CLI"; // App description
    public string $appVersion = "0.0-alpha.1"; // App version
    public string $fileName = "Unknown"; // this will be replaced at runtime
    public bool $isTitleDisplay = false; // app status if already title display
    public array $commands = []; // this is where we store the command

    // Customizable behavior
    protected ?Closure $topHeader = null; // this is for app running without any argumments
    protected ?Closure $commandDisplay = null; // this is for displaying command
    protected ?Closure $titleDisplay = null; // this is for title displaying
    protected ?Closure $confirmationDisplay = null; // this is for confirmation display
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
     * Set the confirmation display, pass the function that accept a single string argumments and should return a bool
     * @param  callable $callback
     * 
     * @return void
     */
    public function setConfirmationDisplay(callable $callback): void
    {
        $this->confirmationDisplay = $callback;
    }

    /**
     * add Command to the app
     * @param  string   $key
     * @param  string   $description
     * @param  callable $callback
     * 
     * @return void
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

    /**
     * handle the confirmation display
     * @param  string $command
     * @return bool
     */
    private function handleConfirmationDisplay(string $command): bool
    {
        if (!is_null($this->confirmationDisplay))
            return call_user_func($this->confirmationDisplay, $command);

        echo "Did you mean \e[32m{$command}\e[0m?\n";
        $userInput = readline("y/N :");

        if ($userInput == "Y" || $userInput == "y")
            return true;

        return false;
    }

    /**
     * A function use to predict what the user input is mean
     * @param string $inputCommand
     * 
     * @return string
     */
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
        if ($this->isTitleDisplay == true)
            return;

        if (is_null($this->titleDisplay)) {
            echo "\e[1m{$this->appName}\e[0m - \e[1;32m{$this->appVersion}\e[0m\n";
        } else {
            call_user_func($this->titleDisplay);
        }

        $this->isTitleDisplay = true;
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
            echo "\e[1;32mphp\e[0m \e[1;32m{$this->fileName}\e[0m \e[33m<command> <flag|arguments>\e[0m\n";

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

        echo "\e[33mGlobal flag :\e[0m \n";
        echo "\e[32m--help, -h\e[0m \t\t - \e[1mShow Help\e[0m\n";
        echo "\e[32m--quiet, -q\e[0m \t\t - \e[1mStop printing the output\e[0m\n";
        echo "\e[32m-V\e[0m \t\t\t - \e[1mShow Version\e[0m\n\n";

        echo "\e[33mCommand :\e[0m \n";

        $longestCommand = 0;
        array_map(function ($command) use (&$longestCommand) {
            if (strlen($command['title']) > $longestCommand)
                $longestCommand = strlen($command['title']);
        }, $commands);

        $displayArray = [];
        foreach ($commands as $command) {
            $displayArray[] = "\e[32m{$command['title']}\e[0m";

            if (strlen($command['title']) == $longestCommand) {
                $displayArray[] = "\t";
            } else {
                for ($i = 0; $i < ($longestCommand - strlen($command['title'])); $i++) {
                    $displayArray[] = " ";
                }
                $displayArray[] = "\t";
            }

            $displayArray[] = "- \e[1m{$command['description']}\e[0m\n";
        }

        echo implode('', $displayArray);
    }
}
