<?php
/**
 * Various form tools used in the demo environment.
 */

class Demo_Form_Xhtml extends T_Form_Xhtml
{

    protected $in_repeated = false;

    function visitFormRepeated(T_Form_Repeated $node)
    {
        $f = new T_Filter_Xhtml;
        $min = max(count($node),$node->getMin()); // show all present, or at least min
        if ($min<1) $min = 1; // display 1 at least when all optional
        $xhtml  = $this->indent.'<li class="repeated"'.
                                    ' title="'._transform($node->getLabel(),$f).'"'.
                                    ' min="'.$min.'"'.
                                    '>'.EOL.
                  $this->indent.'<ol>'.EOL;
        $this->addXhtml($xhtml);
        $this->in_repeated = true;
        $this->addPostCallback($node,array($this,'postFormRepeated'));
    }

    protected function postFormRepeated()
    {
        $this->in_repeated = false;
        $this->addXhtml($this->indent.'</ol>'.EOL.$this->indent.'</li>'.EOL);
    }

    function visitFormGroup(T_Form_Group $node)
    {
        if ($this->in_repeated) {
            $xhtml  = $this->indent.'<li class="group">'.EOL.
                      $this->indent.'<ol>'.EOL;
            $this->addXhtml($xhtml);
            $xhtml = $this->indent.'</ol>'.EOL.$this->indent.'</li>'.EOL;
            $this->addPostXhtml($node,$xhtml);
        }
    }

    protected function preGroup($node)
    {
        $xhtml = $this->indent.'<ol>'.EOL;
        $this->changeIndent(1);
        return $xhtml;
    }

    protected function preLabel($node)
    {
        return $this->indent.'<li>'.EOL;
    }

    protected function postElement($node)
    {
        $xhtml = null;
        if ($help = $node->getHelp()) {
            $f = new T_Filter_Xhtml;
            $xhtml = $this->indent.'<span class="help">'._transform($help,$f).'</span>'.EOL;
        }
        $xhtml .= $this->indent.'</li>'.EOL;
        return $xhtml;
    }

    protected function postGroup($node)
    {
        $this->changeIndent(-1);
        return $this->indent.'</ol>'.EOL;
    }

    protected function preNestedFieldset($node)
    {
        return $this->preLabel($node);
    }

    protected function postNestedFieldset($node)
    {
        return $this->postElement($node);
    }

}
