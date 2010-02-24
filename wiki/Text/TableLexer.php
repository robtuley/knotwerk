<?php
/**
 * Contains the T_Text_TableLexer class.
 *
 * @package wiki
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Visitor that parses formatted text tables.
 *
 * The syntax is easiest to display by example:
 *
 * +----------+----------+
 * | header 1 | header 2 |
 * +----------+----------+   Normal table with headers. The '+--..' at
 * | content  | more..   |   the beginning and end are optional, the one
 * | 2nd      | row      |   under the headers is not (it makes them headers).
 * | 3rd      | line     |
 * +----------+----------+
 *
 * +----------+--------------+
 * | content  | content      |  Normal table wtihout headers. The '+--' bits
 * | content  | content      |  are optional.
 * | content  | more content |
 * +----------+--------------+
 *
 * +-----------+--------------+
 * |^ Header 1 | content      |  Horizontal header cells are delimited by using '|^' at
 * |^ Header 2 | content      |  the start of a row.
 * |^ Header 3 | more content |
 * +-----------+--------------+
 *
 * +----------+--------------+
 * | content  | content      |  Cell spans can be accomplished by leaving no space between
 * | content that spans     ||  trailing pipes (these cells are merged into the previous one).
 * | content  | more content |
 * +----------+--------------+
 *
 * @package wiki
 */
class T_Text_TableLexer extends T_Text_LexerTemplate
{

    /**
     * Parses a piece of text into a number of headers.
     *
     * @param T_Text_Parseable $element
     */
    protected function parse(T_Text_Parseable $element)
    {
        $lf = '(?:\r\n|\n|\x0b|\r|\f|\x85|^|$)';
        $divider = '(?:'.$lf.'[ \t]*[\+-]+[ \t]*)';
        $table = '!'.$lf.'*'.                                // consume any empty lines at start
                 $divider.'?'.                               // starts with optional divider
                 $lf.'[ \t]*(\|.+'.                          // at least one table row
                 '(?:'.$lf.'[ \t]*\|.+|'.$divider.')*)'.     // then 1 or more lines of table/dividers
                 '[ \t]*'.$lf.'!u';                          // last LF to remove ambiguity about where to end table
        $content = $element->getContent();
        $num = preg_match_all($table,$content,$matches,PREG_OFFSET_CAPTURE);
        if ($num < 1) {
            return;  /* no change, as no tables */
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
            /* table set */
            $element->addChild($table=new T_Text_Table());
            $this->populateTable($matches[1][$i][0],$table);
                                       // ^ note using sub-pattern content
            $offset = $matches[0][$i][1] + strlen($matches[0][$i][0]); // update offset
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
     * Create a table object from a block of text.
     *
     * @param string $text  table definition
     * @return T_Text_Table  table object
     */
    protected function populateTable($text,T_Text_Table $table)
    {
        $lines = preg_split('/(?:\r\n|\n|\x0b|\r|\f|\x85)\s*/u',$text); // also removes starting spaces
        // starting delimiter has already been stripped by the parsing regex, so we just need
        // to check whether the auto header 2nd line exists, and then parse text into table data array.
        $is_headers = (count($lines)>1 && strncmp($lines[1],'|',1)!==0);
        $data = array();
        foreach ($lines as $row) {
            if (strncmp($row,'|',1)!==0) continue; // skip delimiters
            $data[] = explode('|',mb_trim(mb_trim($row),'|'));
        }
        // now we need to actually flesh out the data into a table. Here we need to account for the
        // fact that trailing empty elements in the array are merged via span into the previous cell.
        $per_row = max(array_map('count',$data));
        foreach ($data as $num => $src) {
            $table->addChild($row = new T_Text_TableRow());
            if (count($src)!=$per_row) { // add extra cols at end which will be merged by span into previous cell
                for ($i=0, $max=$per_row-count($src); $i<$max; $i++ ) $src[] = null;
            }
            foreach ($src as $n => $cell) {
                if ($n>0 && strlen($cell)==0) continue; // skip cells already created into spans
                $type = ($is_headers || strncmp($cell,'^',1)===0) ? T_Text_TableCell::HEADER : T_Text_TableCell::PLAIN;
                $cell = mb_trim(mb_ltrim($cell,'^'));
                $span = 1;
                while ( (++$n)<$per_row && strlen($src[$n])==0) ++$span;
                $row->addChild(new T_Text_TableCell($cell,$type,$span));
            }
            $is_headers = false; // turn off auto headers after first line
        }
        return $table;
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