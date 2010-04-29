<?php
/**
 * Class docs.
 *
 * @param Package $this->package
 * @param ReflectionClass $this->class
 */
$f = new T_Filter_Xhtml();
$docblock = new T_Code_DocBlock($this->class->getDocComment());
$construct = $this->class->getConstructor();
?>

<div class="class-doc">

<h1><?= $f->transform($this->class->getName()); ?>
    <span class="summary"><?= $docblock->getSummary($f); ?></span></h1>

<?= $docblock->getDesc(new Xhtml_DocBlockText($this->root())); ?>

<? // methods (inc __construct).
foreach ($this->class->getMethods() as $method) {
    if ($method->isPublic()) {
        $this->partial('method',array('class'=>$this->class,
                                      'method'=>$method)     );
    }
} ?>

</div><!-- .class-doc -->

<? $this->placeholder('sidebar')->append(); ?>

    <div class="box first">
    <h2><a href="<?= $this->url('ref',$this->package->getAlias()); ?>">
        <?= $this->package->getName($f); ?></a></h2>
    <? if (count($classes=$this->package->getClassnames())>0) : ?>
        <ul>
        <? $base=$this->url('ref',$this->package->getAlias()).'/';
           foreach ($classes as $classname) : ?>
            <li><a href="<?= $base.$f->transform($classname); ?>"><?= $f->transform($classname); ?></a></li>
        <? endforeach; ?>
        </ul>
    <? endif; ?>
    </div>

<? $this->placeholder('sidebar')->stop();
   $this->placeholder('features')->capture(); ?>

<p>Prefer reading code to documentation?
   <a href="<?= $this->url('download'); ?>">Checkout the source using git</a>.
</p>

<? $this->placeholder('features')->stop(); ?>
