<?php
/**
 * Contains the T_Unit_CoverageForTerminal class.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Renders coverage stats for the terminal.
 *
 * @package unit
 */
class T_Unit_CoverageForTerminal extends T_Filter_Skeleton
{

    /**
     * Converts coverage to text.
     *
     * @param T_Unit_CodeCoverage $value  data to filter
     * @return string  rendered as string
     */
    protected function doTransform($value)
    {
        $total = $value->getNumExe()+$value->getNumNotExe();
        $render = 'Code coverage: '.round($value->getCoverageRatio()*100,2).'% '.
                  '(over '.$total.' executable lines)'.EOL;
        if ($value->getNumNotExe()>0) {
            $table = array(array('File','Lines Not Exe'));
            foreach ($value->getNotExe() as $path => $missed ) {
                $table[] = array($path,implode(',',$missed));
            }
            $as_table = new T_Filter_TextTable();
            $render .= EOL._transform($table,$as_table).EOL;
        }
        return $render;
    }

}