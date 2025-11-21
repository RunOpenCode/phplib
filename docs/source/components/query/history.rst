=======
History
=======

.. _runopencode/query-resources-loader-bundle: https://github.com/RunOpenCode/query-resources-loader-bundle/blob/master/docs/introduction.md
.. _Twig: https://twig.symfony.com

To fully leverage raw SQL queries and statements in PHP projects, more then a 
decade ago `runopencode/query-resources-loader-bundle`_ was created. General 
idea was to separate PHP code from SQL code and to easily load and execute 
queries and statements.

For dynamic queries, `Twig`_ is used as a template which enables integrating 
business logic into a final query.

During years and years of improvements of the original bundle, some commonly 
requested features were lacking. Major rewrite occurred in version 8 where 
middleware arhitecture was introduced. However, bundle API was lacking and PHP
improved on itself so requirement for full rewrite was more then obvious.

A goals of the rewrite of the original library
----------------------------------------------

* **Removal of the vendor lock-in** by implementing framework as framework 
  agnostic library and providing Symfony bundle separately. Since general idea
  of the library is valid outside of Symfony's ecosystem as well, separating
  library from Symfony would allow library to be used in other frameworks as 
  well as in plain PHP projects.
* **Improvement of API** by keeping public API footprint extremely small (i.e.
  only query/statement is required argument for executor, 
  ``$executor->query('@app/query.sql')``), while through utilization of variadic
  arguments every aspect of execution could be configured as needed.
* **Separation of query and statement calls** as they produce different types of
  results.
* **Support for common middlewares out-of-the-box**, such as:
   * **Caching** of query results with support for static and dynamic tags.
   * Redirecting execution to **read replica** with fallback strategy support.
   * **Retrying** failed queries and statements due to deadlocks.
   * **Type-casting** of records to targeted PHP types.
   * **Logging of slow queries** based on developer defined criteria on query 
     level.
   * Improvement of **error handling** and exception model as well.
   * Providing better **developer experience** by monitoring and notifying about 
     errors in execution logic and misconfigurations.
* Decoupling configuration of executor adapter from configuration of transaction 
  scope.

Bundle ``runopencode/query-resources-loader-bundle`` lacked support for 
profiling, which is crucial for getting insight into execution process, 
especially when it comes to middleware based arhitecture.
