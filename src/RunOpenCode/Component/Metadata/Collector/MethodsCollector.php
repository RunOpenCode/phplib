<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Collector;

/**
 * {@internal}
 */
final readonly class MethodsCollector
{
    public bool $empty;

    /**
     * @param \ReflectionClass<object> $class
     * @param \ReflectionMethod[]      $methods
     */
    private function __construct(
        public \ReflectionClass $class,
        public array            $methods,
    ) {
        $this->empty = $this->methods === [];
    }

    /**
     * Create new instance of methods collector for the given class.
     *
     * @param \ReflectionClass<object> $class Class reflection instance.
     */
    public static function create(\ReflectionClass $class): self
    {
        $methods       = $class->getMethods();
        $parentMethods = false !== $class->getParentClass() ? MembersCollector::instance()->methods($class->getParentClass()) : [];
        $selected      = [];

        foreach ($methods as $method) {
            // We are only interested in methods that are declared in the current class.
            if ($method->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            $selected[] = $method;

            if ($method->isPrivate()) {
                continue;
            }

            $parentMethods = \array_filter($parentMethods, static function(\ReflectionMethod $current) use ($method): bool {
                if ($current->isPrivate()) {
                    return true;
                }
                return $current->getName() !== $method->getName();
            });
        }

        return new self($class, \array_merge(
            $parentMethods,
            $selected
        ));
    }
}
