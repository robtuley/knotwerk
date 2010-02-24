<?php
/**
 * Defines the T_Form_Address class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates an address input as a fieldset.
 *
 * @package forms
 */
class T_Form_Address extends T_Form_Fieldset
{

    /**
     * Country gateway.
     *
     * @var T_Geo_CountryGateway
     */
    protected $countries = null;

	/**
	 * Address factory.
	 *
	 * @var T_Factory
	 */
	protected $factory;

    /**
     * Create address input.
     *
     * @param string $alias  alias
     * @param string $label  label
     * @param T_Factory  address factory
     */
    function __construct($alias,$label,T_Factory $factory)
    {
        parent::__construct($alias,$label);
		$this->factory = $factory;

        $line_1 = new T_Form_Text($alias.'_line_1','Line #1');
        $line_1->setAttribute('maxlength',200)
               ->setAttribute('size',30);
        $this->addChild($line_1);

        $line_2 = new T_Form_Text($alias.'_line_2','Line #2');
        $line_2->setAttribute('maxlength',200)
               ->setAttribute('size',30)
               ->setOptional();
        $this->addChild($line_2);

        $city = new T_Form_Text($alias.'_city','Town/City');
        $city->setAttribute('maxlength',200)
               ->setAttribute('size',30);
        $this->addChild($city);

        $region = new T_Form_Text($alias.'_state','County/State');
        $region->setAttribute('maxlength',200)
               ->setAttribute('size',30)
               ->setOptional();
        $this->addChild($region);

        $postcode = new T_Form_Text($alias.'_postcode','Postcode');
        $postcode->setAttribute('maxlength',20)
               ->setAttribute('size',10)
               ->setOptional();
        $this->addChild($postcode);
    }

	/**
	 * Set the countries available.
	 *
	 * @param T_Geo_CountryGateway $countries
	 * @param string $default  default country code (e.g. GB)
	 * @return T_Form_Address  fluent interface
	 */
	function setCountries(T_Geo_CountryGateway $countries,$default='GB')
	{
		$this->countries = $countries;

		// add (or overwrite) form element
		$world = $countries->getAll();
        $options = array();
        foreach ($world as $c) {
            $options[$c->getCode()] = $c->getName();
        }
        $country = new T_Form_Select($this->getAlias().'_country','Country');
        $country->setOptions($options)
                ->setDefault($default);
        $this->addChild($country,$this->getAlias().'_country');

		return $this;
	}

    /**
     * Set collection as required.
     *
     * @return T_Form_Group  fluent interface
     */
    function setRequired()
    {
        $alias = $this->getAlias();
        // in this case, line 1 and city are required inputs
        //  (some country addresses do not have a postcode)
        $this->search($alias.'_line_1')->setRequired();
        $this->search($alias.'_city')->setRequired();
		$ctry = $this->search($alias.'_country');
		if ($ctry) $ctry->setRequired();
        return $this;
    }

	/**
     * Set collection as optional.
     *
     * @return T_Form_Group  fluent interface
     */
    function setOptional()
    {
        $alias = $this->getAlias();
        $this->search($alias.'_line_1')->setOptional();
        $this->search($alias.'_city')->setOptional();
		$ctry = $this->search($alias.'_country');
		if ($ctry) $ctry->setOptional();
        return $this;
    }

    /**
     * Whether address is present.
     *
     * In this area country is ignored -- i.e. the fieldset is present only
     * when something *other* than country is available.
     */
    function isPresent()
    {
        $alias = $this->getAlias();
        $fields = array($alias.'_line_1',$alias.'_line_2',$alias.'_city',$alias.'_state',$alias.'_postcode');
        foreach ($fields as $name) {
            if ($this->search($name)->isPresent()) return true;
        }
        return false;
    }

    /**
     * Validate.
     *
     * If fields are present, build address.
     */
    function validate(T_Cage_Array $src)
    {
        $this->clean = null;
        $this->error = false;
        foreach ($this->children as $child) {
            $child->validate($src);
        }
		$alias = $this->getAlias();

		// check address components are present
		if ($this->isPresent() && $this->isValid()) {
            $line_1 = $this->search($alias.'_line_1');
            $city = $this->search($alias.'_city');
            if (!$line_1->isPresent()) {
                $line_1->setError(new T_Form_Error('is missing'));
            }
            if (!$city->isPresent()) {
                $city->setError(new T_Form_Error('is missing'));
            }
        }

		// build address
        if ($this->isPresent() && $this->isValid()) {
			$data = array();
            $data['line1'] = $this->search($alias.'_line_1')->getValue();
            $data['line2'] = $this->search($alias.'_line_2')->getValue();
            $data['city'] = $this->search($alias.'_city')->getValue();
            $data['state'] = $this->search($alias.'_state')->getValue();
            $data['postcode'] = $this->search($alias.'_postcode')->getValue();
            if ($this->countries) {
                $code = $this->search($alias.'_country')->getValue();
                $data['country'] = $this->countries->getByCode($code);
            } else {
                $data['country'] = null;
            }
            $address = $this->factory->like('T_Geo_Address',$data);

            // filter address
            try {
                foreach ($this->filters as $filter) {
                    $address = _transform($address,$filter);
                }
                $this->setValue($address);
            } catch (T_Exception_Filter $e) {
                $this->setError(new T_Form_Error($e->getMessage()));
                return $this;
            }
        } elseif (!$this->isPresent() && $this->isRequired()) {
            $this->setError(new T_Form_Error('is missing'));
        }
        return $this;
    }

    /**
     * Set default.
     *
     * @param T_Geo_Address $addr
     */
    function setDefault($addr)
    {
        $alias = $this->getAlias();
        if (!$addr) {
            $this->search($alias.'_line_1')->setDefault(null);
            $this->search($alias.'_line_2')->setDefault(null);
            $this->search($alias.'_city')->setDefault(null);
            $this->search($alias.'_state')->setDefault(null);
            $this->search($alias.'_postcode')->setDefault(null);
                // country is left with no default
        } else {
            $this->search($alias.'_line_1')->setDefault($addr->getLineOne());
            $this->search($alias.'_line_2')->setDefault($addr->getLineTwo());
            $this->search($alias.'_city')->setDefault($addr->getCity());
            $this->search($alias.'_state')->setDefault($addr->getState());
            $this->search($alias.'_postcode')->setDefault($addr->getPostCode());
            $ctry = $addr->getCountry();
            if ($ctry && $this->countries) {
                $this->search($alias.'_country')->setDefault($ctry->getCode());
            }
        }
    }

    /**
     * Allows sub-portions of address to be accessed by keywords.
     *
     * Access to the sub-portions of the address fields is by:
     * <code>
     * $addr = new T_Form_Address('addr','Address');
     * $addr->line_1;  // line 1
     * $addr->line_2;  // line 2
     * $addr->city;  // city
     * $addr->state;  // state
     * $addr->postcode; // postcode
     * $addr->country; // country (note this may or may not exist)
     * </code>
     *
     * To achieve this, we need to add the alias to any of these incoming string
     * requests accessed in this manner, as the alias is prefixed to prevent clashes
     * with other items.
     *
     * @param string $key  child keyword
     * @return OKT_HttpUrl  the tree sub-portion
     */
    function __get($key)
    {
        $prefixed =$this->getAlias().'_'.$key;
        if (strlen($key) && array_key_exists($prefixed,$this->children)) {
            return $this->children[$prefixed];
        } else {
            return parent::__get($key);
        }
    }

}