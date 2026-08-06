<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Tests\Command;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RunOpenCode\Component\Intent\Command\ClearExpiredIntentsCommand;
use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ClearExpiredIntentsCommandTest extends TestCase
{
    #[Test]
    public function success(): void
    {
        $storage = $this->createMock(IntentStorageInterface::class);

        $storage->expects(self::once())->method('maintenance');

        $tester = new CommandTester($this->getCommand($storage));

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('[OK] All expired intents are removed.', $tester->getDisplay());
    }

    #[Test]
    public function failure(): void
    {
        $storage = $this->createMock(IntentStorageInterface::class);

        $storage->expects(self::once())->method('maintenance')->willThrowException(new \Exception('foo'));

        $tester = new CommandTester($this->getCommand($storage));

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('[ERROR] Unable to clear expired contents.', $tester->getDisplay());
    }

    private function getCommand(IntentStorageInterface $storage): Command
    {
        return new Command('runopencode:intent:maintenance')
            ->setCode(new ClearExpiredIntentsCommand($storage));
    }
}
