<?php
/**
 * Defines the Text_PhpLexer class.
 *
 * @package doc
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses out sections of PHP code.
 *
 * @package doc
 */
class Text_PhpLexer extends T_Text_LexerTemplate
{

    /**
     * Parses out PHP code.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $content = $element->getContent();
        /* for performance reasons, we avoid executing the regex if there is no
           tags in the text. */
        if (strpos($content,'<?php')===false) return;
        $regex = '/('.preg_quote('<?php').'.+?'.preg_quote('?>').')/su';
          // question mark makes dot repetition LAZY
        $matches = null;
        $num = preg_match_all($regex,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) return;  /* no change, as no emphasised text */
        $offset = 0;
        /* Note that the offset produced from preg_match_all is in bytes, not
           unicode characters. Therefore, in the following section we do NOT use
           the mb_* functions to assess length, as we are working in bytes not
           characters. */
        for ($i=0; $i<$num; $i++) {
            /* pre content */
            if ($offset<$matches[0][$i][1]) {
                $pre = substr($content,$offset,$matches[0][$i][1]-$offset);
                $element->addChild(new T_Text_Plain($pre));
            }
            /* php */
            $emph = new Text_Php($matches[1][$i][0]);
            $element->addChild($emph);
            /* update offset */
            $offset = $matches[0][$i][1] + strlen($matches[0][$i][0]);
        }
        /* post content */
        if ($offset<strlen($content)) {
            $post = substr($content,$offset);
            $element->addChild(new T_Text_Plain($post));
        }
        /* reset original content */
        $element->setContent(null);
    }

    /**
     * Visit a formatted text node.
     *
     * @param T_Text_Element $element
     */
    function visitTextPlain($element)
    {
        $this->parse($element);
    }

}
