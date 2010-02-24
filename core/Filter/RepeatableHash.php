<?php
/**
 * Contains the T_Filter_RepeatableHash class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Repeatable hash.
 *
 * @package core
 */
class T_Filter_RepeatableHash extends T_Filter_Skeleton
{

    /**
     * Relibably hashes value.
     *
     * @param mixed $value  data to filter
     * @return string  hashed value
     */
    protected function doTransform($value)
    {
        return sha1(serialize($value).php_uname());
          // ^ always returns the same value, but not predictable as
          //   it differs between different setups.
    }

}
