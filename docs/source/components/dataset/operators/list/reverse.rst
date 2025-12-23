=========
reverse()
=========

Reverse operator iterates over given stream source and yields items in reverse
order.

.. warning:: The memory consumption of this operator depends on the number of 
             items in the stream and it is considered as **memory unsafe**.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Reverse

   .. php:method:: __construct(iterable<TKey, TValue> $source)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over in reverse order.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Items from the stream source yielded in reverse order.

Use cases
---------

* When reverse order of items in stream is required.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream(['a' => 1, 'b' => 2, 'c' => 3])
       ->reverse(); // yields 'c' => 3, 'b' => 2, 'a' => 1
