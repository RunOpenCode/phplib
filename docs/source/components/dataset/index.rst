=================
Dataset component
=================

This library is heavily inspired by the `Java Stream API`_ for working with
collections in a functional(ish), declarative way. In some aspects, it is also
inspired by `ReactiveX`_, but with a much simpler approach and far fewer 
features.

If your problem can be described as:

   I have a data stream from some source (file, database query result, etc.), 
   and I want to iterate over its records and process them using a small memory
   footprint.

then this library can help you achieve that goal using a declarative approach.

There are several PHP implementations of this idea; however, this implementation
focuses on PHP iterable values, assuming that the underlying implementation
is most likely an instance of `Generator`_. Of course, it also works with the
``array`` data type or anything that implements ``\Traversable``. The real
strength of this library lies in its focus on simple, declarative data stream
processing with minimal memory usage.

If you need full fledged `ReactiveX`_ in PHP, please take a look at the official
implementation of the specification: `RxPHP`_.

.. _Java Stream API: https://docs.oracle.com/javase/8/docs/api/java/util/stream/Stream.html
.. _ReactiveX: https://reactivex.io
.. _Generator: https://www.php.net/manual/en/class.generator.php
.. _RxPHP: https://github.com/ReactiveX/RxPHP

Features
--------

* Declarative approach to process data streams.
* Designed to work with any ``iterable`` while using as little memory as 
  possible.
* Provides a set of operators, reducers, and collectors that can be easily
  extended or added as needed.
* Focused on small memory consumption during processing.
* Introduces the concept of **aggregators**, allowing you to process a stream 
  and reduce (aggregate) values simultaneously without interrupting the data 
  stream.

Table of Contents
-----------------

.. toctree::
   :maxdepth: 1

   installation
   concepts
   stream/index
   operators/index
   reducers/index
   collectors/index


Quick example
-------------

A simple example of using this library to list online transactions is shown
below. Assume that we want to display a list of online transactions executed
within a certain time period, and we also want to calculate the total amount for
each currency as well as the overall total. This is how it can be done using 
this library:

.. code-block:: php
   :linenos:

   <?php

   namespace App\Reporting\Finance;

   use RunOpenCode\Component\Dataset\Stream;
   use RunOpenCode\Component\Dataset\Reducer\Sum;
   use RunOpenCode\Component\Dataset\Reducer\Average;

   final readonly class OnlinePurchaseReport
   {
       // "Database" is just made up service to demonstrate usage of this library.
       public function __construct(private Database $database)
       {
           /* noop */
       }
   
       /**
        * return iterable<array{
        *     client_id: int,
        *     transaction_id: string,
        *     total_amount: int,
        *     currency: 'EUR'|'USD',
        *     converted: int,
        * }>
        */
       public function getReportData(\DateTimeInterface $from, \DateTimeInterface $to, int $conversionRate): iterable
       {
           /**
            * @var iterable<array{
            *     client_id: int,
            *     transaction_id: string,
            *     total_amount: int,
            *     currency: 'EUR'|'USD'
            * }>
            */
           $dataset = $this->database->execute('SELECT * FROM online_transactions WHERE created_at BETWEEN :from AND :to;', [
               'from' => $from,
               'to'   => $to,
           ]);

           return Stream::create($dataset)
               ->aggregate('total_eur', Sum::class, static fn(array $row): int => 'EUR' === $row['currency'] ? $row['total_amount'] : 0)
               ->aggregate('total_usd', Sum::class, static fn(array $row): int => 'USD' === $row['currency'] ? $row['total_amount'] : 0)
               ->map(function(array $row) use ($conversionRate): array {
                   $row['converted'] = 'USD' === $row['currency'] ? $row['total_amount'] *  $conversionRate : $row['total_amount'];
                   return $row;
               })
               ->aggregate('total_converted', Sum::class, static fn(int $reduced, array $row): int => $row['converted'])
               ->aggregate('average_converted', Sum::class, static fn(int $reduced, array $row): int => $row['converted'])
       }
   }

**Explanation of the code:** On line 35 we fetch data from database. PHP returns 
an iterable that points to the first row of the result set, which means that no
rows are loaded into the PHP virtual machine's memory upfront.

Line 41 wraps that iterable into instance of 
``RunOpenCode\Component\Dataset\Stream``. We then apply the operations that
should be executed while iterating over the stream.

Line 42 applies aggregator that sums all transactions executed in ``EUR``. Line 
43 does the same for ``USD``.

Line 44 adds a new column to each row, ``converted`` which will convert all 
amounts to ``EUR`` using the provided conversion rate.

Finally, lines 48 and 49 apply aggregators that calculate the total sum of all
transactions in ``EUR`` as well as the average transaction amount in ``EUR``.

**None of the processing is executed, until the stream is iterated**. The 
iterable is only wrapped with processing logic. To execute it, you must iterate 
over it. In practice, this will often be done in a templating engine.

The example below uses ``echo`` to demonstrate the concept:

.. code-block:: php
   :linenos:

   <?php 

   foreach($stream as $row) {
      echo \sprintf(
          'Client: %s, Transaction: %s, Amount: %d, Currency: %d, EUR: %d',
          $row['client_id'],
          $row['transaction_id'],
          $row['total_amount'],
          $row['currency'],
          $row['converted'],
      );
      echo "\n";
   }

   // Since we iterated, our aggregated values are available too.
   echo \sprintf('Total in EUR: %d', $stream->aggregated['total_converted']);
   echo "\n";
   echo \sprintf('Average in EUR: %d', $stream->aggregated['average_converted']);

So, during this process, memory footprint is almost as low as amount of memory
required for storing one row.
