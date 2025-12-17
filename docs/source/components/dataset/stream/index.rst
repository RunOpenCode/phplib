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
variety of operators to transform or filter the data. Operators process each 
item in the stream lazily, meaning that no computation is performed until the 
stream is iterated.

Examples of some of the available operators include:

* ``map()`` – transform each value in the stream.
* ``filter()`` – include only values that meet a given condition.
* ``take()``, ``skip()``, ``distinct()`` – control which items are emitted.
* ``sort()``, ``reverse()`` – ordering operators (note: these load the entire stream into memory).
* etc.

Additionally, ``RunOpenCode\Component\Dataset\Stream`` supports aggregators, 
which are attached reducers that compute a reduced value while the stream is 
being iterated. This allows you to process a stream and calculate totals, 
averages, or other summary values simultaneously without breaking the data flow.

   **Applying operators and aggregators on stream DOES NOT break the stream and 
   its fluent API.**

With the fluent API, you are able to write stream processing code in declarative 
manner:

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;

   Stream::create(/* ... */)
       ->map(/* ... */)
       ->tap(/* ... */)
       ->takeUntil(/* ... */)
       ->finally(/* ... */);

Internals
---------

Class ``RunOpenCode\Component\Dataset\Stream`` implements 
``RunOpenCode\Component\Dataset\Contract\StreamInterface``. While 