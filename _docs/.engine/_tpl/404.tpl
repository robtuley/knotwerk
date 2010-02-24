<?php
/**
 * 404 alert.
 * @version SVN: $Id$
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                      "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Page Not Found</title>
<meta http-equiv="content-type" content="text/html; charset=utf8" />

<style type="text/css">
body
{
  font-family: arial, helvetica, sans-serif;
  text-align: center;
}
#page
{
  text-align: left;
  width: 30em;
  margin: 5em auto 0 auto;
}
h1
{
  font-size: 200%;
  margin: 0;
  color: #aaa;
}
.actions
{
  border: 2px solid #aaa;
  padding: 0.5em;
}
p
{
  margin: 0 0 0.5em 0;
}
/* Google Widget */
#goog-wm { }
#goog-wm h3.closest-match, #goog-wm h3.other-things
{
  font-size: 120%;
  margin: 0.5em 0;
}
#goog-wm h3.closest-match a { }
#goog-wm ul
{
  margin: 0;
  padding: 0;
  margin-left: 2em;
  list-style: square outside;
}
#goog-wm ul li
{
    margin: 0.5em;
}
#goog-wm li.search-goog { display: list-item; }

</style>
</head>
<body>
<div id="page">

<h1>Ooops, Not Found!</h1>
<div class="actions">
<p>Sorry, but we can't find the page you've asked for.</p>

<script type="text/javascript">
  var GOOG_FIXURL_LANG = 'en';
  var GOOG_FIXURL_SITE = 'knotwerk.com';
</script>
<script type="text/javascript"
    src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js"></script>
<noscript>
<p>We suggest you try
   <a href="http://knotwerk.com">browsing to the homepage</a> to see if you can find what you're looking for.</p>
</noscript>
</div>
</div><!-- #page -->

<!--
This is a short and sweet custom 404 page that uses Google's
404 widget. The page is so short and sweet that to ensure that
it works in IE we need a bit of an HTML comment at the end to
fool IE that the page is long enough to be useful.
-->

</body>
</html>
