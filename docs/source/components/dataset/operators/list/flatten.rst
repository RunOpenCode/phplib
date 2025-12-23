=========
flatten()
=========

Flatten operator iterates over given stream of iterables and yields each item 
from each iterable in a single flat sequence.

By default, keys from inner iterables are not preserved, which can be overridden
in constructor.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Flatten

   .. php:method:: __construct(iterable<mixed, iterable<TKey, TValue>> $source, bool $preserveKeys = false)

      :param $source: ``iterable<mixed, iterable<TKey, TValue>>`` Stream of streams to iterate over.
      :param $preserveKeys: ``bool`` Should keys be preserved from the flattened stream, false by default.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` or ``\Traversable<int, TValue>`` Flattened stream.

Use cases
---------

* Use this operator to flatten nested structures, per example, to flatten buffer.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream($dataset)
       ->bufferCount(100)
       ->tap(function(Buffer $buffer): void {
           // Do a batch processing...
       })
       ->flatten(); // Continue with original stream.
