======
take()
======

Take operator iterates over given stream source and yields only the first N 
items.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Take

   .. php:method:: __construct(iterable<TKey, TValue> $source, positive-int $count)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $count: ``positive-int`` Number of items to take.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` First N items from the stream source.

Use cases
---------

* When only first N items are needed to be iterated.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream(['a' => 1, 'b' => 2, 'c' => 3])
       ->take(2); // yields 'a' => 1, 'b' => 2
