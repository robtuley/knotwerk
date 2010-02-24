<?php
/**
 * Contains the T_Form_Xhtml class.
 *
 * @package forms
 * @author Andy Morgan
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A form XHTML renderer.
 *
 * This class can be used as a visitor for a form, and renders the result as a
 * XHTML strict compliant form for data input.
 *
 * @package forms
 */
class T_Form_Xhtml implements T_Visitor
{

    /**
     * XHTML.
     *
     * @var xhtml
     */
    protected $xhtml = '';

	/**
	 * Function callback to execute after closing the element.
	 *
	 * @var array
	 */
	protected $post_callback = array();

    /**
     * Depth of composite.
     *
     * @var int
     */
    protected $depth = 0;

    /**
     * How far to indent the text
     *
     * @var int
     */
    protected $indent = '';

	// LAYUP ELEMENTS

	// Override these methods to generate form layup in your own forms using
	// (un)ordered lists, definition lists, divs, etc.

    /**
     * Piece of code to be executed before the group
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function preGroup($node) { }

    /**
     * Piece of code to be executed after the group
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function postGroup($node) { }

    /**
     * Piece of code to be executed before the label
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function preLabel($node) { }

    /**
     * Piece of code to be executed after the label
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function postLabel($node) { }

    /**
     * Piece of code to be executed before the element
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function preElement($node) { }

    /**
     * Piece of code to be executed after the element
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function postElement($node) { }

    /**
     * Piece of code to be executed before a nested fieldset
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function preNestedFieldset($node) { }

    /**
     * Piece of code to be executed after the nested fieldset
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function postNestedFieldset($node) { }

	// HELPER FUNCTIONS

    /**
     * Escapes output.
     *
     * @return string
     */
    protected function escape($value)
    {
        return _transform($value,new T_Filter_Xhtml);
    }

    /**
     * Generate "required" markup.
     *
     * @param T_Form_Element $node
     * @return string
     */
    protected function getIsRequiredMarkup(T_Form_Element $node)
    {
        return ($node->isRequired()) ? ' <em>required</em>' : null;
    }

    /**
     * Gets the error label markup.
     *
     * @param T_Form_Element $node
     * @return string
     */
    protected function getErrorLabelMarkup(T_Form_Element $node)
    {
        if (!$node->isValid()) {
            $msg =  $this->escape($node->getError()->getMessage());
            return " <strong>$msg</strong>";
        }
        return '';
    }

    /**
     * Increment indent.
     *
     * @param int $step  change indent by a step
     * @return void
     */
    protected function changeIndent($step)
    {
        $indent = '    ';
        if ($step>0) {
            $this->indent .= str_repeat($indent,$step);
        } elseif($step<0) {
            $this->indent = substr($this->indent,0,
                    strlen($this->indent)+strlen($indent)*$step);
        }
    }

    /**
     * Appends XHTML to output.
     *
     * @param string $xhtml
     */
    protected function addXhtml($xhtml)
    {
        $this->xhtml .= $xhtml;
    }

    /**
     * Register some XHTML to be output at after node children.
     *
     * @param T_Form_Input $node
     * @param string $xhtml
     */
    protected function addPostXhtml($node,$xhtml)
    {
        $this->addPostCallback($node,array($this,'addXhtml'),array($xhtml));
    }

    /**
     * Register a callback to execute after closing child.
     *
     * @param string $xhtml
     */
    protected function addPostCallback($node,$callback,$args=array())
    {
        if ($node->isChildren()) {
            $this->post_callback[$this->depth] = array($callback,$args);
        } else{
            call_user_func_array($callback,$args);
        }
    }

    /**
     * Gets the ID from a node.
     *
     * @param T_Form_Input $node
     * @return string  ID text
     */
    protected function getNodeId($node)
    {
        $id = $node->getAlias();
        if ($attrib=$node->getAttribute('id')) $id = $attrib;
        return $id;
    }

	// RENDER METHODS

    /**
     * Render a T_Form_Button.
     *
     * @param T_Form_Button $button  button object to render
     * @return string  XHTML
     */
    protected function renderButton(T_Form_Button $button)
    {
        $xhtml  = $this->indent.'<input type="submit"'.EOL;
        $xhtml .= $this->indent.'       name="'.$button->getFieldname().'"'.EOL;
        $xhtml .= $this->indent.'       class="submit"'.EOL;
        $xhtml .= $this->indent.'       value="'.$this->escape($button->getLabel()).'" />'.EOL;
        return $xhtml;
    }

