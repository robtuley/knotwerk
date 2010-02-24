<?php
/**
 * Documentation index.
 *
 * @param Package[] $this->packages
 */
$f = new T_Filter_Xhtml();
?>

<h1>Reference</h1>

<p>The library is divided into a number of packages with minimal
   inter-dependencies. A typical deployment technique would be to compile
   the required packages for an application into a single include file.</p>

<? foreach ($this->packages as $p) : ?>
   <h2><a href="<?= $this->url('ref',$p->getAlias()); ?>"><?= $p->getName($f); ?></a></h2>
   <? $p->getDesc(new Filter_AsFormattedText())
        ->accept($desc=new Xhtml_TextPreview(200,$this->root()));
      echo $desc; ?>
<? endforeach; ?>

<? $this->placeholder('sidebar')->append(); ?>

<div class="box first">
<h2>Not sure where to start?</h2>
<p>This area is useful as a reference if you are familar with the
   codebase and know roughly what you are looking for. If you're new to
   the library it's probably easier to
   <a href="<?= $this->url('howto'); ?>">start in the How To section</a>.</p>
</div>

<div class="box">
<h2>Packages</h2>
<ul>
<? foreach ($this->packages as $p) : ?>
   <li><a href="<?= $this->url('ref',$p->getAlias()); ?>"><?= $p->getName($f); ?></a></li>
<? endforeach; ?>
</ul>
</div>

<? $this->placeholder('sidebar')->stop();
   $this->placeholder('features')->append(); ?>

<ul>
    <li><a href="<?= $this->url('howto'); ?>">How To...
        <span>A series of tutorials introduce the library</span>
        </a>
    </li>
    <li><a href="<?= $this->url('download'); ?>">Download
        <span>Get stuck straight into a copy of the code</span>
        </a>
    </li>
    <li><a href="<?= $this->url('qa'); ?>">Quality Assurance
        <span>What checks are in place that this all actually works?</span>
        </a>
    </li>
</ul>

<? $this->placeholder('features')->stop(); ?>
