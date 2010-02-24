<?php
/**
 * Home page.
 */
$f = new T_Filter_Xhtml();
?>

<h1>Knotwerk: a collection of reusable <abbr title="Object Orientated">OO</abbr>
<abbr title="PHP: Hypertext Preprocessor">PHP</abbr>5 code</h1>

<? $this->partial('in_dev'); ?>

<p>The code forms a &quot;full stack framework&quot; in that no dependencies are required and the existing codebase forms a full <abbr title="Model View Controller">MVC</abbr> implementation: URL to controller mapping, template views and a collection of common model functionality.</p>

<ul>
<li><abbr title="Model View Controller">MVC</abbr> implementation with RESTful
    single URL to controller mapping.</li>
<li>Primary library focus is on simple form generation, validation and rendering.</li>
<li>User formatted input is accepted using a wiki-format lexer and rendering engine.</li>
<li><abbr title="Access Control List">ACL</abbr> functionality is included with user roles and authentication.</li>
<li>Based on the MySQL, SQLite or PostgreSQL database engines, and utilises a master-slave separation wrapper around the PDO extension.</li>
<li>Heavily utilises the concept of dependency injection to add flex points and ease of extension to the codebase (almost zero static methods!).</li>
<li>As far as possible code is unit tested using an in-built testing suite.</li>
</ul>

<h2>Some tempting code?</h2>

<div class="code wide">
<?= $this->php(file_get_contents(DEMO_DIR.'form_simple_enquiry.php')); ?>
</div>

<p>Like it? <a href="<?= $this->url('demo','form_simple_enquiry.php'); ?>">See this code working &rsaquo;</a></p>

<ul>
<li><a href="<?= $this->url('howto'); ?>">Tutorials</a></li>
<li><a href="<?= $this->url('ref'); ?>">Full docs</a></li>
<li><a href="<?= $this->url('download'); ?>">Download the Code</a></li>
</ul>

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
