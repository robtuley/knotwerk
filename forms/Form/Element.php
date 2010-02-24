<?php
/**
 * Defines the T_Form_Element class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a user input element.
 *
 * @package forms
 */
abstract class T_Form_Element implements T_Form_Input
{

    /**
     * Element alias.
     *
     * @var string
     */
    protected $alias;

    /**
     * Element label.
     *
     * @var string
     */
    protected $label;

    /**
     * Default value.
     *
     * @var string
     */
    protected $default = null;

    /**
     * Clean user input value.
     *
     * @var mixed
     */
    protected $clean;

    /**
     * Array of element filters.
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Error exception.
     *
     * @var T_Form_Error
     */
    protected $error;

    /**
     * Whether we are expecting a scalar (alternative is an array).
     *
     * This will depend on the type of input we are dealing with. A textbox
     * input will expect a scalar (the default), whereas a checkbox set will
     * expect an array.
     *
     * @var bool
     */
    protected $as_scalar = true;

    /**
     * Whether element is present or not.
     *
     * @var bool
     */
    protected $is_present;

    /**
     * Whether input is required.
     *
     * @var bool
     */
    protected $is_required = true;

    /**
     * Whether to redisplay an invalid submission.
     *
     * @var bool
     */
    protected $redisplay_invalid = true;

    /**
     * Whether to redisplay a valid submission.
     *
     * @var bool
     */
    protected $redisplay_valid = true;

    /**
     * Fieldname salt.
     *
     * @var string
     */
    protected $salt = false;

    /**
     * Fieldname hashing function.
     *
     * @var T_Filter_RepeatableHash
     */
    protected $hash = false;

    /**
     * A text note to help explain the field.
     *
     * @var string|T_Template_File
     */
    protected $help = null;

    /**
     * Array of attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Create form element.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     */
    function __construct($alias,$label)
    {
        $this->alias = (string) $alias;
        $this->label = (string) $label;
        if (strlen($alias)==0 || strlen($label)==0) {
            throw new InvalidArgumentException('zero length alias or label');
        }
        $this->reset(); /* reset validation affected parameters */
    }

    /**
     * Gets the clean user input or null if none.
     *
     * @param T_Filter  optional filter
     * @return mixed  filtered user input
     */
    function getValue($filter=null)
    {
        return _transform($this->clean,$filter);
    }

    /**
     * Gets the default input.
     *
     * @param function $f  optional filter to apply
     * @return mixed  default input
     */
    function getDefault($f=null)
    {
        if ($this->as_scalar) {
            $default = null;
        } else {
            $default = array();
        }
        if ($this->redisplay_invalid && !$this->isValid()) {
            $default = $this->default;
        } elseif ($this->redisplay_valid && $this->isValid()) {
            $default = $this->default;
        }
        return _transform($default,$f);
    }

    /**
     * Set default.
     *
     * @param mixed $value default value.
     * @return OKT_FormElement  fluent interface
     */
    function setDefault($value)
    {
        $this->default = $value;
        return $this;
    }

