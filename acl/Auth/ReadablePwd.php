<?php
/**
 * Defines the T_Auth_ReadablePwd class.
 *
 * @package ACL
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Readable password factory.
 *
 * @package ACL
 */
class T_Auth_ReadablePwd implements T_Auth_PwdFactory
{

    /**
     * Array of password prefixes (latin root).
     *
     * @var array
     */
    static protected $prefix = array('aero','anti','auto','bi','bio',
                                     'cine','deca','demo','dyna','eco',
                                     'ergo','geo','gyno','hypo','kilo',
                                     'mega','tera','mini','nano','duo');

    /**
     * Array of password suffixes (latin root).
     *
     * @var array
     */
    static protected $suffix = array('dom','ity','ment','sion','ness',
                                     'ence','er','ist','tion','or');

    /**
     * Vowels.
     *
     * @var array
     */
    static protected $vowels = array('a','o','e','i','y','u','ou','oo');

    /**
     * Consonants.
     *
     * @var array
     */
    static protected $consonants = array('w','r','t','p','s','d','f','g','h','j',
                                         'k','l','z','x','c','v','b','n','m','qu');

    /**
     * Number of syllables to generate.
     *
     * @var int
     */
    protected $syllables;

    /**
     * Create password factory.
     *
     * @param int $syllables  number of syllables to place in password centre
     */
    function __construct($syllables=2)
    {
        $this->syllables = $syllables;
    }

    /**
     * Gets a random member from an array.
     *
     * @param array $array
     * @return mixed  random member
     */
    protected function getRandMember(array &$array)
    {
        return $array[array_rand($array)];
    }

    /**
     * Create password.
     *
     * @return string  human readable password
     */
    function create()
    {
        $pwd = $this->getRandMember(self::$prefix);
        $suffix = $this->getRandMember(self::$suffix);
        for($i=0; $i<$this->syllables; $i++)
        {
            $doubles = array('n','m','t','s');
            $c = $this->getRandMember(self::$consonants);
            if (in_array($c,$doubles)&&($i!=0))  // maybe double it with 33% prob
                if (mt_rand(0,2) == 1)
                    $c .= $c;
            $pwd .= $c;
            $pwd .= $this->getRandMember(self::$vowels);
            if ($i==$this->syllables-1) // on last iteration, if suffix begin with vowel
                if (in_array($suffix[0],self::$vowels)) // add one more consonant
                    $pwd .= $this->getRandMember(self::$consonants);
        }
        return $pwd.$suffix;
    }

}
