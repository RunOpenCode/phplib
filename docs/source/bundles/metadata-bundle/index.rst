===============
Metadata Bundle
===============

Metadata bundle integrates the :doc:`Metadata component<../../components/metadata/index>`
into Symfony applications by providing a service to read metadata using PHP
attributes.

Installation
------------

You can install the bundle using Composer:

.. code-block:: bash

   composer require runopencode/metadata-bundle

And then enable the bundle in your Symfony application:

.. code-block:: php
   :linenos:

   <?php

   // config/bundles.php

   return [
       // ...
       RunOpenCode\MetadataBundle\MetadataBundle::class => ['all' => true],
   ];

Configuration
-------------

The bundle provides you with a possibility to configure cache driver for metadata
reader service. By default, it uses in-memory array cache for ``dev`` and ``test``
environments. For ``prod`` environment, it uses Symfony's ``system.cache`` cache 
pool. You may override this behavior by configuring the bundle as shown below.

However, do note that your cache pool is always ignored in ``dev`` and ``test``
environments and only in-memory caching is used instead.

.. tab:: PHP

   .. code-block:: php
      :linenos:

      <?php
      // config/packages/runopencode-metadata.php
      use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
       
      return static function (ContainerConfigurator $container): void {
          $container->extension('runopencode_metadata', [
              'cache_pool' => 'cache.adapter.filesystem',
          ]);
      };

.. tab:: YAML

   .. code-block:: yaml
      :linenos:

      # config/packages/runopencode-metadata.yaml
      runopencode_metadata:
          cache_pool: cache.adapter.filesystem

Usage
-----

Once the bundle is installed and configured, you can use the metadata reader
service in your Symfony application. The service is available via dependency
injection using the ``\RunOpenCode\Component\Metadata\Contract\ReaderInterface``.

Read more about how to use the metadata reader in the :doc:`Metadata component<../../components/metadata/index>`.