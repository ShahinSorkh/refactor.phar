<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model\EditingAction;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\EditingAction\ReplaceWithMethodCall;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;

class ReplaceWithMethodCallTest extends TestCase
{
    private $buffer;

    protected function setUp(): void
    {
        $this->buffer = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');
    }

    public function test_it_is_an_editing_action()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\EditingAction',
            new ReplaceWithMethodCall(
                LineRange::fromLines(1, 2),
                new MethodSignature('testMethod')
            )
        );
    }

    public function test_buffer_replaces_at_given_range()
    {
        $range = LineRange::fromLines(1, 2);

        $action = new ReplaceWithMethodCall(
            $range,
            new MethodSignature('testMethod')
        );

        $this->buffer
            ->expects($this->once())
            ->method('replace')
            ->with($this->equalTo($range), $this->anything());

        $this->setCodeBeingReplaced();

        $action->performEdit($this->buffer);
    }

    public function test_method_call_is_correct_for_simple_method()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod')
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('$this->testMethod();', $action);
    }

    public function test_method_call_uses_given_method_name()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('realMethod')
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('$this->realMethod();', $action);
    }

    public function test_static_method_call()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', MethodSignature::IS_STATIC)
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('self::testMethod();', $action);
    }

    public function test_method_call_with_single_return_variable()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, [], ['result'])
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('$result = $this->testMethod();', $action);
    }

    public function test_method_call_with_multiple_return_variables()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, [], ['result1', 'result2'])
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches(
            'list($result1, $result2) = $this->testMethod();',
            $action
        );
    }

    public function test_method_call_with_arguments()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, ['arg1', 'arg2'])
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches(
            '$this->testMethod($arg1, $arg2);',
            $action
        );
    }

    public function test_extracted_range_is_read_from_the_buffer()
    {
        $range = LineRange::fromLines(1, 2);

        $this->buffer
            ->expects($this->once())
            ->method('getLines')
            ->with($this->equalTo($range))
            ->will($this->returnValue([]));

        $action = new ReplaceWithMethodCall(
            $range,
            new MethodSignature('testMethod')
        );

        $action->performEdit($this->buffer);
    }

    public function test_extract_range_indents_method_call_for_first_line_with_extra_indent()
    {
        $lines = [
            '            echo "Something";',
        ];

        $this->buffer
            ->expects($this->once())
            ->method('getLines')
            ->will($this->returnValue($lines));

        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod')
        );

        $this->assertGeneratedMethodCallMatches(
            '$this->testMethod();',
            $action,
            12
        );
    }

    private function setCodeBeingReplaced(
        array $lines = ['        echo "Replace me";']
    ) {
        $this->buffer
            ->expects($this->any())
            ->method('getLines')
            ->will($this->returnValue($lines));
    }

    private function assertGeneratedMethodCallMatches($expected, $action, $indentSize = 8)
    {
        $expected = str_repeat(' ', $indentSize).$expected;

        $this->buffer
            ->expects($this->once())
            ->method('replace')
            ->with($this->anything(), $this->equalTo([$expected]));

        $action->performEdit($this->buffer);
    }
}
