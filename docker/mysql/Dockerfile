FROM mysql/mysql-server:latest

RUN chown -R mysql:mysql /var/lib/mysql

ADD init.sql /docker-entrypoint-initdb.d/init.sql

WORKDIR /
