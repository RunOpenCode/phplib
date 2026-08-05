============
Installation
============

To install the Intent component, you will need to use Composer. Run the
following command in your terminal:

.. code-block:: console

    composer require runopencode/intent

Intents have to be stored somewhere and this library does not impose which
storage you should use. Therefore, depending on the storage of your choice, you
will have to install additional dependencies as well.

For storing intents within a database table, using Doctrine Dbal:

.. code-block:: console

    composer require doctrine/dbal

If you want the storage table to be generated for you along with the rest of
your schema, Doctrine ORM is required as well:

.. code-block:: console

    composer require doctrine/orm

For storing intents within a PSR-6 cache pool, any implementation will do, in
example:

.. code-block:: console

    composer require symfony/cache

If you intend to use the provided console command for removing expired intents:

.. code-block:: console

    composer require symfony/console

Basic setup
-----------

In your project, you will need to initialize the storage of your choice. Storage
which uses Doctrine Dbal expects a connection and, optionally, a name of the
table within which intents are stored:

.. code-block:: php
   :linenos:

    <?php

    declare(strict_types=1);

    use Doctrine\DBAL\DriverManager;
    use RunOpenCode\Component\Intent\Storage\DbalStorage;

    $connection = DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        // ...
    ]);

    $storage = new DbalStorage(
        connection: $connection,
        tableName: 'runopencode_intent' // Optional, this is the default value.
    );

Do note that identifier of an intent is stored as ULID, so that type has to be
registered within Doctrine as well, see :doc:`storages` for details.

Storage which uses a PSR-6 cache pool expects the pool only:

.. code-block:: php
   :linenos:

    <?php

    declare(strict_types=1);

    use RunOpenCode\Component\Intent\Storage\CacheStorage;
    use Symfony\Component\Cache\Adapter\RedisAdapter;

    $storage = new CacheStorage(RedisAdapter::createConnection('redis://localhost'));

See :doc:`storages` for details about the provided storages and about
implementing your own.

Using the interface
-------------------

Concrete implementation of the storage should not be used as a dependency in
your classes. Instead, use ``IntentStorageInterface``, which will allow you to
change the storage without changing the code which depends on it:

.. code-block:: php
   :linenos:

    <?php

    declare(strict_types=1);

    namespace App\Security;

    use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;

    final readonly class PasswordResetService
    {
        public function __construct(private IntentStorageInterface $storage)
        {
            // noop.
        }
    }

Symfony integration
-------------------

If you are using Symfony framework, you should use the
``runopencode/intent-bundle`` package which registers the storage of your choice
as a service within your container and provides configuration options.

See the :doc:`Intent Bundle documentation <../../bundles/intent-bundle/index>`
for more information about Symfony integration.
