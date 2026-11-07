=====
zip()
=====

Iterates over two streaming sources in parallel, yielding pairs of keys and
values from both sources.

There are three modes of operation:

* **strict** - Iteration continues until the first source is depleted. If the second
  source is depleted before the first, an exception is thrown.
* **longest** - Iteration continues until both sources are depleted. If one source is
  depleted before the other, `null` is yielded for the missing values.
* **shortest** - Iteration continues until the first source is depleted. If one source
  is depleted before the other, iteration stops.

.. attention:: For each mode, there is also a corresponding operator: **`zipStrict()`**, **`zipLongest()`** 
               and **`zipShortest()`** which can be used instead of `zip()` operator with mode parameter.
               Those operators better describe return type for codebases using PHPStan or Psalm static analysis 
               tools.

.. php:namespace:: RunOpenCode\Component\Dataset\Operator


.. php:class:: Zip

   .. php:method:: __construct(iterable<TKey1, TValue1> $first, iterable<TKey2, TValue2> $second, string $mode = 'strict')

      :param $first: ``iterable<TKey1, TValue1>`` First stream source to iterate over.
      :param $second: ``iterable<TKey2, TValue2>`` Second stream source to iterate over.
      :param $mode: ``string`` Mode of operation ('strict', 'longest', 'shortest').

   .. php:method:: getIterator()

      :returns: ``\Traversable<array{TKey1|null, TKey2|null}, array{TValue1|null, TValue2|null}>`` Items from the stream sources yielded in pairs of keys and values.

Use cases
---------

* Combining two streams into one stream of pairs of keys and values.

Example
-------

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Dataset\Stream;

   new Stream(['Bob', 'Alice', 'John'])
         ->zip(['Smith', 'Johnson', 'Doe']); // yields ['Bob', 'Smith'], ['Alice', 'Johnson'], ['John', 'Doe']

