<?php
/**
 * Package index.
 *
 * @param array $this->other
 * @param Package $this->package
 * @param T_Url $this->root
 */
$f = new T_Filter_Xhtml();
?>

<h1><?= $this->package->getName($f); ?> Package</h1>

<? $desc=new Xhtml_Text($this->root());
   $desc->setHeaderAdjustment(1);
   $this->package->getDesc(new Filter_AsFormattedText())
                 ->accept($desc);
   echo $desc; ?>

<? $this->placeholder('sidebar')->append(); ?>

    <div class="box first">
    <h2>Classes</h2>
    <? if (count($classes=$this->package->getClassnames())>0) : ?>
        <ul>
        <? $base=$this->url('ref',$this->package->getAlias()).'/';
           foreach ($classes as $classname) : ?>
            <li><a href="<?= $base.$f->transform($classname); ?>"><?= $f->transform($classname); ?></a></li>
        <? endforeach; ?>
        </ul>
    <? else: ?>
        <p>No PHP classes in this package.</p>
    <? endif; ?>
    </div>

<? $this->placeholder('sidebar')->stop();
   $this->placeholder('features')->capture();
   $others = array();
   foreach ($this->others as $p) {
       $others[] = '<a href="'.$this->url('ref',$p->getAlias()).'">'.$p->getName($f).'</a>';
   }
   $others = implode(', ',$others);
   ?>
<p>Other packages include <?= $others; ?>.</p>
<? $this->placeholder('features')->stop(); ?>
