======
sort()
======

The sort operator processes a stream source and yields items ordered by keys or
values. Ordering may be defined by a user-supplied comparator or by the default 
comparator, which relies on the spaceship operator (<=>) for key or value 
comparison.

.. warning:: The memory consumption of this operator depends on the number of 
             items in the stream and it is considered as **memory unsafe**.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Sort

   .. php:method:: __construct(iterable<TKey, TValue> $source, ?callable(TKey|TValue, TKey|TValue): int $comparator = null, bool $byKeys = false)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $comparator: ``?callable(TKey|TValue, TKey|TValue): int`` User defined callable to compare two items. If ``null``, spaceship operator (``<=>``) is used.
      :param $byKeys: ``bool`` If ``$byKeys`` is ``true``, keys will be compared instead of values.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Sorted items from the stream source.

Use cases
---------

* When items from stream needs to be sorted, either by keys or by values.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   // Sort by values.
   new Stream(['a' => 3, 'b' => 1, 'c' => 2])
       ->sort(
           comparator: static fn(int $first, int $second): int => $first <=> $second,
           byKeys: false,
       );

   // Sort by keys.
   new Stream(['a' => 3, 'b' => 1, 'c' => 2])
       ->sort(
           comparator: static fn(string $first, string $second): int => \strcmp($first, $second),
           byKeys: true,
       );