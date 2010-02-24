<?php
/**
 * Contains the class T_View_Csv.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Csv view of structured data.
 *
 * @package views
 */
class T_View_Csv implements T_View
{

    /**
     * Data to view as CSV.
     *
     * @var array
     */
    protected $data;

    /**
     * Create CSV view.
     *
     * @param array $data
     */
    function __construct(&$data)
    {
        $this->data = $data;
          // ^ cannot use ref here as ref is invalid in a foreach
    }

    /**
     * Outputs array as csv staright to buffer.
     *
     * @return string  file contents
     */
    function toBuffer()
    {
        $first = true;
        foreach ($this->data as $line) {
            if (!$first) echo EOL;
            if (is_array($line)) {
                echo implode(',',array_map(array($this,'escape'),$line));
            } else {
                echo $this->escape($line);
            }
            $first = false;
        }
        return $this;
    }

    /**
     * Outputs array as csv.
     *
     * @return string  file contents
     */
    function __toString()
    {
        ob_start();
        $this->toBuffer();
        return ob_get_clean();
    }

    /**
     * Escapes a string value for csv.
     *
     * @param string $value
     * @return string
     */
    protected function escape($value)
    {
        $value = str_replace('"','\\"',$value);
        return '"'.$value.'"';
    }

}