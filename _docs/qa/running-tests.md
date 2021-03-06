Running the Unit Tests
======================

Once you've downloaded the code you need to test it on your environment to make sure it all operates correctly. The library is covered by a suite of unit tests which can be executed from the command line using the test.php script in the root directory.

    shell> cd path/to/library/root
    shell> php test.php

Configuring your Test Environment
---------------------------------

The first time the tests are executed a default configuration file called unit.xml is created in the root library directory (the template for this file is `/unit/template.xml`). By default the test suite just runs SQLite queries (which can be done in-memory), and skips MySQL tests, AWS tests, etc. To configure your testing (e.g. to add a MySQL server) update the XML document.

Executing Partial Tests
-----------------------

Executing the test.php script without any arguments executes all the available tests. If you want to execute partial tests you can use the -p switch to specify package names, and/or the -c switch to directly execute class tests:

    shell> # execute tests for packages core and db
    shell> php test.php -p core -p db

    shell> # execute tests for just the T_Image_Gd class
    shell> php test.php -c T_Image_Gd
