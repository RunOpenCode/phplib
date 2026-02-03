=================
Context providers
=================

Context providers allow you to automatically enrich all log entries with
additional context information from various sources. This is useful for adding
consistent metadata to all logs, such as request IDs, user information,
environment details, etc.

What are context providers?
----------------------------

A context provider is a class that implements the ``LoggerContextInterface``
and provides additional context data to be merged with the context passed to
any log method. This allows you to inject contextual information into all log
entries without having to manually pass it every time you log something.

Common use cases include:

* Adding request ID to all logs for request tracing
* Including authenticated user information
* Adding environment details (hostname, instance ID, etc.)
* Including application version or build number
* Adding correlation IDs for distributed systems

The LoggerContextInterface
---------------------------

To create a context provider, implement the ``LoggerContextInterface``:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace RunOpenCode\Component\Logger\Contract;

   interface LoggerContextInterface
   {
       /**
        * Get context data to append to the existing context.
        *
        * @param array<mixed> $current Current context passed to the logger method.
        *
        * @return array<mixed> Context data to append to the current context.
        */
       public function get(array $current): array;
   }

The ``get()`` method receives the current context (the context that was passed
to the log method) and returns additional context data to be merged with it.

Creating a context provider
----------------------------

Here's an example of a simple context provider that adds request ID to all logs:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Logger\Context;

   use RunOpenCode\Component\Logger\Contract\LoggerContextInterface;

   final readonly class RequestIdContextProvider implements LoggerContextInterface
   {
       public function __construct(private string $requestId)
       {
           // noop.
       }

       public function get(array $current): array
       {
           return ['request_id' => $this->requestId];
       }
   }

Using context providers
-----------------------

To use context providers, pass them to the Logger constructor:

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Logger\Logger;
   use App\Logger\Context\RequestIdContextProvider;
   use App\Logger\Context\UserContextProvider;

   $requestIdProvider = new RequestIdContextProvider($requestId);

   $logger = new Logger(
       decorated: $psrLogger,
       contextProviders: [
           $requestIdProvider,
           $userContextProvider,
       ]
   );

   // Now all log entries will automatically include request_id and user information
   $logger->info('User action performed');
   
   // Resulting context will be something like:
   // [
   //     'request_id' => '96a101dd-c49a-4fea-aee2-a76510f32190',
   // ]

Context merging
---------------

Context from providers is merged with the context passed to log methods. The
merge happens in this order:

1. Context from each provider is collected (in the order they were registered)
2. Each provider's context is merged with the accumulated context
3. The final context is merged with the context passed to the log method

If there are duplicate keys, later values override earlier ones:

.. code-block:: php
   :linenos:

   <?php

   // Provider 1 returns: ['request_id' => 'abc', 'env' => 'prod']
   // Provider 2 returns: ['user_id' => 42, 'env' => 'staging']
   // Passed context: ['env' => 'dev', 'action' => 'create']

   // Final context will be:
   // [
   //     'request_id' => 'abc',
   //     'env' => 'dev',           // From passed context (overrides providers)
   //     'user_id' => 42,
   //     'action' => 'create',
   // ]

This means that the context passed directly to the log method always has the
highest priority.

Access to current context
-------------------------

Context providers have access to the current context (including context from
previous providers) through the ``$current`` parameter. This allows you to make
decisions based on what's already in the context:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Logger\Context;

   use RunOpenCode\Component\Logger\Contract\LoggerContextInterface;

   final class ConditionalContextProvider implements LoggerContextInterface
   {
       public function get(array $current): array
       {
           // Only add debug info if debug flag is set
           if (isset($current['debug']) && $current['debug'] === true) {
               return [
                   'memory_usage' => memory_get_usage(true),
                   'peak_memory' => memory_get_peak_usage(true),
                   'time' => microtime(true),
               ];
           }

           return [];
       }
   }

Performance considerations
--------------------------

Context providers are called for every log entry, so keep their implementation
lightweight. Avoid expensive operations like database queries or external API
calls in context providers.

If you need to include data that's expensive to compute, consider:

* Caching the data in the provider instance
* Making the computation lazy (only when actually needed)
* Using a different approach for expensive context data

Good example (lightweight):

.. code-block:: php
   :linenos:

   <?php

   final readonly class EnvironmentContextProvider implements LoggerContextInterface
   {
       public function get(array $current): array
       {
           return [
               'env' => $_ENV['APP_ENV'] ?? 'unknown',
               'hostname' => gethostname(),
           ];
       }
   }

Bad example (expensive - avoid this):

.. code-block:: php
   :linenos:

   <?php

   final readonly class BadContextProvider implements LoggerContextInterface
   {
       public function __construct(private Database $db)
       {
           // noop.
       }

       public function get(array $current): array
       {
           // DON'T DO THIS - database query on every log entry!
           return [
               'active_users' => $this->db->query('SELECT COUNT(*) FROM users WHERE active = 1'),
           ];
       }
   }

See the :doc:`Logger Bundle documentation <../../bundles/logger-bundle/index>` 
for details on Symfony integration.
