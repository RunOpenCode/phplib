========
values()
========

Values operator iterates over given stream of iterables and yields only list of
values.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Values

   .. php:method:: __construct(iterable<TKey, TValue> $source)

      :param $source: ``iterable<TKey, TValue>`` Stream of items to iterate over.


   .. php:method:: getIterator()

      :returns: ``\Traversable<int, TKey>`` Stream of values.

Use cases
---------

* Use this operator to get only values of a dataset.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Dataset\Stream;

   new Stream($dataset)
       ->values(); // yields only values of the dataset, zero-based indexed.
