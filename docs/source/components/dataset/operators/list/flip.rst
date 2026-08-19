======
flip()
======

Flip operator iterates over given stream of iterables and yields each item with
keys and values flipped. This operator is useful when you want to invert the 
keys and values of a dataset.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Flip

   .. php:method:: __construct(iterable<TKey, TValue> $source)

      :param $source: ``iterable<TKey, TValue>`` Stream of items to iterate over.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TValue, TKey>`` Flipped stream.

Use cases
---------

* Use this operator to flip keys and values of a dataset.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream($dataset)
       ->keys();
