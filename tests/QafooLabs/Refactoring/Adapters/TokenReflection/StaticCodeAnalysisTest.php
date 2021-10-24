<?php

namespace Tests\QafooLabs\Refactoring\Adapters\TokenReflection;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Domain\Model\File;

class StaticCodeAnalysisTest extends TestCase
{
    public function test_namespace_declaration_for_file_without_namespace_is_in_line0()
    {
        $file = new File(
            'without-namespace.php',
            <<<'PHP'
<?php

class WithoutNamespace
{

}
PHP
        );

        $analysis = new StaticCodeAnalysis();
        $classes = $analysis->findClasses($file);
        $class = $classes[0];

        $this->assertEquals(0, $class->namespaceDeclarationLine(), 'namespace declaration line for file without namespace');
    }
}
