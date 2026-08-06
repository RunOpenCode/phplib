<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Intent\Command;

use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'runopencode:intent:maintenance',
    description: 'Clear expired intents from storage.'
)]
final readonly class ClearExpiredIntentsCommand
{
    public function __construct(private IntentStorageInterface $storage)
    {
        // noop
    }

    public function __invoke(SymfonyStyle $io): int
    {
        try {
            $this->storage->maintenance();
            $io->success('All expired intents are removed.');
        } catch (\Exception) {
            $io->error('Unable to clear expired contents.');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