    /**
     * Creates the label.
     *
     * @param T_Form_Input $node
     * @return string $xhtml
     */
    protected function createLabel(T_Form_Input $node)
    {
        $xhtml  = $this->preLabel($node);
        $xhtml .= $this->indent.'<label for="'.$this->getNodeId($node).'">'.
                        $this->escape($node->getLabel()).
                        $this->getIsRequiredMarkup($node).
                        $this->getErrorLabelMarkup($node).'</label>'.EOL;
        $xhtml .= $this->postLabel($node);
        return $xhtml;
    }

    /**
     * Creates the input element.
     *
     * @param T_Form_Input $node
     * @param string[] $attributes
     * @return string $xhtml
     */
    protected function createInput(T_Form_Input $node,$attributes)
    {
        $xhtml  = $this->preElement($node);
        $xhtml .= $this->indent.'<input ';
        foreach($attributes as $key=>$value) {
            $xhtml .= $key.'="'.$this->escape($value).'"'.EOL.$this->indent.'       ';
        }
        $xhtml = rtrim($xhtml).' /> '.EOL;
        $xhtml .= $this->postElement($node);
        return $xhtml;
    }

    /**
     * Visit a text input node.
     *
     * @param T_Form_Input $node
     */
    function visitFormText(T_Form_Text $node)
    {
        $attributes  = $node->getAllAttributes();
        $attributes += array('type' => 'text',
                             'name' => $node->getFieldname(),
                             'id' => $this->getNodeId($node),
                             'value' => $node->getDefault()
                            );
        $xhtml = $this->createLabel($node).
                 $this->createInput($node,$attributes);
        $this->addXhtml($xhtml);
    }

    /**
     * Visit a password input node.
     *
     * @param T_Form_Input $node
     */
    function visitFormPassword(T_Form_Password $node)
    {
        $attributes  = $node->getAllAttributes();
        $attributes += array('type' => 'password',
                             'name' => $node->getFieldname(),
                             'id' => $this->getNodeId($node),
                             'value' => $node->getDefault()
                            );
        $xhtml = $this->createLabel($node).
                 $this->createInput($node,$attributes);
        $this->addXhtml($xhtml);
    }

    /**
     * Visit a file upload input node.
     *
     * @param T_Form_Upload $node
     */
    function visitFormUpload(T_Form_Upload $node)
    {
        $attributes  = $node->getAllAttributes();
        $attributes += array('type' => 'file',
                             'name' => $node->getFieldname(),
                             'id' => $this->getNodeId($node)
                            );
        $xhtml = $this->createLabel($node).
                 $this->createInput($node,$attributes);
        $this->addXhtml($xhtml);
    }

    /**
     * Visit a hidden input node.
     *
     * @param T_Form_Hidden $node
     */
    function visitFormHidden(T_Form_Hidden $node)
    {
		$xhtml = $this->indent.'<div class="hidden">'.EOL;

        // render value
        $attributes  = $node->getAllAttributes();
        $attributes += array('type' => 'hidden',
                             'name' => $node->getFieldname(),
                             'value' => $node->getFieldValue() );
        $xhtml .= $this->indent.'<input ';
        foreach($attributes as $key=>$value) {
            $xhtml .= $key.'="'.$this->escape($value).'"'.EOL.$this->indent.'       ';
        }
        $xhtml = rtrim($xhtml).' /> '.EOL;

        // render checksum
        $attributes = array('type' => 'hidden',
                            'name' => $node->getChecksumFieldname(),
                            'value' => $node->getChecksumFieldValue() );
        $xhtml .= $this->indent.'<input ';
        foreach($attributes as $key=>$value) {
            $xhtml .= $key.'="'.$this->escape($value).'"'.EOL.$this->indent.'       ';
        }
        $xhtml = rtrim($xhtml).' /> '.EOL;

		$xhtml .= $this->indent.'</div>'.EOL;
        $this->addXhtml($xhtml);
    }

    /**
     * Visit a textarea input node.
     *
     * @param T_Form_TextArea $node
     */
    function visitFormTextArea(T_Form_TextArea $node)
    {
        $attributes  = $node->getAllAttributes();
        $attributes += array('rows' => 5,
                             'cols' => 32,
                             'name' => $node->getFieldname(),
                             'id' => $this->getNodeId($node),
                            );
        $xhtml = $this->createLabel($node);

		// <textarea>
        $xhtml .= $this->preElement($node);
        $xhtml .= $this->indent.'<textarea ';
        foreach($attributes as $key=>$value) {
            $xhtml .= $key.'="'.$this->escape($value).'"'.EOL.$this->indent.'       ';
        }
        $xhtml = rtrim($xhtml).' >'.EOL;
        $xhtml .= $this->escape($node->getDefault());
          $xhtml .= '</textarea>'.EOL;
        $xhtml .= $this->postElement($node);

        $this->addXhtml($xhtml);
    }

