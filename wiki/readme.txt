Adds plain text parsing and XHTML rendering tools using wiki-like markup syntax. The package consists of a number of plain text parsers which convert plain text into a composite formatted text object. The composite can then be visited by the renderer of choice, and an XHTML full and preview renderers are provided as examples.

== Parsing Plain Text ==

To parse formatted plain text, a T_Text_Plain object must be created from the text, and the composite is modified by various "lexers" which visit the object. For example, to parse a piece of plain text into paragraphs and tables:

<?php
$text = '... some user text ...';
$formatted = new T_Text_Plain($text);
$formatted->accept(new T_Text_TableLexer)
          ->accept(new T_Text_ParagraphLexer);
?>

The order in which lexers are applied to the composite is important -- in the example above any tables would end up inside paragraphs if the paragraph lexer was applied first. To help apply lexers in the correct order, lexing can be delegated to the T_Text_Lexer class, which acts as a filter and can be configured in the constructor. Rewriting the above example:

<?php
$text = '... some user text ...';
$mode = T_Text_Lexer::TABLE|T_Text_Lexer::PARAGRAPH;
$lexer = new T_Text_Lexer($mode);
$formatted = $lexer->transform($text);
?>

The first argument to the T_Text_Lexer constructor is a binary mode parameter, and the default is T_Text_Lexer::ALL (to apply all lexers). It can be used to create different lexing filters for different text areas for you application.

== Rendering the Formatted Text ==

Once a piece of plain text is formatted, it can be rendered by passing in a visitor via the accept() method. Default XHTML and XHTML preview renderers are supplied, or you can write you own for PDF, etc.

<?php
$env = new T_Environment_Http;
$text = '... some user text ...';

// parse
$lexer = new T_Text_Lexer;
$formatted = $lexer->transform($text);

// render as XHTML
$xhtml = new T_Xhtml_Text($env->getAppRoot());
$formatted->accept($xhtml);
echo $xhtml;

// render as XHTML preview (max 100 chars)
$preview = new T_Xhtml_TextPreview(100,$env->getAppRoot());
$formatted->accept($preview);
echo $preview;
?>

== "Formatted" Text: What is the Format? ==

~~~~
The text format that is currently parsed is wiki-like
structured plain text. This is an example of the plain
text format written in it's own format.

== Paragraphs ==

Paragraphs are naturally formed from text that is divided
by two or more line breaks.

== Headers ==

Headers of various levels are formed by appending/prepending
matching numbers of equals signs to a piece of text (the
more equals signs the lower level the header).

=== Sub-Header ===

==== Sub-Sub Header ====

Note that the maximum header level is a double equals.

== Links ==

Links are enclosed in square brackets with the URL at the
start: [http://example.com external links] and
[/download internal links].

== Emphasised Text ==

Text can be **emphasised** using pairs of double asterisks.

== Quotations ==

Quotations can be included bounded by a pair of double-quotes
at the start of a line. Citations can be included on the
last line if required.

""
Remember that the entiure library is unicode safe, so including
words like Iñtërnâtiônàlizætiøn at any position in the
formatted text is OK.
"" Rob Tuley

""
This is a quote with no citation.
""

== Lists ==

It is possible to define both ordered..

- Item #1
- Item #2
- Item #3

... and unordered lists:

* Item #1
* Item #2

Nesting of lists is possible by using different indenting:

* This is
* a
  * nested
  * list
* and different
  - list types
  - can be nested
    * within each
    * other
* smart, eh?

== Tables ==

Tables can be drawn in ASCII and will be parsed and rendered.

+----------+----------+
| header 1 | header 2 |
+----------+----------+
| content  | more..   |
| 2nd      | row      |
| 3rd      | line     |
+----------+----------+

+-----------+-----------+
| content 1 | content 4 |
| content 2 | content 5 |
| content 3 | content 6 |
+-----------+-----------+

+-----------+--------------+
|^ Header 1 | content      |
|^ Header 2 | content      |
|^ Header 3 | more content |
+-----------+--------------+

The table lexer also allows for the fact that you
can span cells. This is achieved by inserting zero
length content in trailing cells.

+-----------+-----------+
| content 1 | content 4 |
| this content spans   ||
| content 3 | content 6 |
+-----------+-----------+

== Images ==

Images can be included directly bounded by exclamation
marks and with some alternative text. For example, the
current UK Google logo is:

!http://www.google.co.uk/intl/en_uk/images/logo.gif Google Logo!

== Dividers ==

It is possible to insert dividers between content using
three dashes. The XHTML renderer will then output these
as <hr /> tags.

Paragraph 1

----

Paragraph 2

Paragraph 3

----

Last Paragraph

== Super- & Subscripts ==

Super- and Subscripts are possible by using the _ and ^
characters. e.g. H_20 is water, H_{longer subscript}O,
and subscripts for things like m^2 and 1^{st}.
~~~~

== Extending to Include your Lexers and Renderers ==

To include your own constructs you will need to:

- create a class to encapsulate your format if it doesn't exist already. If you want your format to be able to contain other formats (such as links, subscripts, etc) it will need to implement the T_Text_Parseable interface.
- create a lexing class that detects your format and modifies the composite. Extending the class T_Text_LexerTemplate might be useful, and make sure you maintain unicode safety if this is important to you.
- modify the lexing chain: once you start including your own lexers, T_Text_Lexer is less useful and it probably makes sense to define your own lexing filters.
- modify/create your renderers to handle rendering your format: extending the existing classes and adding extra visit methods is usually all that is required.
