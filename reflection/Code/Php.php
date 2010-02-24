<?php
/**
 * Contains the T_Code_Php class.
 *
 * @package reflection
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Encapsulates a piece of PHP code.
 *
 * @package reflection
 */
class T_Code_Php
{

    /**
     * Cached meta-data about this source.
     *
     * @var mixed[]
     */
    protected $cache = array();

    /**
     * Source code.
     *
     * @var string
     */
    protected $src;

    /**
     * Encapsulate source code
     *
     * @param string $src  PHP code
     */
    function __construct($src)
    {
        $this->src = $src;
    }

    /**
     * Gets the source code.
     *
     * @param function $filter  optional filter
     * @return string  source code
     */
    function getSrc($filter=null)
    {
        return _transform($this->src,$filter);
    }

    /**
     * Trims PHP tags from start/end of source for inlining.
     *
     * @param function $filter  optional filter
     * @return string  code for inlining
     */
    function getInline($filter=null)
    {
        $new = mb_trim($this->src);

        // remove starting tag..
        if (strncmp('<?php',$new,5)===0) $new = mb_substr($new,5);
        if (strncmp('<?',$new,2)===0 && strncmp('<?=',$new,3)!==0) $new = mb_substr($new,2);

        // remove ending tag
        $end = mb_substr($new,mb_strlen($new)-2);
        if (strcmp($end,'?>')===0) $new = mb_substr($new,0,mb_strlen($new)-2);

        return _transform(mb_trim($new),$filter);
    }

    /**
     * Tries to retrieve SVN version info from the source.
     *
     * @param function $filter  optional filter
     * @return int  version number
     */
    function getVersion($filter=null)
    {
        if (!isset($this->cache['version'])) {
            $regex = '/@version\s+SVN:\s*\$Id:\s*[a-z\._-]+\s+(\d+)\s/i';
            $svn = null;
            $num = preg_match_all($regex,$this->src,$matches);
            for ($i=0;$i<$num;$i++) {
                $v = (int) $matches[1][$i];
                if ($v>$svn) $svn = $v;
            }
            $this->cache['version'] = $svn;
        }
        return _transform($this->cache['version'],$filter);
    }

    /**
     * Gets an array of classes/interfaces that are defined in this code.
     *
     * @param function $filter  optional filter
     * @return string[]  array of class & interface names
     */
    function getClasses($filter=null)
    {
        if (!isset($this->cache['class'])) {
            $this->cache['class'] = array();
            $tokens = $this->tokenize();
            reset($tokens);
            while ($t = current($tokens)) {
                if (is_array($t) && ($t[0]==T_CLASS || $t[0]==T_INTERFACE)) {

                    next($tokens); // move pass class definition

                    // skip whitespace and comments
                    while ($t=current($tokens)) {
                        if (is_array($t) &&
                            ($t[0]==T_WHITESPACE || $t[0]==T_DOC_COMMENT || $t[0]==T_COMMENT)) {
                            next($tokens);
                        } else {
                            break;
                        }
                    }

                    $t = current($tokens);
                    $this->cache['class'][] = $t[1]; // class name
                }
                next($tokens);
            }
        }
        return _transform($this->cache['class'],$filter);
    }

    /**
     * Gets an array of class/interface dependencies for this code.
     *
     * @param function $filter  optional filter
     * @return string[]  array of class & interface names
     */
    function getDependencies($filter=null)
    {
        if (!isset($this->cache['deps'])) {
            $deps = array();
            foreach ($this->getClasses() as $c) {
                $class = new ReflectionClass($c);
                do {
                    $deps[] = $class->getName();
                    $ifaces = $class->getInterfaces();
                    foreach ($ifaces as $iface) {
                        $deps[] = $iface->getName();
                    }
                } while ($class=$class->getParentClass());
            }
            $deps = array_unique($deps);
            // remove dependencies that are already satisfied by itself
            foreach ($this->getClasses() as $c) {
                if (false!==($key=array_search($c,$deps))) unset($deps[$key]);
            }
            $this->cache['deps'] = array_values($deps);
        }
        return _transform($this->cache['deps'],$filter);
    }

    /**
     * Expands any require or include statements.
     *
     * This method attempts to expand any include or require statements. It
     * handles require_once statements in exactly the same way, so care should
     * be taken as the same code can be combined more than once in this case!
     *
     * @return T_Code_Php  expanded code
     */
    function expand()
    {
        $lf = '(?:\r\n|\n|\x0b|\r|\f|\x85|^)';
        $regex = '@'.$lf.'(?:require|include)(?:_once)?\s*[\s\(]([^;]+?)\)?\;@ui';
          // note that the require or include statement will only be picked
          // up if it is NOT indented. This is to prevent picking up runtime
          // logic includes/requires.
        $matches = null;
        $src = $this->src;
        do { // iterate to catch includes in included files!
            $num = preg_match_all($regex,$src,$matches,PREG_OFFSET_CAPTURE);
            if ($num<1) {
                return new T_Code_Php($src);  /* no requires/includes left */
            }
            $new = '';
            $offset = 0;
            for ($i=0;$i<$num;$i++) {
                // pre content
                if ($offset<$matches[0][$i][1]) {
                    $new .= substr($src,$offset,$matches[0][$i][1]-$offset);
                }
                // include/require statement
                $path = eval('return '.$matches[1][$i][0].';');
                if (!is_file($path)) {
                    $msg = "required/included $path is not a file (from ".
                           $matches[0][$i][0].')';
                    throw new RuntimeException($msg);
                }
                $php = new T_Code_Php(file_get_contents($path));
                $new .= $php->getInline();
                // update offset
                $offset = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            }
            // post content
            if ($offset<strlen($src)) {
                $new .= substr($src,$offset);
            }
            $src=$new;
        } while (true); // breaks out using return at start
    }

