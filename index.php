<?php

require "./vendor/autoload.php";

use UnknownRori\Console\Console;

$console = new Console();
$console->addCommand('test', "It's working", function () {
    echo "it work";
});
$console->addCommand('sum', "It's working", function (float $a, float $b) {
    echo $a + $b;
});
$console->addCommand('concat', "It's working", function (string $a, string $b) {
    echo $a . $b;
});
$console->addCommand('div', "It's working", function (float $a, float $b) {
    echo $a / $b;
});

$console->serve($argv);
