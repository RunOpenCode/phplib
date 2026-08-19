======
keys()
======

Keys operator iterates over given stream of iterables and yields only list of
keys.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Keys

   .. php:method:: __construct(iterable<TKey, TValue> $source)

      :param $source: ``iterable<TKey, TValue>`` Stream of items to iterate over.


   .. php:method:: getIterator()

      :returns: ``\Traversable<int, TKey>`` Stream of keys.

Use cases
---------

* Use this operator to get only keys of a dataset.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream($dataset)
       ->keys();
