=====
tap()
=====

Provides a way to observe the stream by executing a callback for each item.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Tap

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(TValue, TKey=): void $callback)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $callback: ``callable(TValue, TKey=): void`` Callable to execute for each item.

   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Items from the stream source.

Use cases
---------

* Monitor yielded items for debugging/logging purposes.
* Execute processing logic for each yielded item.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   new Stream(['a' => 1, 'b' => 2, 'c' => 3])
       ->tap(static fn(int $value, string $key): bool => print("Key: $key, Value: $value\n"));
