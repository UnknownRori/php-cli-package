<?php

namespace UnknownRori\Console\Tests;

use PHPUnit\Framework\TestCase;
use UnknownRori\Console\Console;

/**
 * @covers \UnknownRori\Console\Console
 */
class ConsoleTest extends TestCase
{
    /**
     * @test
     */
    public function check_if_instance_correctly_made()
    {
        $consoleInstance = new Console();

        $this->assertInstanceOf(Console::class, $consoleInstance);
    }

    /**
     * @test
     */
    public function first_command_that_return_1()
    {
        $app = new Console();
        $app->addCommand("test", "it should return 1", function () {
            return 1;
        });

        $result =  $app->serve('index.php test');
        $this->assertEquals(1, $result);
    }
}
