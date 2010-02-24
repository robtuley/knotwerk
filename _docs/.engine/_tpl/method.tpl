<?php
/**
 * Method docs.
 *
 * @param ReflectionClass $this->class
 * @param ReflectionMethod $this->method
 */
$f = new T_Filter_Xhtml();
$docblock = new T_Code_DocBlock($this->method->getDocComment());
$params = array();
$return = false;
foreach ($docblock->getTags() as $tag) {
    switch ($tag->getName()) {
        case 'param'  : $params[$tag->getVar()] = $tag; break;
        case 'return' : $return = $tag;
    }
}
?>

<h2><code><?
   if ($return) {
       $desc = $return->getDesc($f);
       if ($desc) echo '<abbr title="'.$desc.'">';
       echo $return->getCombinedType($f);
       if ($desc) echo '</abbr>';
       echo ' ';
   }
   $name = (string) $this->method->getName();
   $is_construct = (strcmp($name,'_'.'_construct')===0);
   if ($is_construct) echo '<span class="keyword">new</span> ';
   echo '<strong>'.$f->transform($is_construct ? $this->class->getName() : $name).'</strong>(';
   $list = '';
   foreach ($this->method->getParameters() as $arg) {
       $name = $arg->getName();
       if (isset($params[$name])) {
           $list .= '<abbr title="'.
                   $params[$name]->getCombinedType($f).' '.
                   ( ($desc=$params[$name]->getDesc($f)) ? ' ('.$desc.')' : '').
                   '">';
       }
       $list .= '$'.$f->transform($name);
       if (isset($params[$name])) $list .= '</abbr>';
       $list .= ',&#8203;'; // soft line break
   }
   if ($list) echo substr($list,0,strlen($list)-8); // remove final ',&#8203;'
?>)</code></h2>

<p class="summary"><?= $docblock->getSummary($f); ?></p>
