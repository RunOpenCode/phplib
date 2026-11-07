=========
withAll()
=========

Iterates over the given stream source and yields its items. During iteration
process, every yielded item is stored into a buffer.

When iteration completes the finalization function is invoked providing the
collected items as argument.

If iteration of the stream source ends prematurely (for example, via a `break`
statement), the finalization function is invoked when the operator instance
is garbage-collected with the items that were collected up to that point.

.. warning:: The memory consumption of this operator depends on the number of 
             items in the stream and it is considered as **memory unsafe**.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: WithAll

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(Buffer<TKey, TValue>): void $finalizer)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $finalizer: ``callable(Buffer<TKey, TValue>): void`` User defined callable to invoke when iterator is depleted, or operator instance is garbage collected.  


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Items from the stream source.

Use cases
---------

* Use this operator when you want to express finalization logic when stream processing 
  is completed, or when stream processing is interrupted prematurely (for example, 
  via a `break` statement) on iterated items.

Example
-------

Mark iterated entities as processed when stream processing is completed.

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Model\Buffer;

   new Stream(...)
       ->withAll(function(Buffer $buffer) use ($database): void {
           foreach ($buffer as $item) {
               $database->markAsProcessed($item);
           }
       });