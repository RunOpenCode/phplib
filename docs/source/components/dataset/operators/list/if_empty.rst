=========
ifEmpty()
=========

IfEmpty operator tracks the number of yielded items. If the stream is empty, it
will invoke the provided callable and yield from it as an alternative source of
data.

If exception is provided instead of alternative source of data, that exception
will be thrown.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: IfEmpty

   .. php:method:: __construct(iterable<TKey, TValue> $source, \Throwable|callable()|null: iterable<TKey, TValue> $fallback = null)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $fallback: ``\Throwable|callable()|null: iterable<TKey, TValue>`` Fallback stream source, or exception to 
                        throw, or ``null`` to use the default empty-stream exception.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Provided stream source or fallback stream source.

Use cases
---------

* Provide an alternative source of data if the original is empty.
* Assert that the stream is not empty and throw an exception if it is.

Example
-------

Try with dataset stored in cache first. If cache is empty, load data from 
database.

.. code-block:: php
   :linenos:

   <?php

   new Stream($dataset)
       ->ifEmpty(function() use ($database): iterable {
           return $database->fetch();
       });

Expect that the query will return at least one result; otherwise, throw an 
exception.

.. code-block:: php
   :linenos:

   <?php

   new Stream($dataset)
       ->ifEmpty(new \RuntimeException('At least one record is expected.'));
