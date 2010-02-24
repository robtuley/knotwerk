<?php
/**
 * The main docs template.
 *
 * @param T_Url_Collection $this->nav  navigation
 * @param mixed $this->primary  primary content
 * @package docs
 * @version SVN: $Id$
 */
$f = new T_Filter_Xhtml;
$root = $this->url().'/'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title><?=$f->transform($this->title); ?></title>
    <meta http-equiv="content-type"
          content="text/html; charset=utf-8" />

    <!-- screen styles -->
    <link rel="stylesheet" type="text/css" media="screen, projection"
          href="<?= $root; ?>css/reset.css" />
    <link rel="stylesheet" type="text/css" media="screen, projection"
          href="<?= $root; ?>css/screen.css" />

    <!-- print styles -->
    <link rel="stylesheet" type="text/css" media="print"
          href="<?= $root; ?>css/print.css" />

    <!-- javascript -->
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
    <script type="text/javascript" src="<?= $root; ?>js/common.js"></script>
</head>

<body>

<div id="main"><div class="inner">

<div class="primary">
<? $this->buffer($this->primary); ?>
</div><!-- .primary -->

<div class="secondary">
<?= $this->placeholder('sidebar'); ?>
</div><!-- .secondary -->

</div></div><!-- #main -->

<!--
Header
-->

<div id="header"><div class="inner">
    <h2 class="logo"><a href="<?= $root; ?>">Knotwerk PHP Library</a></h2>
	<? $crumbs = $this->nav->getCrumbs();
	if (count($crumbs)>1) : ?>
	<ul id="crumbs">
		<? foreach ($crumbs as $url => $name) : ?>
        <li><? if (!is_numeric($url)) echo '<a href="'.$f->transform($url).'">'; ?>
			<?= $f->transform($name); if (!is_numeric($url)) echo '</a>'; ?></li>
		<? endforeach; ?>
	</ul>
	<? endif; ?>
</div></div>

<!--
Nav
-->

<div id="nav"><div class="inner">
<? $visitor = new T_Xhtml_UrlList();
   $visitor->setExcludeLevel(array(0));
   $this->nav->get()->accept($visitor); ?>
</div></div>

<!--
Features
-->

<div id="features"><div class="inner">
<? echo $this->placeholder('features'); ?>
</div></div><!-- .inner, #footer -->

<!--
Footer
-->

<div id="footer"><div class="inner">

<div class="primary">
	<h2>Sitemap</h2>
</div><!-- .primary -->
<div class="secondary">
    <ul>
	<li><a href="<?= $root; ?>changelog">Changelog</a></li>
        <li><a href="<?= $root; ?>roadmap">Roadmap</a></li>
    </ul>
    <p>&copy; <a href="http://openknot.com">Openknot</a>. All rights reserved.</p>
</div><!-- .secondary -->

</div></div><!-- .inner, #footer -->

</body>
</html>
