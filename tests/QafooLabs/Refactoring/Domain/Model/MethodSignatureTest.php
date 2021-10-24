<?php

namespace Tests\QafooLabs\Refactoring\Domain\Model;

use PHPUnit\Framework\TestCase;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;

class MethodSignatureTest extends TestCase
{
    /**
     * @test
     */
    public function when_create_method_signature_with_defaults__then_is_private_and_not_static()
    {
        $method = new MethodSignature('foo');

        $this->assertTrue($method->isPrivate());
        $this->assertFalse($method->isStatic());
    }

    /**
     * @test
     */
    public function when_create_method_signature_with_invalid_visibility__then_throw_exception()
    {
        $this->expectException('InvalidArgumentException');

        $method = new MethodSignature('foo', MethodSignature::IS_PRIVATE | MethodSignature::IS_PUBLIC);
    }

    /**
     * @test
     */
    public function when_create_method_signature_with_static_only__then_assume_private_visibility()
    {
        $method = new MethodSignature('foo', MethodSignature::IS_STATIC);

        $this->assertTrue($method->isPrivate());
        $this->assertTrue($method->isStatic());
    }
}
