<?php

namespace UnknownRori\Console\Tests;

use PHPUnit\Framework\TestCase;
use UnknownRori\Console\CommandBuilder;

/**
 * @covers \UnknownRori\Console\CommandBuilder
 */
class CommandBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function command_builder_init()
    {
        $commandBuilder = new CommandBuilder('Test command', 'Hello, World');
        $this->assertInstanceOf(CommandBuilder::class, $commandBuilder);
    }
}
