<?php
/**
 * Contains the T_Text_ListLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses formatted text lists.
 *
 * Th sysntax for a list is:
 *
 *   1. Newline followed by optional whitespace then a '*' (ordered) or '-' (unordered).
 *   2. Nesting is indicated by the level of indentation: more indentation, more nesting.
 *   3. Suffixed by a new line break (preceded by any amount of whitespace).
 *
 * This lexer is designed to be implemented after headers and quotations, before the
 * paragraph lexer.
 *
 * @package wiki
 */
class T_Text_ListLexer extends T_Text_LexerTemplate
{

    /**
     * Types of list to lex for.
     *
     * @var array
     */
    protected $types;

    /**
     * Create list lexer.
     *
     * @param int $mode  lexer modes
     */
    function __construct($mode=T_Text_Lexer::ALL)
    {
        $type = array();
        if ($mode & T_Text_Lexer::ORDERED_LIST) $type[] = T_Text_List::ORDERED;
        if ($mode & T_Text_Lexer::ORDERED_LIST) $type[] = T_Text_List::UNORDERED;
        if (count($type)==0) {
            throw new InvalidArgumentException("Must lex for at least one list type");
        }
        $this->types = $type;
    }

    /**
     * Parses a piece of text into a number of headers.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $lf = '(?:\r\n|\n|\x0b|\r|\f|\x85|^|$)';
        $types = '[';
        foreach ($this->types as $t) {
        	$types .= preg_quote($t);
        }
        $types .= ']';
          /* types to look for e.g. [\*\-] */
        $li = $lf.'[\s]*'.$types.'[^\*].*';
          /* line breaks followed by optional whitespace, then delimiter, not then content
             (remember the '.' does not by default include line breaks. Note that the space
             is important here otherwise */
        $block = '/'.$li.'(?:'.$li.'|['.$lf.'\s]+)*/u';
          /* a list block: started by an initial list bloc, it then continues including
             any blank lines for the entire list section. */
        $content = $element->getContent();
        $num = preg_match_all($block,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) {
            return;  /* no change, as no lists */
        }
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
            /* list set */
            $list_block = $matches[0][$i][0];
            $items = preg_split('/'.$lf.'/u',$list_block);
            $list_objs = array();
            $li_regex = '/^([\s]*)('.$types.')\s*(.+)$/u';
            $li_matches = null;
            foreach ($items as $line) {
                if (strlen(trim($line))<=1) {
                   // no content, or just list delimiter, skip to next
                   continue;
                } elseif (preg_match($li_regex,$line,$li_matches)) {
                    $li_level = strlen($li_matches[1]);
                    $li_type = $li_matches[2];
                    $li_content = new T_Text_ListItem($li_matches[3]);
                    if (count($list_objs)==0) {
                        // if no lists objects at all, create one.
                        $list_objs[$li_level] = new T_Text_List($li_type);
                        $element->addChild($list_objs[$li_level]);
                    }
                    if ($li_level!=($key=key($list_objs))) {
                        if ($li_level>$key) {
                            // if the indent is going up above from the current key
                            // this indicates a nested list -- note we need to add the
                            // nested list as a child to the last list item on the list
                            // we're nesting into..
                            $list_objs[$li_level] = new T_Text_List($li_type);
                            $list_objs[$key]->getLastChild()->addChild($list_objs[$li_level]);
                            end($list_objs); // keep current pos at end
                        } else {
                            // the indent is going down, so we need to fallback on a
                            // previous value.
                            if (!isset($list_objs[$li_level])) {
                                // indent has been screwed up. What we do in this situation
                                // is find the first level above the current level and pretend
                                // we 'dropped' to that.
                                foreach (array_keys($list_objs) as $value) {
                                    if ($value>$li_level) break;
                                }
                                $li_level = $value;
                            }
                            // remove all higher levels
                            foreach (array_keys($list_objs) as $value) {
                                if ($value>$li_level) unset($list_objs[$value]);
                            }
                            end($list_objs);
                        }
                    }
                    $list_objs[$li_level]->addChild($li_content);
                } // line preg_match: should never *not* match!
            }
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

    /**
     * Visit a quote.
     *
     * @param T_Text_Element $element
     */
    function visitTextQuote($element)
    {
        $this->parse($element);
    }

}