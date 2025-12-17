========
Concepts
========

The library defines several building blocks for processing data streams. It is
recommended that you familiarize yourself with the concepts described in this
document before reading about how to use and extend the library.

.. contents::
   :depth: 1
   :local:
   :class: this-will-duplicate-information-and-it-is-still-useful-here
   

Stream source
-------------

.. _iterable: https://www.php.net/manual/en/language.types.iterable.php 

A stream source (or collection) is any iterable that can be iterated over, which
means either an ``array`` or an instance of ``\Traversable``. In short, it is
any iterable_.

Each stream source emits values indexed by a key. The key is usually an ``int`` 
or ``string``, as PHP arrays are commonly used. However, the library assumes any
``iterable``, including, but not limited to:
 
* ``\Generator``, which may emit values with any type of key,
* ``\WeakMap``, which emits objects as a key,
* and so on.

The common characteristic of a stream source is that it is not rewindable.
Generators, for example, cannot be rewound; you cannot iterate over them twice.
For this reason, even when using arrays (or any other rewindable stream source),
the library assumes that the stream source is not rewindable.

Data stream, or stream wrapper
------------------------------

A data stream (or stream wrapper) is the 
``RunOpenCode\Component\Dataset\Stream`` class, which wraps a stream source and
provides stream processing using operators, reducers, collectors, and 
aggregators (which will be discussed later in the document).

Using an object-oriented approach, you can apply various operations to your data
source through the fluent API provided by the data stream instance.

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;

   Stream::create(/* ... */)
       ->map(/* ... */)
       ->tap(/* ... */)
       ->takeUntil(/* ... */)
       ->finally(/* ... */);

.. _pipe operator: https://wiki.php.net/rfc/pipe-operator-v3

With PHP 8.5 in mind, the library also provides functions to support a
functional approach using the `pipe operator`_.

.. code-block:: php
   :linenos:

   <?php 

   use function RunOpenCode\Component\Dataset\stream;
   use function RunOpenCode\Component\Dataset\map;
   use function RunOpenCode\Component\Dataset\tap;
   use function RunOpenCode\Component\Dataset\takeUntil;
   use function RunOpenCode\Component\Dataset\finally;

   stream(/* ... */)
      |> map(/* ... */)
      |> tap(/* ... */)
      |> takeUntil(/* ... */)
      |> finally(/* ... */);

A data stream is, of course, iterable, and none of the operators are applied
until the stream is iterated.

Operators
---------

Operators are used to perform specific operations on a data stream. They process
each yielded value one by one and yield the result of their operation.

The library provides a set of commonly used operators, such as ``map()``,
``filter()``, ``take()``, and others. However, you can extend the available set
of operators by implementing your own.

The general idea behind operators is to execute various operations that read
from and/or modify the original stream as it is being iterated.

Reducers
--------

Reducers iterate over a data stream and reduce all elements into a single value
of any kind. Common examples of reducers include ``sum()``, ``average()``, 
``min()``, ``max()``, all of which are provided by this library.

However, reducers are designed to be iterable as well and can be applied as
aggregators (a concept introduced by this library, explained later in this 
document). This allows you to apply a reducer to a stream while still being able
to iterate over it and obtain the reduced value at the same time.

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Reducer\Sum;

   echo Stream::create([1, 3, 2, 5])
       ->reduce(Sum::class); // prints 11

Collectors
----------

When operators (and aggregators) are applied to a stream, you can access the
stream data simply by iterating over it.

Sometimes, however, you may want to collect all the data into a specific data
structure for further processing using other methods.

The library supports this concept and provides common collectors, such as
``RunOpenCode\Component\Dataset\Collector\ArrayCollector``, which collects all
items into an array, or 
``RunOpenCode\Component\Dataset\Collector\ListCollector``, which collects items
into a numerically ordered array, and more.

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Collector\ArrayCollector;

   $generator = function(): iterable {
       yield 1;
       yield 3;
       yield 2;
       yield 5;
   };

   $collected = Stream::create($generator())
       ->collect(ArrayCollector::class);

   var_dump($collected->value); // prints [0 => 1, 1 => 3, 2 => 2, 3 => 5]

Aggregators
-----------

Aggregators are a concept introduced by this library. The general idea is that 
you can iterate over a stream with applied operators while simultaneously 
calculating a reduced value in a single pass.

This is useful, for example, when rendering a table of financial data and you
want to display totals, averages, or similar summary values at the bottom of the
table.

Aggregators are essentially "attached" reducers to a stream and can be accessed
once the stream has been fully iterated.

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Reducer\Sum;

   $stream = Stream::create([1, 3, 2, 5])
       ->aggregate('sum', Sum::class);

   foreach($stream as $item) {
       echo $item;
       echo "\n";
   }

   echo $stream->aggregated['sum'];

With an understanding of the concepts used in this library, you can now proceed
with the rest of the documentation.
