<?php
/**
 * Contains the T_Template_Helper_PlaceholderItem.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Template placeholder helper item.
 *
 * @package views
 */
class T_Template_Helper_PlaceholderItem implements T_View
{

    /**
     * Capture status.
     */
    const APPEND = 1,
          PREPEND = 2,
          REPLACE = 3;

    /**
     * Content.
     *
     * @var string
     */
    protected $content = null;

    /**
     * Signature.
     *
     * @var string
     */
    protected $signature;

    /**
     * Capture status.
     *
     * @var int
     */
    protected $status = false;

    /**
     * Create signature.
     */
    function __construct()
    {
        $this->signature = '@@'.uniqid(rand(),true).'@@';
    }

    /**
     * Start capturing in append mode.
     *
     * @return T_Template_Helper_PlaceholderItem
     */
    function append()
    {
        return $this->start(self::APPEND);
    }

    /**
     * Start capturing in prepend mode.
     *
     * @return T_Template_Helper_PlaceholderItem
     */
    function prepend()
    {
        return $this->start(self::PREPEND);
    }

    /**
     * Start capturing in replace mode.
     *
     * @return T_Template_Helper_PlaceholderItem
     */
    function capture()
    {
        return $this->start(self::REPLACE);
    }

    /**
     * Start capturing in specified mode.
     *
     * @return T_Template_Helper_PlaceholderItem
     */
    protected function start($status)
    {
        if (false!==$this->status) {
            throw new RuntimeException("Placeholder already buffering");
        }
        $this->status = $status;
        ob_start();
        return $this;
    }

    /**
     * Stop capturing.
     *
     * @return T_Template_Helper_PlaceholderItem
     */
    function stop()
    {
        if (false===$this->status) {
            throw new RuntimeException("Placeholder is not buffering");
        }
        $text = ob_get_clean();
        if ($this->status==self::APPEND) {
            $this->content .= $text;
        } elseif ($this->status==self::APPEND) {
            $this->content = $text.$this->content;
        } else {
            $this->content = $text;
        }
        $this->status = false;
        return $this;
    }

    /**
     * Push signature to buffer.
     */
    function toBuffer()
    {
        echo $this->signature;
        return $this;
    }

    /**
     * Get signature as string.
     *
     * @return string
     */
    function __toString()
    {
        return $this->signature;
    }

    /**
     * Gets the placeholder content.
     *
     * @return string
     */
    function getContent()
    {
        return $this->content;
    }

}
