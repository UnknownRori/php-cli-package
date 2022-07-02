<?php

namespace UnknownRori\Console\Tests;

use PHPUnit\Framework\TestCase;
use UnknownRori\Console\Console;

/**
 * @covers \UnknownRori\Console\Console
 */
class ConsoleTests extends TestCase
{
    /**
     * @test
     */
    public function check_if_instance_correctly_made()
    {
        $consoleInstance = new Console();

        $this->assertInstanceOf(Console::class, $consoleInstance);
    }
}
