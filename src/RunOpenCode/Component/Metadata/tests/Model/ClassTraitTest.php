<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Tests\Model;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Metadata\Model\ClassMetadata;

final class ClassTraitTest extends TestCase
{
    #[Test]
    public function analyses_traits(): void
    {
        $metadata = ClassMetadata::create(ClassTraitTestStub\Bar::class);
        $property = $metadata->property(ClassTraitTestStub\FooAttribute::class);
        $method   = $metadata->method(ClassTraitTestStub\FooAttribute::class);

        $this->assertSame(
            'RunOpenCode\Component\Metadata\Tests\Model\ClassTraitTestStub\Foo::$foo',
            \sprintf('%s::$%s', $property->class, $property->name),
        );

        $this->assertSame(
            'RunOpenCode\Component\Metadata\Tests\Model\ClassTraitTestStub\Foo::getFoo()',
            \sprintf('%s::%s()', $method->class, $method->name),
        );
    }
}

namespace RunOpenCode\Component\Metadata\Tests\Model\ClassTraitTestStub;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FooAttribute
{
    public function __construct(
        public string $value
    ) {
    }
}

trait FooTrait
{
    #[FooAttribute('foo::property')]
    public string $foo;

    #[FooAttribute('foo::method')]
    public function getFoo(): string
    {
        return $this->foo;
    }
}

trait BarTrait
{
    use FooTrait;
}

class Foo
{
    use BarTrait;
}

class Bar extends Foo
{
}
