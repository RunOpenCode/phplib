==========
finalize()
==========

Iterates over the given stream source and yields its items. When iteration
completes or an exception occurs, the finalization function is invoked.

This is equivalent to executing the iteration inside a try/finally block.

If iteration of the stream source ends prematurely (for example, via a ``break``
statement), the finalization function is invoked when the operator instance
is garbage-collected.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Finalize

   .. php:method:: __construct(iterable<TKey, TValue> $source, callable(): void $finalizer)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $finalizer: ``callable(): void`` User defined callable to invoke when iterator is depleted, or exception is thrown,
                                           or operator instance is garbage collected.  


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Items from the stream source.

Use cases
---------

* Use this operator when you want to express finalization logic in a declarative 
  manner.

Example
-------

Finalization logic for stream processing may be provided by wrapping stream
processing with try/finally block:

.. code-block:: php
   :linenos:

   <?php

   try {
       new Stream(...)
           ->filter(...)
           ->map(...)
           ->tap(...);
   } finally {
       // Finalization logic...
   }

However, with this operator, same result can be achieved in declarative manner:

.. code-block:: php
   :linenos:

   <?php

   new Stream(...)
       ->filter(...)
       ->map(...)
       ->tap(...)
       ->finalize(function(): void {
           // Finalization logic
       });