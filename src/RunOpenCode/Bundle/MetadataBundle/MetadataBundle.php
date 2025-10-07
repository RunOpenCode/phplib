<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\MetadataBundle;

use RunOpenCode\Bundle\MetadataBundle\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MetadataBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension(): ExtensionInterface
    {
        return new Extension();
    }
}
