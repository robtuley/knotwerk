<?php
/**
 * Contains the T_Code_DocBlock class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * A code DocBlock.
 *
 * @package reflection
 */
class T_Code_DocBlock
{

    /**
     * Short desc.
     *
     * @var string
     */
    protected $short = null;

    /**
     * Longer textual description.
     *
     * @var string
     */
    protected $long = null;

    /**
     * Tags.
     *
     * @var T_Code_DocBlockTag
     */
    protected $tags = array();

    /**
     * Parse doc block text.
     *
     * @param string $comment  doc block comment
     */
    function __construct($comment)
    {
        // normalise EOL
        $regex = '/(?:\r\n|\n|\x0b|\f|\x85)/';
        $comment = preg_replace($regex,EOL,$comment);

        // remove doc block line starters
        $regex = '#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#';
        $comment = trim(preg_replace($regex,'$1',$comment));

        // parse out long and short descriptions & tags
        //   o short desc is first non blank line.
        //   o long desc follows
        //   o tags are preceded by an '@'
        $short = $long = null;
        $first_blank = false;
        $tags = array();
        $tag = new T_Pattern_Regex('/^\@([a-z]+) *(.*)$/i');
        foreach (explode(EOL,$comment) as $line) {
            if (!$first_blank && strlen($line)==0) {
                $first_blank=true; continue; // skip line
            }
            if (!$first_blank) {
                $short .= $line.EOL;
            } elseif ($tag->isMatch($line)) {
                $matches = $tag->getFirstMatch($line);
                $name = strtolower($matches[1]);
                if ($name) {
                    $content = mb_trim($matches[2]);
                    if (strcmp($name,'param')===0) {
                        $regex = new T_Pattern_Regex('/^([0-9a-z_]+(?:\[\])?) +\$([0-9a-z_]+)( +.*)?$/i');
                        if ($regex->isMatch($content)) {
                            $match = $regex->getFirstMatch($content);
                            $desc = isset($match[3]) ? trim($match[3]) : null;
                            $tags[] = new T_Code_DocBlockParamTag($name,$match[1],$match[2],$desc);
                        }
                    } elseif (strcmp($name,'return')===0 || strcmp($name,'var')===0) {
                        $break = strpos($content,' ');
                        if ($break===false) {
                            $type = $content;
                            $desc = null;
                        } else {
                            $type = mb_substr($content,0,$break);
                            $desc = trim(mb_substr($content,$break+1));
                        }
                        $tags[] = new T_Code_DocBlockTypeTag($name,$type,$desc);
                    } else {
                        $tags[] = new T_Code_DocBlockTag($name,$content);
                    }
                }
            } else {
                $long .= $line.EOL;
            }
        }
        $long = mb_trim($long,EOL);
        $short = mb_rtrim($short,EOL);
        if ($short) $this->short = $short;
        if ($long) $this->long = $long;
        $this->tags = $tags;
    }

    /**
     * Gets the short param summary.
     *
     * @param function $filter
     * @return string
     */
    function getSummary($filter=null)
    {
        return _transform($this->short,$filter);
    }

    /**
     * Gets the description.
     *
     * @param function $filter
     * @return string
     */
    function getDesc($filter=null)
    {
        return _transform($this->long,$filter);
    }

    /**
     * Gets the tags.
     *
     * @param function $filter
     * @return T_Code_DocBlockTag[]
     */
    function getTags($filter=null)
    {
        return _transform($this->tags,$filter);
    }

}