<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use RunOpenCode\Component\Intent\Exception\NotExistsException;

abstract class AbstractIntentStorageTestBase extends TestCase
{
    #[Test]
    public function store(): void
    {
        $storage = $this->getIntentStorage();
        $intent  = new Foo();

        $identifier = $storage->store($intent);
        $retrieved  = $storage->fetch($identifier);

        $this->assertInstanceOf(Foo::class, $retrieved);
        $this->assertSame($intent->name, $retrieved->name);
        $this->assertEquals($intent->createdAt, $retrieved->createdAt);
        $this->assertSame($intent->bar->number, $retrieved->bar->number);
    }

    #[Test]
    public function fetch_invalidates_intent(): void
    {
        $this->expectException(NotExistsException::class);

        $storage    = $this->getIntentStorage();
        $intent     = new Foo();
        $identifier = $storage->store($intent);

        $retrieved = $storage->fetch($identifier);
        $this->assertInstanceOf(Foo::class, $retrieved);

        $storage->fetch($identifier);
    }

    #[Test]
    public function fetch_preserves_intent(): void
    {
        $storage    = $this->getIntentStorage();
        $intent     = new Foo();
        $identifier = $storage->store($intent);

        $retrieved = $storage->fetch($identifier, false);
        $this->assertInstanceOf(Foo::class, $retrieved);

        $retrieved = $storage->fetch($identifier);
        $this->assertInstanceOf(Foo::class, $retrieved);
    }

    #[Test]
    public function expired_intent_throws_exception(): void
    {
        $this->expectException(NotExistsException::class);

        $storage    = $this->getIntentStorage();
        $intent     = new Foo();
        $identifier = $storage->store($intent, 1, new \DateTimeImmutable('-1 days'));

        $storage->fetch($identifier);
    }

    #[Test]
    public function future_intent_throws_exception(): void
    {
        $this->expectException(NotExistsException::class);

        $storage    = $this->getIntentStorage();
        $intent     = new Foo();
        $identifier = $storage->store($intent, 86400, new \DateTimeImmutable('+1 days'));

        $storage->fetch($identifier);
    }

    #[Test]
    public function invalidate_intent(): void
    {
        $this->expectException(NotExistsException::class);

        $storage    = $this->getIntentStorage();
        $intent     = new Foo();
        $identifier = $storage->store($intent);

        $retrieved = $storage->fetch($identifier, false);
        $this->assertInstanceOf(Foo::class, $retrieved);

        $storage->invalidate($identifier);

        $storage->fetch($identifier);
    }

    #[Test]
    public function fetch_with_invalid_identifier_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getIntentStorage()->fetch('foo');
    }

    #[Test]
    public function invalidate_with_invalid_identifier_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->getIntentStorage()->invalidate('foo');
    }

    #[Test]
    public function maintenance(): void
    {
        $this->expectException(NotExistsException::class);

        $storage = $this->getIntentStorage();

        $valid   = $storage->store(new Foo());
        $expired = $storage->store(new Foo(), 1, new \DateTimeImmutable('-1 days'));

        $storage->maintenance();

        $this->assertInstanceOf(Foo::class, $storage->fetch($valid));

        $storage->fetch($expired);
    }

    abstract protected function getIntentStorage(): IntentStorageInterface;
}

class Foo
{
    public string $name = 'foo';

    public \DateTimeImmutable $createdAt;

    public Bar $bar;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable('2026-01-01 00:00:00');
        $this->bar       = new Bar();
    }
}

class Bar
{
    public int $number = 42;
}
