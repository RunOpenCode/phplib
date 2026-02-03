============
Installation
============

To install the Logger component, you will need to use Composer. Run the 
following command in your terminal:

.. code-block:: console

    composer require runopencode/logger

This will download and install the Logger component along with its dependencies.

Basic setup
-----------

In your project, you will need to initialize the Logger by wrapping an existing
PSR-3 logger implementation:

.. code-block:: php
   :linenos:
    
    <?php

    declare(strict_types=1);

    use Psr\Log\LogLevel;
    use RunOpenCode\Component\Logger\Logger;
    use Monolog\Logger as MonologLogger;
    use Monolog\Handler\StreamHandler;

    // Create your PSR-3 logger (using Monolog as an example)
    $psrLogger = new MonologLogger('app');
    $psrLogger->pushHandler(new StreamHandler('/path/to/logs/app.log', LogLevel::DEBUG));

    // Wrap it with RunOpenCode Logger
    $logger = new Logger(
        decorated: $psrLogger,
        contextProviders: [],           // Optional: context providers
        debug: false,                    // Optional: debug mode (default: false)
        defaultLevel: LogLevel::CRITICAL // Optional: default log level (default: CRITICAL)
    );

The Logger component is a decorator that wraps any PSR-3 logger implementation
and adds additional functionality on top of it.

Using the interface
-------------------

It is highly recommended to use the ``LoggerInterface`` from this component
as a dependency in your classes, rather than the concrete implementation:

.. code-block:: php
   :linenos:
    
    <?php

    declare(strict_types=1);

    namespace App\Service;

    use RunOpenCode\Component\Logger\Contract\LoggerInterface;

    final readonly class UserService
    {
        public function __construct(private LoggerInterface $logger)
        {
            // noop.
        }

        public function deleteUser(int $userId): void
        {
            try {
                $this->repository->delete($userId);
            } catch (\Throwable $exception) {
                $this->logger->exception(
                    $exception,
                    'Failed to delete user',
                    ['user_id' => $userId]
                );
                // ...
            }
        }
    }

The ``LoggerInterface`` extends PSR-3's ``LoggerInterface`` and adds the 
``exception()`` and ``throw()`` methods for convenient exception logging.

Debug mode
----------

When creating a Logger instance, you can enable debug mode. In debug mode, the
``exception()`` method will throw the exception after logging it, which is
useful during development:

.. code-block:: php
   :linenos:

    <?php

    use Psr\Log\LogLevel;
    use RunOpenCode\Component\Logger\Logger;

    // In development environment
    $logger = new Logger(
        decorated: $psrLogger,
        debug: true  // Exceptions will be thrown after logging
    );

    // In production environment
    $logger = new Logger(
        decorated: $psrLogger,
        debug: false  // Exceptions will only be logged, not thrown
    );

This allows you to use the same code in both development and production
environments, with exceptions being thrown in development for easier debugging
and only logged in production for graceful error handling.

Default log level
-----------------

You can configure the default log level for exception logging. This level is
used when you call ``exception()`` or ``throw()`` without specifying a log
level:

.. code-block:: php
   :linenos:

    <?php

    use Psr\Log\LogLevel;
    use RunOpenCode\Component\Logger\Logger;

    $logger = new Logger(
        decorated: $psrLogger,
        defaultLevel: LogLevel::ERROR  // Use ERROR instead of CRITICAL
    );

    // This will log at ERROR level
    $logger->exception($exception);

    // You can still override it per-call
    $logger->exception($exception, level: LogLevel::CRITICAL);

The default log level is ``LogLevel::CRITICAL`` if not specified.

Symfony integration
-------------------

If you are using Symfony framework, you should use the 
``runopencode/logger-bundle`` package which automatically registers the Logger
as a service in your container and provides configuration options.

See the :doc:`Logger Bundle documentation <../../bundles/logger-bundle/index>` 
for more information about Symfony integration.
