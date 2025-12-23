==========
leftJoin()
==========

This operator mimics the SQL LEFT JOIN found in relational databases.

It iterates over the source stream and joins each item with values from the
joining stream based on strict key equality.

The operator yields the key from the source stream and a tuple containing the 
source value as the first element and an iterable of joined values from the
joining stream as the second element.


.. warning:: This operator is not memory-efficient. Memory consumption depends 
             on the size of the joining stream.


.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: LeftJoin

   .. php:method:: __construct(iterable<TKey, TValue> $source, iterable<TKey, TJoinValue> $join)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over on the left side of the left join operation.
      :param $join: ``iterable<TKey, TJoinValue>`` Stream source to iterate over on the right side of the left join operation.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Stream source joined with values from joining stream source.

Use cases
---------

* Join operation is required which can not be executed on database level.

Example
-------

Migrate users and their addresses from legacy system in batches.



.. code-block:: php
   :linenos:

   <?php

   use App\Model\User;
   use App\Model\Address;
   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Model\Buffer;

   $users = $database->executeQuery('SELECT * FROM users...');

   new Stream($users)
       ->bufferCount(100)
       ->map(function(Buffer $buffer) use ($database): array {
           $users = $buffer
               ->stream()
               ->map(keyTransform: static fn(array $row): int => $row['id'])
               ->collect(ArrayCollector::class);

           $addresses = new Stream($database->executeQuery('SELECT * FROM addresses ... WHERE user_id IN (:ids)', [
               'ids' => \array_keys($users),
           ]))->map(keyTransform: static fn(array $row): int => $row['user_id']);

           return [ $users, $addresses ];
       })
       ->map(function(array $batch): Stream {
           [$users, $addresses] = $batch;
           
           return new Stream($users)
               ->leftJoin($addresses);
       })
       ->flatten()
       ->map(valueTransform: function(array $joined): User {
           [$user, $addresses] = $joined;
           
           return User::fromArray($user)
                      ->setAddresses(\array_map(static fn(array $address): Address => Address::fromArray($address), $addresses));
       })
       ->bufferCount(100)
       ->tap(function(Buffer $buffer) use ($orm) {
           $buffer
               ->stream()
               ->tap(function(User $user) use ($orm) {
                   $orm->persist($user);
               })
               ->flush();
           
           $orm->flush();
           $orm->clear();
       })
       ->flush();
