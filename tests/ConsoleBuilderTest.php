<?php

namespace UnknownRori\Console\Tests;

use PHPUnit\Framework\TestCase;
use UnknownRori\Console\ConsoleBuilder;

/**
 * @covers \UnknownRori\Console\ConsoleBuilder
 */
class ConsoleBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function console_builder_init()
    {
        $app = new ConsoleBuilder('Test app', 'Test app');
        $this->assertInstanceOf(ConsoleBuilder::class, $app);
    }
}
