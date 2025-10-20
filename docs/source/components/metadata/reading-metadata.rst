================
Reading metadata
================

Your starting point is instance of 
``RunOpenCode\Component\Metadata\Contract\MetadataReaderInterface``. Reader 
provides you with methods to:

* Check if class is marked with specific attribute. 
* Get instance of `RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface`` for 
  further processing (i.e. searching for attributes on class, properties and methods).
* Find properties and methods that are marked with specific attributes. Properties
  and methods may be searched separately or together. Each property or method found
  is represented with instance of ``RunOpenCode\Component\Metadata\Contract\PropertyMetadataInterface`` 
  and ``RunOpenCode\Component\Metadata\Contract\MethodMetadataInterface`` respectively.

Class attributes and members resolution
---------------------------------------

When reading metadata from classes, getting attributes from class is simple, only
attributes from the current class are considered, no inheritance is consulted.

However, should you need to read class attributes from parent classes, you may use
``RunOpenCode\Component\Metadata\Contract\ClassMetadataInterface::$parent`` property
to traverse the class hierarchy.

When comes to members (properties and methods), library will resolve members 
navigating the class hierarchy, meaning that members from parent classes will be
considered as well. Rules for resolving members are as follows:

* **Private members**: All private members are considered from all classes in the
  hierarchy. This means that if a parent class has a private property or method,
  it will be included in the results, even though there is a name collision with 
  a child class member.
* **Public and protected members**: Only the members from the most derived class are 
  considered. If a child class overrides a public or protected member from a parent
  class, only the child class member will be included in the results.

This resolution strategy can be explained with the example given below (do note
that example uses properties only for brevity, but the same rules apply to methods 
as well):

.. code-block:: php
   :linenos:
    
    <?php

    declare(strict_types=1);

    use RunOpenCode\Component\Metadata\Attribute as Meta;

    #[Meta\Example('foo')]
    class ParentClass
    {
        #[Meta\Property('foo_parent')]
        private string $privateProperty;

        #[Meta\Property('foo_parent')]
        protected string $protectedProperty;

        #[Meta\Property('foo_parent')]
        public string $publicProperty;
    }

    #[Meta\Example('bar')]
    class ChildClass extends ParentClass
    {
        #[Meta\Property('foo_child')]
        private string $privateProperty;

        #[Meta\Property('foo_child')]
        protected string $protectedProperty; // overrides parent

        #[Meta\Property('foo_child')]
        public string $publicProperty; // overrides parent
    }

By introspecting ``ChildClass``, the following will be resolved:

* Class attributes: ``Meta\Example('bar')`` only.
* Properties:

  * Private properties: Both ``Meta\Property('foo_parent')`` and 
    ``Meta\Property('foo_child')`` will be resolved, as they are private to their
    respective classes.
  * Protected properties: Only ``Meta\Property('foo_child')`` will be resolved,
    as it overrides the parent class property.
  * Public properties: Only ``Meta\Property('foo_child')`` will be resolved,
    as it overrides the parent class property.

Expected results and exceptions
-------------------------------

When using metadata reader methods, do note that methods ``properties()``, 
``methods()`` and ``members()`` will always return arrays, even if no members
were found with requested attribute.

However, methods ``get()``, ``property()``, ``method()`` and ``member()`` may 
throw:

* ``RunOpenCode\Component\Metadata\Exception\NotExistsException`` if there
  is no attribute of requested type found on class or member, or no members
  were found with requested attribute.
* ``RunOpenCode\Component\Metadata\Exception\UnexpectedResultException.php`` if
  there are multiple attributes of requested type found on class, or,
  there are multiple members found with requested attribute.

In order to avoid exceptions, you may use methods that return arrays, and
handle the results accordingly, or use methods ``has()`` to check for existence
of attributes or members with requested attributes.