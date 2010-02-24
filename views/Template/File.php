<?php
/**
 * Contains the T_Template_File class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Template view class.
 *
 * This class forms the basic library template rendering engine. It is
 * possible to expose two different components to a template file: helper
 * functions and attributes.
 *
 *
 * @package views
 * @license http://knotwerk.com/licence MIT
 */
class T_Template_File implements T_View,T_CompositeLeaf
{

    /**
     * Template file path.
     *
     * @var string
     */
    protected $_tpl = false;

    /**
     * Template Helpers.
     *
     * @var array
     */
    protected $_helpers = array();

    /**
     * Parents of current template.
     *
     * @var array
     */
    protected $_parents = array();

    /**
     * Attributes of the view.
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Setup template file.
     *
     * @param string $tpl  template file name
     */
    function __construct($tpl)
    {
        $this->setTemplate($tpl);
    }

    /**
     * Set the actual page template.
     *
     * @param string $tpl  template file
     * @return T_Template_File  fluent interface
     */
    protected function setTemplate($tpl)
    {
        if (!is_readable($tpl)) {
            $msg = "Template file $tpl not found, or not readable.";
            throw new RuntimeException($msg);
        }
        $this->_tpl = $tpl;
        return $this;
    }

    /**
     * Compile template.
     *
     * @return string  filename to include
     */
    protected function _compile()
    {
        if (!is_readable($this->_tpl)) {
            $msg = 'No template file set';
            throw new RuntimeException($msg);
        }
        // default "compilation" simply expands short php tags to
        // long tags. If short tags is on, no compilation is required.
        if (!ini_get('short_open_tag')) {
            $f = new T_Filter_ExpandShortPhpTag();
            return $this->_compileUsingFilter($f);
        }
        return $this->_tpl;
    }

    /**
     * Compiles a template file using a filter.
     *
     * @param mixed  filter
     * @return string  compiled template filename
     */
    function _compileUsingFilter($filter)
    {
        $dir   = new T_File_Dir(T_CACHE_DIR.'template');
        $cache = new T_File_Path($dir->__toString(),
                                 md5($this->_tpl),'php');
        if (!$cache->exists() ||
            ($cache->exists() &&
             $cache->getLastModified()<filemtime($this->_tpl))) {
            $data = @file_get_contents($this->_tpl);
            if ($data === false) {
                $msg = "Error reading template file {$this->_tpl}";
                throw new RuntimeException();
            }
            $data = _transform($data,$filter);
            $swap = new T_File_Swap($cache,'wb');
            $swap->write($data);
            $swap->close();
        }
        return $cache->__toString();
    }

    /**
     * Outputs the view to the output buffer.
     *
     * @return T_Template_File  fluent interface
     */
    function toBuffer()
    {
        // note vars are prefixed with an underscore to try and prevent any
        // clashes with local template vars
        $_filter = $this->getFilters();
        foreach ($_filter as $_f) $_f->start();
        include($this->_compile());
        foreach (array_reverse($_filter) as $_f) $_f->complete();
        return $this;
    }

    /**
     * Render the template output.
     *
     * This method uses the template stream to render the template as an include
     * and return the results as a string.
     *
     * @return string  rendered template string
     */
    function __toString()
    {
        try {
            ob_start();
            $this->toBuffer();
            return ob_get_clean();
        } catch (Exception $e) {
            // exceptions cannot be thrown in __toString magic methods as
            // the PHP compiler cannot recover. Trigger error instead.
            $msg = "[Template error: {$this->_tpl}] ";
            $msg .= "{$e->getMessage()}, line {$e->getLine()}, file {$e->getFile()}";
            trigger_error($msg,E_USER_ERROR);
        }
    }

    /**
     * Adds a template helper.
     *
     * @param mixed $callback  helper callback function
     * @param string $name  template helper name
     * @return T_Template_File  fluent interface
     */
    function addHelper($callback,$name)
    {
        $this->_helpers[$name] = $callback;
        return $this;
    }

    /**
     * Checks whether a helper is available.
     *
     * If the helper is not found in this template instance, the template
     * parents are also queried to see if there is a helper available there.
     *
     * @param string $name  template helper name
     * @return bool  whether the helper is available
     */
    function isHelper($name)
    {
        if (array_key_exists($name,$this->_helpers)) {
            return true; // found in this object
        } else {
            foreach ($this->_parents as $tpl) {
                if ($tpl->isHelper($name)) { return true; } // found in parent
            }
        }
        return false; // not found
    }

    /**
     * Gets the helper object.
     *
     * If the helper object is not found in this template instance, the template
     * parents (if it is nested) are searched to locate the helper. If it is not
     * found in either location, an InvalidArgumentException is thrown.
     *
     * @param string $name  template helper name
     * @return T_Template_Helper  template helper
     * @throws InvalidArgumentException  when no such helper exists
     */
    function getHelper($name)
    {
        if (array_key_exists($name,$this->_helpers)) {
            // helper from this object.
            return $this->_helpers[$name];
        } else {
            foreach ($this->_parents as $tpl) {
                if ($tpl->isHelper($name)) {
                    // helper from parent object.
                    return $tpl->getHelper($name);
                }
            }
        }
        throw new InvalidArgumentException("$name template helper not found.");
    }

