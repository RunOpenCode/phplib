<?php

declare(strict_types=1);

namespace RunOpenCode\Component\Metadata\Collector;

/**
 * {@internal}
 */
final readonly class PropertiesCollector
{
    public bool $empty;

    /**
     * @param \ReflectionClass<object> $class
     * @param \ReflectionProperty[]    $properties
     */
    private function __construct(
        public \ReflectionClass $class,
        public array            $properties,
    ) {
        $this->empty = $this->properties === [];
    }

    /**
     * Create new instance of properties collector for the given class.
     *
     * @param \ReflectionClass<object> $class Class reflection instance.
     */
    public static function create(\ReflectionClass $class): self
    {
        $properties       = $class->getProperties();
        $parentProperties = false !== $class->getParentClass() ? MembersCollector::instance()->properties($class->getParentClass()) : [];
        $selected         = [];

        foreach ($properties as $property) {
            // We are only interested in methods that are declared in the current class.
            if ($property->getDeclaringClass()->getName() !== $class->getName()) {
                continue;
            }

            $selected[] = $property;

            // If property is private we do not care about parent properties with the same name.
            if ($property->isPrivate()) {
                continue;
            }

            $parentProperties = \array_filter($parentProperties, static function(\ReflectionProperty $current) use ($property): bool {
                // We always keep private properties from parent.
                if ($current->isPrivate()) {
                    return true;
                }
                // If names are different we keep the parent property.
                return $current->getName() !== $property->getName();
            });
        }

        return new self($class, \array_merge(
            $parentProperties,
            $selected
        ));
    }
}
