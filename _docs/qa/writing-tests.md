Writing Unit Tests
==================

While reasonable unit test coverage exists for this library code, you will need to write your own tests to cover your own application. The popular PHP testing frameworks are [PHPUnit](http://phpunit.de) and [SimpleTest](http://simpletest.com), both are highly recommended and PHPUnit in particular integrates well with some popular PHP IDEs. To reduce external dependencies, this library contains a lightweight unit testing layer, most of whose functionality is a subset of PHPUnit. It's limitations should be noted (mainly lack of support for Mock objects), but it provides a simple way to manage your tests if you wish to use it.

A Test Case Class
-----------------

Tests are organised into groups, each contained in a class extending `T_Unit_Case`. The convention in this library is to have a separate test class for each class definition, but these classes are just a way of grouping tests and can be organised as you wish. In this class any method starting with 'test' is assummed to be a test case.

    <?php
    // Application Code:
    class Person
    {
        protected $name,$email;
        function __construct($name,$email)
        {
            $this->name = $name;
            $this->email = $email;
        }
        function getEmail()
        {
            return $this->email;
        }
        function getName()
        {
            return $this->name;
        }
        function setEmail($email)
        {
            $this->email = $email;
            return $this;
        }
        function setName($name)
        {
            $this->name = $name;
            return $this;
        }
    }

    // ... Testing Code:
    class Test_Person extends T_Unit_Case
    {
        function testNameAndEmailSetInConstructor()
        {
            $person = new Person('Joe','joe@example.com');
            $this->assertSame('Joe',$person->getName());
            $this->assertSame('joe@example.com',$person->getEmail());
        }
        function testEmailCanBeChanged()
        {
            $person = new Person('Joe','joe@example.com');
            $this->assertSame($person,$person->setEmail('j@ex.com'),'fluent');
            $this->assertSame('j@ex.com',$person->getEmail());
        }
        function testNameCanBeChanged()
        {
            $person = new Person('Joe','joe@example.com');
            $this->assertSame($person,$person->setName('Harry'),'fluent');
            $this->assertSame('Harry',$person->getName());
        }
    }
    ?>

Making Assertions
-----------------

Notice that checks are made using the assertXXXX family of functions that are defined in T_Unit_Case. These are used to check actual results against expected results: usually the first two arguments are used in the comparison, and the third is an optional message describing the assertion. By default, the available assertions are:

+--------------------+----------------------------------------------------------------+
| Method             | Checks that...                                                 |
+--------------------+----------------------------------------------------------------+
| assertSame         | first two args are identical (===)                             |
| assertNotSame      | first two args are not identical (!==)                         |
| assertEquals       | first two args are equal (==)                                  |
| assertNotEquals    | first two args are not equal (!=)                              |
| assertTrue         | first arg is a boolean, and is true                            |
| assertFalse        | first arg is a boolean, and is false                           |
| assertContains     | second arg contains the first arg                              |
| assertNotContains  | second arg does not contain the first arg                      |
| assertSimilarFloat | first two args are similar floats (within a certain tolerance) |
+--------------------+----------------------------------------------------------------+

If you want to extend T_Unit_Case you can create your own general assertions, or indeed just add them as helper metods directly to the test class. Assertion failure is triggered by throwing a T_Exception_AssertFail exception.

    <?php
    class MyTestCase extends T_Unit_Case
    {
        function assertLowerCase($value,$msg='')
        {
            if (strcmp(mb_strtolower($value),$value)!==0) {
                $msg = "$value is not lower case";
                throw new T_Exception_AssertFail();
            }
        }
    }
    class Test_Person extends MyTestCase
    {
        /* ... snip ... */

        // perhaps we've added a business requirement to lowercase
        // email addresses..
        function testEmailAddressAlwaysNormalisedToLowerCase()
        {
            $person = new Person('Joe','JOE@exAMple.com');
            $this->assertLowerCase($person->getEmail());
        }
    }
    ?>

Fixtures
--------

Some unit tests require a certain environment to be created before the test, often common to all the tests in a test case. For example, in all the tests in Test_Person a new Person object is created. This could be refactored into a getPerson() method, or the function setUp() and tearDown() from the T_Unit_Case can be used: these methods are executed before and after any test to create and destroy any "fixtures" that are required for every test.

    <?php
    class Test_Person extends T_Unit_Case
    {
        protected $person;
        function setUp()
        {
            $this->person = new Person('Joe','joe@example.com');
        }
        function testNameAndEmailSetInConstructor()
        {
            $this->assertSame('Joe',$this->person->getName());
            $this->assertSame('joe@example.com',$this->person->getEmail());
        }
        /* + other tests ... */
        function tearDown()
        {
            unset($this->person); // not really needed in this demo
        }
    }
    ?>

The methods setUp and tearDown are executed before *every* test method. If you require a fixture to be setup that can be shared between tests (e.g. a db connection), the methods setUpSuite and tearDownSuite are executed at the start and end of executing the entire test case (these are similar to __construct and __destruct class methods but using them means the test case can be executed more than once by the same script if necessray).

Running Your Test Case
----------------------

Once you've written some test cases for your code you need to run them! The file test.php in the library root directory can be used to run the library tests but you need to write your own script to execute your tests.

Building the Suite
------------------

You may have a number of test cases to execute, and all these need to be gathered into a single T_Unit_Suite using the method addChild().

    <?php
    $suite = new T_Unit_Suite;
    $suite->addChild(new Test_Person)
          ->addChild(new Test_SomethingElse);
    ?>

To help automate the process of including test cases in your test runner, the T_Unit_Directory test suite is provided that searches a directory for test case classes and adds them to the suite.

    /test/dir/
        Person.php  <-- Test_Person class
        Person/
            Email.php  <-- Test_Person_Email class

With the above directory structure:

    <?php
    $suite = new T_Unit_Directory('/test/dir/','Test_');
                                  // class prefix ^
    ?>

Running the Tests
-----------------

Once a test suite has been built, it can be executed using the execute() method.

    <?php
    $suite = new T_Unit_Directory('/test/dir/','Test_');
    $suite->execute();
    ?>

This will execute each of the child test cases or suites, and these will execute all the unit test methods within them. However, although all the tests will be executed, you won't see any visible output unless an observer is registered with the suite. Two in-built observers are available: T_Unit_TerminalDisplay displays the results for command line execution, and T_Unit_XmlLog logs the overall statistics to an XML file.

    <?php
    $suite = new T_Unit_Directory('/test/dir/','Test_');
    $suite->attach(new T_Unit_TerminalDisplay);
    $suite->execute();
    ?>

You can write your own test suite observers that conform to the T_Unit_Observer interface to perform any custom test behaviour that you require.

Reusing Unit Tests and Cases
----------------------------

Sometimes it is useful to be able to execute the same unit test method a number of times with different parameters: a common use-case in this library is testing the same functionality against a number of different DB types. Test cases can be given arguments as long as these arguments are registered using the T_Unit_Case::cycleOn method.

    <?php
    class Test_Person_Gateway extends T_Unit_Case
    {
        function setUpSuite()
        {
            $dbs = array(new Db_Sqlite,new Db_MySQL);
            $this->cycleOn('db',$dbs);
        }
        function testPersonCanBeSavedAndRetrievedByEmail($db)
        {
            $person = new Person('Joe','joe@example.com');
            $gw = new Person_Gateway($db);
            $gw->save($person);
            $this->assertSame($person,$gw->getByEmail('joe@example.com'));
        }
        function testGatewayConformsToTableGatewayInterface()
        {
            $class = new ReflectionClass('Person_Gateway');
            $this->assertTrue($class->isSubClassOf('Table_Gateway'));
        }
        /* + other tests ... */
    }
    ?>

In the example above, an array of different DB connections is created before any tests in it are executed. The T_Unit_Case::cycleOn calls registers this array of parameters to be populated one at a time into any test method with an argument named 'db'. Thus when this case is executed the first test (with a $db arg) is executed twice (once for each value in the db array). Other tests that don't have any args get executed once in the normal way.

Another way to re-use test cases is to leverage inheritance, and this is worth considering when building your tests. For example, if we had a Person class and a CarOwner class that extended it with some extra functionality, our test cases might look something like:

    <?php
    class Test_Person extends T_Unit_Case
    {
        function getPerson()
        {
            return new Person('Joe','joe@example.com');
        }
        function testNameAndEmailSetInConstructor()
        {
            $person = $this->getPerson();
            $this->assertSame('Joe',$person->getName());
            $this->assertSame('joe@example.com',$person->getEmail());
        }
        /* + other tests ... */
    }
    class Test_CarOwner extends Test_Person
    {
        function getPerson()
        {
            return new CarOwner('Joe','joe@example.com');
        }
        /* ... Test_Person tests gets executed on CarOwner .. */
        function testAddCarHasFluentInterface()
        {
            $owner = $this->getPerson();
            $this->assertSame($owner,$owner->addCar(new Car('reg')));
        }
        /* + other tests specific to CarOwner... */
    }
    ?>
