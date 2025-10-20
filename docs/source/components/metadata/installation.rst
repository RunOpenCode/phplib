============
Installation
============

To install the Metadata component, you will need to use Composer. Run the 
following command in your terminal:

.. code-block:: console

    composer require runopencode/metadata

This will download and install the Metadata component along with its 
dependencies.

In your project, you will need to initialize the Reader which you may use to 
read class, property and method metadata:

.. code-block:: php
   :linenos:
    
    <?php

    declare(strict_types=1);

    use RunOpenCode\Component\Metadata\Reader;
    use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

    // Optionally, provide a cache adapter to the Reader 
    // that implements \Psr\Cache\CacheItemPoolInterface
    $cache  = new PhpFilesAdapter(directory: '/path/to/cache/directory');
    $reader = new Reader($cache);

However, concrete implementation of reader should not be used as dependency in 
your classes. Instead, use ``ReaderInterface``.

.. code-block:: php
   :linenos:
    
    <?php

    declare(strict_types=1);

    namespace App;

    use RunOpenCode\Component\Metadata\Contract\ReaderInterface;

    final readonly class Foo {

        public function __construct(private ReaderInterface $reader)
        {
        }
    }

By default, metadata is always cached using in-memory cache. It is highly
recommended to provide a persistent cache adapter to the Reader in order to
improve performance. Choice of cache adapter is up to you, however, it is 
advisable to use the ones which are "local" to your application, such as file
system cache or APCu cache.

Cache will not check for changes in attributes or classes. In that matter, use
only in-memory cache during development, and persistent cache only in production
environments.
