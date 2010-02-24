<?php
/**
 * Defines the T_Form_Hidden class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a hidden input.
 *
 * @package forms
 */
class T_Form_Hidden implements T_Form_Input
{

    /**
     * Element alias.
     *
     * @var string
     */
    protected $alias;

    /**
     * value.
     *
     * @var string
     */
    protected $value;

    /**
     * Error exception.
     *
     * @var T_Form_Error
     */
    protected $error = false;

    /**
     * Fieldname salt.
     *
     * @var string
     */
    protected $salt = false;

    /**
     * Fieldname hashing filter.
     *
     * @var T_Filter_RepeatableHash
     */
    protected $hash = false;

    /**
     * Reversable filters.
     *
     * @var T_Filter_Reversable[]
     */
    protected $filters = array();

    /**
     * Whether element is present or not.
     *
     * @var bool
     */
    protected $is_present = false;

    /**
     * Array of attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Create hidden element.
     *
     * @param string $alias  element alias
     * @param mixed $value  hidden value
     */
    function __construct($alias,$value)
    {
        $this->alias = (string) $alias;
        $this->value = $value;
        if (strlen($alias)==0) {
            throw new InvalidArgumentException('zero length alias');
        }
    }

    /**
     * Gets the value.
     *
     * @return mixed  value of hidden field
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of the hidden input.
     *
     * @param mixed $value
     * @return T_Form_Hidden  fluent interface
     */
    function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get field value.
     *
     * @param function $f  optional filter to apply
     * @return  string
     */
    function getFieldValue($f=null)
    {
        $value = $this->value;
        foreach ($this->filters as $filter) {
            $value = _transform($value,$filter);
        }
        return _transform($value,$f);
    }

    /**
     * Gets the alias.
     *
     * @return string alias
     */
    function getAlias()
    {
        return $this->alias;
    }

    /**
     * Sets the alias.
     *
     * @return T_Form_Hidden  fluent interface
     */
    function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Search for element with a particular name.
     *
     * @param string $alias  alias to search for
     * @return bool|T_Form_Hidden  element required or false if not found
     */
    function search($alias)
    {
        if (strcmp($alias,$this->alias)===0) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Add a filter to apply.
     *
     * @param T_Filter_Reversable $filter
     * @return T_Form_Hidden  fluent interface
     */
    function attachFilter(T_Filter_Reversable $filter)
    {
        $this->filters[] = $filter;
        return $this;
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

    /**
     * Whether the field is submitted in a particular array cage.
     *
     * @param T_Cage_Array $source  source array to check
     * @return bool  whether hidden value is submitted
     */
    function isSubmitted(T_Cage_Array $source)
    {
        return $source->exists($this->getFieldname());
    }

    /**
     * Validate hidden input.
     *
     * @param T_Cage_Array $source  source array to validate
     * @return T_Form_Hidden  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        $this->error = false;
        $this->is_present = $this->isSubmitted($source);
        if (!$this->is_present) {
            $this->setError();
            return $this;
        }
        // check matches checksum
        $cage = $source->asScalar($this->getFieldname());
        $checksum = $this->getChecksumFieldname();
        if ($source->exists($checksum)) {
            $provided = $source->asScalar($checksum)->uncage();
            $should_be = $this->toChecksum($cage->uncage());
            if (strcmp($provided,$should_be)!==0) {
                $this->setError();
                return $this;
            }
        } else {
            $this->setError();
            return $this;
        }
        // apply filters
        $value = $cage->uncage();
        try {
            foreach (array_reverse($this->filters) as $filter) {
            	$value = $filter->reverse($value);
            }
        } catch (T_Exception_Filter $e) {
            $this->error = new T_Form_Error($e->getMessage());
            return $this;
        }
        // store value
        $this->value = $value;
        return $this;
    }

    /**
     * Whether the element is valid.
     *
     * @return bool  whether the element is valid
     */
    function isValid()
    {
        return $this->error === false;
    }

    /**
     * Gets the error that has occurred.
     *
     * @return bool|T_Form_Error  error experienced, or false if no error
     */
    function getError()
    {
        return $this->error;
    }

    /**
     * Clears any errors (includes children).
     *
     * @return T_Form_Hidden  fluent interface
     */
    function clearError()
    {
        $this->error = false;
        return $this;
    }

    /**
     * Whether the element has been submitted.
     *
     * @return bool  if the element is submitted
     */
    function isPresent()
    {
        return $this->is_present;
    }

    /**
     * Sets the fieldname salt.
     *
     * @param string $salt  salt to use for this field
     * @return T_Form_Hidden  fluent interface
     */
    function setFieldnameSalt($salt,T_Filter_RepeatableHash $hash)
    {
        $this->salt = (string) $salt;
        $this->hash = $hash;
        return $this;
    }

    /**
     * Gets the fieldname of this element.
     *
     * @return string  fieldname
     */
    function getFieldname()
    {
        if ($this->salt && $this->hash) {
            return 'c'._transform($this->alias.$this->salt,$this->hash);
        } else {
            return $this->alias;
        }
    }

    /**
     * Gets the checksum fieldname.
     *
     * @return string
     */
    function getChecksumFieldname()
    {
        $fieldname = $this->alias.'_checksum';
        if ($this->salt && $this->hash) {
            return 'c'._transform($fieldname.$this->salt,$this->hash);
        } else {
            return $fieldname;
        }
    }

    /**
     * Gets the checksum value.
     *
     * @return string
     */
    function getChecksumFieldValue($f=null)
    {
        $value = $this->toChecksum($this->getFieldValue());
        return _transform($value,$f);
    }

    /**
     * Accept a visitor.
     *
     * @param T_Visitor $visitor  visitor object
     */
    function accept(T_Visitor $visitor)
    {
        $name   = explode('_',get_class($this));
        array_shift($name);
        $method = 'visit'.implode('',$name);
        $visitor->$method($this);
    }

    /**
     * Converts a value to a checksum.
     *
     * @param string $value
     * @return string  checksum
     */
    protected function toChecksum($value)
    {
        if ($this->hash && $this->salt) {
            return _transform($value.$this->salt,$this->hash);
        } else {
            return sha1($value);
        }
    }

    /**
     * Set the hidden input into error state.
     *
     * @return void
     */
    protected function setError()
    {
        $msg = 'A technical error occurred during the submission, please try again';
        $this->error = new T_Form_Error($msg);
    }

    /**
     * Sets an attribute on a particular member.
     *
     * @param string $name  attribute name
     * @param mixed  $value  attribute value
     * @return T_Form_Element  fluent
     */
    function setAttribute($name,$value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Gets the value of an attribute.
     *
     * @param string $name   attribute name
     * @return mixed
     */
    function getAttribute($name)
    {
        return array_key_exists($name,$this->attributes) ? $this->attributes[$name] : null;
    }

    /**
     * Gets the values of all set attributes.
     *
     * @return array   attribute name=>value pairs
     */
    function getAllAttributes()
    {
        return $this->attributes;
    }

}
