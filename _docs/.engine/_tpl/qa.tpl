<?php
/**
 * QA container.
 *
 * @param T_Template_File $this->content  primary content
 * @param SimpleXMLElement $this->map
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();
$this->buffer($this->content);
?>

<? $this->placeholder('sidebar')->append(); ?>

<? if ($this->report) : ?>
    <div class="box first">
    <? if ($this->green) : ?>
      <p class="success"><a href="<?= $this->report->getUrl($f); ?>">Last test indicates build is stable</a></p>
    <? else : ?>
      <p class="error"><a href="<?= $this->report->getUrl($f); ?>">Last test failed: unstable build!</a></p>
    <? endif; ?>
    </div>
<? endif; ?>

<div class="box<?= $this->report ? '' : ' first'; ?>">
    <h2>QA (aka Testing!)</h2>
    <? $this->partial('map_list',array('map'=>$this->map,
                                       'section'=>'qa')); ?>
</div>

<div class="box">
<h2>Not sure where to start?</h2>
<p>This area is useful once you've
<a href="<?= $this->url('download'); ?>">downloaded the code</a> and are looking
to test it to make sure it is compatible with your environment. If you're completely new to the library it's probably easier to
<a href="<?= $this->url('howto'); ?>">start in the How To section</a>.</p>
</div>

<div class="box">
<h2>Want to see the code?</h2>
<p>If you want to poke around the code itself, you can
   <a href="<?= $this->url('download'); ?>">use git to grab yourself a copy</a>.</p>
</div>

<? $this->placeholder('sidebar')->stop();
   $this->placeholder('features')->append(); ?>

<p>Had enough about testing?</p>
<ul>
    <li><a href="<?= $this->url('download'); ?>">Download
        <span>Get the code so you can test it..</span>
        </a>
    </li>
    <li><a href="<?= $this->url('how-to'); ?>">How-To..
        <span>Understand how to actually use the code</span>
        </a>
    </li>
    <li><a href="<?= $this->url('howto'); ?>">Reference
	         <span>Browse the code reference</span>
        </a>
    </li>
</ul>
<? $this->placeholder('features')->stop(); ?>
