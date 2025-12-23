======
Stream
======

The main purpose of the ``RunOpenCode\Component\Dataset\Stream`` class is to
wrap an ``iterable`` and provide a convenient abstraction for processing streams
of data. By wrapping an ``iterable``, the class allows you to perform multiple 
operations (mapping, filtering, tapping into stream, etc.) in a declarative and 
composable manner without loading the entire collection into memory.

Further on, stream can simultaneously aggregate data, reduced it to single value 
or collect data into some convenient data structure using collectors.

Fluent API
----------

With an instance of ``RunOpenCode\Component\Dataset\Stream``, you can apply a
variety of operators to transform or filter data. Operators process each item
in the stream lazily, which means that no computation is performed until the
stream is iterated.

Examples of some of the available operators include:

* ``map()`` – transform each value in the stream.
* ``filter()`` – include only values that meet a given condition.
* ``take()``, ``skip()``, ``distinct()`` – control which items are emitted.
* ``sort()``, ``reverse()`` – ordering operators (note: these load the entire 
  stream into memory).
* etc.

In addition, ``RunOpenCode\Component\Dataset\Stream`` supports aggregators.
Aggregators are attached reducers that compute a reduced value while the stream
is being iterated. This makes it possible to process a stream and compute totals,
averages, or other summary values at the same time, without interrupting the
data flow.

   **NOTE**: Applying operators and aggregators to a stream **does NOT** break
   the stream or its fluent API. However, the fluent API may end with either a
   reducer or a collector, which will break fluent API.

Using the fluent API, you can write stream-processing code in a declarative
manner:

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;

   new Stream(/* ... */)
       ->map(/* ... */)
       ->tap(/* ... */)
       ->takeUntil(/* ... */)
       ->finally(/* ... */);

.. _pipe operator: https://wiki.php.net/rfc/pipe-operator-v3

If you are using PHP 8.5 or higher, you can leverage the `pipe operator`_ and 
write stream-processing code in a functional style using functions.

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

Internals
---------

Class ``RunOpenCode\Component\Dataset\Stream`` implements
``RunOpenCode\Component\Dataset\Contract\StreamInterface``. However, this
interface **is not intended to be implemented** directly. The library provides
different extension mechanisms, which are discussed in detail in separate
chapters of this documentation.

The interface defines the following:

* An extension of the ``\IteratorAggregate<TKey, TValue>`` interface, which is
  required for the stream to be iterable.
* A property ``bool $closed`` that indicates whether the stream has been
  iterated. It does not indicate whether the stream has been fully iterated, as
  iteration may be terminated early.
* A property ``array<non-empty-string, mixed> $aggregated`` that contains a list
  of all aggregators attached to the stream, along with the values aggregated
  during the current iteration. This means that after each iteration, you can
  retrieve the current aggregated values.

The interface also defines additional properties; however, they are annotated as
``@internal``, which means they should not be used directly in your code. They
are described here only to provide a better understanding of the internal
implementation:

* A property ``list<iterable<mixed, mixed>> $upstreams`` that contains a list of
  streams preceding the current stream.
* A property ``array<non-empty-string, AggregatorInterface>`` that contains a
  list of all attached aggregators from the current stream or any upstream
  stream. You will not interact with this property directly, as the
  ``array<non-empty-string, mixed> $aggregated`` property provides a more
  developer-friendly way to access aggregated values.

Upstreams
~~~~~~~~~

Applying operators to a stream is essentially a composition of functions,
presented in a more developer-friendly way. Consider the following example:

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;

   new Stream(/* ... */)
       ->map(/* ... */)
       ->tap(/* ... */)
       ->takeUntil(/* ... */)
       ->finally(/* ... */);

Under the hood, the library creates a composition of function calls, where each
subsequent call decorates the previous one. Illustratively, this can be written
as:

.. code-block:: php
   :linenos:

   <?php 

   finally(takeUntil(tap(map(new Stream(...)))));

The return value of each applied operator or aggregator is a new stream
instance, which results in a chain of streams. If we take the previous example
and name each newly created stream, we get the following:

.. code-block:: php

   <?php

   new Stream(/* ... */)        // This is stream A
       ->map(/* ... */)         // This is stream B
       ->tap(/* ... */)         // This is stream C
       ->takeUntil(/* ... */)   // This is stream D
       ->finally(/* ... */);    // This is stream E

We can form a mental model of the stream composition as follows:

   A -> B -> C -> D -> E

This can be interpreted as "*data flows from A into B, then into C*", and so on.
However, since we always interact with the last stream instance in the chain,
the model used by this library is based on *upstreams* rather than
*downstreams*. This means that each stream holds a reference to its preceding
stream (or streams), not to its child stream.

This data structure enables support for aggregators (reducers applied to the
stream) that can be attached at any point in the stream composition and later
accessed from the final stream instance or collector.
