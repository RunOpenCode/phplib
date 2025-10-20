===========
Inspiration
===========

In text below, you may find some ideas on how to use ``runopencode/metadata``
library in your projects.

Implementing timestampable behavior using ``runopencode/metadata``
------------------------------------------------------------------

Assume that you want to implement, in example, timestampable behavior for your
Doctrine entities and you do not want to use 3rd party library (such as 
``gedmo/doctrine-extensions``, https://github.com/doctrine-extensions/DoctrineExtensions)
for that, this libray makes that a trivial task.

First thing which you would need is an attribute so you can mark properties
which should be automatically updated with current timestamp on entity creation
and update:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Doctrine\Attribute;

   #[\Attribute(\Attribute::TARGET_PROPERTY)]
   final readonly class Timestampable
   {
        public const string CREATE = 'create';
        public const string UPDATE = 'update';

        public function __construct(public string $on)
        {
            // noop.
        }
   }

Then, you may add these attributes to your entity properties which you would 
like to update automatically when entity is created or updated:

.. code-block:: php
   :linenos:

   <?php

   namespace App\Entity;

   use App\Doctrine\Attribute\Timestampable;

   class MyEntity
   {
         #[Timestampable(Timestampable::CREATE)]
         private \DateTimeImmutable $createdAt;
    
         #[Timestampable(Timestampable::UPDATE)]
         private \DateTimeImmutable $updatedAt;
    
         // ...
   }

The last piece of the puzzle is to implement the logic which will read these
attributes and update the properties accordingly. This can be done using
``RunOpenCode\Metadata\MetadataReader`` class (that is, 
``RunOpenCode\Component\Metadata\Contract\ReaderInterface``, assuming you are
following dependency inversion principle):

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Doctrine\Listener;

   use App\Doctrine\Attribute\Timestampable;
   use Doctrine\ORM\Event\LifecycleEventArgs;
   use RunOpenCode\Component\Metadata\Contract\ReaderInterface;

   final readonly class TimestampableListener
   {
        public function __construct(private ReaderInterface $reader)
        {
            // noop.
        }

        public function prePersist(LifecycleEventArgs $args): void
        {
            $entity = $args->getObject();
            $when   = new \DateTimeImmutable('now');

            $this->touch($entity, $when, Timestampable::CREATE, Timestampable::UPDATE);
        }

        public function preUpdate(LifecycleEventArgs $args): void
        {
            $entity = $args->getObject();
            $when   = new \DateTimeImmutable('now');

            $this->touch($entity, $when, Timestampable::UPDATE);
        }

        private function touch(object $entity, \DateTimeImmutable $when, string ...$on): void
        {
            $properties = $this->reader->properties($entity, Timestampable::class);

            if (0 === \count($properties)) {
                return;
            }
            
            foreach ($on as $condition) {
                foreach ($properties as $property) {
                    $attribute = $property->get(Timestampable::class);
                    
                    if ($condition !== $attribute->on) {
                        continue;
                    }

                    $property->write($entity, $when);
                }
            }   
        }
   }
