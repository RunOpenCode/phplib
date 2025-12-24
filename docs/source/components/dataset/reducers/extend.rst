======================
Write your own reducer
======================


Typically, a reducer function has the following signature:

.. code-block:: php
   :linenos:

   <?php

   function reducer(mixed $accumulator, mixed $item): mixed {
       // Update the accumulator based on the item and return it.
   }

.. _array_sum: https://www.php.net/manual/en/function.array-sum.php
.. _array_product: https://www.php.net/manual/en/function.array-product.php
.. _count: https://www.php.net/manual/en/function.count.php

PHP out of the box provides several built-in functions that can be used as 
reducers, such as `array_sum`_, `array_product`_, and `count`_. These functions
can be used to perform common reduction operations on arrays.

.. _array_reduce: https://www.php.net/manual/en/function.array-reduce.php

There is also a general-purpose array reducer function called `array_reduce`_ 
that allows you to define custom reduction logic.

These library deviates from the traditional implementation of reducers in order
to accommodate possibility of reducing streams to a single value without the
breaking the stream from emitting items.

For that purpose, reducers are stateful objects that maintain an internal state
throughout the reduction process. They expose methods to process each item and
retrieve the reduced value.

Reducers in this library implement the 
``RunOpenCode\Component\Dataset\Contract\ReducerInterface`` interface, which 
defines the following members:

.. code-block:: php
   :linenos:

   <?php
   
   declare(strict_types=1);
   
   namespace RunOpenCode\Component\Dataset\Contract;
   
   /**
    * Interface for dataset reducers.
    *
    * Each reducer is a simple, stateful class instance which does
    * data reduction. For each iteration, aggregates the value and
    * stores it into value property.
    *
    * @template TKey
    * @template TValue
    * @template TReducedValue
    */
   interface ReducerInterface
   {
       /**
        * Reduced value.
        *
        * @var TReducedValue
        */
       public mixed $value {
           get;
       }
   
       /**
        * Provide key and value from next iteration for reduction.
        *
        * @param TValue        $value Value from current iteration.
        * @param TKey          $key   Key from current iteration.
        */
       public function next(mixed $value, mixed $key): void;
   }
   
.. note:: Remember, **reducers are stateful**. Each instance maintains its own 
          state during the reduction process and reduced value can be retrieved 
          before, during, or after the stream iteration.

