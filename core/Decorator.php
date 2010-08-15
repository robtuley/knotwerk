<?php
/**
 * A decorator.
 *
 * Provides an skeleton decorator implementation. A decorator is simply a
 * "transparent" object that is wrapped around a core object to add additional
 * functionality.
 *
 * <?php
 * $date = new T_Decorator(new T_Date(17,2,1980));
 * // can use $date just like you would use a normal date object, but the
 * // decorator can add or override function calls.
 * ?>
 *
 * There are two actions that decorators make difficult that need to be addressed
 * by extra class methods (defined in the T_Decorated interface). Firstly, if
 * you have a decoratored object, you need to be able to query whether it is of a
 * certain type (instanceof). This should include the base target object and its
 * decorators.
 *
 * <?php
 * $date = new T_Decorator(new T_Date(17,2,1980));
 * if ($date->isA('T_Date')) {
 *     // do something
 * }
 * ?>
 *
 * To aid the process when code is unsure whether an object is T_Decorated an
 * _isA($obj,$name) global helper method is defined.
 *
 * The other task that becomes difficult with decorated objects is to get the
 * classname of the base object; useful for dependency injection containers etc.
 * for this, use the getClass method to get the classname of the base target
 * object.
 *
 * <?php
 * $date = new T_Decorator(new T_Date(17,2,1980));
 * echo $date->getClass(); // T_Date
 * ?>
 *
 * @package core
 */
class T_Decorator implements T_Decorated
{

    protected $target;

    /**
     * Create decorator with a target.
     *
     * @param T_Decorated $target
     */
    function __construct(T_Decorated $target)
    {
        $this->target = $target;
    }

    /**
     * Get the classname of the decorated object.
     *
     * @return string  classname
     */
    function getClass()
    {
        return $this->target->getClass();
    }

    /**
     * Whether the object is of a particular type.
     *
     * @return bool
     */
    function isA($name)
    {
        return ($this instanceof $name) || $this->target->isA($name);
    }

    /**
     * Pass any unhandled calls through to the decorated target.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    function __call($method,$args)
    {
        $out = call_user_func_array(array($this->target,$method),$args);
        // if the function has a fluent interface, i.e. is returning the
        // target object, we need to return it correctly wrapped in the
        // decorators it has.
        if ($out === $this->target) $out = $this;
        return $out;
    }

}
