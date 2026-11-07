==========
pairwise()
==========

Iterates stream source and yields paired keys and values from previous iteration
and current iteration.

If stream source has less than two elements, the stream will be empty.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Pairwise

   .. php:method:: __construct(iterable<TKey, TValue> $source

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.

   .. php:method:: getIterator()

      :returns: ``\Traversable<array{TKey, TKey}, array{TValue, TValue}>`` Stream containing paired keys and values from previous and current iteration.

Use cases
---------

* Combine previous and current iteration values to calculate delta between two iterations.

Example
-------

Combine sequence of transactions to yield deltas.

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Dataset\Stream;

   $transactions = $connection->execute('SELECT ...');

   new Stream($transactions)
       ->pairwise()
       ->map(function (array $previous, array $current): array {
           return [
                'delta' => $current['amount'] - $previous['amount'],
                'previous' => $previous,
                'current' => $current,
           ];
       }) 
