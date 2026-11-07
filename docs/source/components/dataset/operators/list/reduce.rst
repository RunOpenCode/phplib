========
reduce()
========

Wraps reducer providing data streaming as well as value reduction during the stream.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Reduce

   .. php:method:: __construct(iterable<TKey, TValue, ReducerInterface<TKey, TValue, TReducedValue>> $source

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TReducedValue>`` Stream with original keys and values.

Use cases
---------

* Reduce keys/values while streaming through iterable.

Example
-------

Calculate sum of values.

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Dataset\Stream;

   new Stream([1, 2, 3, 4, 5])
       ->reduce($total = new Count())
       ->flush();

   echo $total->value; // 15
