========
Storages
========

Storage is the persistence layer for intents and this library provides two
implementations out of the box. Which one you should use depends on your project
and on the guarantees which you expect from the storage.

Doctrine Dbal storage
---------------------

``RunOpenCode\Component\Intent\Storage\DbalStorage`` stores intents within a
single database table. Intents survive a restart of your infrastructure and they
are removed only when they are fetched, invalidated, or when the storage is
maintained.

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Intent\Storage\DbalStorage;

   $storage = new DbalStorage($connection, 'runopencode_intent');

Table which is used for storing intents has the following columns:

* ``id`` — identifier of an intent, stored as ULID.
* ``intent`` — serialized intent object.
* ``valid_from`` — moment from which an intent becomes available.
* ``expires_at`` — moment after which an intent is no longer available.
* ``created_at`` — moment when an intent has been stored.

Table is indexed by ``expires_at`` as well, since that column is used when
expired intents are removed from the storage.

Registering the ULID type
~~~~~~~~~~~~~~~~~~~~~~~~~

Identifier of an intent is stored as ULID, by using the type provided by
``symfony/doctrine-bridge``. Doctrine has to be aware of that type, so make sure
that it is registered before the storage is used:

.. code-block:: php
   :linenos:

   <?php

   use Doctrine\DBAL\Types\Type;
   use Symfony\Bridge\Doctrine\Types\UlidType;

   if (!Type::hasType(UlidType::NAME)) {
       Type::addType(UlidType::NAME, UlidType::class);
   }

If you are using Symfony framework, this is done for you, see the
:doc:`Intent Bundle documentation <../../bundles/intent-bundle/index>`.

Generating the table
~~~~~~~~~~~~~~~~~~~~

If you are using Doctrine ORM, storage is able to contribute its table to the
schema which ORM generates for your entities. Register it as a listener for the
``postGenerateSchema`` event and the table will be created for you along with
the rest of your schema:

.. code-block:: php
   :linenos:

   <?php

   use Doctrine\ORM\Tools\ToolEvents;

   $entityManager
       ->getEventManager()
       ->addEventListener([ToolEvents::postGenerateSchema], $storage);

Storage will contribute its table only if the schema is generated for the very
same connection which is used for storing intents, which makes it safe to use
within projects having more than one entity manager.

If you are using Symfony framework, this is done for you, see the
:doc:`Intent Bundle documentation <../../bundles/intent-bundle/index>`.

Cache storage
-------------

``RunOpenCode\Component\Intent\Storage\CacheStorage`` stores intents within any
PSR-6 cache pool, which makes Redis, Memcached, filesystem, or any other cache
implementation available to you without any additional code:

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Intent\Storage\CacheStorage;
   use Symfony\Component\Cache\Adapter\RedisAdapter;

   $storage = new CacheStorage(RedisAdapter::createConnection('redis://localhost'));

Expiration is delegated to the cache pool itself, which means that expired
intents are evicted without any effort on your side and that ``maintenance()``
does nothing.

However, do note that a cache pool is a cache, and that it is allowed to evict
an item before it expires. If losing an intent is not acceptable for your use
case, use the storage which uses Doctrine Dbal instead. Also, it is advisable to
use a dedicated cache pool for intents, so that clearing an application cache
does not remove pending intents as well.

Implementing your own storage
-----------------------------

Should neither of the provided storages suit your needs, implementing your own
is a trivial task. Implement
``RunOpenCode\Component\Intent\Contract\IntentStorageInterface`` and make sure
that your implementation honours the following rules:

* ``store()`` returns a newly generated ``Ulid``, which must be unguessable,
  since it is the only thing which protects an intent from being fetched by
  somebody else.
* ``fetch()`` and ``invalidate()`` accept an identifier which is a ``Ulid``, its
  string representation, or any object which is ``Stringable``, so both have to
  normalize such a value into a ``Ulid`` before it is used.
* ``fetch()`` throws ``NotExistsException`` if an intent does not exist, if it
  has expired, or if it is not available yet.
* ``fetch()`` invalidates an intent after it has been fetched, unless it is
  explicitly instructed not to do so.
* An intent which is not available yet must be preserved, while an expired
  intent may be removed.
* ``invalidate()`` does not throw an exception if an intent with the given
  identifier does not exist.
* ``maintenance()`` removes all expired intents, or does nothing, if a storage
  removes them on its own.
