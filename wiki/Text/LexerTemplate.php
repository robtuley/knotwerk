<?php
/**
 * Defines the T_Text_LexerTemplate class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Base template for various lexer visitors.
 *
 * @package wiki
 */
abstract class T_Text_LexerTemplate implements T_Visitor
{

    /**
     * Parses current node.
     *
     * @param T_Text_Parseable $element
     */
    abstract protected function parse(T_Text_Parseable $element);

    /**
     * Default to no parse on visit call.
     *
     * @param string $method  method name called
     * @param array $args  method call arguments
     * @return mixed  filtered property (if valid call made).
     */
    function __call($method,$args)
    {
        if (strncmp($method,'visit',1)!==0) {
            throw new RuntimeException("Method $method does not exist");
        }
        return; // only lex into obejct when specified
    }

    /**
     * No pre-Child visitor event.
     */
    function preChildEvent() { }

    /**
     * No post-Child visitor event.
     */
    function postChildEvent() { }

    /**
     * Always traverse children.
     *
     * @return bool  traverse children.
     */
    function isTraverseChildren()
    {
        return true;
    }

}