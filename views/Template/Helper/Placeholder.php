<?php
/**
 * Contains the T_Template_Helper_Placeholder.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Template placeholder helper.
 *
 * @package views
 */
class T_Template_Helper_Placeholder implements T_Template_Helper_Filter
{

    /**
     * Placeholders.
     *
     * @var T_Template_Helper_PlaceholderItem[]
     */
    protected $holders = array();

    /**
     * Get a placeholder.
     *
     * @return T_Template_Helper_PlaceholderItem
     */
    function get($name)
    {
        if (!isset($this->holders[$name])) {
            $this->holders[$name] = new T_Template_Helper_PlaceholderItem();
        }
        return $this->holders[$name];
    }

    /**
     * Start of output buffering for view.
     */
    function start()
    {
        ob_start(array($this,'filter'));
    }

    /**
     * Output buffering is complete.
     */
    function complete()
    {
        ob_end_flush();
    }

    /**
     * Filter the output to search and replace content signatures.
     *
     * @param string $buffer
     * @return string
     */
    function filter($buffer)
    {
        $search = $replace = array();
        foreach ($this->holders as $h) {
            $search[] = $h->__toString();
            $replace[] = $h->getContent();
        }
        return str_replace($search,$replace,$buffer);
    }

}
