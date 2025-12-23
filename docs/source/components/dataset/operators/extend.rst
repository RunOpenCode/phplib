=======================
Write your own operator
=======================

To create your own operator, you need to define a class that implements
``RunOpenCode\Component\Dataset\Contract\OperatorInterface``. The library also 
provides a base class, ``RunOpenCode\Component\Dataset\AbstractStream``,
which serves as a prototype for custom operators and significantly simplifies
their implementation.

.. _PHPStan: https://phpstan.org

.. note:: This tutorial assumes that you are familiar with `PHPStan`_ and 
          generics.

For the purpose of this tutorial, we will implement a log() operator. The goal 
of this operator is to monitor a stream and write each item (key and value) to a
file.

.. note:: Security, usability, bugs, and edge cases are intentionally ignored 
          in this example. The goal of the tutorial is to demonstrate the 
          process of writing and using a custom operator.

Creating the operator class
---------------------------

First, we need to create our operator class and define its signature.

.. code-block:: php
   :linenos:

   <?php 

   declare(strict_types=1);

   namespace App\Operator;

   use RunOpenCode\Component\Dataset\AbstractStream;
   use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

   /**
    * @template TKey
    * @template TValue
    *
    * @extends AbstractStream<TKey, TValue>
    * @implements OperatorInterface<TKey, TValue>
    */
   final class Log extends AbstractStream implements OperatorInterface
   {
       /**
        * @param iterable<TKey, TValue> $source Stream source to iterate over.
        * @param non-empty-string $file         File where items should be stored.
        */
       public function __construct(
           iterable $source, 
           private readonly string $file,
       ) {
           parent::__construct($source);
       }
   }

The operator class extends ``RunOpenCode\Component\Dataset\AbstractStream`` and
implements ``RunOpenCode\Component\Dataset\Contract\OperatorInterface``.

The class defines two generic template parameters, ``TKey`` and ``TValue``,
which describe the key and value types yielded by the operator. This information
is required by PHPStan for correct type inference.

The constructor accepts two arguments:

* The source stream to iterate over.
* A non-empty string representing the path to the file where stream items
  will be written.

From the declared generic types and constructor signature, it is clear that this
operator does not modify the original stream.

.. note:: If you need to implement an operator that modifies the stream, you can
          refer to existing implementations such as 
          ``RunOpenCode\Component\Dataset\Operator\Map`` or 
          ``RunOpenCode\Component\Dataset\Operator\Merge``.

The constructor must pass the source stream to the parent ``AbstractStream`` 
implementation. This is required so that the library can correctly track 
upstreams and provide proper support for aggregators.

Lines 12 and 13 are required in order to provide information to the PHPStan what
will operator yield. Line 22 provides information about input stream source and 
what input source streams. Line 23 defines required parameter for operator - a 
path to a file where stream items will be stored.

It is clear from lines 12, 13 and 22 that operator does not modifies the 
original stream.

Line 25 is required and in its essence, it will provide source stream to 
prototype stream implementation ``RunOpenCode\Component\Dataset\AbstractStream`` 
which will track upstreams and provide correct support for aggregators.  

Implementing the operator logic
-------------------------------

Next, implement the ``iterate(): \Traversable<TKey, TValue>`` method.
This method is executed when the stream is iterated and contains the actual
operator logic.

.. code-block:: php
   :linenos:

   <?php 

   declare(strict_types=1);

   namespace App\Operator;

   use RunOpenCode\Component\Dataset\AbstractStream;
   use RunOpenCode\Component\Dataset\Contract\OperatorInterface;

   /**
    * @template TKey
    * @template TValue
    *
    * @extends AbstractStream<TKey, TValue>
    * @implements OperatorInterface<TKey, TValue>
    */
   final class Log extends AbstractStream implements OperatorInterface
   {
       // Code omitted for the sake of readability.

       /**
        * {@inheritdoc}
        */
       public function iterate(): \Traversable
       {
           $handler = \Safe\fopen($this->file, 'wb'); // @see https://github.com/thecodingmachine/safe

           try {
               foreach($this->source as $key => $value) {
                   \Safe\fwrite($handler, \sprintf(
                       'Key: "%s", Value: "%s"',
                       self::stringify($key),
                       self::stringify($value),
                   ));

                   yield $key => $value;
               }
           } finally {
               \Safe\fclose($handler);
           }
       }

       /**
        * Cast anything to its string representation.
        */ 
       private static function stringify(mixed $value): string 
       {
           // Implementation omitted.
       }
   }

