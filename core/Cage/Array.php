<?php
/**
 * Defines the T_Cage_Array class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Cages insecure data array.
 *
 * This class is used to "cage" insecure data to remind developers that it has
 * to be treated with appropriate care. It is mostly used in conjunction with
 * the superglobal arrays ($_GET,$_POST,etc) to ensure such data is filtered
 * before use.
 *
 * @package core
 */
class T_Cage_Array extends T_Cage_Scalar
{

    /**
     * Exists.
     *
     * @param mixed $key  string or integer array key
     * @return bool  whether or not the key exists in the array
     */
    function exists($key)
    {
        return array_key_exists($key,$this->data);
    }

    /**
     * Retrieve scalar value from caged array.
     *
     * @param mixed $key  array key of data to extract
     * @return T_Cage_Scalar  caged scalar value
     * @throws T_Exception_Cage  if not a scalar or doesn't exist
     */
    function asScalar($key)
    {
        if ( $this->exists($key) && !is_array($this->data[$key]) ) {
            return new T_Cage_Scalar($this->data[$key]);
        } else {
            throw new T_Exception_Cage("$key does not exist, or not a scalar");
        }
    }

    /**
     * Retrieve array value from caged array.
     *
     * @param mixed $key  array key of data to extract
     * @return T_Cage_Array  caged scalar value
     * @throws T_Exception_Cage  if not a scalar or doesn't exist
     */
    function asArray($key)
    {
        if ( $this->exists($key)  && is_array($this->data[$key]) ) {
            return new T_Cage_Array($this->data[$key]);
        } else {
            throw new T_Exception_Cage("$key does not exist, or not an array");
        }
    }

}