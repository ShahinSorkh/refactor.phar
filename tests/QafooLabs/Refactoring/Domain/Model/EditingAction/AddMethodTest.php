<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model\EditingAction;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\EditingAction\AddMethod;
use QafooLabs\Refactoring\Domain\Model\LineCollection;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;

class AddMethodTest extends TestCase
{
    private $action;

    private $buffer;

    protected function setUp(): void
    {
        $this->buffer = $this->createMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');
    }

    public function test_it_is_an_editing_action()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\EditingAction',
            new AddMethod(0, new MethodSignature('test'), new LineCollection())
        );
    }

    public function test_buffer_append_is_performed_at_the_given_line_number()
    {
        $lineNumber = 27;

        $this->buffer
            ->expects($this->once())
            ->method('append')
            ->with($this->equalTo($lineNumber), $this->anything());

        $action = new AddMethod($lineNumber, new MethodSignature('test'), new LineCollection());

        $action->performEdit($this->buffer);
    }

    public function test_appends_method()
    {
        $action = new AddMethod(0, new MethodSignature('testMethod'), new LineCollection());

        $this->assertGeneratedCodeMatches([
            '',
            '    private function testMethod()',
            '    {',
            '    }',
        ], $action);
    }

    public function test_return_statement_for_single_variable()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(['returnVar']),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private function testMethod()',
            '    {',
            '',
            '        return $returnVar;',
            '    }',
        ], $action);
    }

    public function test_return_statement_for_single_variable_has_correct_name()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(['specialVar']),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private function testMethod()',
            '    {',
            '',
            '        return $specialVar;',
            '    }',
        ], $action);
    }

    public function test_return_statement_for_multiple_variables()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(['ret1', 'ret2']),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private function testMethod()',
            '    {',
            '',
            '        return array($ret1, $ret2);',
            '    }',
        ], $action);
    }

    public function test_method_name_is_used()
    {
        $action = new AddMethod(
            0,
            new MethodSignature('realMethodName'),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private function realMethodName()',
            '    {',
            '    }',
        ], $action);
    }

    public function test_static_methods_are_defined_correctly()
    {
        $action = new AddMethod(
            0,
            new MethodSignature(
                'realMethodName',
                MethodSignature::IS_PRIVATE | MethodSignature::IS_STATIC
            ),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private static function realMethodName()',
            '    {',
            '    }',
        ], $action);
    }

    public function test_method_arguments_are_defined_correctly()
    {
        $action = new AddMethod(
            0,
            new MethodSignature(
                'testMethod',
                MethodSignature::IS_PRIVATE,
                ['param1', 'param2']
            ),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private function testMethod($param1, $param2)',
            '    {',
            '    }',
        ], $action);
    }

    public function test_selected_code_is_added()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars([]),
            LineCollection::createFromArray([
                'echo "Hello World!";',
            ])
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private function testMethod()',
            '    {',
            '        echo "Hello World!";',
            '    }',
        ], $action);
    }

    public function test_selected_code_is_added_with_correct_indetations()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars([]),
            LineCollection::createFromArray([
                '    if ($something) {',
                '        echo "Hello World!";',
                '    }',
            ])
        );

        $this->assertGeneratedCodeMatches([
            '',
            '    private function testMethod()',
            '    {',
            '        if ($something) {',
            '            echo "Hello World!";',
            '        }',
            '    }',
        ], $action);
    }

    private function assertGeneratedCodeMatches(array $expected, AddMethod $action)
    {
        $this->makeBufferAppendExpectCode($expected);

        $action->performEdit($this->buffer);
    }

    private function createMethodSignatureWithReturnVars(array $returnVars)
    {
        return new MethodSignature(
            'testMethod',
            MethodSignature::IS_PRIVATE,
            [],
            $returnVars
        );
    }

    private function makeBufferAppendExpectCode(array $codeLines)
    {
        $this->buffer
            ->expects($this->once())
            ->method('append')
            ->with($this->anything(), $this->equalTo($codeLines));
    }
}