    /**
     * Add a filter to apply.
     *
     * @param function $filter
     * @return T_Form_Element  fluent interface
     */
    function attachFilter($filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Removes a filter.
     *
     * @param function $filter  filter to remove
     * @return T_Form_Element  fluent interface
     */
    function removeFilter($filter)
    {
        foreach ($this->filters as $key => $f) {
        	if ($f === $filter) {
        	    unset($this->filters[$key]);
        	    return $this;
        	}
        }
        throw new InvalidArgumentException('filter not attached');
    }

    /**
     * Whether the field is submitted in a particular array cage.
     *
     * @param T_Cage_Array $source  source array to check
     * @return bool  whether a non-zero length value has been submitted
     */
    function isSubmitted(T_Cage_Array $source)
    {
        $submitted = $source->exists($this->getFieldname());
        if ($submitted) {
            if ($this->as_scalar) {
                $cage = $source->asScalar($this->getFieldname());
                $submitted = strlen($cage->uncage()) > 0;
            } else {
                $cage = $source->asArray($this->getFieldname());
                $submitted = count($cage->uncage())>0;
            }
        }
        return $submitted;
    }

    /**
     * Whether to redisplay invalid submissions (true by default).
     *
     * @param bool $yesno  whether to redisplay invalid submissions
     * @return T_Form_Element  fluent interface
     */
    function redisplayInvalid($yesno=true)
    {
        $this->redisplay_invalid = (bool) $yesno;
        return $this;
    }

	/**
     * Whether to redisplay valid submissions (true by default).
     *
     * @param bool $yesno  whether to redisplay valid submissions
     * @return T_Form_Element  fluent interface
     */
    function redisplayValid($yesno=true)
    {
        $this->redisplay_valid = (bool) $yesno;
        return $this;
    }

    /**
     * Whether the field data is redisplayed after a valid submission.
     *
     * @return bool
     */
    function isRedisplayValid()
    {
        return $this->redisplay_valid;
    }

    /**
     * Validate element user input source.
     *
     * @param T_Cage_Array $source  source array to validate
     * @return T_Form_Element  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        $this->reset();
        $this->is_present = $this->isSubmitted($source);
        /* quit if not present, checking that is present if required */
        if (!$this->is_present) {
            if ($this->is_required) {
                $this->error = new T_Form_Error('is missing');
            } else {
                /* if it is NOT present, and permitted to be not present,
                 * we need to clear any default value from before. */
                $this->clearDefault();
            }
            return $this;
        }
        /* retrieve caged value */
        if ($this->as_scalar) {
            $cage = $source->asScalar($this->getFieldname());
        } else {
            $cage = $source->asArray($this->getFieldname());
        }
        /* if there is a possibility that the default might be needed,
           it needs to be stored. */
        if ($this->redisplay_invalid || $this->redisplay_valid) {
            try {
                $this->setDefault($cage->uncage());
                  // using setDefault() means any attempt to submit values not
                  // in options array for radio or checkbox bubbles to a
                  // InvalidArgumentException error.
            } catch (InvalidArgumentException $e) {
                $this->clearDefault();
            }
        }
        /* now validate input by filtering */
        try {
            foreach ($this->filters as $filter) {
            	$cage = $cage->filter($filter);
            }
        } catch (T_Exception_Filter $e) {
            $this->error = new T_Form_Error($e->getMessage());
            return $this;
        }
        /* value has been filtered clean, and is valid */
        $this->clean = $cage->uncage();
        return $this;
    }

    /**
     * Resets result of validation.
     *
     * @return void
     */
    protected function reset()
    {
        $this->error = false;
        $this->clean = null;
        $this->is_present = false;
    }

    /**
     * Clears the default value.
     */
    protected function clearDefault()
    {
        if ($this->as_scalar) {
            $this->default = null;
        } else {
            $this->default = array();
        }
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
     * Set the error.
     *
     * @param $error T_Form_Error  error experienced
     * @return T_Form_Element  fluent interface
     */
    function setError(T_Form_Error $error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Clears any errors.
     *
     * @return T_Form_Element  fluent interface
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
     * Sets the submission as optional.
     *
     * @return T_Form_Element  fluent interface
     */
    function setOptional()
    {
        $this->is_required = false;
        return $this;
    }

    /**
     * Sets the submission as required.
     *
     * @return T_Form_Element  fluent interface
     */
    function setRequired()
    {
        $this->is_required = true;
        return $this;
    }

    /**
     * Whether the element is required or not.
     *
     * @return bool whether the element is required or not.
     */
    function isRequired()
    {
        return $this->is_required;
    }

    /**
     * Sets the fieldname salt.
     *
     * @param string $salt  salt to use for this field
     * @param T_Filter_RepeatableHash $hash  repeatable hash function
     * @return T_Form_Element  fluent interface
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
     * @return T_Form_Input  fluent interface
     */
    function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Get element label.
     *
     * @param function $f  filter to apply
     * @return string  element label
     */
    function getLabel($f=null)
    {
        return _transform($this->label,$f);
    }

    /**
     * Set element label.
     *
     * @param string $label  label
     * @return T_Form_Element  fluent interface
     */
    function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Search for element with a particular name.
     *
     * @param string $alias  alias to search for
     * @return bool|T_Form_Element  element required or false if not found
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
     * Gets the available composite object (null in this case).
     *
     * @return null  no composite available
     */
    function getComposite()
    {
        return null;
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
     * Get element help text.
     *
     * @param function $f  filter to apply
     * @return string  element label
     */
    function getHelp($f=null)
    {
        return _transform($this->help,$f);
    }

    /**
     * Set element help text.
     *
     * @param string $help  help
     * @return T_Form_Element  fluent interface
     */
    function setHelp($help)
    {
        $this->help = $help;
        return $this;
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
