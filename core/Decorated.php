<?php
/**
 * An object that can be decorated.
 *
 * Any object that can be decorated needs to support a few utility methods so
 * it can be queried by type and used in dependency injection containers etc.
 *
 * @package core
 */
interface T_Decorated
{

    /**
     * Gets the classname of the object.
     *
     * @return string
     */
    function getClass();

    /**
     * Whether the object is of a particular type.
     *
     * @param string $class  class or interface name
     * @return bool
     */
    function isA($class);

}