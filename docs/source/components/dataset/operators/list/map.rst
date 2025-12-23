=====
map()
=====

Map operator iterates over given stream source and applies transformation
functions on keys/values before yielding.

Operator may be used to transform only keys, or only values, or both.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Map

   .. php:method:: __construct(iterable<TKey, TValue> $source, ?callable(TValue, TKey=): TModifiedValue $valueTransform = null, ?callable(TKey, TValue=): TModifiedKey $keyTransform = null)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $valueTransform: ``?callable(TValue, TKey=): TModifiedValue`` Optional transformation function for transforming values.
      :param $keyTransform: ``?callable(TKey, TValue=): TModifiedKey`` Optional transformation function for transforming keys.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TModifiedKey, TModifiedValue>`` Modified keys and values from the stream source.

Use cases
---------

* Modify values and/or keys.
* Index stream records by value.

Example
-------

Transform rows from database into entity objects, collect them into an array 
indexed by identifier.

.. code-block:: php
   :linenos:

   <?php

   $dataset = $database->execute('SELECT ...');

   new Stream($dataset)
       ->map(
           valueTransform: static function(array $row): Entity {
               return Entity::fromArray($row);
           }, 
           keyTransform: static function(int $key, array $row): string {
               return $row['id'];
           }
       );
