==========
Motivation
==========

For most of the use cases ORMs and query builders are perfectly fine tools for 
handling data retrieval within project. However, in certain scenarios, such as 
reporting with complex filtering where many tables are joined by fields that are
not necessarily connected related through referential integrity (foreign keys),
raw SQL is still the best tool for the job.

In examples bellow, common issues are presented in projects using query builders
or raw SQL when queries are complex.

Mixing PHP and SQL
------------------

Mixing PHP and SQL feels so unnatural. 

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Repository;

   use Doctrine\DBAL\Connection;
   use Doctrine\DBAL\Types\Types;

   final readonly class ReportRepository 
   {
       public function __construct(private Connection $connection) { /* noop */ }

       public function getReport(): iterable
       {
           $sql = <<<SQL

           SELECT
        
           T.field_1 AS f1,
           T.field_2 AS f2,
               
           # ...
               
           X.field_42 AS f42,
                       
           # ...
                       
           N.field_n AS fn
                       
           FROM 
                       
           table_name T
                       
           INNER JOIN table_name_2 T2 ON (T.id = T2.t1_id)
                       
           INNER JOIN table_name_3 T3 ON (T2.id = T3.t2_id)
                        
           # Even more joins...
                       
           WHERE
                       
           T.create_at >= :from
                       
           # Even more WHERE conditions...
    
           SQL;

           return $this->connection->executeQuery($sql, [
               'from' => $from
           ], [
               'from' => Types::DATE_IMMUTABLE
           ])->fetchAllAssociative();
       }
   }

One would expect for PHP code to be placed into PHP files, while SQL code into 
SQL files. Hence, we separate views (template) files from business logic, but
somehow, SQL was left behind. As it was hard for us to maintain files with mixed
HTML and PHP, mixed SQL and PHP is not an exception as well.

Building SQL query with string concatenation
--------------------------------------------

Even if you can tolerate SQL code within PHP classes, building complex SQL 
queries based on business logic is just painful and almost impossible to reason
about, nor to maintain it.

.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Repository;

   use Doctrine\DBAL\Connection;
   use Doctrine\DBAL\Types\Types;

   final readonly class ReportRepository 
   {
       public function __construct(private Connection $connection) { /* noop */ }

       public function getReport(Criteria $criteria): iterable
       {
           $sql = 'SELECT field_1.A AS foo_a, field_2.A AS bar_a';
        
           if ($criteria->b) {
               $sql .= ', field_1.B as foo_b';
           }
   
           if ($criteria->c) {
               $sql .= ', field_1.C as foo_c';
           }
           
           $sql .= ' FROM A ';
   
           if ($criteria->b) {
               $sql .= ' INNER JOIN B ON B.id = A.id ';
           }
   
           if ($criteria->c) {
               $sql .= ' INNER JOIN C ON C.id = A.id';
           }
           
           $sql .= ' WHERE 1 ';
           
           if ($criteria->b) {
               $sql .= ' AND B.field_n = :criteria_b ';
           }
   
           if ($criteria->c) {
               $sql .= ' AND C.field_n = :criteria_c ';
           }
   
           return $this->connection->execute($sql, [
               'criteria_b' => $criteria->b,
               'criteria_c' => $criteria->c,
           ])->fetchAllAssociative();
       }
   }

With just two criteria parameters which determine if tables will be joined or
not and/or how ``WHERE`` statement will look like, you have unmaintainable mess.

Truth to be told, object oriented approach and builder pattern solves this 
problem to some extent.

Building SQL query using query builders
---------------------------------------

Query builders do resolve some of the above mentioned issues. You do not deal
with SQL within PHP files. Instead, you have object oriented approach to 
building queries by invoking PHP functions which configures pieces of your query
through builder. That makes somewhat easier to build queries which shape depends
on some business logic.

However, even then, complex queries will require code which is hard to reason
about and maintain. Errors in final SQL query are not obvious as order of 
adding, modifying or removing pieces of query through builder can be arbitrary. 
Per example, selection can be added at the end of the building process, which 
brakes the usual mental model when dealing with SQL.


.. code-block:: php
   :linenos:

   <?php

   declare(strict_types=1);

   namespace App\Repository;

   use Doctrine\DBAL\Connection;
   use Doctrine\DBAL\Types\Types;

   final readonly class ReportRepository 
   {
       public function __construct(private Connection $connection) { /* noop */ }

       public function getReport(Criteria $criteria): iterable
       {
           $query = $this->connection->createQueryBuilder();
           
           $query->from('A', 'A');
           
           if ($criteria->b) {
               $query->join('A', 'B', 'B', 'A.id = B.id');
               $query->addSelect('field_1.B AS foo_b');
               $query->andWhere('B.field_n = :criteria_b');
               $query->setParameter('criteria_b', $criteria->b);
           }
   
           if ($criteria->c) {
               $query->join('A', 'C', 'C', 'A.id = C.id');
               $query->addSelect('field_1.C AS foo_c');
               $query->andWhere('C.field_n = :criteria_c');
               $query->setParameter('criteria_c', $criteria->c);
           }

           $subQuery = $this->connection->createQueryBuilder();
           $subQuery->from('A', 'A_sub');
           $subQuery->addSelect('field_x.A_sub AS field_x');
           $subQuery->where('A_sub.field_n = :criteria_sub');
           $subQuery->andWhere('A_sub.id = A.id');
           
           $query->addSelect('field_1.A AS foo_a, field_2.A AS bar_a');
           $query->andWhere(\sprintf('EXISTS (%s', $subQuery->getSQL()));
           
           $query->setParameter('criteria_sub', 'foo');
           
           $query->addSelect('field_1.A AS foo_a, field_2.A AS bar_a');
   
           return $this->connection->executeQuery($query->getSQL(), $query->getParameters())->fetchAllAssociative();
       }
   }

Utilization of database experts
-------------------------------

SQL is, more or less, universal language used in almost all applications that 
uses relational DBMS regardless of programming language. It is a critical 
resource which is often a source of bad application performances.

When mixing PHP and SQL, or using query builders, utilization of database 
experts is almost impossible. They need to use a language (PHP) and/or query 
builder to understand how query is built in order to be able to help you. 

Simply put, you can not send them a query and database and ask them to find and 
fix an issue, you have to be actively involved in process of debugging and 
optimisation.

