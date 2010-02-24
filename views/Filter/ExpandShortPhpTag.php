<?php
/**
 * Contains the T_Filter_ExpandShortPhpTag class.
 *
 * @package views
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * This expands any short open tags to long tags.

 * @package views
 */
class T_Filter_ExpandShortPhpTag extends T_Filter_Skeleton
{

    /**
     * Expands short open tags to long tags.
     *
     * @param string $value  data to filter
     * @return string  valid data with long PHP tags
     */
    protected function doTransform($value)
    {
        if (!ini_get('short_open_tag')) {
            $value = str_replace('<?=','<?php echo ',$value);
            $delimit = '(?:\r\n|\n|\x0b|\r|\f|\x85| |\t)';
            $regex = '/'.preg_quote('<?').$delimit.'/';
            $value = preg_replace($regex,'<?php ',$value);
        }
        return $value;
    }

}