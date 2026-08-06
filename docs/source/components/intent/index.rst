================
Intent component
================

Some use cases require a state to be preserved between two stateless requests.
Password reset is a typical example: user requests a password reset, receives an
email containing a link and, at some point in time, clicks on that link in order
to complete the process which has been started within some earlier request.

Session storage is not always an option here, since the request which completes
the use case may originate from a different browser, a different device, or even
a different machine.

This library allows you to store an object into a persistent storage and to
retrieve it later by using a randomly generated identifier which you may safely
put into an URL.

Features
--------

* **Store any serializable object** and retrieve it later by using its
  identifier.
* **Time to live** is defined per intent, after which intent is no longer
  available and is removed from the storage.
* **Deferred availability** allows you to store an intent which becomes
  available at some moment in the future.
* **Invalidated on read** by default, so a single intent may be used only once,
  which is a sane default for one time links.
* **Doctrine Dbal and PSR-6 storages** are provided out of the box, while other
  storages may be added with ease.
* **Symfony ready** via dedicated ``runopencode/intent-bundle`` package, see
  :doc:`../../bundles/intent-bundle/index` for integration details.

Table of Contents
-----------------

.. toctree::
   :maxdepth: 1

   installation
   usage
   storages

Quick example
-------------

Assume that you are implementing a password reset. Within the request which
initiates the process, you will store an intent describing what has to be done
and send its identifier to the user:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Security\Controller;

   use App\Security\Intent\ResetPassword;
   use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;

   final readonly class RequestPasswordResetController
   {
       public function __construct(private IntentStorageInterface $storage)
       {
           // noop.
       }

       public function __invoke(User $user): Response
       {
           // Intent is valid for one hour only.
           $identifier = $this->storage->store(new ResetPassword($user->getId()), 3600);

           $this->mailer->send(new PasswordResetEmail($user, (string)$identifier));

           // ...
       }
   }

Within the request which completes the process, you will fetch the intent by
using its identifier and proceed with the use case:

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Security\Controller;

   use RunOpenCode\Component\Intent\Contract\IntentStorageInterface;
   use RunOpenCode\Component\Intent\Exception\NotExistsException;
   use Symfony\Component\Uid\Ulid;

   final readonly class ResetPasswordController
   {
       public function __construct(private IntentStorageInterface $storage)
       {
           // noop.
       }

       public function __invoke(Ulid|string $identifier): Response
       {
           try {
               /** @var ResetPassword $intent */
               $intent = $this->storage->fetch($identifier);
           } catch (NotExistsException) {
               // Link is invalid, expired, or it has been used already.
               // ...
           }

           // ...
       }
   }

Note that intent is invalidated as soon as it is fetched, which means that the
link which you have sent to the user may be used only once.

Password reset is just one example, intents may be used for various cases where
session storage can not, or should not be used.
