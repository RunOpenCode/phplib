==========
pairwise()
==========

Iterates over two streaming sources in parallel, yielding pairs of keys and 
values from both sources.

If size of both sources is not equal, missing keys and values will be filled 
with `null` values.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Pairwise

   .. php:method:: __construct(iterable<TKey1, TValue1> $first, iterable<TKey2, TValue2> $second)

      :param $first: ``iterable<TKey1, TValue1>`` First stream source to iterate over.
      :param $second: ``iterable<TKey2, TValue2>`` Second stream source to iterate over.


   .. php:method:: getIterator()

      :returns: ``\Traversable<array{TKey1|null, TKey2|null}, array{TValue1|null, TValue2|null}>`` Stream containing paired keys and values from both sources.

Use cases
---------

* Combine two stream sources into one and iterate over them in parallel.

Example
-------

Combine client records from a sharded database to produce a single consolidated
report for all clients.

.. code-block:: php
   :linenos:

   <?php

   $usClients = $usDbConnection->execute('SELECT ...');
   $euClients = $euDbConnection->execute('SELECT ...');

   new Stream($usClients)
       ->merge($euClients);
