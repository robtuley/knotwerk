<?php
/**
 * Defines the T_Decorator class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A decorator object.
 *
 * This provides an skeleton decorator implementation. A decorator is simply an
 * "transparent" object that can be called around a core class to add
 * functionality to an object.
 *
 * <?php
 * $gateway = new T_Decorator(new T_Geo_CountryGateway());
 * // can use $gateway just like you would use a country gateway, but the
 * // decorator can 'add' or override function calls.
 * ?>
 *
 * @package core
 */
class T_Decorator implements T_Transparent
{

    /**
     * Decorator target.
     *
     * @var object
     */
    protected $target;

    /**
     * Create decorator with a target.
     *
     * @param object $target
     */
    function __construct($target)
    {
        $this->target = $target;
    }

    /**
     * Returns the target object underneath.
     *
     * @return object
     */
    function lookUnder()
    {
        if ($this->target instanceof T_Transparent) {
            return $this->target->lookUnder();
        }
        return $this->target;
    }

    /**
     * Pass any unhandled calls through to the decoarted target.
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
