=========
Operators
=========

Operator, also known as intermediate operations, are functions which allows you
to transform, combine and/or control streams of data. Each time when you apply
operator on stream, you get new instance of 
``RunOpenCode\Component\Dataset\Stream``. That means that you can chain 
operators describing in declarative way process of data processing from the 
start to the end on each individual streamed record.

.. toctree::
   :maxdepth: 1
   :hidden:

   list/index
   extend
   

As already mentioned, applying operators to a stream is essentially a
composition of functions, presented in a more developer-friendly way:

.. code-block:: php
   :linenos:

   <?php 

   use RunOpenCode\Component\Dataset\Stream;

   new Stream(/* ... */)
       ->map(/* ... */)
       ->tap(/* ... */)
       ->takeUntil(/* ... */)
       ->finally(/* ... */);

which maps to:

.. code-block:: php
   :linenos:

   <?php 

   finally(takeUntil(tap(map(new Stream(...)))));

Library provides you with set of common operators which you may use *out-of-the*
box, as well as possibility to create and use your own operators.

.. _pipe operator: https://wiki.php.net/rfc/pipe-operator-v3

Each operator may be applied in object-oriented manner (as presented in example 
above). If you are using PHP 8.5 or higher, you can leverage the 
`pipe operator`_ and write stream-processing code in a functional style.

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

