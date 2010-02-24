<?php
/**
 * Contains the T_Xhtml_Php class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Converts PHP source to XHTML.
 *
 * @see http://shiflett.org/blog/2006/oct/formatting-and-highlighting-php-code-listings
 * @package reflection
 * @license http://knotwerk.com/licence MIT
 */
class T_Xhtml_Php extends T_Filter_Skeleton
{

    /**
     * Convert PHP source code to XHTML.
     *
     * The code is exported as an ordered list, with keywords, comments, etc.
     * highlighted with class spans.
     *
     * @return string  source code as marked up XHTML.
     */
    function doTransform($src)
    {
        $tokens = token_get_all(trim($src));
        $f = new T_Filter_Xhtml();
        $is_even = true;     /* next line is even */
        $in_quotes = false;  /* tracks if in *parsed* quoted string */

        $out = '<ol class="php">'.EOL.'    '.'<li><code>';
        foreach ($tokens as $t) {

            /* standardize token (maybe array, or just plain content). */
            if (is_array($t)) {
                list($t_type,$t_content) = $t;
                $t_class = $this->getClassFromToken($t_type);
            } else {
                $t_type  = false;
                $t_class = false;
                $t_content = $t;
            }

            /* If there is a double quoted string that contains embedded
               variables, the string is tokenized as components, and within
               this string is the only time we want to label T_STRING types as
               actual strings. The double quotes in this case always appear in
               on their own,and we use the $in_quotes to track this status */
            if ($t_type === false && $t_content === '"') {
                $t_class = $this->getClassFromToken(T_CONSTANT_ENCAPSED_STRING);
                $in_quotes = !$in_quotes;
            } elseif ($in_quotes && $t_type === T_STRING) {
                $t_class = $this->getClassFromToken(T_CONSTANT_ENCAPSED_STRING);
            }

            /* act on token. This is complicated by the fact that a token
               content can contain EOL markers, and in this case the token
               content must be separated at these lines. */
            $t_content = explode("\n",$t_content);
            for ($i=0,$max=count($t_content)-1; $i<=$max; $i++) {

                /* add new line (only from 2nd iteration onwards) */
                if ($i>0) {
                    $e_class = $is_even ? ' class="even"' : '';
                    $is_even = !$is_even;
                    $out .= '</code></li>'.EOL.'    '.'<li'.$e_class.'><code>';
                }

                /* right trim token content if it is at end of a line */
                $line = $t_content[$i];
                if ($i<$max) {
                    $line = rtrim($line);
                }

                /* wrap content in spans */
                if ($t_class !== false && strlen($line)>0) {
                    $out .= '<span class="'.$t_class.'">'.
                            _transform($line,$f).
                            '</span>';
                } else {
                    $out .= _transform($line,$f);
                }

            }
        }
        $out .= '</code></li>'.EOL.'</ol>';

        return $out;
    }

    /**
     * Converts a token identifier to a classname.
     *
     * @param int $token  token constant
     * @return string  classname of subject
     */
    protected function getClassFromToken($token)
    {
        switch ($token) {
            case T_OPEN_TAG:
            case T_OPEN_TAG_WITH_ECHO:
            case T_CLOSE_TAG:
                return 'tag';
            case T_COMMENT:
            case T_DOC_COMMENT:
                return 'comment';
            case T_INLINE_HTML:
                return 'inline-content';
            case T_CONSTANT_ENCAPSED_STRING:
            case T_ENCAPSED_AND_WHITESPACE:
                return 'string';
            case T_VARIABLE:
            case T_STRING_VARNAME:
                return 'variable';
            case T_LNUMBER:
            case T_DNUMBER:
                return 'number';
            case T_ABSTRACT:
            case T_ARRAY:
            case T_AS:
            case T_BREAK:
            case T_CASE:
            case T_CATCH:
            case T_CLASS:
            case T_CLONE:
            case T_CONST:
            case T_CONTINUE:
            case T_DECLARE:
            case T_DEFAULT:
            case T_DO:
            case T_ECHO:
            case T_ELSE:
            case T_ELSEIF:
            case T_EMPTY:
            case T_EVAL:
            case T_EXIT:
            case T_EXTENDS:
            case T_FINAL:
            case T_FOR:
            case T_FUNCTION:
            case T_FOREACH:
            case T_GLOBAL:
            case T_IF:
            case T_IMPLEMENTS:
            case T_INCLUDE:
            case T_INCLUDE_ONCE:
            case T_INSTANCEOF:
            case T_INTERFACE:
            case T_ISSET:
            case T_LIST:
            case T_NEW:
            case T_PRINT:
            case T_PRIVATE:
            case T_PUBLIC:
            case T_PROTECTED:
            case T_REQUIRE:
            case T_REQUIRE_ONCE:
            case T_RETURN:
            case T_STATIC:
            case T_SWITCH:
            case T_THROW:
            case T_TRY:
            case T_UNSET:
            case T_WHILE:
                return 'keyword';
            default:
                return false;
        }
    }

}
