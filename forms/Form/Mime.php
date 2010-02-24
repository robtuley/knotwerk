<?php
/**
 * Contains the T_Form_Mime class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A form MIME type.
 *
 * This class can be used as a visitor for a form, and estabilishes what the
 * form MIME type is. This is usually 'application/x-www-form-urlencoded',
 * but changes to 'multipart/form-data' if an upload is involved.
 *
 * @package forms
 */
class T_Form_Mime extends T_Mime implements T_Visitor
{

    /**
     * Create default mime type.
     */
    function __construct()
    {
        $this->type = parent::FORM_URL_ENCODED;
    }

    /**
     * Change form MIME type when upload is present.
     *
     * @param T_Form_Upload $node
     */
    function visitFormUpload($node)
    {
        $this->type = parent::FORM_MULTIPART;
    }

    /**
     * Catches irrelevant nodes.
     *
     * @param string $method  method name that has been called (visit..)
     * @param array $arg  array of arguments
     */
    function __call($method,$arg)
    {
        $node = $arg[0];
        if ($node instanceof T_Form_Upload) {
            $this->visitFormUpload($node);
        }
    }

    /**
     * Always traverse children.
     */
    function isTraverseChildren()
    {
        return true;
    }

    /**
     * No child event.
     */
    function preChildEvent() { }

    /**
     * No post child event.
     */
    function postChildEvent() { }

}