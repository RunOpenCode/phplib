===============
Query component
===============

Mixing PHP and SQL code does not seams right. Object oriented approach of 
building queries is appropriate for simpler queries, however, when things get
complicated debugging and optimization makes things hard.

.. _Twig: https://twig.symfony.com

This library enables you to execute queries from their own separate SQL files. 
With the help of `Twig`_ building complex query is a breeze.

ORM's are not silver bullet, some use cases are better handled by executing raw
SQL. The very purpose of this library is to make utilisation of raw SQLs within
PHP project easy and maintainable, where applicable.

Features
--------

* **Store all your queries in separate** files (``*.sql``, ``*.sql.twig``, 
  etc.), in your project directory or any other directory or directories that 
  you want to use.
* **Execute queries with one line of code** simply by invoking 
  ``$executor->query('@app/my_query.sql.twig')``.
* **Full integration with Doctrine Dbal** out of the box, while other adapters 
  may be added with ease too.
* **Build complex queries using Twig** based on passed variables and parameters 
  and control structures supported by Twig.
* **Full support for transactions** and distributed transactions as well. 
  Doctrine Dbal adapter supports specifying isolation level for individual 
  transaction, query and statement.
* **Caching** supported out of the box with possibility to tag cache items based
  on returned resultset.
* **Use database replica** on demand, per example, for queries which generate 
  various reports. Disable replica in development/test environment.
* **Middleware driven arhitecture** allows you to extend behaviour of this
  library and control how your queries are processed and executed.
* **Symfony ready** via dedicated bundle.

Table of Contents
-----------------

.. toctree::
   :maxdepth: 1

   motivation
   history

Quick example
-------------

A simple example of using this library for executing a query is given code 
example below.

.. code-block:: php
   :linenos:

   <?php 

   namespace App\User\Repository;
   
   use RunOpenCode\Component\Query\Cache\CacheIdentity;
   use RunOpenCode\Component\Query\Contract\ExecutorInterface;
   use RunOpenCode\Component\Query\Doctrine\Dbal\Options;
   use RunOpenCode\Component\Query\Doctrine\Parameters\Named;
   use RunOpenCode\Component\Query\Replica\FallbackStrategy;
   use RunOpenCode\Component\Query\Replica\Replica;

   final readonly class UserRepository
   {
       public function __construct(private ExecutorInterface $executor)
       {
           /* noop */
       }
   
       public function getReport(Criteria $criteria): iterable
       {
           return $this->executor->query(
               '@user/list_users.sql.twig',
               Options::readCommitted(),
               new Named()
                   ->string('first_name', $criteria->firstName)
                   ->string('last_name', $criteria->lastName)
                   ->boolean('active', $criteria->active)
                   ->integer('limit', $criteria->limit)
                   ->integer('offset', $criteria->offset),
               CacheIdentity::static(
                   key: $criteria->getCacheKey(),
                   tags: $criteria->getCacheTags(),
                   ttl: 3600,
               ),
               new Replica(
                   connection: 'cache_database',
                   fallback: FallbackStrategy::Primary,
               )
           );
       }
   }

In example above, we are executing query defined in ``@user/list_users.sql.twig``
file. We are setting connection isolation level to ``READ_COMMITED`` and passing
named parameters to the query.

We want to utilize cache for result set, therefore, we are providing cache 
identity to executor with cache key and cache tags built inside criteria object.
Query will be executed if result set is not already in cache.

Instead of using primary connection, we would like to use read replica. However,
if replica fails, we will fallback to primary connection and try again.





