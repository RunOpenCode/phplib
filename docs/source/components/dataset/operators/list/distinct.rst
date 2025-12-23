==========
distinct()
==========

The distinct operator emits only items that are unique, preserving FIFO order.
By default, items are compared using the strict equality operator (===).

Optionally, you may provide a callable that computes an identity for each item
based on its value and key. When provided, distinctness is determined by 
performing strict equality comparisons on the computed identities instead of the
original items.

.. warning:: The memory consumption of this operator depends on the number of 
             distinct items emitted by the upstream. As a result, it is
             considered memory-unsafe, since memory usage can grow without bound
             for unbounded streams.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Distinct

   .. php:method:: __construct(iterable<TKey, TValue> $source, ?callable(TValue, TKey=): string $identity = null)

      :param $source: ``iterable<TKey, TValue>`` Stream to iterate over.
      :param $predicate: ``?callable(TValue, TKey=): string`` User defined callable to determine item identity. If null, strict comparison (===) of values is used.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Distinct items from the stream source (distinct by value or by identity).

Use cases
---------

* Use this operator to eliminate duplicate items from a stream.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream((static function(): iterable {
           yield 'a' => 1;
           yield 'a' => 2;
           yield 'a' => 1;
           yield 'b' => 1;
           yield 'b' => 1;
       })())
       ->distinct(static fn(int $value, string $key): string => \sprintf('%s_%d', $key, $value));

   // preserves: 'a' => 1, 'a' => 2, 'b' => 1 