=============
Intent Bundle
=============

Intent bundle integrates the :doc:`Intent component<../../components/intent/index>`
into Symfony applications by providing a service for storing objects which have
to be preserved between two stateless requests.

Installation
------------

You can install the bundle using Composer:

.. code-block:: console

   composer require runopencode/intent-bundle

Depending on the driver which you intend to use, ``doctrine/dbal`` is required
for storing intents within a database table, while ``doctrine/orm`` is required
if you want the storage table to be generated along with the rest of your
schema. Both are, most commonly, already a part of your project.

And then enable the bundle in your Symfony application:

.. code-block:: php
   :linenos:

   <?php

   // config/bundles.php

   return [
       // ...
       RunOpenCode\Bundle\IntentBundle\IntentBundle::class => ['all' => true],
   ];

Configuration
-------------

The bundle allows you to choose the driver which is used for storing intents,
and to configure it. By default, intents are stored within a database table by
using the default Doctrine Dbal connection.

.. tab:: PHP

   .. code-block:: php
      :linenos:

      <?php
      // config/packages/runopencode_intent.php
      use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

      return static function (ContainerConfigurator $container): void {
          $container->extension('runopencode_intent', [
              'driver' => 'dbal',
              'dbal'   => [
                  'connection' => 'Doctrine\DBAL\Connection',
                  'table_name' => 'runopencode_intent',
              ],
          ]);
      };

.. tab:: YAML

   .. code-block:: yaml
      :linenos:

      # config/packages/runopencode_intent.yaml
      runopencode_intent:
          driver: dbal
          dbal:
              connection: 'Doctrine\DBAL\Connection'
              table_name: runopencode_intent

Configuration options
~~~~~~~~~~~~~~~~~~~~~

* ``driver`` (enum, default: ``dbal``): driver used to persist intents, either
  ``dbal`` or ``cache``. Only the configuration of the chosen driver is used,
  while the other one is ignored.

* ``dbal.connection`` (string, default: ``Doctrine\DBAL\Connection``): service id
  of the connection within which intents are stored. By default, the default
  connection is used. If you have more than one connection, provide the service
  id of the connection of your choice, in example
  ``doctrine.dbal.reporting_connection``.

* ``dbal.table_name`` (string, default: ``runopencode_intent``): name of the
  table within which intents are stored.

* ``cache.pool`` (string, default: ``cache.app``): service id of the PSR-6 cache
  pool within which intents are stored.

Storing intents within a cache pool
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you would rather store intents within Redis, Memcached, or any other cache
implementation, configure the ``cache`` driver. It is advisable to use a
dedicated cache pool for that purpose, so that clearing an application cache
does not remove pending intents as well.

.. tab:: PHP

   .. code-block:: php
      :linenos:

      <?php
      // config/packages/runopencode_intent.php
      use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

      return static function (ContainerConfigurator $container): void {
          $container->extension('framework', [
              'cache' => [
                  'pools' => [
                      'app.cache_pool.intent' => [
                          'adapter' => 'cache.adapter.redis',
                      ],
                  ],
              ],
          ]);

          $container->extension('runopencode_intent', [
              'driver' => 'cache',
              'cache'  => [
                  'pool' => 'app.cache_pool.intent',
              ],
          ]);
      };

.. tab:: YAML

   .. code-block:: yaml
      :linenos:

      # config/packages/cache.yaml
      framework:
          cache:
              pools:
                  app.cache_pool.intent:
                      adapter: cache.adapter.redis

      # config/packages/runopencode_intent.yaml
      runopencode_intent:
          driver: cache
          cache:
              pool: app.cache_pool.intent

Do note that a cache pool is a cache, and that it is allowed to evict an item
before it expires. If losing an intent is not acceptable for your use case, use
the ``dbal`` driver instead.

Database schema
---------------

Identifier of an intent is stored as ULID. Bundle registers that Doctrine type
for you, so there is no need to add it to the ``doctrine.dbal.types``
configuration on your own.

When the ``dbal`` driver is used, the storage registers itself as a listener for
the Doctrine ORM ``postGenerateSchema`` event. Table within which intents are
stored is, therefore, a part of your schema and it will be generated for you:

.. code-block:: console

   bin/console doctrine:schema:update --dump-sql

If you are using Doctrine migrations, table will be a part of a generated
migration as well:

.. code-block:: console

   bin/console doctrine:migrations:diff

Note that Doctrine ORM is required for this convenience only. If your project
uses Doctrine Dbal without ORM, everything else works as expected, but you will
have to create the table on your own.

Usage
-----

Once the bundle is installed and configured, the storage is automatically
available for dependency injection. Inject it using the interface:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Security;

   use App\Security\Intent\ResetPassword;
   use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
   use RunOpenCode\Component\Intent\Exception\NotExistsException;

   final readonly class PasswordResetService
   {
       public function __construct(private IntentStorageInterface $storage)
       {
           // noop.
       }

       public function request(User $user): string
       {
           // Intent is valid for one hour only.
           $identifier = $this->storage->store(new ResetPassword($user->getId()), 3600);

           return (string)$identifier;
       }

       public function complete(string $token, string $password): void
       {
           try {
               /** @var ResetPassword $intent */
               $intent = $this->storage->fetch($token);
           } catch (NotExistsException) {
               // Link is invalid, expired, or it has been used already.
               // ...
           }

           // ...
       }
   }

Read more about how to store and fetch intents in the
:doc:`Intent component<../../components/intent/usage>` documentation.

Removing expired intents
------------------------

Expired intents are removed from the storage when they are fetched, which is not
enough if a link is never clicked. For that purpose, a console command is
provided:

.. code-block:: console

   bin/console runopencode:intent:maintenance

Do note that this command does nothing when the ``cache`` driver is used, since
cache pools evict expired items on their own.
