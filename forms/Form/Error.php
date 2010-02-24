<?php
/**
 * Contains the class T_Form_Error.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * User Input Error.
 *
 * @package forms
 */
class T_Form_Error
{

    /**
     * Error message.
     *
     * @var string
     */
    protected $message;

    /**
     * Create error.
     *
     * @param string $message
     */
    function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get error message.
     *
     * @return string error message
     */
    function getMessage()
    {
        return $this->message;
    }

}