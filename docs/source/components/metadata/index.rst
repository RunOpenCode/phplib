==================
Metadata component
==================

With the introduction of attributes, PHP allows to the developers to add 
additional metadata to the classes, methods, properties and function parameters
using native syntax.

Even though there is built-in reflection support for attributes, it lacks some
functionality that would make it easier to work with them.

This library allows you to:

* Check if class has specific attribute, get class attribute instance(s).
* Get class members (properties and methods) that are marked with specific
  attribute.
* Read attribute instance(s) from class members (properties and methods).

In general, this library supports building other useful libraries that rely on
metadata defined using PHP attributes, such as behavior implementations for
Doctrine entities (e.g. timestampable, soft deletable, blameable, etc.).

Table of Contents
-----------------

.. toctree::
   :maxdepth: 1

   installation
   features
   reading-metadata
   inspiration

Example
-------

Read a property marked with ``Deletable`` attribute from an entity
and check if it is marked as deleted.

.. code-block:: php
   :linenos:

   <?php 

   $deleted = $reader->property($entity, Deletable::class)->read($entity);


See :doc:`inspiration` for a full example of how to use the library to implement, 
per example, timestampable behavior for Doctrine entities.
