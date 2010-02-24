<?php
/**
 * Defines the T_Form_MultiStep class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a multistep form.
 *
 * @package forms
 */
class T_Form_MultiStep extends T_Form_Post
{

    /**
     * Form steps.
     *
     * @var T_Form_Step[]
     */
    protected $steps = array();

    /**
     * Whether the form is completed.
     *
     * @var bool
     */
    protected $is_complete = false;

    /**
     * Is prev action available?
     *
     * @var bool
     */
    protected $is_prev_action = null;

    /**
     * Create Multistep form.
     *
     * @param string $alias  element alias
     * @param string $label  element label
     */
    function __construct($alias,$label)
    {
        $this->alias = (string) $alias;
        $this->label = (string) $label;
    }

    /**
     * Adds a child to the composite.
     *
     * @param T_CompositeLeaf $child  child to add
     * @param string $key  optional key to refer to composite.
     * @return T_Form_MultiStep  fluent interface
     */
    function addChild(T_CompositeLeaf $child,$key=null)
    {
        if ($child instanceof T_Form_Step) {
            if (!isset($key)) $key = $child->getAlias();
            $this->steps[$key] = $child;
            reset($this->steps); // keep array pointer at start
            $this->init();
            return $this;
        } else {
            return parent::addChild($child,$key);
        }
    }

    /**
     * Whether the current object has any children.
     *
     * @return bool  whether there are any children
     */
    function isChildren()
    {
        return (count($this->children)+count($this->steps))>0;
    }

    /**
     * Gets an array of step labels.
     *
     * This is an array wher the keys are the labels, and the values are booleans
     * which indicate whether the current step is active.
     *
     * @return array
     */
    function getStepLabels()
    {
        $labels = array();
        $steps = $this->steps; // preserve internal position
        foreach ($steps as $s) {
            $labels[$s->getLabel()] = (current($this->steps)===$s);
        }
        return $labels;
    }

    /**
     * Accept a visitor.
     *
     * @param T_Visitor $visitor  visitor object
     * @return T_Form_Group  fluent interface
     */
    function accept(T_Visitor $visitor)
    {
        $name = explode('_',get_class($this));
        array_shift($name);
        $method = 'visit'.implode('',$name);
        $visitor->$method($this);
        if ($visitor->isTraverseChildren() && $this->isChildren()) {
            $visitor->preChildEvent();
            if ($cur=current($this->steps)) $cur->accept($visitor);
            foreach ($this->children as $child) {
        	   $child->accept($visitor);
            }
            $visitor->postChildEvent();
        }
        return $this;
    }

    /**
     * Gets an array of actions.
     *
     * @return array
     */
    function getActions()
    {
        $actions = $this->action;
        if (!$this->is_prev_action) unset($actions['prev']);
        return $actions;
    }

    /**
     * Validates the form.
     *
     * @param T_Cage_Array $source  source data
     * @return T_Form_Group  fluent interface
     */
    function validate(T_Cage_Array $source)
    {
        // validate normal portion of form
        parent::validate($source); // validate basic form
        if (count($this->steps)==0) return $this;

        // populate and validate previous history
        $history = $this->search('history');
        if ($history && $history->isPresent() && $history->isValid()) {
            $cage = new T_Cage_Array($history->getValue());
        } else {
            $cage = new T_Cage_Array(array());
        }
        foreach ($this->steps as $c) {
            $c->validate($cage);  // always validate... as this is required
                                  // to make sure optional steps are accounted
                                  // for OK in the validation.
        }

        // work out where we are now, and validate this step submission
        $seek = $this->search('seek');
        if ($seek && $seek=$seek->getValue()) {
            reset($this->steps);
            do {
                if (strcmp(key($this->steps),$seek)===0) break;
            } while (next($this->steps));
            if (!current($this->steps)) reset($this->steps); // not found
        }
        $cur = current($this->steps);
        $cur->validate($source);

        // if we're going backwards, the form submission has to be valid, but
        // the current step doesn't (as we'll come back to it!)
        if (parent::isValid() && $this->isAction('prev')) {
            if (false===prev($this->steps)) reset($this->steps);
                 // ^ reset if no prev
        }
        if (parent::isValid() && $cur->isValid() && $this->isAction('forward')) {
            if (false===next($this->steps)) {
                reset($this->steps);
                do {
                    $cur = current($this->steps);
                    if ( $cur && !$cur->isValid() ) {
                        // problem at this step, break here and display. We
                        // can
                        break;
                    }
                } while (next($this->steps));
                if (!current($this->steps)) {
                    reset($this->steps);
                    $this->is_complete = true;
                }
                // ^ reset if got to end unless there is an element that
                //   is not present/or is invalid.
            } else {
                // we've moved forward using a standard forward step to a new
                // area. We need to clear errors on this step from previous
                // cross-form navigation, and the user is about to re-complete
                // it.
                current($this->steps)->clearError();
            }

        }
        $this->init();
          // ^ prepare form in possible new position, with possible new form
          //   data (whether it is present/not valid/etc.)
        return $this;
    }

