<?php

declare(strict_types=1);

use Monorepo\LibraryIterator;
use Monorepo\ReleaseWorker as ProjectReleaseWorker;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function(MBConfig $config): void {
    $config->packageDirectories(\array_map(
        static fn(string $directory): string => \sprintf('%s/%s', \rtrim(__DIR__, DIRECTORY_SEPARATOR), $directory),
        LibraryIterator::DIRECTORIES,
    ));

    $config->dataToRemove([
        ComposerJsonSection::REQUIRE_DEV  => [
            Option::REMOVE_COMPLETELY,
        ],
        ComposerJsonSection::REPOSITORIES => [
            Option::REMOVE_COMPLETELY,
        ],
    ]);

    // release workers - in order to execute
    $config->workers([
        ProjectReleaseWorker\PrepareSubsplitDirectoryWorker::class,
        ProjectReleaseWorker\BranchValidatorWorker::class,
        ReleaseWorker\UpdateReplaceReleaseWorker::class,
//        ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker::class,
//        ReleaseWorker\AddTagToChangelogReleaseWorker::class,
//        ReleaseWorker\TagVersionReleaseWorker::class,
//        ReleaseWorker\PushTagReleaseWorker::class,
//        ReleaseWorker\SetNextMutualDependenciesReleaseWorker::class,
//        ReleaseWorker\UpdateBranchAliasReleaseWorker::class,
//        ReleaseWorker\PushNextDevReleaseWorker::class,
//        ProjectReleaseWorker\SubsplitReleaseWorker::class,
    ]);
};
