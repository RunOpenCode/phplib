=============
bufferWhile()
=============

Buffers stream items while the predicate returns ``true``. When the predicate 
evaluates to ``false`` (or the stream ends), a 
``RunOpenCode\Component\Dataset\Model\Buffer`` instance is emitted and buffering 
restarts.

.. warning:: Memory usage depends on the predicate and data distribution, so 
             memory safety depends on the specific use case.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: BufferWhile

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(Buffer<TKey, TValue>, TKey=, TValue=): bool $predicate)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $predicate: ``callable(Buffer<TKey, TValue>, TKey=, TValue=): bool`` Predicate function to evaluate if current item should be placed into existing buffer, 
                                                                                  or existing buffer should be yielded and new one should be created with current item.


   .. php:method:: getIterator()

      :returns: ``\Traversable<int, Buffer<TKey, TValue>>`` Stream of buffers.


Use cases
---------

* You want to load large dataset (per example, from file or from database) and 
  process it in batches (batch import). However, you want to cluster stream data
  based on yielded keys/values from data stream.

Example
-------

Model defines ``Person`` entity and ``Address`` entity which needs to be 
migrated from legacy system. In the first iteration, all legacy data for 
``Person`` is migrated into new system.

In the next iteration, data for ``Address`` is loaded, ordered by ``person_id``
column, and for each related ``Person``, addresses are stored.

On each processed ``Person`` and its addresses, system is notified about
successful migration of ``Person`` addresses.


.. code-block:: php
   :linenos:

   <?php

   use App\Model\Person;
   use App\Model\Address;
   use App\Events\PersonAddressesMigrated;
   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Model\Buffer;

   $orm, $dispatcher;
   $dataset = $database->executeQuery('...');

   new Stream($dataset)
       ->bufferWhile(function(Buffer $buffer, array $values): bool {
           return $buffer->first()->value()['person_id'] === $values['person_id'];
       })
       ->map(function(Buffer $buffer) use ($orm): Buffer {
           $person = $orm->fetchOne(Person::class, $buffer->first()->value()['person_id']);
           $addresses = new Stream($buffer)
               ->map(function(array $row): Address {
                   return Address::fromLegacy($row);                    
               })
               ->tap(function(Address $entity) use ($orm): void {
                   $orm->persist($entity);
               })
               ->collect(ArrayCollector::class);

           $person->setAddresses($addresses);

           $orm->flush();
           $orm->clear();

           return $person;
       })
       ->tap(function(Person $person) use ($dispatcher): void {
            $dispatcher->notify(new PersonAddressesMigrated($person::class, $person->id));           
       })
       ->flush();