    /**
     * Visit the checkbox input.
     *
     * @param T_Form_Checkbox $node
     */
    function visitFormCheckBox(T_Form_Checkbox $node)
    {
        $attr_id = $this->getNodeId($node);

        // open fieldset
        $xhtml  = $this->preNestedFieldset($node);
        $xhtml .= $this->indent.'<fieldset id="'.$attr_id.'">'.EOL;
        $xhtml .= $this->indent.'<legend><span>'.$this->escape($node->getLabel()).
                          $this->getIsRequiredMarkup($node).
                          $this->getErrorLabelMarkup($node).'</span></legend>'.EOL;
        $this->changeIndent(1);

        $xhtml .= $this->preGroup($node);

        // add option list
        $options = $node->getOptions();
        $default = $node->getDefault();
        $i=0;
        foreach ($options as $value => $label) {

            $id = $attr_id.'__'.$i;
            $attributes  = $node->getAllAttributes();
            $attributes += array('type' => 'checkbox',
                                 'name' => $node->getFieldname().'[]',
                                 'id' => $id,
                                 'value' => $value
                                );
            $attributes['id'] = $id; // make sure ID is correct
            if (in_array($value,$default)) {
                $attributes['checked'] = 'checked';
            }

            // label & element
            $xhtml .= $this->preLabel($node);
            $xhtml .= $this->indent.'<label for="'.$id.'">'.
                            $this->escape($label).'</label>'.EOL;
            $xhtml .= $this->postLabel($node);
            $xhtml .= $this->createInput($node,$attributes);

            $i++;
        }

        // close fieldset
        $xhtml .= $this->postGroup($node);
        $this->changeIndent(-1);
        $xhtml .= $this->indent.'</fieldset>'.EOL;
        $xhtml .= $this->postNestedFieldset($node);

        $this->addXhtml($xhtml);
    }

    /**
     * Visit the radio input.
     *
     * @param T_Form_Radio $node  radio input node.
     */
    function visitFormRadio(T_Form_Radio $node)
    {
        $attr_id = $this->getNodeId($node);

        // open fieldset
        $xhtml  = $this->indent.'<fieldset id="'.$attr_id.'">'.EOL;
        $xhtml .= $this->indent.'<legend><span>'.$this->escape($node->getLabel()).
                           $this->getIsRequiredMarkup($node).
                           $this->getErrorLabelMarkup($node).'</span></legend>'.EOL;
        $this->changeIndent(1);

        $xhtml .= $this->preGroup($node);

        // add optiona list
        $options = $node->getOptions();
        $default = $node->getDefault();
        $i=0;
        foreach ($options as $value => $label) {
            $id = $attr_id.'__'.$i;

            $attributes  = $node->getAllAttributes();
            $attributes += array('type' => 'radio',
                                'name' => $node->getFieldname(),
                                'value' => $value
                                );
            $attributes['id'] = $id; // special edge case for id
            if (!is_null($default) && $value==$default) {
                $attributes['checked'] = 'checked';
            }

            // label & element
            $xhtml .= $this->preLabel($node);
            $xhtml .= $this->indent.'<label for="'.$id.'">'.
                            $this->escape($label).'</label>'.EOL;
            $xhtml .= $this->postLabel($node);
            $xhtml .= $this->createInput($node, $attributes);

            $i++;
        }

        // close fieldset
        $xhtml .= $this->postGroup($node);
        $this->changeIndent(-1);
        $xhtml .= $this->indent.'</fieldset>'.EOL;

        $this->addXhtml($xhtml);
    }

    /**
     * Visit select element.
     *
     * @param T_Form_Select $node  select node.
     */
    function visitFormSelect(T_Form_Select $node)
    {
        $attributes  = $node->getAllAttributes();
        $attributes += array('name' => $node->getFieldname(),
                             'id' => $this->getNodeId($node),
                            );
        $xhtml = $this->createLabel($node);

        // create <select>
        $xhtml .= $this->preElement($node);
        $xhtml .= $this->indent.'<select ';
        foreach($attributes as $key=>$value) {
            $xhtml .= $key.'="'.$this->escape($value).'"'.EOL.$this->indent.'       ';
        }
        $xhtml = rtrim($xhtml).' >'.EOL;

        $this->changeIndent(1);

        // render the option list
        $options = $node->getOptions();
        $default = $node->getDefault();
        foreach ($options as $value => $label) {
            $xhtml .= $this->indent.'<option value="'.$this->escape($value).'"';
            if (strcmp($value,$default)===0) {
                $xhtml .= ' selected="selected"';
            }
            $xhtml .= '>'.$this->escape($label).'</option>'.EOL;
        }

        // close <select>
        $this->changeIndent(-1);
        $xhtml .= $this->indent.'</select>'.EOL;
        $xhtml .= $this->postElement($node);

        $this->addXhtml($xhtml);
    }

