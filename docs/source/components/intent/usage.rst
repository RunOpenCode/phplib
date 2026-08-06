=====
Usage
=====

Regardless of the storage which you have chosen, you will always work with
``RunOpenCode\Component\Intent\Contract\IntentStorageInterface``. It exposes
four methods only, which are covered in detail within this document.

Storing an intent
-----------------

Any serializable object may be stored as an intent. There is no interface to
implement and no attribute to add, an intent is just a plain object which
describes what should be done once it is fetched:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Security\Intent;

   final readonly class ResetPassword
   {
       public function __construct(
           public int                $userId,
           public \DateTimeImmutable $requestedAt,
       ) {
           // noop.
       }
   }

Once stored, an identifier is returned, which is an instance of
``Symfony\Component\Uid\Ulid``:

.. code-block:: php
   :linenos:

   <?php

   $identifier = $storage->store(new ResetPassword($userId, new \DateTimeImmutable('now')));

Time to live
~~~~~~~~~~~~

By default, an intent is stored for one day (86400 seconds). You may provide a
different time to live, expressed in seconds, as a second argument:

.. code-block:: php
   :linenos:

   <?php

   // Intent is available for one hour only.
   $identifier = $storage->store($intent, 3600);

Once an intent expires, it is no longer available and it will be removed from
the storage.

Deferred availability
~~~~~~~~~~~~~~~~~~~~~

You may, optionally, store an intent which becomes available at some moment in
the future by providing a third argument:

.. code-block:: php
   :linenos:

   <?php

   // Intent becomes available tomorrow and it is available for one day.
   $identifier = $storage->store($intent, 86400, new \DateTimeImmutable('+1 day'));

Note that time to live is relative to that moment, and not to the moment when
the intent has been stored. In example given above, the intent is available
between tomorrow and the day after tomorrow.

An intent which is not available yet behaves exactly as an intent which does not
exist. However, it is not removed from the storage, so it will become available
once its time comes.

Fetching an intent
------------------

An intent is fetched by using its identifier:

.. code-block:: php
   :linenos:

   <?php

   /** @var ResetPassword $intent */
   $intent = $storage->fetch($identifier);

You will get the very same object which you have stored, so you may safely type
hint against your own classes.

Identifier may be an instance of ``Ulid``, its string representation, or any
object which is ``Stringable``. Since an identifier usually arrives from a
request, as a part of an URL, you may pass that value directly, without
converting it yourself:

.. code-block:: php
   :linenos:

   <?php

   $intent = $storage->fetch($request->attributes->get('token'));

If an intent does not exist, if it has expired, if it is not available yet, or
if it has been fetched already, ``NotExistsException`` is thrown. From the
perspective of the code which fetches it, all these cases are the same and
should be handled in the same manner:

.. code-block:: php
   :linenos:

   <?php

   use RunOpenCode\Component\Intent\Exception\NotExistsException;

   try {
       $intent = $storage->fetch($identifier);
   } catch (NotExistsException) {
       // Link is invalid, expired, or it has been used already.
   }

Do note that a string which is not a valid ULID is not the same case. Such a
value never identified an intent, so ``\InvalidArgumentException`` is thrown
instead, before the storage is even queried. If you pass a value which comes
from a request, and you want to treat a malformed identifier in the same manner
as an intent which does not exist, catch it as well:

.. code-block:: php
   :linenos:

   <?php

   try {
       $intent = $storage->fetch($token);
   } catch (NotExistsException|\InvalidArgumentException) {
       // Link is malformed, invalid, expired, or it has been used already.
   }

Preserving an intent
~~~~~~~~~~~~~~~~~~~~

An intent is invalidated as soon as it is fetched, which makes one time links
the default behaviour. You may, however, fetch an intent without invalidating
it, which is useful when you have to render a form before the use case is
actually completed:

.. code-block:: php
   :linenos:

   <?php

   // Render the form, intent is still available.
   $intent = $storage->fetch($identifier, false);

   // ...

   // Form is submitted, intent is consumed now.
   $intent = $storage->fetch($identifier);

Invalidating an intent
----------------------

An intent may be invalidated explicitly, in example, when a user cancels the
process which has been initiated:

.. code-block:: php
   :linenos:

   <?php

   $storage->invalidate($identifier);

Identifier may be an instance of ``Ulid``, its string representation, or any
object which is ``Stringable``, exactly as it is the case when an intent is
fetched.

Method does not throw an exception if an intent with the given identifier does
not exist. It does, however, throw ``\InvalidArgumentException`` if given a
string which is not a valid ULID.

Removing expired intents
------------------------

Expired intents are removed from the storage when they are fetched, which is not
enough if a link is never clicked. Therefore, storages have to be maintained
from time to time:

.. code-block:: php
   :linenos:

   <?php

   $storage->maintenance();

How often you should invoke this method depends on how many intents you store
and for how long.

Do note that storages which are able to remove expired intents on their own do
nothing here. Such is the storage which uses a PSR-6 cache pool, since cache
pools evict expired items themselves.

Console command
~~~~~~~~~~~~~~~

Since maintenance has to be executed periodically, a console command is provided
for that purpose. It requires ``symfony/console`` only, so you may use it within
any console application, regardless of the framework which you are using, or of
whether you are using one at all:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   use RunOpenCode\Component\Intent\Command\ClearExpiredIntentsCommand;
   use Symfony\Component\Console\Application;

   $application = new Application();

   $application->addCommand(new ClearExpiredIntentsCommand($storage));

   $application->run();

Command name and description are declared by using the ``#[AsCommand]``
attribute, so there is nothing else for you to configure. Once registered, it is
available as:

.. code-block:: console

   bin/console runopencode:intent:maintenance

If you are using Symfony framework, command is registered for you, see the
:doc:`Intent Bundle documentation <../../bundles/intent-bundle/index>`.