    /**
     * Get the template filters.
     *
     * @return T_Template_Helper_Filter[]
     */
    function getFilters()
    {
        $filter = array();
        foreach ($this->_helpers as $h) {
            // a helper can be a filter if it is a closure in itself
            // (via PHP5.3 __invoke()) or an array with first element
            // as an instance of filter.
            if (is_array($h)) $h = _first($h);
            if ($h instanceof T_Template_Helper_Filter) {
                $filter[] = $h;
            }
        }
        return $filter;
    }

    /**
     * Adds a parent template.
     *
     * It is possible for a single template to have multiple parents (i.e. it
     * may be nested within more than one other template).
     *
     * @param T_Template_File $parent  parent to add
     * @return T_Template_File  fluent interface
     */
    function addParent(T_Template_File $parent)
    {
        if (!$this->isParent($parent)) {
            $this->_parents[] = $parent;
        }
        return $this;
    }

    /**
     * Whether a template object is a parent of this template.
     *
     * @param T_Template_File $parent  parent template object
     * @return bool  whether the template is a parent of this one
     */
    function isParent($parent)
    {
        foreach ($this->_parents as $p) {
        	if ($parent === $p) {
        	    return true; // direct parent
        	} else {
        	    if ($p->isParent($parent)) {
        	        return true; // parent of parent
        	    }
        	}
        }
        return false;
    }

    /**
     * Gets the direct parents of this template.
     *
     * @return array  direct parents to this template
     */
    function getParents()
    {
        return $this->_parents;
    }

    /**
     * Add attribute.
     *
     * This method adds a child attribute to the template.
     *
     * @param mixed $value  attribute value
     * @param string $name  attribute name
     */
    function __set($name,$value)
    {
        $this->_children[$name] = $value;
        if ($value instanceof T_Template_File) {
            $value->addParent($this);
        }
    }

    /**
     * Gets the value of an attribute.
     *
     * This method returns the value of a specific data attribute. If the
     * attribute is not found, an exception is thrown.
     *
     * @param string $name  attribute name
     * @return mixed  value of the attribute
     * @throws InvalidArgumentException  when no such attribute exists
     */
    function __get($name)
    {
        if (array_key_exists($name,$this->_children)) {
            // ** hack ** this strange hack is necessary to circumvent the
            // PHP 5.2.0 bug #39449, to ensure we don't return a reference.
            // It is necessary when using foreach constructs on the data
            // (warning results if performed on a reference), but it does
            // mean that cannot do things like $tpl->property[] = 'extra';
            if (is_array($this->_children[$name])) {
        	return (array) $this->_children[$name];
            } else {
            	return $this->_children[$name];
            }
        } else {
            throw new InvalidArgumentException("no $name attribute exists.");
        }
    }

    /**
     * Gets whether an attribute is set.
     *
     * @param string $name  attribute name
     * @return bool  whether the attribute is set or not.
     */
    function __isset($name)
    {
        return array_key_exists($name,$this->_children);
    }

    /**
     * Unsets an attribute.
     *
     * @param string $name  attribute name
     */
    function __unset($name)
    {
        if (array_key_exists($name,$this->_children)) {
           unset($this->_children[$name]);
        }
    }

    /**
     * Gets the available composite object (null in this case).
     *
     * @return null  no composite available
     */
    function getComposite()
    {
        return null;
    }

    // ------ VIEW HELPERS

    /**
     * Sends output to the buffer.
     *
     * @param mixed $output
     * @return void
     */
    function buffer($output)
    {
        if (is_object($output) && ($output instanceof T_View)) {
            $output->toBuffer();
        } else {
            echo $output;
        }
    }

    /**
     * Render a partial template.
     *
     * @param string $name  template name (in parent tpl dir)
     * @param array $params array of parameters for partial
     * @return T_Template_File  fluent interface
     */
    function partial($name,$params=array())
    {
        // try relative path..
        $path = dirname($this->_tpl).'/'.  // match dir & ext
                $name.substr($this->_tpl,strrpos($this->_tpl,'.'));
        if (!file_exists($path)) { // fall back to use absolute path
            $path = $name;
        }
        $tpl = new T_Template_File($path);
        $tpl->addParent($this);
        foreach ($params as $key=>$val) $tpl->$key = $val;
        $tpl->toBuffer();
        return $this;
    }

    /**
     * Render a template as a loop.
     *
     * @param string $name  template name (in parent tpl dir)
     * @param string $key  attribute name
     * @param array $iterator  values to iterate over
     * @param array $params array of parameters for partial
     * @return T_Template_File  fluent interface
     */
    function loop($name,$key,$iterator,$params=array())
    {
        foreach ($iterator as $val) {
            $params[$key] = $val;
            $this->partial($name,$params);
        }
        return $this;
    }

    /**
     * Call a helper.
     *
     * Helpers can be callback function names, class methods by passing
     * in a array of class and function name, or indeed in PHP 5.3 a
     * closure or lambda function variable.
     *
     * @param string $name  name of the helper
     * @param array $args  array of arguments passed to function
     */
    function __call($name,$args)
    {
        return call_user_func_array($this->getHelper($name),$args);
    }

}
