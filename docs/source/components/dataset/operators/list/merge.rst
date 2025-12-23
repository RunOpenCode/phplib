=======
merge()
=======

Merges two streaming sources into a single stream, yielding items from both 
sources.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Merge

   .. php:method:: __construct(iterable<TKey1, TValue1> $first, iterable<TKey2, TValue2> $second)

      :param $first: ``iterable<TKey1, TValue1>`` First stream source to iterate over.
      :param $second: ``iterable<TKey2, TValue2>`` Second stream source to iterate over.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey1|TKey2, TValue1|TValue2>`` Stream containing keys and values from both sources.

Use cases
---------

* Combine two stream sources into one.

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
