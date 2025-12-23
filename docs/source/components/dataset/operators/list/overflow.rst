==========
overflow()
==========

Monitors the number of items yielded by the stream and raises an exception when
the allowed limit is exceeded.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Overflow

   .. php:method:: __construct(iterable<TKey, TValue> $source, positive-int $capacity, \Throwable|(callable(StreamOverflowException=): \Throwable)|null $throw = null)

      :param $source: ``iterable<TKey, TValue>`` Stream source to iterate over.
      :param $capacity: ``positive-int`` Maximum number of items to iterate over.
      :param $throw: ``\Throwable|(callable(StreamOverflowException=): \Throwable)|null`` Exception to throw if stream yielded more items then capacity allows. 
                                                                                          If ``null`` is provided, ``StreamOverflowException`` is thrown. Otherwise,
                                                                                          provided exception will be thrown, or callable invoked to create exception to throw.


   .. php:method:: getIterator()

      :returns: ``\Traversable<TKey, TValue>`` Items from the stream source.

Use cases
---------

* When number of yielded items is expected not to exceed given capacity. 

Example
-------

Execute query which should yield number of items not more than expected count 
(per example, business logic allows for user to have up to 10 bank accounts).

.. code-block:: php
   :linenos:

   <?php

   $accounts = $database->execute('SELECT * FROM accounts WHERE accounts.user_id = :user');

   new Stream($accounts)
       ->overflow(10)
       ->collect(ArrayCollector::class);
