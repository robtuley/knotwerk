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

<p>The code library is managed in subversion, and anonymous checkout is
   possible from:</p>

<p class="download">
    <a href="http://svn.knotwerk.com/trunk/">http://svn.knotwerk.com/trunk/</a>
</p>

<h2>What is 'Subversion'?</h2>

<p>Subversion is a version control tracking system for code. For windows
   users it is usually easiest to use a graphical tool such as
   <a href="http://tortoisesvn.tigris.org/">TortoiseSVN</a> to
   manage the files directly in Explorer. On other systems there are a
   variety of command line and GUI tools for managing Subversion repositories.</p>

<p>To get the knotwerk library, you will need to 'checkout' the
   URL <a href="http://svn.knotwerk.com/trunk/">http://svn.knotwerk.com/trunk/</a>.   This gets you the latest copy of the knotwerk library. As updates are made
   to the library, you can simply 'update' your existing set of files which
   will merge any changes to the library with any you have made yourself.</p>

<p>Subversion is a powerful tool to track the history of a codebase, and if
   you are new to it you will find comprehensive document in
   the <a href="http://svnbook.red-bean.com/">SVN book</a>.</p>

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
