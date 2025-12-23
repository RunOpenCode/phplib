========
filter()
========

Filter operator iterates over given stream source and yields only those items 
for which user defined callable returns ``true``.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Filter

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(TValue, TKey=): bool $filter)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $filter: ``callable(TValue, TKey=): bool`` User defined callable to filter items.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Filtered items from the stream source.

Use cases
---------

* Use this operator to eliminate items according to some filtering criteria.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream([1, 2, 3, 4, 5, 6])
       ->filter(static fn(int $value): bool => 0 === $value % 2);

   // preserves: 2, 4, 6