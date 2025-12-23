===========
takeUntil()
===========

The take operator processes a stream source and yields items until the predicate 
callable indicates that iteration should stop.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: TakeUntil

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(TValue, TKey=): bool $predicate)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $predicate: ``callable(TValue, TKey=): bool`` Predicate callable to evaluate stop condition.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` First N items from the stream source until predicate callable was satisfied.

Use cases
---------

* When only the first N items need to be yielded until some condition is met.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream(['a' => 1, 'b' => 2, 'c' => 3])
       ->takeUntil(static fn(int $value, string $key): bool => $value > 2); 
       // yields 'a' => 1, 'b' => 2
