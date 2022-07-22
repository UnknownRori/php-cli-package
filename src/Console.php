<?php

namespace UnknownRori\Console;

use Closure;
use ReflectionFunction;

/**
 * Helper class to simplify cli development
 */
class Console
{
    // Run the flag action before the command is triggered
    const FLAG_BEFORE = 0;
    // Run the flag action after the command is triggered
    const FLAG_AFTER = 1;
    // Overide the current command action
    const FLAG_OVERIDE = 2;

    // Console name
    public string $appName = "UnknownRori's CLI";
    // App description
    public string $appDescription = "A very simple abstracted CLI";
    // App version
    public string $appVersion = "0.0-alpha.1";
    // this will be replaced at runtime
    public string $fileName = "Unknown";
    // this is where we store the command
    public array $commands = [];

    // this is for app running without any argumments
    protected ?Closure $topHeader = null;
    // this is for displaying command
    protected ?Closure $commandDisplay = null;
    // this is for title displaying
    protected ?Closure $titleDisplay = null;
    // this is for confirmation display
    protected ?Closure $confirmationDisplay = null;
    // this is for displaying help on specific command
    protected ?Closure $commandHelpDisplay = null;
    // console setting for verbose output
    protected bool $verbose = false;

    // app status if already title display
    protected bool $isAlreadyDisplayTitle = false;

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
     * Add command on specific key
     * @param  string   $key         - Command key
     * @param  string   $description - Command description
     * @param  callable $callback    - Action
     * 
     * @return void
     */
    public function addCommand(string $key, string $description, callable $callback): void
    {
        $this->commands[$key] = [
            'description' => $description,
            'action' => $callback,
            'flag' => [],
            'aliasFlag' => []
        ];
    }

    /**
     * Add flag on the specific command key
     * @param  string   $commandKey - Command Key that registered using `addCommand` method
     * @param  string   $key        - Flag Key
     * @param  int      $type       - Flag Type
     * @param  callable $callback   - Action
     * 
     * @return void
     */
    public function addFlag(string $commandKey, string $key, string $description, int $type, callable $callback): void
    {
        if (array_key_exists($commandKey, $this->commands)) {
            $this->commands[$commandKey]['flag'][$key] = [
                'type' => $type,
                'description' => $description,
                'action' => $callback,
            ];
        }
    }

    /**
     * Serving the cli
     * @param string|array  $argumments - passed data from $argv
     * 
     * @return mixed
     */
    public function serve($argumments): mixed
    {
        if (!is_array($argumments))
            $argumments = explode(' ', $argumments);


        $argumments = $this->parseUserInput($argumments);

        if (!is_null($argumments['command'])) {
            $this->titleDisplayHandler();

            return $this->call($argumments);
        } else {
            $this->topHeaderHandler();
            $this->commandDisplayHandler($this->commands);

            return null;
        }
    }

    // Customizable Logic

    /**
     * handle the confirmation display
     * @param  string $command
     * 
     * @return bool
     */
    protected function confirmationHandler(string $command): bool
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
     * The default Title Display
     * 
     * @return void
     */
    protected function titleDisplayHandler()
    {
        if ($this->isAlreadyDisplayTitle == true)
            return;

        if (is_null($this->titleDisplay)) {
            echo "\e[1m{$this->appName}\e[0m - \e[1;32m{$this->appVersion}\e[0m\n";
        } else {
            call_user_func($this->titleDisplay);
        }

        $this->isAlreadyDisplayTitle = true;
    }

    /**
     * Handler for top header
     * 
     * @return void
     */