Inside this method, a file handler is opened using the provided file path.
The source stream is then iterated item by item.

For each key–value pair:

* The key and value are converted to their string representations 
  (implementation of method ``Log::stringify()`` is omitted for the sake of
  readability).
* The formatted output is written to the file.
* The original key and value are yielded back to the stream.

**Yielding the original key and value is the most important step**, as it allows
the stream to continue flowing to downstream operators or consumers.

The file handler is closed in a finally block to ensure that resources are
released even if an error occurs during iteration.

.. warning:: This implementation assumes that the stream will be fully iterated, 
             which is not always the case. Consumer of the stream can break 
             iteration (either by using ``break`` in loop, or by using operator 
             which limits number of iterations, such as ``take()``). When 
             writing your own operators, always account for early termination 
             and ensure that resources are handled correctly.

Testing the operator
--------------------

Once the operator is complete, it can be unit tested without requiring any
external dependencies.

.. code-block:: php
   :linenos:

   <?php 

   declare(strict_types=1);

   namespace App\Tests\Operator\Log;

   use App\Operator\Log;
   use PHPUnit\Framework\Attributes\Test;
   use PHPUnit\Framework\TestCase;

   final class LogTest extends TestCase 
   {
       #[Test]
       public function logs(): void 
       {
           $this->assertFileDoesNotExist('/tmp/test_logs.log');

           $operator = new Log([1, 2, 3], '/tmp/test_logs.log');
           
           \iterator_to_array($operator);

           $this->assertSame(
               \Safe\file_get_contents('/path/to/expected/output/file.log'),
               \Safe\file_get_contents('/tmp/test_logs.log'),
           );

           \Safe\unlink('/tmp/test_logs.log');
       }
   }

Using the operator
------------------

When using streams in an object-oriented style, you can call the ``operator()``
method on an instance of ``RunOpenCode\Component\Dataset\Stream`` to apply your
operator.


.. code-block:: php
   :linenos:

   <?php 

   declare(strict_types=1);

   use RunOpenCode\Component\Dataset\Stream;
   use App\Operator\Log;

   Stream(...)
       ->operator(Log::class, '/path/to/file.log');

When using the functional style in PHP 8.5 or later, the ``operator()`` function
is also available.

.. code-block:: php
   :linenos:

   <?php 

   declare(strict_types=1);

   use function RunOpenCode\Component\Dataset\stream;
   use function RunOpenCode\Component\Dataset\operator;
   use App\Operator\Log;

   $source = [...];

   $processed = $source |> stream(...)
                        |> static fn(iterable $stream): Stream => operator($stream, Log::class, '/path/to/file.log'); 

General advices for implementing operators
------------------------------------------

* **Keep your operators simple.** The general idea of each operator is to 
  perform one simple, fundamental operation. Any data-processing complexity 
  should be achieved by composing multiple simple operators, not by implementing
  a single complex operator that performs multiple tasks simultaneously.
* **Reuse existing operators whenever possible.** If your operator can be
  expressed as a composition of existing operators, with only a small amount of
  custom stream-specific logic, do not reimplement existing functionality.
  Existing operators are already unit-tested, which makes it easier to reason 
  about their composition than to write everything from scratch.
* **Keys can be anything — do not break this assumption.** A generator may emit
  values with any type of key.
* **Keys are not unique — do not break this assumption.** A generator may emit
  multiple items with the same key.
* **Streams are not rewindable — do not break this assumption.** Although 
  ``array`` is ``iterable``, ``\Generator`` is also ``iterable``, and generators
  cannot be rewound.
* **Streams may not be fully iterated** — do not assume that users of your 
  operator will consume the entire stream. They may use ``break`` or ``take()`` 
  in their code.
* **Do not lock or hold resources.** If you do, release them when the operator
  finishes streaming. Since there is no reliable way to detect whether streaming
  was interrupted (for example, via ``break`` or another operator), ensure that
  your operator implements the ``__destruct()`` method to properly release any
  resources on garbage collection.

If you believe your operator would be valuable to the wider community, feel free
to submit a pull request!