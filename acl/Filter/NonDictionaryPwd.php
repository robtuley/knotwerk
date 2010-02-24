<?php
/**
 * Contains the T_Filter_NonDictionaryPwd class.
 *
 * @package acl
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Checks a password against a common word dictionary to make sure it is strong.
 *
 * @package acl
 */
class T_Filter_NonDictionaryPwd extends T_Filter_Skeleton
{

    /**
     * Checks password against multi-lingual attack dictionary.
     *
     * @param string $value  data to filter
     * @return string  filtered value
     * @throws T_Exception_Filter  when too close to attack word
     */
    protected function doTransform($value)
    {
        $words = array($value);
        // do common letter-number replacements
        $leet = array('0'=>'o','1'=>'i','2'=>'z','3'=>'e','4'=>'a','6'=>'g','7'=>'t','8'=>'b');
        $words[] = str_replace(array_keys($leet),array_values($leet),$value);
        $leet['1']='l';
        $words[] = str_replace(array_keys($leet),array_values($leet),$value);
        // strip numbers from start and end and check sub-portions
        $words[] = preg_replace('/[\d]+$/','',$value);
        $words[] = preg_replace('/^[\d]+/','',$value);
        // now look-up any words this password is close to
        $db = T_Mysql_Factory::getInstance('user')->slave();
        $words = array_map(array($db,'to_sql_literal'),$words);
        $list = implode(',',$words);
        $sql = "SELECT word FROM pwd_dictionary WHERE word IN ($list) LIMIT 1";
        $result = $db->query($sql);
        if ($result->num_rows>0) {
            $similar = $result->fetch_object()->word;
            $msg = 'is very close to the dictionary word "'.$similar.'". Please choose a stronger password.';
            throw new T_Exception_Filter($msg);
        }
        return $value;
    }

}