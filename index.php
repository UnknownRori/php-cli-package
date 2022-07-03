<?php

require "./vendor/autoload.php";

use UnknownRori\Console\Console;

$console = new Console();
$console->addCommand('test', "It's working", function () {
    echo "it work";
});
$console->addCommand('make:seeder', "It's working", function () {
    echo "it work";
});
$console->addCommand('make:seeder:another:long', "It's working", function () {
    echo "it work";
});
$console->addCommand('make:seeder:long:long:command', "It's working", function () {
    echo "it work";
});
$console->serve($argv);
