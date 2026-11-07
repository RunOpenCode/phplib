=========
indexBy()
=========

IndexBy operator iterates over given stream source and applies
callable function to generate new keys for the resulting stream.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: IndexBy

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(TValue, TKey): TModifiedKey $callable)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $callable: ``callable(TValue, TKey): TModifiedKey`` Callable function to generate new keys for the resulting stream.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TModifiedKey, TValue>`` Provided stream source or fallback stream source.

Use cases
---------

* Provide a simple way to index stream source by given callable function. Per 
  example, when you iterate entities from database and you want to index them by 
  their ID.

Example
-------

Index entities by their ID.

.. code-block:: php
   :linenos:

   <?php

   new Stream($dataset)
       ->indexBy(function(MyEntity $entity): int {
           return $entity->getId();
       });
