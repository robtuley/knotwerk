<?php
/**
 * Contains the T_Filter_TextTable class.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This creates a text table (like that produced by command-line mysql queries).
 *
 * <code>
 * $users = array(
 *     array('id','name','age','city'),
 *     array(12,'Sally','25','Yoknapatawha'),
 *     array(123,'Yu','35','Seoul'),
 *     array(12345,'Ermenegildo','45','Roma')
 * );
 * </code>
 *
 * This array produces:
 * <code>
 * +-------+-------------+-----+--------------+
 * | id    | name        | age | city         |
 * +-------+-------------+-----+--------------+
 * | 12    | Sally       | 25  | Yoknapatawha |
 * | 123   | Yu          | 35  | Seoul        |
 * | 12345 | Ermenegildo | 45  | Roma         |
 * +-------+-------------+-----+--------------+
 * </code>
 *
 * @package unit
 */
class T_Filter_TextTable extends T_Filter_Skeleton
{

    /**
     * Converts array to text table.
     *
     * @see http://www.tagarga.com/blok/on/070116
     * @param array $data  data to filter
     * @return string  text table
     */
    protected function doTransform($value)
    {
        $title = array_shift($value);
        // calculate maximum string lengths of each column
        $size = array_map('mb_strlen',$title);
        foreach (array_map('array_values',$value) as $row) {
            $size = array_map('max',$size,array_map('mb_strlen',$row));
        }
        // create formats to output data
        foreach ($size as $n) {
            $format[] = "%-{$n}s";
            $line[] = str_repeat('-',$n);
        }
        $format = '| '.implode(' | ',$format)." |".EOL;
        $line   = '+-'.implode('-+-',$line)."-+".EOL;
        // build table from title, data and existing formats
        $table = $line.vsprintf($format,$title).$line;
        foreach ($value as $row) {
            $table .= vsprintf($format,$row);
        }
        if (count($value)>0) $table .= $line;
        return rtrim($table);
    }

}