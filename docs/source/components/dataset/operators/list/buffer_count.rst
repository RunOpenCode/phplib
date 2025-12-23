=============
bufferCount()
=============

Buffers the stream of data until buffer reaches predefined number of items (or 
stream is exhausted) and yields instance of 
``RunOpenCode\Component\Dataset\Model\Buffer`` for batch processing.

Memory consumption depends on the size of the buffer; however, the operator is
still considered **memory-safe**.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: BufferCount

   .. php:method:: __construct(iterable<TKey, TValue> $source, int $count = 1000)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $count: ``positive-int`` Number of items to store into buffer.


   .. php:method:: getIterator()

      :returns: ``\Traversable<int, Buffer<TKey, TValue>>`` Stream of buffers.


Use cases
---------

* You want to load large dataset (per example, from file or from database) and 
  process it in batches (batch import).

Example
-------

Load data from legacy database and execute batch import of entities into new
system. Notify rest of the application that new entity has been created.


.. code-block:: php
   :linenos:

   <?php

   use App\Model\Entity;
   use App\Events\EntityCreated;
   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Model\Buffer;

   $orm, $dispatcher;
   $dataset = $database->executeQuery('...');

   new Stream($dataset)
       ->bufferCount(100)
       ->map(function(Buffer $buffer) use ($orm): Buffer {
           $entities = new Stream($buffer)
               ->map(function(array $row): Entity {
                   return Entity::fromLegacy($row);                    
               })
               ->tap(function(Entity $entity) use ($orm): void {
                   $orm->persist($entity);
               })
               ->collect(ArrayCollector::class);

           $orm->flush();
           $orm->clear();

           return $entities;
       })
       ->flatten()
       ->tap(function(Entity $entity) use ($dispatcher): void {
            $dispatcher->notify(new EntityCreated($entity::class, $entity->id));           
       })
       ->flush();
