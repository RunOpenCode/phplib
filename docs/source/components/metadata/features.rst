==================
Supported features
==================

Major purpose of this library is to provide you with easy to use API for reading
metadata from classes, properties and methods using attributes. Instead of 
traversing reflection objects and reading attributes manually, you may use this 
library to do that for you.

* **Read attributes from class**: you may check if class has specific attribute,
  get all attributes from class or get specific attribute from class (initialized).
  You may also get all properties or methods of class which have specific attribute
  or get specific property or method of class which has specific attribute.
* **Find annotated properties and methods**: you may get all properties or 
  methods of class which have specific attribute.
* **Read attributes from property**: you may check if property has specific 
  attribute, get all attributes from property or get specific attribute from 
  property (initialized). You may also read and write value to property and check
  if property is initialized.
* **Read attributes from method**: you may check if method has specific 
  attribute, get all attributes from method or get specific attribute from method
  (initialized). You may also invoke method with specific arguments.
* **Read other class, property and method metadata**: you may read class name,
  namespace, short name, parent class, and so on.
* **Cached by default**: all metadata read operations are cached by default, so 
  subsequent reads are very fast. Cache may be configured to use different
  backends (in-memory, filesystem, redis, memcached, etc).
* **Symfony ready**: if you are using Symfony framework, you may use
  ``runopencode/metadata-bundle`` package which registers metadata reader as a
  service in your service container. It also provides you with possibility of 
  defining cache backend using configuration.

If you are not sure how and when to use this library, check out
`inspiration examples <inspiration.html>`_ for some ideas.

Documentation for integration with Symfony framework is available in dedicated 
:doc:`document<../../bundles/metadata-bundle/index>`.

Important notes
---------------

This library does not replaces reflection API, as this library only considers 
class members with attributes. On top of that, members without attributes as
well as those which are overridden in inheritance tree are ignored by this
library.

Stated above is explained in more details in :doc:`dedicated document<reading-metadata>`.



