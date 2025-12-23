======
skip()
======

The skip operator processes a stream source by discarding the first N items 
and yielding all subsequent items.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Skip

   .. php:method:: __construct(iterable<TKey, TValue> $source, positive-int $count)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $count: ``positive-int`` Number of items to skip.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Items from the stream source after first ``$count`` items.

Use cases
---------

* When first N items needs to be skipped.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream(['a' => 1, 'b' => 2, 'c' => 3])
       ->skip(2); // yields 'c' => 3