    /**
     * Whether any elements in form are present.
     *
     * @return bool
     */
    function isPresent()
    {
        $steps = $this->steps; // preserve internal array pointer
        foreach ($steps as $s) {
            if ($s->isPresent()) return true;
        }
        return parent::isPresent();
    }

    /**
     * Whether the form is valid.
     *
     * In this case, this checks whether the entire form is valid -- i.e. it
     * checks that all the steps are present if they contain required content,
     * and checks whether the form has been labelled as complete -- i.e. the
     * user is happy with all the data.
     *
     * @return bool  is valida and complete?
     */
    function isValid()
    {
        $valid = parent::isValid();
        if ($valid && $this->isPresent()) {
            // if the form is present, all steps with required content must
            // be present and valid...
            $steps = $this->steps;
            foreach ($steps as $s) {
                if (!$s->isValid()) $valid=false;
            }
            // ... and the user must have marked this form as complete, i.e.
            // they are not still trying to edit it!
            if ($valid && !$this->is_complete) $valid = false;
        }
        return $valid;
    }

    /**
     * Search for element.
     *
     * @param string $alias  alias to search for
     * @return bool|T_Form_Input  element required or false if not found
     */
    function search($alias)
    {
        $element = parent::search($alias);
        if ($element) return $element;
        $children = $this->steps; // leave original pointer intact
        foreach ($children as $c) {
            $element = $c->search($alias);
            if ($element) return $element;
        }
        return false;
    }

    /**
     * Initialise the form to display the current step.
     *
     * @return void
     */
    protected function init()
    {
        $cur = current($this->steps);
        if (!$cur) return;

        // parse prev/next steps
        $steps = $this->steps;
        $prev = false;
        $next = false;
        $found = false;
        foreach ($steps as $s) {
            if (!$found && $s===$cur) {
                $found = true; continue;
            }
            if (!$found) $prev = $s;
            if ($found && false==$next) $next = $s;
        }

        // setup forward label
        if ($next) {
            $label = 'Next: '.$next->getLabel();
        } else {
            $label = $this->getLabel();
        }
        if (isset($this->action['forward'])) {
            $this->action['forward']->setLabel($label);
        } else {
            $forward = new T_Form_Button('forward',$label);
            $this->addAction($forward);
        }

        // *always* setup prev action (needed to detect prev actions)
        //
        // NB: The prev action is always setup and validated internally. It
        //     is hidden in external getActions() calls by the is_prev_action
        //     flag. This is necessary to detect prev action when a salted form
        //     is validated.
        if ($this->is_prev_action = (bool) $prev) {
            $label = 'Back to '.$prev->getLabel();
        } else {
            $label = 'Back';
        }
        if (isset($this->action['prev'])) {
            $this->action['prev']->setLabel($label);
        } else {
            $back = new T_Form_Button('prev',$label);
            $this->addAction($back);
        }

        // save history for other form steps form
        $data = new T_Form_Export;
        $child = $this->steps; // leave original pointer intact
        foreach ($child as $f) {
            if ($f===$cur) continue;
            if ($f->isPresent()) $f->accept($data);
        }
        if ($history=$this->search('history')) {
            $history->setValue($data->getData());
        } else {
            $field = new T_Form_Hidden('history',$data->getData());
            $field->attachFilter(new T_Filter_ToUrlQuery);
            $this->addChild($field);
        }

        // set current seek point
        if ($seek=$this->search('seek')) {
            $seek->setValue(key($this->steps));
        } else {
            $field = new T_Form_Hidden('seek',key($this->steps));
            $this->addChild($field);
        }

    }

}