<?php
/**
 * Template for simple knotwerk demos.
 *
 * @version SVN: $Id$
 */

// bootstrap
require dirname(__FILE__).'/../../bootstrap.php';
$env = new T_Environment_Http();
$env->addRule(new T_Find_ClassInDir(T_ROOT_DIR.'core','T_'));
$env->addRule(new T_Find_ClassInDir(T_ROOT_DIR.'forms','T_'));
$env->addRule(new T_Find_ClassInDir(T_ROOT_DIR.'views','T_'));

// utilities
require dirname(__FILE__).'/forms.php';

// setup error_reporting
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors',1);

// setup UTF-8
header('Content-Type:text/html; charset='.T_CHARSET);
$f = new T_Filter_Xhtml;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo _transform(DEMO,$f); ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf8" />

<!-- cross-browser javascript and css -->
<link rel="stylesheet" type="text/css" href="css/reset.css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="css/forms.css" media="screen,projection" />
<link rel="stylesheet" type="text/css" href="css/screen.css" media="screen,projection" />

<!-- IE7 and below javascript and css -->
<!--[if lte IE 7]>
<link rel="stylesheet" type="text/css" href="css/IE_lte_7.css" media="screen,projection" />
<![endif]-->

<!-- IE6 javascript and css -->
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="css/IE_lt_7.css" media="screen,projection" />
<![endif]-->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
</head>

<body>

<p class="demo">This is a <strong><?php echo _transform(DEMO,$f); ?></strong> demo of the <a href="http://knotwerk.com">Knotwerk</a> library. <a href="./">See other demos &rsaquo;</a></p>

<h1><?php echo _transform(DEMO,$f); ?></h1>
