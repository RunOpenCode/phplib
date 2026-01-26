=====
Usage
=====

The Logger component provides a decorator for PSR-3 loggers with additional
methods specifically designed for exception handling. This document covers the
main usage patterns and features.

Exception logging
-----------------

The primary feature of this component is the ability to log exceptions with
a clean API. The component provides two methods for exception logging:

* ``exception()`` - Logs the exception but does not throw it (unless in debug mode)
* ``throw()`` - Logs the exception and always throws it

exception() method
~~~~~~~~~~~~~~~~~~

Use the ``exception()`` method when you want to log an exception and handle it
gracefully without re-throwing it:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Service;

   use RunOpenCode\Component\Logger\Contract\LoggerInterface;

   final readonly class OrderService
   {
       public function __construct(private LoggerInterface $logger)
       {
           // noop.
       }

       public function placeOrder(Order $order): OrderResult
       {
           try {
               $this->validateOrder($order);
               $this->processPayment($order);
               $this->notifyCustomer($order);
               
               return OrderResult::success($order);
           } catch (\Throwable $exception) {
               // Log the exception with context
               $this->logger->exception(
                   $exception,
                   'Order placement failed',
                   [
                       'order_id' => $order->getId(),
                       'customer_id' => $order->getCustomerId(),
                       'total' => $order->getTotal(),
                   ]
               );
               
               return OrderResult::failure($exception->getMessage());
           }
       }
   }

In the example above, when an exception occurs, it is logged with additional
context, but the method returns a failure result instead of crashing. This is
useful for non-critical operations where you want to continue execution.

throw() method
~~~~~~~~~~~~~~

Use the ``throw()`` method when you want to log an exception and then re-throw
it:

.. code-block:: php
   :linenos:

   <?php

   public function criticalDatabaseOperation(): void
   {
       try {
           $this->database->execute('CRITICAL_OPERATION');
       } catch (\Exception $exception) {
           // Log and re-throw
           $this->logger->throw(
               $exception,
               'Critical database operation failed',
               ['operation' => 'CRITICAL_OPERATION']
           );
       }
   }

This is useful when you need to log the exception with context but still want
the exception to propagate up the call stack.

Custom messages
~~~~~~~~~~~~~~~

Both methods accept an optional custom message parameter. If provided, this
message will be used for logging instead of the exception's message:

.. code-block:: php
   :linenos:

   <?php

   try {
       $this->externalApi->call();
   } catch (\Throwable $exception) {
       // Use a custom message for logging
       $this->logger->exception(
           $exception,
           'External API call failed - retrying later'
       );
   }

If no custom message is provided, the exception's message will be used.

Log levels
~~~~~~~~~~

You can specify the log level for each exception:

.. code-block:: php
   :linenos:

   <?php

   use Psr\Log\LogLevel;

   try {
       $this->cache->clear();
   } catch (\Throwable $exception) {
       // Log at WARNING level instead of the default CRITICAL
       $this->logger->exception(
           $exception,
           'Cache clear failed',
           level: LogLevel::WARNING
       );
   }

If no level is specified, the logger will use the default level configured
during initialization (which defaults to ``LogLevel::CRITICAL``).

Context enrichment
~~~~~~~~~~~~~~~~~~

The ``exception()`` and ``throw()`` methods automatically add the exception
object to the log context under the ``exception`` key:

.. code-block:: php
   :linenos:

   <?php

   $this->logger->exception(
       $exception,
       'Operation failed',
       ['user_id' => 123]
   );

   // The actual context passed to the underlying PSR-3 logger will be:
   // [
   //     'user_id' => 123,
   //     'exception' => $exception - Automatically added
   // ]

This ensures that the exception object is always available in the log context
for further processing by your logging infrastructure (e.g., Sentry, error
tracking services, etc.).

Additionally, if you have configured context providers (see 
:doc:`context-providers`), they will automatically enrich the context with
additional information.

PSR-3 compatibility
-------------------

The Logger component fully implements PSR-3's ``LoggerInterface``, so you can
use all standard PSR-3 methods:

.. code-block:: php
   :linenos:

   <?php

   use Psr\Log\LogLevel;

   // Standard PSR-3 methods work as expected
   $logger->emergency('System is down');
   $logger->alert('Database connection lost');
   $logger->critical('Critical error occurred');
   $logger->error('An error occurred');
   $logger->warning('Warning: deprecated method used');
   $logger->notice('User logged in');
   $logger->info('Operation completed successfully');
   $logger->debug('Debug information');

   // Generic log method
   $logger->log(LogLevel::INFO, 'Custom log message', ['key' => 'value']);

All these methods are forwarded to the underlying PSR-3 logger that was passed
to the Logger constructor. If context providers are configured, they will also
enrich the context for these standard PSR-3 methods.

Debug mode behavior
-------------------

The behavior of the ``exception()`` method changes based on the debug mode:

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Logger\Logger;

   // Production mode (debug = false)
   $logger = new Logger($psrLogger, debug: false);

   try {
       throw new \RuntimeException('Test exception');
   } catch (\Throwable $exception) {
       // This will log the exception but NOT throw it
       $logger->exception($exception);
       
       // Execution continues here
       echo "Exception was logged, continuing execution...";
   }

   // Development mode (debug = true)
   $logger = new Logger($psrLogger, debug: true);

   try {
       throw new \RuntimeException('Test exception');
   } catch (\Throwable $exception) {
       // This will log the exception AND throw it again
       $logger->exception($exception);
       
       // This line will NOT be reached
       echo "This will never be printed";
   }

This allows you to use the same code in both environments, with different
behavior based on the debug flag. In production, you can log exceptions and
handle them gracefully, while in development, exceptions are still thrown so
you can see stack traces and debug issues.

Note that the ``throw()`` method always re-throws the exception regardless of
the debug mode.

Working with context
--------------------

You can pass any additional context data when logging exceptions:

.. code-block:: php
   :linenos:

   <?php

   try {
       $this->api->updateUser($userId, $data);
   } catch (\Throwable $exception) {
       $this->logger->exception(
           $exception,
           'User update failed',
           [
               'user_id' => $userId,
               'request_data' => $data,
               'timestamp' => time(),
               'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
           ]
       );
   }

The context array can contain any data that will help you debug the issue. This
data is passed to the underlying PSR-3 logger and can be processed by your log
handlers (e.g., written to files, sent to log aggregation services, etc.).

For automatically enriching context across all log calls, see 
:doc:`context-providers`.