    protected function topHeaderHandler(): void
    {
        $this->titleDisplayHandler();

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

    /**
     * Handler for command display
     * 
     * @return void
     */
    protected function commandDisplayHandler(array $commands): void
    {
        if (!is_null($this->commandDisplay)) {
            call_user_func($this->commandDisplay, $this->commands);
            return;
        }

        // echo "\e[33mGlobal flag :\e[0m \n";
        // echo "\e[32m--help, -h\e[0m \t\t - \e[1mShow Help\e[0m\n";
        // echo "\e[32m--quiet, -q\e[0m \t\t - \e[1mStop printing the output\e[0m\n";
        // echo "\e[32m-V\e[0m \t\t\t - \e[1mShow Version\e[0m\n\n";

        echo "\e[33mCommand :\e[0m \n";

        $longestCommand = 0;
        array_filter($commands, function (array $command, string $key) use (&$longestCommand) {
            if (strlen($key) > $longestCommand)
                $longestCommand = strlen($key);
        }, ARRAY_FILTER_USE_BOTH);

        $displayArray = [];

        array_filter($commands, function (array $command, string $key) use (&$longestCommand, &$displayArray) {
            $displayArray[] = "\e[32m{$key}\e[0m";

            if (strlen($key) == $longestCommand) {
                $displayArray[] = "\t";
            } else {
                for ($i = 0; $i < ($longestCommand - strlen($key)); $i++) {
                    $displayArray[] = " ";
                }
                $displayArray[] = "\t";
            }

            $displayArray[] = "- \e[1m{$command['description']}\e[0m\n";
        }, ARRAY_FILTER_USE_BOTH);

        echo implode('', $displayArray);
    }

    /**
     * Handle a `--help` flag on specific command
     * @param  array  $command
     * 
     * @return void
     */
    protected function commandHelpDisplayHandler(array $meta, string $name): void
    {
        if (!is_null($this->commandHelpDisplay)) {
            call_user_func($this->commandHelpDisplay, $meta, $name);
            return;
        }

        $reflectionFunction = new ReflectionFunction($meta['action']);
        $reflectionParam = $reflectionFunction->getParameters();
        $param = $this->getFunctionArgumments($reflectionParam);

        echo "\n";
        echo "\e[32mphp\e[0m \e[32m{$this->fileName}\e[0m \e[1;33m{$name} \e[0;33m<flag|arguments>\e[0m\n";

        echo "\e[1m{$meta['description']}\e[0m\n\n";
        echo "\e[33mArgumments : \e[0m\n";

        $longestCommand = 0;
        array_filter($param, function (string $argumments) use (&$longestCommand) {
            if (strlen($argumments) > $longestCommand)
                $longestCommand = strlen($argumments);
        });

        $displayArray = [];

        array_filter($param, function (string $key, string $argumments) use (&$longestCommand, &$displayArray) {
            $displayArray[] = "\e[32m{$argumments}\e[0m";

            if (strlen($argumments) == $longestCommand) {
                $displayArray[] = "\t";
            } else {
                for ($i = 0; $i < ($longestCommand - strlen($argumments)); $i++) {
                    $displayArray[] = " ";
                }
                $displayArray[] = "\t";
            }

            $displayArray[] = "- \e[1m{$key}\e[0m\n";
        }, ARRAY_FILTER_USE_BOTH);

        echo implode('', $displayArray) . "\n";

        echo "\e[33mFlag : \e[0m\n";

        $longestCommand = 0;
        array_filter($meta['flag'], function (array $flag, string $key) use (&$longestCommand) {
            if (strlen($key) > $longestCommand)
                $longestCommand = strlen($key);
        }, ARRAY_FILTER_USE_BOTH);

        $displayArray = [];

        array_filter($meta['flag'], function (array $flag, string $key) use (&$longestCommand, &$displayArray) {
            $displayArray[] = "\e[32m--{$key}\e[0m";

            if (strlen($key) == $longestCommand) {
                $displayArray[] = "\t";
            } else {
                for ($i = 0; $i < ($longestCommand - strlen($key)); $i++) {
                    $displayArray[] = " ";
                }
                $displayArray[] = "\t";
            }

            $displayArray[] = "- \e[1m{$flag['description']}\e[0m\n";
        }, ARRAY_FILTER_USE_BOTH);

        echo implode('', $displayArray);

        return;
    }

    // Console Logic

    /**
     * Parse the passed user input
     * @param  array $argumments

     * @return array
     */
    protected function parseUserInput(array $in): array
    {
        $out = [
            'command' => NULL,
            'argumments' => [],
            'flag' => [],
        ];

        $this->fileName = $in[0];

        if (count($in) > 1) {
            $out['command'] = $in[1];
            $in = array_slice($in, 2);
        }

        if (count($in) >= 1) {
            array_map(function ($param) use (&$out) {
                if (is_int(strpos($param, "="))) {
                    $parsed = explode("=", $param);
                    $out['argumments'][$parsed[0]] = $parsed[1];
                } else if (count(explode('--', $param)) >= 2) {
                    $parsed = explode("--", $param);
                    $out['flag'][] = $parsed[1];
                } else if (count(explode('-', $param)) >= 2) {
                    $parsed = explode("-", $param);
                    $out['flag'][] = $parsed[1];
                } else {
                    $out['argumments'][] = $param;
                }
            }, $in);
        }

        return $out;
    }

    /**
     * Console action call
     * @param  array $argumments
     * 
     * @return mixed
     */
    protected function call(array $input): mixed
    {
        $this->checkInputCommandExist($input);

        if (in_array('help', $input['flag']) || in_array('h', $input['flag'])) {
            return $this->commandHelpDisplayHandler($this->commands[$input['command']], $input['command']);
        }


        $commandFunction = $this->commands[$input['command']]['action'];

        $flagActionQueue = $this->filterFlagInput($input, $commandFunction);

        if (!$this->isArgummentsSatisfied($commandFunction, count($input['argumments']))) {
            echo "Too little argumments\n";
            return null;
        }

        $out = call_user_func($commandFunction, ...$input['argumments']);

        array_map(function (Closure $action) use (&$input) {
            call_user_func($action, ...$input['argumments']);
        }, $flagActionQueue);

        return $out;
    }

    /**
     * Predict the user input command if the input doesn't make any sense
     * @param  string $inputCommand
     * 
     * @return string
     */
    protected function predictCommand(string $inputCommand): string
    {
        $probabilityCommand = [];

        array_filter($this->commands, function (array $command, string $key) use (&$probabilityCommand, $inputCommand) {
            $percentage = 0;
            similar_text($inputCommand, $key, $percentage);
            $probabilityCommand[] = ['percentage' => $percentage, 'command' => $key];
        }, ARRAY_FILTER_USE_BOTH);

        $percentage = 0.0;
        $outCommand = '';

        foreach ($probabilityCommand as $command) {
            if ($command['percentage'] >= $percentage) {
                $percentage = $command['percentage'];
                $outCommand = $command['command'];
            }
        }

        if ($outCommand != '')
            return $outCommand;

        echo "Invalid command!";
        exit(0);
    }

    /**
     * Get the argumments name and type
     * @param  array<\ReflectionParameter> $argumments
     * 
     * @return array
     */
    protected function getFunctionArgumments(array $argumments): array
    {
        $argummentsList = [];

        foreach ($argumments as $argumment) {
            $argummentsList[$argumment->getName()] = (string) $argumment->getType();
        }

        return $argummentsList;
    }

    /**
     * Check the passed string if exist in command list if it doesn't exist trigger `predictCommand` method
     * @param string $command
     * 
     * @return void
     */
    protected function checkInputCommandExist(array &$input): void
    {
        if (!array_key_exists($input['command'], $this->commands)) {
            $input['command'] = $this->predictCommand($input['command']);

            echo "\n";

            $result = $this->confirmationHandler($this->predictCommand($input['command']));

            echo "\n";
            if (!$result) {
                echo "Invalid Command";
            }
        }
    }

    /**
     * Check if the passed argumments count satisfy the passed closure
     * @param  Closure  $action
     * @param  int      $argummentsCurrentlyPassed
     * 
     * @return bool
     */
    protected function isArgummentsSatisfied(Closure $action, int $argummentsCurrentlyPassed): bool
    {
        $reflectionFunction = new ReflectionFunction($action);
        $reflectionParam = $reflectionFunction->getParameters();
        $param = $this->getFunctionArgumments($reflectionParam);

        if (count($param) > $argummentsCurrentlyPassed) {
            return false;
        }

        return true;
    }

    /**
     * Filter the passed flag in input and run specific type of the flag and return queued flag
     * @param  array   $input
     * @param  Closure $commandFunction
     * 
     * @return array
     */
    protected function filterFlagInput(array &$input, Closure &$commandFunction): array
    {
        $flagActionQueue = [];

        array_filter($input['flag'], function (string $flag) use (&$input, &$commandFunction, &$flagActionQueue) {
            if (array_key_exists($flag, $this->commands[$input['command']]['flag'])) {
                switch ($this->commands[$input['command']]['flag'][$flag]['type']) {
                    case self::FLAG_BEFORE:
                        call_user_func($this->commands[$input['command']]['flag'][$flag]['action'], ...$input['argumments']);
                        break;
                    case self::FLAG_OVERIDE:
                        $commandFunction = $this->commands[$input['command']]['flag'][$flag]['action'];
                        break;
                    case self::FLAG_AFTER:
                        $flagActionQueue[] = $this->commands[$input['command']]['flag'][$flag]['action'];
                        break;
                }
            }
        });

        return $flagActionQueue;
    }
}
