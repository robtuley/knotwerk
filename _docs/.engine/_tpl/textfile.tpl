<?php
/**
 * Text file render.
 *
 * @param T_Text_Plain $this->text  formatted text
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();

// render main content
$xhtml = new Xhtml_Text($this->root());
$this->text->accept($xhtml);
echo $xhtml;
?>

<? $this->placeholder('sidebar')->append(); ?>

<div class="box first">
<h2>Not sure where to start?</h2>
<p>If you're new to the library it's probably best to
   <a href="<?= $this->url('howto'); ?>">start in the How To section</a>.</p>
</div>

<div class="box">
<h2>Want to see the code?</h2>
<p>If you want to poke around the code itself, you can
   <a href="<?= $this->url('download'); ?>">use subversion to grab yourself a copy</a>.</p>
</div>

<div class="box">
<h2>Reference</h2>
<p>If you're already familar with the codebase but simply want to look something up,
   best to <a href="<?= $this->url('ref'); ?>">head over to the code reference</a>.</p>
</div>

<? $this->placeholder('sidebar')->stop();
$this->placeholder('features')->capture();
	$this->partial('feature');
$this->placeholder('features')->stop();