    /**
     * Visit a fieldset node.
     *
     * @param T_Form_Fieldset $node
     */
    function visitFormFieldset(T_Form_Fieldset $node)
    {
        $attributes  = $node->getAllAttributes();
        $attributes += array('id' => $node->getAlias());
        $node->setAttribute('id',$attributes['id']);

        // <fieldset>
        $xhtml  = $this->indent.'<fieldset ';
        foreach($attributes as $key=>$value) {
            $xhtml .= $key.'="'.$value.'"'.EOL.$this->indent.'       ';
        }
        $xhtml = rtrim($xhtml).' >'.EOL;
        $xhtml .= $this->indent.'<legend><span>'.$this->escape($node->getLabel()).'</span></legend>'.EOL;
        $xhtml .= $this->preGroup($node);
        $this->addXhtml($xhtml);

        // </fieldset>
        $xhtml  = $this->postGroup($node);
        $xhtml .= $this->indent.'</fieldset>'.EOL;
        $this->addPostXhtml($node,$xhtml);
    }

    /**
     * Visit a form.
     *
     * @param T_Form_Container $node
     */
    function visitFormContainer(T_Form_Container $node)
    {
        $forward = $node->getForward();
        $mode = strpos(_end($forward->getPath()),'.')===false ? T_Url::AS_DIR : null;
        $action = $this->escape($forward->getUrl(null,$mode));

        $attributes  = $node->getAllAttributes();
        $attributes += array('action' => $action,
                             'id' => $this->getNodeId($node),
                             'accept-charset' => $node->getCharset(),
                             'enctype' => $node->getMimeString(),
                             'method' => $node->getMethod());

        // <form>
        $xhtml  = $this->indent.'<form ';
        foreach($attributes as $key=>$value) {
            $xhtml .= $key.'="'.$value.'"'.EOL.$this->indent.'       ';
        }
        $xhtml = rtrim($xhtml).' >'.EOL;
        $this->addXhtml($xhtml);

        // <fieldset class="submit">
        $this->changeIndent(1);
        $xhtml  = $this->indent.'<fieldset class="submit">'.EOL;
        $actions = $node->getActions();
        $this->changeIndent(1);
        if (count($actions)==0) {
			$actions[] = new T_Form_Button('submit',$node->getLabel());
        }
		foreach ($actions as $button) {
            $name   = explode('_',get_class($button));
            $method = 'render'.array_pop($name);
            $xhtml .= $this->$method($button);
        }
        $this->changeIndent(-1);

        // </fieldset></form>
        $xhtml .= $this->indent.'</fieldset>'.EOL;
        $this->changeIndent(-1);
        $xhtml .= $this->indent.'</form>'.EOL;

        $this->addPostXhtml($node,$xhtml);
    }

    // "invisible" elements
    function visitFormStep(T_Form_Step $node) { }
	function visitFormGroup(T_Form_Group $node) { }

    /**
     * Pre-Child visitor event.
     */
    function preChildEvent()
    {
        $this->depth++;
        $this->changeIndent(1);
    }

    /**
     * Post-Child visitor event.
     */
    function postChildEvent()
    {
        $this->depth--;
        $this->changeIndent(-1);
        // see if we need to close anything
        if (isset($this->post_callback[$this->depth])){
            call_user_func_array($this->post_callback[$this->depth][0],
								 $this->post_callback[$this->depth][1]);
            unset($this->post_callback[$this->depth]);
        }
    }

    /**
     * Always traverse children.
     *
     * @return bool
     */
    function isTraverseChildren()
    {
        return true;
    }

    /**
     * Catches non-available visit method calls.
     *
     * This catching function tries to route the visit call to an appropriate
     * handler by looking at the class and the class parents.
     *
     * @param string $method
     * @param array $arg
     */
    function __call($method,$arg)
    {
        if (isset($arg[0]) && strncmp($method,'visit',5)===0) {
            $node = $arg[0];
			$class = get_class($node);
			do {
				$bits = explode('_',$class);
				// try full method
				$method = 'visit'.implode($bits);
				if (method_exists($this,$method)) {
					return $this->$method($node);
				}
				// try mapping partial method
				array_shift($bits);
				$method = 'visit'.implode($bits);
				if (method_exists($this,$method)) {
					return $this->$method($node);
				}
			} while ($class=get_parent_class($class));
		}
		throw new BadFunctionCallException("Cannot handle call to $method");
    }

    /**
     * Return XHTML string.
     *
     * @return string
     */
    function __toString()
    {
        return $this->xhtml;
    }

}
