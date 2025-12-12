=================
Dataset component
=================

This library is heavily inspired by `Java Stream API`_ for dealing with 
collections in functionali(ish), declarative way. In some way, it is inspired 
with `ReactiveX`_ as well, only with much, much simpler approach and with less
features, of course.

If your problem can be described as:

   I have a data stream from some source (file, database query result, etc.) and
   I want to iterate through its records and do some processing with small 
   memory footprint.

this is the library which can help you achieve that goal by using declarative
approach.

There are several PHP implementations of same idea, however, this implementation
focuses on PHP ``iterable`` assuming that underlying implementation is most 
probably instance of `Generator`_. Of course, it will work with ``array`` data 
type, or anything which implements ``\Traversable``, however, power of this
library is in its focus of simple declarative data stream processing with small 
memory footprint.

If you need full fledged `ReactiveX`_ in PHP, please take a look at the official
implementation of the specification at `RxPHP`_.

.. _Java Stream API: https://docs.oracle.com/javase/8/docs/api/java/util/stream/Stream.html
.. _ReactiveX: https://reactivex.io
.. _Generator: https://www.php.net/manual/en/class.generator.php
.. _RxPHP: https://github.com/ReactiveX/RxPHP

Features
--------

* Declarative approach to process data streams.
* Designed to work with any ``iterable`` using as less as possible memory.
* Provides bunch of operators, reducers and collectors, which can be easily 
  extended or added as needed.
* Focused on small memory consumption during processing.
* Introduces concept of **aggregators** allowing you to simultaneously process
  stream and reduce (aggregate) values during processing without breaking the 
  data stream.

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

A simple example of using this library for listing online transactions is given 
below. Assume that we want to display list of online transactions executed in
some time period, and we want to show total amount for each individual currency
as well as in total, this would be a way to do that using this library:

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

**Explanation of the code:** On line no 35 we fetch data from database. PHP
returns iterable which is pointer on the first row of the returned dataset, 
which means that no rows are loaded into memory of the PHP virtual machine.

Line 41 wraps that iterable into instance of 
``RunOpenCode\Component\Dataset\Stream`` and then we apply operations which we 
want to conduct against the stream during its iteration.

Line 42 applies aggregator which will sum all transactions executed using
``EUR`` as currency, line 43 does that for ``USD``.

Line 44 will add new column to the row, ``converted`` which will convert all 
amounts to ``EUR`` using given conversion rate.

Lastly, lines 48 and 49 will apply aggregator which will provide us with total 
sum of all transactions in ``EUR`` as well as average transaction amount in 
``EUR``.

**None of the processing is executed, until you iterate stream**. Iterable is
just wrapped with processing logic, to execute it, you need to iterate it. You
will probably do that in some templating language. However, example below will
just use ``echo`` to demonstrate concept:


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
   echo \sprint('Total in EUR: %d', $stream->aggregators['total_converted']);
   echo "\n";
   echo \sprint('Average in EUR: %d', $stream->aggregators['average_converted']);

So, during this process, memory footprint is almost as low as amount of memory
required for storing one row.