    /**
     * Compresses the code.
     *
     * @return T_Code_Php  compressed code
     */
    function compress()
    {
        // code from PHP manual by gelamu at gmail dot com
        // @see http://php.net/php_strip_whitespace
        $IW = array(T_CONCAT_EQUAL,T_DOUBLE_ARROW,T_BOOLEAN_AND,T_BOOLEAN_OR,
                    T_IS_EQUAL,T_IS_NOT_EQUAL,T_IS_SMALLER_OR_EQUAL,T_IS_GREATER_OR_EQUAL,
                    T_INC,T_DEC,T_PLUS_EQUAL,T_MINUS_EQUAL,T_MUL_EQUAL,T_DIV_EQUAL,
                    T_IS_IDENTICAL,T_IS_NOT_IDENTICAL,T_DOUBLE_COLON,T_PAAMAYIM_NEKUDOTAYIM,
                    T_OBJECT_OPERATOR,T_DOLLAR_OPEN_CURLY_BRACES,T_AND_EQUAL,T_MOD_EQUAL,
                    T_XOR_EQUAL,T_OR_EQUAL,T_SL,T_SR,T_SL_EQUAL,T_SR_EQUAL);
        $tokens = $this->tokenize();
        $new = "";
        $c = count($tokens);
        $iw = false; // ignore whitespace
        $ih = false; // in HEREDOC
        $ls = "";    // last sign
        $ot = null;  // open tag
        for($i=0; $i<$c; $i++) {
            $token = $tokens[$i];
            if(is_array($token)) {
                list($tn, $ts) = $token; // tokens: number, string, line
                $tname = token_name($tn);
                if($tn == T_INLINE_HTML) {
                    $new .= $ts;
                    $iw = false;
                } else {
                    if($tn == T_OPEN_TAG) {
                        $ts = rtrim($ts);
                        $ts .= " ";
                        $new .= $ts;
                        $ot = T_OPEN_TAG;
                        $iw = true;
                    } elseif($tn == T_OPEN_TAG_WITH_ECHO) {
                        $new .= $ts;
                        $ot = T_OPEN_TAG_WITH_ECHO;
                        $iw = true;
                    } elseif($tn == T_CLOSE_TAG) {
                        if($ot == T_OPEN_TAG_WITH_ECHO) {
                            $new = rtrim($new, "; ");
                        } else {
                            $ts = " ".$ts;
                        }
                        $new .= $ts;
                        $ot = null;
                        $iw = false;
                    } elseif(in_array($tn, $IW)) {
                        $new .= $ts;
                        $iw = true;
                    } elseif($tn == T_CONSTANT_ENCAPSED_STRING
                           || $tn == T_ENCAPSED_AND_WHITESPACE) {
                        if($ts[0] == '"') {
                            $ts = addcslashes($ts, "\n\t\r");
                        }
                        $new .= $ts;
                        $iw = true;
                    } elseif($tn == T_WHITESPACE) {
                        $nt = isset($tokens[$i+1]) ? $tokens[$i+1] : null;
                        if(!$iw && (!is_string($nt) || $nt == '$') && !in_array($nt[0], $IW)) {
                            $new .= " ";
                        }
                        $iw = false;
                    } elseif($tn == T_START_HEREDOC) {
                        $new .= "<<<S\n";
                        $iw = false;
                        $ih = true; // in HEREDOC
                    } elseif($tn == T_END_HEREDOC) {
                        $new .= "S;";
                        $iw = true;
                        $ih = false; // in HEREDOC
                        for($j = $i+1; $j < $c; $j++) {
                            if(is_string($tokens[$j]) && $tokens[$j] == ";") {
                                $i = $j;
                                break;
                            } else if($tokens[$j][0] == T_CLOSE_TAG) {
                                break;
                            }
                        }
                    } elseif($tn == T_COMMENT || $tn == T_DOC_COMMENT) {
                        $iw = true;
                    } else {
                        if(!$ih) {
                            // $ts = strtolower($ts);
                            // ^ valid, but we want to keep case.
                        }
                        $new .= $ts;
                        $iw = false;
                    }
                }
                $ls = "";
            } else {
                if(($token != ";" && $token != ":") || $ls != $token) {
                    $new .= $token;
                    $ls = $token;
                }
                $iw = true;
            }
        }
        return new T_Code_Php($new);
    }

    /**
     * Gets the tokenized source code.
     *
     * @return array  tokens
     */
    protected function tokenize()
    {
        if (!isset($this->cache['tokens'])) {
            $this->cache['tokens'] = token_get_all($this->src);
        }
        return $this->cache['tokens'];
    }

}