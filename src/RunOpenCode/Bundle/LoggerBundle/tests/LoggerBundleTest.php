<?php

declare(strict_types=1);

namespace RunOpenCode\Bundle\LoggerBundle\Tests;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LogLevel;
use RunOpenCode\Bundle\LoggerBundle\LoggerBundle;
use RunOpenCode\Component\Logger\Contract\LoggerInterface;
use RunOpenCode\Component\Logger\Logger;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class LoggerBundleTest extends AbstractExtensionTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up minimal required parameters that Symfony kernel normally provides
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.build_dir', 'tmp');
    }

    #[Test]
    public function load_extension_with_default_configuration(): void
    {
        $this->load();

        $this->assertContainerBuilderHasService(Logger::class);
        $this->assertContainerBuilderHasAlias(LoggerInterface::class, Logger::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Logger::class, '$decorated');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Logger::class, '$contextProviders');
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Logger::class, '$debug', false);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Logger::class, '$defaultLevel', LogLevel::CRITICAL);
    }

    #[Test]
    public function load_extension_with_custom_configuration(): void
    {
        $this->load([
            'debug'             => true,
            'default_log_level' => 'warning',
        ]);

        $this->assertContainerBuilderHasService(Logger::class);
        $this->assertContainerBuilderHasAlias(LoggerInterface::class, Logger::class);

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Logger::class, '$debug', true);
        $this->assertContainerBuilderHasServiceDefinitionWithArgument(Logger::class, '$defaultLevel', 'warning');
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions(): array
    {
        $bundle    = new LoggerBundle();
        $extension = $bundle->getContainerExtension();

        if (!$extension instanceof ExtensionInterface) {
            throw new \RuntimeException('Failed to get container extension from LoggerBundle.');
        }

        return [$extension];
    }
}
