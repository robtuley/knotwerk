Using Filters
=============

A filter is an entity that converts something from one state to another, and are supported as arguments on almost all getter methods. A filter can be:

* any valid callback
* in PHP5.3, a lambda function or closure
* a T_Filter object

Getter Support
--------------

Filtering can be supported on a getter method by adding an optional argument and using the _transform() shortcut:

    <?php
    class MyUser
    {
        protected $name;
        function __construct($name)
        {
            $this->name = $name;
        }
        function getName($filter=null)
        {
            return _transform($this->name,$filter);
        }
    }

    $u = new MyUser('Rob');
    echo $u->getName(); // Rob
    echo $u->getName('mb_strtoupper'); // ROB

    $fn = function($val){return mb_substr($val,0,1);};
    echo $u->getName($fn); // R

    $f = new T_Filter_Crypt('mypasswd');
    echo $u->getName($f); // <encrypted string>
    ?>

Defining Filter Objects
-----------------------

Filtering using callbacks or lambda functions is handy, but for more complex and reusable transformations it is often necessary to define a filter object: a class that implements the T_Filter interface (i.e. has a transform() method).

    <?php
    class FirstLetter implements T_Filter
    {
        function transform($val)
        {
            return mb_substr($val,0,1);
        }
    }

    $f = new FirstLetter();
    echo $f->transform('Rob'); // R
    $u = new MyUser('Rob');
    echo $u->getName($f); // R
    ?>

Filter Chains
-------------

Depending on how the filter is used, it often useful to allow the filter to be part of a chain.

    <?php
    $filter = new Filter1(new Filter2);
    $filter->transform('something');
      // filter 2, and then filter 1 is applied
    ?>

Most filters in the library allow a prior filter to be passed into the constructor, and the T_Filter_Skeleton abstract provides a standard template for this functionality.

Reversable Filters
------------------

Filters are normally one-way conversions, but for two-way (i.e. reversable) transformations you can implement the T_Filter_Reversable interface and provide both transform() and reverse() methods.

    <?php
    $f = new T_Filter_Crypt('my-passwd');
    $encrypted = $f->transform('data');
    echo $f->reverse($encrypted); // data
    ?>
