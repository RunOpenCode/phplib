================
Logger component
================

The Logger component is a decorator for PSR-3 logger implementations that adds
convenience methods for exception logging and context enrichment. It wraps any
PSR-3 compatible logger and extends its functionality with exception-specific
logging methods and the ability to automatically enrich log context from 
multiple sources.

When working with exceptions, you often need to log them with proper context
before either throwing them or suppressing them based on the environment (e.g.,
development vs. production). This component provides a clean API for these
common scenarios while maintaining full compatibility with PSR-3.

Features
--------

* **Exception logging methods**: dedicated ``exception()`` and ``throw()`` 
  methods for logging exceptions with automatic context enrichment.
* **Debug mode support**: toggle between logging and throwing exceptions 
  depending on the environment.
* **Context providers**: automatically enrich log entries with additional 
  context from multiple sources (e.g., request ID, user information, etc.).
* **PSR-3 compatible**: fully implements PSR-3 ``LoggerInterface`` and can wrap
  any PSR-3 logger.
* **Configurable default log level**: set the default severity level for 
  exception logging.
* **Symfony ready**: via dedicated ``runopencode/logger-bundle`` package,
  see :doc:`../../bundles/logger-bundle/index` for integration details.

Table of Contents
-----------------

.. toctree::
   :maxdepth: 1

   installation
   usage
   context-providers

Quick example
-------------

Basic usage of the Logger component to log exceptions with custom context:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Service;

   use Psr\Log\LoggerInterface as PsrLoggerInterface;
   use RunOpenCode\Component\Logger\Contract\LoggerInterface;
   use RunOpenCode\Component\Logger\Logger;

   final readonly class PaymentProcessor
   {
       public function __construct(private LoggerInterface $logger)
       {
           // noop.
       }

       public function processPayment(Payment $payment): void
       {
           try {
               // Process payment logic
               $this->gateway->charge($payment);
           } catch (\Throwable $exception) {
               // Log the exception with additional context
               $this->logger->exception(
                   $exception,
                   'Payment processing failed',
                   [
                       'payment_id' => $payment->getId(),
                       'amount' => $payment->getAmount(),
                       'currency' => $payment->getCurrency(),
                   ]
               );
               // ...
           }
       }
   }

In the example above, if an exception occurs during payment processing, it will
be logged with the custom message and context, including payment details. The
``exception()`` method logs the exception but does not re-throw it (unless debug
mode is enabled), allowing you to handle the error yourself.

For cases where you want to both log and throw the exception, use the 
``throw()`` method instead:

.. code-block:: php
   :linenos:

   <?php

   public function criticalOperation(): void
   {
       try {
           $this->performOperation();
       } catch (\Throwable $exception) {
           // Log and re-throw the exception
           $this->logger->throw(
               $exception,
               'Critical operation failed',
               ['operation' => 'critical']
           );
       }
   }

See :doc:`usage` for more detailed examples and :doc:`context-providers` to 
learn how to automatically enrich all log entries with additional context.

For Symfony framework integration, see the 
:doc:`Logger Bundle documentation <../../bundles/logger-bundle/index>`.

