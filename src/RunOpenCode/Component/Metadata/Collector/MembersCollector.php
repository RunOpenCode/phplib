<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Collector;

/**
 * @internal
 */
final class MembersCollector
{
    /**
     * Singleton instance.
     */
    private static MembersCollector $instance;

    /**
     * @var array<class-string, PropertiesCollector>
     */
    private array $propertyCollectors = [];

    /**
     * @var array<class-string, MethodsCollector>
     */
    private array $methodCollectors = [];

    private function __construct()
    {
        // noop
    }

    /**
     * Get singleton instance.
     */
    public static function instance(): MembersCollector
    {
        return self::$instance ??= new self();
    }

    /**
     * Get all properties for the given class including inherited ones, while respecting visibility.
     *
     * @param \ReflectionClass<object>|class-string $class Class name or reflection instance.
     *
     * @return \ReflectionProperty[] List of properties.
     */
    public function properties(\ReflectionClass|string $class): array
    {
        $className = $class instanceof \ReflectionClass ? $class->getName() : $class;

        if (!isset($this->propertyCollectors[$className])) {
            $reflectionClass                      = $class instanceof \ReflectionClass ? $class : new \ReflectionClass($class);
            $this->propertyCollectors[$className] = PropertiesCollector::create($reflectionClass);
        }

        return $this->propertyCollectors[$className]->properties;
    }

    /**
     * Get all methods for the given class including inherited ones, while respecting visibility.
     *
     * @param \ReflectionClass<object>|class-string $class Class name or reflection instance.
     *
     * @return \ReflectionMethod[] List of methods.
     */
    public function methods(\ReflectionClass|string $class): array
    {
        $className = $class instanceof \ReflectionClass ? $class->getName() : $class;

        if (!isset($this->methodCollectors[$className])) {
            $reflectionClass                    = $class instanceof \ReflectionClass ? $class : new \ReflectionClass($class);
            $this->methodCollectors[$className] = MethodsCollector::create($reflectionClass);
        }

        return $this->methodCollectors[$className]->methods;
    }
}
