=================
Development setup
=================

To set up your development environment for contributing to this project, you 
need to have installed:

* Docker and Docker Compose.
* Git with global ``.gitignore`` file configured. You can find a recommended 
  ``.gitignore_global`` template file within this project with the instructions 
  on how to use it.
* A code editor of your choice (e.g., VSCode, PHPStorm).
* Pythin 3.x installed on your machine.
* ``pip`` package manager for Python.

Project uses ``Python`` and ``Pipenv`` to manage development environment setup,
while ``Docker`` is used to containerize services required for development and
testing.

Assuming that you have forked and cloned the repository, navigate to the project 
root directory and run the following commands:

.. code-block:: bash

    pipenv shell
    bin/run.py --verbose

This is all what is needed to set up your development environment. You can now 
start developing your features and fixes.

Project structure
-----------------

You will notice that project structure contains several directories and files. 
Here is a brief overview of the most important ones:

* ``bin/`` - This directory contains various command-line scripts to help with 
  development tasks, such as running development environment, attaching to the
  development container, shutting down environment, etc.
* ``build/`` - This directory contains build-related files, such as this 
  documentation, PHPUnit reports, infections report, etc.
* ``docker/`` - This directory contains Dockerfiles with installed software and 
  configurations for development and testing environments.
* ``docs/`` - This directory contains the documentation source files.
* ``monorepo/`` - This directory contains files related to monorepo management.
  They are used internally by the release managers.
* ``src/`` - This directory contains the source code of the project. Libraries
  are organized in subdirectories within this directory. In ``Components`` you 
  may find reusable PHP libraries, while in ``Bundles`` you may find Symfony 
  bundles that integrate those libraries into Symfony applications. Tests are 
  contained within component/bundle directories, within ``Tests/`` subdirectory.
* ``var/`` - This directory contains various temporary files created during
  development and testing, such as logs, cache files, database files, etc.
* ``vendor/`` - Contains all project dependencies installed via Composer. Do 
  note that this directory is created after running the development environment
  setup commands and dependencies are installed automatically.

Commands
--------

To facilitate development, several command-line scripts are provided in the 
``bin/`` directory. Here are some of the most commonly used commands:

* ``bin/run.py`` - Starts the development environment using Docker Compose. 
  There are two option flags available:

   * ``--verbose`` or ``-v`` - Enables verbose output for debugging purposes.
   * ``--skip-install`` or ``-s`` - Skips installation of vendor dependencies.

* ``bin/attach.py`` - Attaches to the running development container defined in 
  ``compose.yaml`` file. There are two option flags available:

   * ``--service`` - Allows you to specify which service to attach to. By 
     default, it attaches to the ``php.local`` service.
   * ``--command`` - You may specify a command to run within the container 
     instead of attaching to a shell. By default, it attaches to a shell using 
     ``bin/bash``.

* ``bin/shutdown.py`` - Stops the development environment and removes any 
  spawned container. There is one option flag available:

   * ``--verbose`` or ``-v`` - Enables verbose output for debugging purposes.

* ``bin/ci.py`` - Runs the continuous integration tasks, such as running tests,
  running static analysis tools, etc. In general, this script is executed on CI
  servers, so prior to submitting your changes, make sure that all checks pass
  by running this command locally. There are several option flags available:

   * ``--verbose`` or ``-v`` - Enables verbose output for debugging purposes.
   * ``--skip-install`` or ``-s`` - Skips installation of vendor dependencies.
   * ``--teardown`` or ``-t`` - Teardown environment after CI tasks are done.

* ``bin/composer.py`` - Instead of attaching to the container, and running 
  composer scripts from within, you may use this command to run composer scripts
  directly from your host machine. Arguments passed to this command are 
  forwarded to ``composer`` command within the container.

* ``bin/docs.py`` - Builds the documentation using Sphinx. There is one option 
  flag available:

   * ``--watch`` or ``-w`` - Starts file watcher and rebuilds and refreshes 
     documentation as soon as change is detected.

Mental model
------------

This project setup used Docker compose to spawn several containers that provide
services required for development and testing. Idea is to share exact same
environment between all developers and CI servers, so that "it works on my
machine" problem is avoided.

Through Python scripts, common tasks are automated, such as starting the 
environment, attaching to containers, running tests, etc. This way, developers 
do not need to remember complex Docker commands, but rather use simple Python 
scripts to perform common tasks.

Do remember that all development is done within the ``php.local`` container, 
so when you attach to the container, you will be in a shell within that 
container. All PHP commands, such as running tests, installing dependencies, 
etc., are done within that container. Of course, if you are lazy, you may use
the provided Python scripts to run those commands from your host machine, but
under the hood, they are executed within the container.

FAQ
---

These are some of the most frequently asked questions regarding development 
setup and usage.


How to run PHPUnit, PHPStan, PHP Code Style Fixer, Infections, Rector?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Run the following commands from your host machine terminal:

.. code-block:: bash

    bin/composer.py run phpunit
    bin/composer.py run phpstan
    bin/composer.py run phpcs
    bin/composer.py run infections
    bin/composer.py run rector


How to add new Composer dependency?
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Run the following command from your host machine terminal:

.. code-block:: bash

    bin/composer.py req [vendor]/[package]



    



