<?php
/**
 * Contains the Xhtml_TextPreview class.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Previews formatted text file.
 *
 * @package docs
 */
class Xhtml_TextPreview extends T_Xhtml_TextPreview
{

    // skip PHP and literals
    function visitPhp($node) { }
    function visitLiteral($node) { }

}
