Using Dependency Injection
==========================

This article briefly introduces a coding technique called "dependency injection" (DI) as it is heavily used throughout the knotwerk library. The technique is not complicated, but it is worth making sure the concept is clearly understood.

What is it?
-----------

"Dependency Injection" (DI) is a technique to reduce coupling between code by elevating a classes' dependencies to arguments in the constructor.

    <?php
    interface Db { /* ... */ }
    class DbConnection implements Db { /* ... */ }
    class Country
    {
      function __construct($code,$name) { /* ... */ }
    }

    // WITHOUT DI:

    class CountryGateway
    {
      static function getByCode($code)
      {
        $db = DbConnection::getInstance();
        $row = $db->query(/* ... */)->fetch();
        return new Country($row[0],$row[1]);
      }
    }

    $ctry = CountryGateway::getByCode('GB');

    // WITH DI

    class CountryGateway
    {
      protected $db;
      function __construct(Db $db)
      {
        $this->db = $db;
      }
      function getByCode($code)
      {
        $row = $this->db->query(/* ... */)->fetch();
        return new Country($row['code'],$row['name']);
      }
    }

    $gateway = new CountryGatway(new DbConnection());
    $ctry = $gateway->getByCode('GB');
    ?>

Without using DI the database connection is typically a global singleton, and The CountryGateway has a concrete dependency on the DbConnection class. Any code thats uses CountryGatway will also have a concrete dependency on that class as CountryGateway::getByCode() is a static method. All these hardcoded dependencies reduce flexibility: what if we want to use a different DB? What if we want to use a different Country class that includes language?

By using DI, the database connection is passed into the constructor, and the CountryGateway concrete dependency is reduced to an interface (or none at all if no type-hinting is used on the constructor argument). The CountryGateway class is no longer static, as it is designed to be instantiated and passed around into other classes as a dependency.

### Pros

* Reduces concrete coupling between code.
* Non-static methods are much easier to extend and override in child classes (although the addition of late static binding to PHP 5.3 goes some way towards already solving this problem).
* Class exposes it's dependencies explicitally in the constructor signature.

### Cons

* Can bloat constructor argument list.
* Usage results in longer code: e.g. have to instantiate gateway, then use it!
* Need to keep track of and pass around all the required dependencies.

Using a DI Container
--------------------

In an attempt to address the 'cons' of dependency injection, this library uses a simple DI container (the T_Factory_Di class). The aim of a DI container is to automatically track and wire together the dependencies for top level usage so the coder does not have to worry about them. It usually needs to be configured (e.g. "for the interface Db, always use the class dbConnection"), but can then automatically populate the constructor signature from type hints. 

    <?php
    // WITH DI CONTAINER
    class Container
    {
      function like($class,$args) { /* ... */ }
      function willUse($class) { /* ... */ }
    }

    class CountryGateway
    {
      protected $db;
      function __construct(Db $db)
      {
        $this->db = $db;
      }
      function getByCode($code)
      {
        $row = $this->db->query(/* ... */)->fetch();
        return new Country($row['code'],$row['name']);
      }
    }

    $di = new Container();
    $di->willUse('DbConnection');
    $ctry = $di->like('CountryGateway')
               ->getByCode('GB');
    ?>

The Container as a Factory
--------------------------

By using DI we have removed the coupling between CountryGateway and DbConnection objects, and made CountryGateway non-static so it is easy to extend. If we wanted to use a different country object we could simply:

    <?php
    class CountryWithLang extends Country
    {
      function __construct($code,$name,$lang) { /* ... */ }
    }
    class CountryWithLangGateway extends CountryGateway
    {
      function getByCode($code)
      {
        $row = $this->db->query(/* ... */)->fetch();
        return new CountryWithLang(/* .. */);
      }
    }

    $di = new Container();
    $di->willUse('DbConnection')
       ->willUse('CountryWithLangGateway');
       // ^ config container to use new gateway
    $ctry = $di->like('CountryGateway')
               ->getByCode('GB');
    ?>

An alternative to this approach is to add more flex into the initial gateway by passing in a country factory. The beauty of such an approach is that once you are using a DI container it can be used as the default factory and flex can be added very cheaply to your code, perhaps at the expense of intuative instantiation.

    <?php
    // WITH DI CONTAINER & FACTORY
    interface Factory
    {
      function like($class,$args);
    }
    class Container implements Factory
    {
      function like($class,$args) { /* ... */ }
      function willUse($class) { /* ... */ }
    }

    class CountryGateway
    {
      protected $db;
      protected $factory;
      function __construct(Db $db,Factory $factory)
      {
        $this->db = $db;
        $this->factory = $factory;
      }
      function getByCode($code)
      {
        $row = $this->db->query(/* ... */)->fetch();
        return $this->factory->like('Country',$row);
      }
    }

    $di = new Container();
    $di->willUse('DbConnection');
    $ctry = $di->like('CountryGateway')
               ->getByCode('GB');

    // or using an extended object..

    class CountryWithLang extends Country
    {
      function __construct($code,$name,$lang) { /* ... */ }
    }
    $di = new Container();
    $di->willUse('DbConnection')
       ->willUse('CountryWithLang');
    $ctry = $di->like('CountryGateway')
               ->getByCode('GB');
    ?>

Whether you choose to use the DI container as a factory is a case-by-case decision, and flex should only be added where it might be useful!

DI and Singletons
-----------------

A DI container "singleton" can be achieved by passing in a concrete instance on configuration:

    <?php
    $di->willUse(new DbConnection());
    ?>

In this case when a DB object is requested this particular instance will always be returned. The class has no knowledge it is being used as a singleton (and therefore the class is more flexible, you can easily maintain two different DB connections in different containers or in the same container with different class names..).  
