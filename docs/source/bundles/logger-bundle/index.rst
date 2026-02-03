=============
Logger Bundle
=============

The Logger Bundle provides seamless integration of the Logger component with 
the Symfony framework. This bundle automatically configures the Logger as a 
service and provides configuration options for customizing its behavior.

The Logger component is a decorator for PSR-3 logger implementations that adds
convenience methods for exception logging and context enrichment. See the
:doc:`../../components/logger/index` for details about the Logger component
itself.

Installation
------------

To use the Logger component in a Symfony application, install the bundle:

.. code-block:: console

    composer require runopencode/logger-bundle

The bundle will be automatically registered in your ``config/bundles.php`` if
you're using Symfony Flex.

Configuration
-------------

The bundle provides several configuration options that can be set in your
``config/packages/runopencode_logger.yaml`` file:

.. code-block:: yaml

    runopencode_logger:
        debug: '%kernel.debug%'
        default_log_level: 'error'

Configuration options
~~~~~~~~~~~~~~~~~~~~~

* ``debug`` (boolean, default: ``false``): Enable debug mode. When enabled, the
  ``exception()`` method will throw exceptions after logging them. This is
  useful during development. Typically set to ``%kernel.debug%`` to match
  Symfony's debug mode.

* ``default_log_level`` (string, default: ``'critical'``): The default log level
  used for exception logging when no level is explicitly specified. Valid values
  are: ``emergency``, ``alert``, ``critical``, ``error``, ``warning``,
  ``notice``, ``info``, ``debug``.

Using the Logger service
-------------------------

Once the bundle is installed and configured, the Logger service is automatically
available for dependency injection. Inject it using the interface:

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

       public function registerUser(array $userData): User
       {
           try {
               $user = $this->repository->create($userData);
               $this->mailer->sendWelcomeEmail($user);
               
               return $user;
           } catch (\Throwable $exception) {
               $this->logger->exception(
                   $exception,
                   'User registration failed',
                   ['email' => $userData['email'] ?? 'unknown']
               );
               // ...
           }
       }
   }

The Logger is automatically configured with Symfony's default logger (the
``logger`` service).

Automatic context provider registration
----------------------------------------

One of the most powerful features of the bundle is automatic registration of
context providers. Any service that implements ``LoggerContextInterface`` is
automatically detected and injected into the Logger.

Creating a context provider
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Simply create a service that implements the interface:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Logger\Context;

   use RunOpenCode\Component\Logger\Contract\LoggerContextInterface;
   use Symfony\Component\HttpFoundation\RequestStack;

   final readonly class RequestContextProvider implements LoggerContextInterface
   {
       public function __construct(private RequestStack $requestStack)
       {
           // noop.
       }

       public function get(array $current): array
       {
           $request = $this->requestStack->getCurrentRequest();

           if (null === $request) {
               return [];
           }

           return [
               'request_id' => $request->headers->get('X-Request-ID') ?? uniqid(),
               'request_uri' => $request->getRequestUri(),
               'request_method' => $request->getMethod(),
               'user_agent' => $request->headers->get('User-Agent'),
           ];
       }
   }

If you have services autoconfiguration enabled (which is the default in Symfony),
this service will be automatically registered and tagged with
``runopencode.logger.context_provider``. No additional configuration is needed!

Manual registration
~~~~~~~~~~~~~~~~~~~

If you're not using autoconfiguration or want to explicitly register a context
provider, you can do so in your services configuration:

.. code-block:: yaml

    services:
        App\Logger\Context\RequestContextProvider:
            tags:
                - { name: 'runopencode.logger.context_provider' }

Best practices
--------------

1. **Use the interface**: Always inject ``LoggerInterface`` from the Logger
   component, not the concrete ``Logger`` class or PSR-3's ``LoggerInterface``.

2. **Configure per environment**: Use different configuration files for dev,
   test, and prod environments to adjust debug mode and log levels.

3. **Create focused context providers**: Each context provider should have a
   single responsibility (e.g., one for request data, one for user data, etc.).

4. **Keep providers lightweight**: Context providers are called on every log
   entry, so avoid expensive operations.

See the :doc:`../../components/logger/context-providers` documentation for more 
information about creating and using context providers.
