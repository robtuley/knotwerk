<?php
/**
 * Download HTML.
 *
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml();
?>

<h1>Download</h1>

<? $this->partial('in_dev'); ?>

<p>The code library is managed using git, and anonymous checkout is
   possible from:</p>

<p class="download">
    <a href="git://openknot.com/knotwerk.git">git://openknot.com/knotwerk.git</a>
</p>

<h2>What is 'Git'?</h2>

<p>Git is a version control tracking system for code. To get the knotwerk
   library, you will need to 'clone' the current repository:</p>

<p><code>git clone git://openknot.com/knotwerk.git</code></p>

<p>When you want to get updates that have been made to the main library,
   'pull' and merge those updates into your local copy:</p>

<p><code>git pull</code></p>

<? $this->placeholder('sidebar')->append(); ?>

<div class="box first">
<h2>I've got the code, what next?</h2>
<p>Once you've downloaded the code, the
   <a href="<?= $this->url('howto'); ?>">How To section</a> introduces various
   aspects of the library and is worth a look.</p>
</div>

<div class="box">
<h2>Prerequisites and Testing</h2>
<p>To check your downloaded code is fully functioning on your system, it
   is a good idea to <a href="<?= $this->url('qa'); ?>">check the
   prerequistes and run the unit test suite</a>.</p>
</div>

<? $this->placeholder('sidebar')->stop();
   $this->placeholder('features')->append(); ?>

<p>Already got a copy of the code?</p>
<ul>
    <li><a href="<?= $this->url('howto'); ?>">How To...
	         <span>A series of tutorials introduce the library</span>
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
