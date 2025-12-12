========
Concepts
========

Library defines couple of building blocks for processing streams of data. It is 
advisable to introduce yourself with concepts defined in this document and then
read about how to use and extend the library.

.. contents::
   :depth: 1
   :local:
   :class: this-will-duplicate-information-and-it-is-still-useful-here
   

Stream source
-------------

.. _iterable: https://www.php.net/manual/en/language.types.iterable.php 

Stream source (or collection) is any iterable which can be iterated through, 
which means either an ``array`` or instance of ``\Traversable``. In short, 
any iterable_. 

Each stream source emits some value, indexed by key. Key is usually associated 
with ``int`` or ``string`` as we rely on arrays in PHP a lot. However, library 
assumes any ``iterable``, which includes, but not limits to:
 
* ``\Generator``, which may emit anything as a key, 
* ``\WeakMap``, which emits objects as a key,
* and so on...

Common denominator for stream source is that it is not rewindable. Generators, 
per example, can not be rewind, you can not iterate them twice. For that reason, 
even if you use an arrays (or any rewindable stream source), library assumes
that stream source is not rewindable.

Data stream, or stream wrapper
------------------------------

Data stream (or stream wrapper) is ``RunOpenCode\Component\Dataset\Stream`` 
class which wraps stream source providing stream processing using operators,
reducers and collectors.

Class is deliberately not final and allow extension in order for you to be able
to integrate your own custom operators, reducers and collectors - should you 
need to do so.

Using object oriented approach, with instance of data stream, you may apply
various operations on your source of data utilizing fluent API.

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;

   Stream::create(/* ... */)
       ->map(/* ... */)
       ->batch(/* ... */)
       ->takeUntil(/* ... */)
       ->finally(/* ... */);

.. _pipe operator: https://wiki.php.net/rfc/pipe-operator-v3

Having in mind PHP 8.5, library provides a functions as well to support
functional approach using `pipe operator`_:

.. code-block:: php
   :linenos:

   <?php 

   use function RunOpenCode\Component\Dataset\stream;
   use function RunOpenCode\Component\Dataset\map;
   use function RunOpenCode\Component\Dataset\batch;
   use function RunOpenCode\Component\Dataset\takeUntil;
   use function RunOpenCode\Component\Dataset\finally;

   stream(/* ... */)
      |> map(/* ... */)
      |> batch(/* ... */)
      |> takeUntil(/* ... */)
      |> finally(/* ... */);

Data stream is, of course, iterable and none of the operators are applied until
stream is being iterated.

Operators
---------

You use operators to execute some "operations" against the stream of data. 
Operators operate on yielded value, one by one, and they yield result of their 
operations.

Library delivers a set of commonly used operators, such as ``map()``, 
``filter()``, ``take()``, etc. However, you may expand set of operators by 
writing your own.

General idea is that with operators, you execute various operations reading 
from and/or modifying original stream. 

Reducers
--------

Reducers iterate over the stream of data and reduce all of them into one single
value of any kind. Common examples of reducers are ``sum()``, ``average()``, 
``min()``, ``max()``, etc. which are delivered with this library.

However, reducers are design to be iterable as well, and may be applied as 
aggregators (which is a new concept defined by this library) which enables you
to apply reducer on stream and get both reduced value as well as iterate through
stream.

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Reducer\Sum;

   echo Stream::create([1, 3, 2, 5])
       ->reduce(Sum::class); // prints 11

Collectors
----------

When operators (and aggregators) are applied on stream, you can get to stream 
data just by iterating.

Sometimes you want to collect all of that data into some data structure to 
continue with processing using some other method.

Library, in that matter, supports such concept and provides common collectors 
such as ``RunOpenCode\Component\Dataset\Collector\ArrayCollector`` which 
collects everything into array, or 
``RunOpenCode\Component\Dataset\Collector\ListCollector`` which collects 
everything into numeric ordered array and so on.

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

Aggregators are concept introduced with this library. General idea is that you
can both iterate stream with applied operators and calculate reduced value 
simultaneously.

This is useful when, per example, you are rendering a table of financial data 
and at the bottom of table you want to render total and/or average sum, or 
similar.

Aggregators are "attached" reducers to a stream and can be accessed when stream
is fully iterated.

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

   echo $stream->aggregators('sum');

Knowing the concepts applied within this library, you may proceed with further 
reading of documentation for this library.
 