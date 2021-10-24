<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\Variable;

class VariableTest extends TestCase
{
    public function testCreateInvalidVariable()
    {
        $this->expectException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'The given variable name "(); " is not valid in PHP.');

        new Variable('(); ');
    }

    public function testGetNameOrToken()
    {
        $variable = new Variable('$var');

        $this->assertEquals('var', $variable->getName());
        $this->assertEquals('$var', $variable->getToken());
    }

    public function testCreateInstanceVariable()
    {
        $variable = new Variable('$this->var');

        $this->assertEquals('this->var', $variable->getName());
        $this->assertEquals('$this->var', $variable->getToken());

        $this->assertTrue($variable->isInstance());
        $this->assertFalse($variable->isLocal());
    }

    public function testCreateLocalVariable()
    {
        $variable = new Variable('$var');

        $this->assertFalse($variable->isInstance());
        $this->assertTrue($variable->isLocal());
    }

    public function testCreateInstanceFromLocal()
    {
        $local = new Variable('$var');
        $instance = $local->convertToInstance();

        $this->assertTrue($instance->isInstance());
        $this->assertFalse($local->isInstance());
    }
}
