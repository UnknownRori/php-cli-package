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

    /**
     * @test
     */
    public function second_command_sum_two_number()
    {
        $app = new Console();
        $app->addCommand("sum", "it should calculate correctly", function (int $a, int $b) {
            return $a + $b;
        });

        $result = $app->serve('index.php sum 1 1');
        $this->assertEquals(2, $result);
    }

    /**
     * @test
     */
    public function third_command_sum_two_number()
    {
        $app = new Console();
        $app->addCommand("sum", "it should calculate correctly", function (int $a, int $b) {
            return $a + $b;
        });

        $result = $app->serve('index.php sum a=1 b=1');
        $this->assertEquals(2, $result);
    }

    /**
     * @test
     */
    public function first_string_concat_command()
    {
        $app = new Console();
        $app->addCommand('concat', 'it should concatenate a string', function (string $a, string $b) {
            return "{$a} {$b}";
        });

        $result = $app->serve('index.php concat a=Hello, b=World!');
        $this->assertEquals("Hello, World!", $result);
    }

    public function second_string_concat_command()
    {
        $app = new Console();
        $app->addCommand('concat', 'it should concatenate a string', function (string $a, string $b) {
            return "{$a} {$b}";
        });

        $result = $app->serve('index.php concat b=World! a=Hello,');
        $this->assertEquals("Hello, World!", $result);
    }
}
