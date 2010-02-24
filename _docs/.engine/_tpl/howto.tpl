<?php
/**
 * Howto container.
 *
 * @param T_Template_File $this->content  primary content
 * @param SimpleXMLElement $this->map
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();
$this->buffer($this->content);
?>

<? $this->placeholder('sidebar')->append(); ?>

<div class="box first">
	<h2>How-Tos</h2>
	<? $this->partial('map_list',array('map'=>$this->map,
					                   'section'=>'howto')); ?>
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
   $this->placeholder('features')->append(); ?>

<p>Had enough of an introduction?</p>
<ul>
    <li><a href="<?= $this->url('download'); ?>">Download
        <span>Get stuck straight into a copy of the code</span>
        </a>
    </li>
    <li><a href="<?= $this->url('howto'); ?>">Reference
	         <span>Browse the code reference</span>
        </a>
    </li>
    <li><a href="<?= $this->url('qa'); ?>">Quality Assurance
        <span>What checks are in place that this all actually works?</span>
        </a>
    </li>
</ul>
<? $this->placeholder('features')->stop(); ?>
