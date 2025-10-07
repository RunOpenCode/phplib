<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;

return RectorConfig
    ::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        strictBooleans: true,
        phpunitCodeQuality: true,
    )
    ->withSkip([
        RemoveUnusedPrivateMethodRector::class => [
            'src/RunOpenCode/Component/Metadata/Tests/Model/ClassMetadataTest.php',
            'src/RunOpenCode/Component/Metadata/Tests/Model/MethodMetadataTest.php'
        ]
    ]);
