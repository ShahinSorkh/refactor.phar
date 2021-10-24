<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;

class MethodSignatureTest extends TestCase
{
    /**
     * @test
     */
    public function whenCreateMethodSignatureWithDefaults_ThenIsPrivateAndNotStatic()
    {
        $method = new MethodSignature("foo");

        $this->assertTrue($method->isPrivate());
        $this->assertFalse($method->isStatic());
    }

    /**
     * @test
     */
    public function whenCreateMethodSignatureWithInvalidVisibility_ThenThrowException()
    {
        $this->expectException("InvalidArgumentException");

        $method = new MethodSignature("foo", MethodSignature::IS_PRIVATE | MethodSignature::IS_PUBLIC);
    }

    /**
     * @test
     */
    public function whenCreateMethodSignatureWithStaticOnly_ThenAssumePrivateVisibility()
    {
        $method = new MethodSignature("foo", MethodSignature::IS_STATIC);

        $this->assertTrue($method->isPrivate());
        $this->assertTrue($method->isStatic());
    }
}
