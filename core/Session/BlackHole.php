<?php
/**
 * Defines the T_Session_BlackHole class.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Session driver that simply discards any data.
 *
 * This session driver simply discards any data that is passed it. It is useful when
 * running command line install scripts, etc. in that no session is maintained.
 *
 * @package core
 */
class T_Session_BlackHole implements T_Session_Driver
{

    /**
     * Discards data.
     *
     * @param mixed $data  data
     * @return T_Session_Driver  fluent interface
     */
    function save($data) { }

    /**
     * Retrieves an empty array.
     *
     * @return array  data
     */
    function get()
    {
        return array();
    }

    /**
     * Discard any filters (no data is saved).
     *
     * @param T_Filter_Reversable $filter
     * @return T_Session_Driver  fluent interface
     */
    function attachFilter(T_Filter_Reversable $filter)
    {
        return $this;
    }

    /**
     * No regeneration required.
     *
     * @return T_Session_Driver  fluent interface
     */
    function regenerate() {}

    /**
     * Cannot destroy, as no data maintained.
     *
     * @return T_Session_Driver  fluent interface
     */
    function destroy() {}

}