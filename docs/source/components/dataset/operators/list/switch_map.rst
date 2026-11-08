===========
switchMap()
===========

Transforms each item from the source stream into an inner iterable
and then yields all values from each inner iterable in order.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator

.. php:class:: SwitchMap

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(TValue, TKey=): iterable $mapper)

      :param $source: ``iterable<TKey, TValue>`` Source stream to iterate over.
      :param $mapper: ``callable(TValue, TKey=): iterable`` Callable that receives each source item and returns an iterable to be flattened into the output stream.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TInnerKey, TInnerValue>`` Flattened stream of inner iterables.

Use cases
---------

* Iterate over a stream, and for each item, produce an inner iterable that will be flattened into the output stream.

Example
-------

For each user, fetch their posts and yield them in a single flattened stream.

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Dataset\Stream;

   $dataset = $database->execute('SELECT ...');

   new Stream($dataset)
       ->switchMap(static function(array $user) use ($database): iterable {
           return $database->execute('SELECT * FROM posts WHERE user_id = ?', [$user['id']]);
       });
