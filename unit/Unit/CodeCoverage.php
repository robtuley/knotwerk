<?php
/**
 * Contains the T_Unit_TerminalDisplay class.
 *
 * @package unit
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Observer to display test results when executed in a terminal window.
 *
 * @package unit
 * @license http://knotwerk.com/licence MIT
 */
class T_Unit_CodeCoverage implements T_Unit_Observer
{

    /**
     * Whether code coverage info is available.
     *
     * @return bool
     */
    static function isAvailable()
    {
        return function_exists('xdebug_start_code_coverage');
    }

    /**
     * Directories to scan for code coverage in.
     *
     * @var array
     */
    protected $dir = array();

    /**
     * Filters to render stats.
     *
     * @var function[]
     */
    protected $render = array();

    /**
     * Number of executable lines executed.
     *
     * @var int
     */
    protected $executed = 0;

    /**
     * Array of line numbers missed.
     *
     * @var array
     */
    protected $missed = array();

    /**
     * Declaration end.
     *
     * @var array
     */
    protected $declare_end = null;

    /**
     * Adds a directory to scan for code coverage.
     *
     * @param string $path  directory path
     * @return T_Unit_CodeCoverage  fluent interface
     */
    function scanDir($path)
    {
        $dir = realpath($path);  // resolve relative, normalize dir sep, etc.
        if ($dir === false) {
            throw new RuntimeException("Failed to resolve $path");
        }
        $this->dir[] = $dir;
        return $this;
    }

    /**
     * Adds a filter to render stats.
     *
     * @param function $filter
     * @return T_Unit_CodeCoverage  fluent interface
     */
    function addRender(T_Filter $filter)
    {
        $this->render[] = $filter;
        return $this;
    }

    /**
     * Gets the number of lines executed.
     *
     * @return int
     */
    function getNumExe()
    {
        return $this->executed;
    }

    /**
     * Gets the number of missed lines.
     *
     * @return int
     */
    function getNumNotExe()
    {
        return array_sum(array_map('count',$this->missed));
    }

    /**
     * Gets an array of the missed executable lines.
     *
     * @return array  key is path, values are line numbers
     */
    function getNotExe()
    {
        return $this->missed;
    }

    /**
     * Gets the coverage ratio.
     *
     * @return float
     */
    function getCoverageRatio()
    {
        if ($this->getNumExe()==0) return 0; // protected against div by zero
        return $this->getNumExe()/($this->getNumExe()+$this->getNumNotExe());
    }

    /**
     * Start code coverage.
     *
     * @return T_Unit_Observer  fluent interface
     */
    function init()
    {
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED|XDEBUG_CC_DEAD_CODE);
    }

    /**
     * Complete test run.
     *
     * @return T_Unit_Observer  fluent interface
     */
    function complete()
    {
        $coverage = xdebug_get_code_coverage();
        xdebug_stop_code_coverage();

        // compile coverage stats
        $this->executed = 0;
        $this->missed = array();

        foreach ($this->dir as $dir) {
            $dir = new T_File_Dir($dir);
            $this->processDir($dir,$this->executed,$this->missed,$coverage);
        }

        // render with any embedded filters
        foreach ($this->render as $filter) {
            echo _transform($this,$filter);
        }
    }

    /**
     * Processes a directory and recurses to sub-directories.
     *
     * @param T_File_Dir $dir
     * @param int $executed
     * @param array $missed
     * @param array $coverage
     */
    protected function processDir(T_File_Dir $dir,&$executed,&$missed,&$coverage)
    {
        foreach ($dir as $file) {
            if ($file instanceof T_File_Dir) {
                $path = explode(DIRECTORY_SEPARATOR,rtrim($file->__toString(),DIRECTORY_SEPARATOR));
                $path = array_pop($path);
                if (strncmp($path,'.',1)!==0 &&
                    strncmp($path,'_',1)!==0 &&
                    strcmp($path,'Test')!==0) {
                    // skip hidden directory (e.g. .svn,_todo,etc.), and any 'Test' files
                    $this->processDir($file,$executed,$missed,$coverage);
                }
            } else {
                if ($file->getMime()->getType()!==T_Mime::PHP ) continue;
                if (strncmp($file->getFilename(),'_',1)===0) continue;
                if (strncmp($file->getFilename(),'.',1)===0) continue;
                $path = realpath($file->__toString());
                $executable = $this->getExeLines($path);
                $is_executed = array();
                if (isset($coverage[$path])) {
                    $is_executed = $coverage[$path];
                }
                foreach ($executable as $line) {
                    if (isset($is_executed[$line]) && $is_executed[$line]>0) {
                        ++$executed;
                    } else {
                        if (!isset($missed[$path])) $missed[$path] = array();
                        $missed[$path][] = $line;
                    }
                }
            }
        }
    }

    /**
     * Register a test error.
     *
     * @param Exception $error
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function error(Exception $error,ReflectionMethod $method) { }

    /**
     * Register a test failure.
     *
     * @param T_Exception_TestFail $fail
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function fail(T_Exception_TestFail $fail,ReflectionMethod $method) { }

    /**
     * Register a test skip.
     *
     * @param T_Exception_TestSkip $skip
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function skip(Exception $skip,$method_or_class) { }

    /**
     * Register a test pass.
     *
     * @param ReflectionMethod $method
     * @return T_Unit_Observer  fluent interface
     */
    function pass(ReflectionMethod $method) { }

    /**
     * Get executable lines.
     *
     * @param string $path  path of source file
     * @return array  array of executable line numbers.
     */
    protected function getExeLines($path)
    {
        $line = 1;
        $is_declare = false;
        $exe = array();
        $tokens = token_get_all(file_get_contents($path));
        foreach ($tokens as $t) {

            /* standardize token, and get whether is executable */
            if (is_array($t)) {
                list($t_type,$t_content) = $t;
                $is_exe = $this->isExeToken($t_type);
                $is_declare = $this->isDeclared($t_type,$t_content);
            } else {
                $t_type  = false;
                $t_content = $t;
                $is_exe = false;
                $is_declare = $this->isDeclared($t_type,$t_content);
            }

            /* EOL markers are contained in tokens.. */
            $t_content = explode("\n",$t_content);
            for ($i=0,$max=count($t_content)-1; $i<=$max; $i++) {

                if ($i>0) {
                    ++$line;  // EOL marker found
                }
                if ($is_exe && !$is_declare) $exe[$line] = true;

            }
        }

        if (!is_null($this->declare_end)) {
            throw new RuntimeException("Declaration still open at file end $path");
        }
        return array_keys($exe);
    }

    /**
     * Whether a token type is 'executed' in normal operation.
     *
     * @param int $token  token constant
     * @return bool  whether is executable
     */
    protected function isExeToken($token)
    {
        switch ($token) {
            case T_OPEN_TAG:
            case T_OPEN_TAG_WITH_ECHO:
            case T_CLOSE_TAG:
            case T_COMMENT:
            case T_DOC_COMMENT:
            case T_INLINE_HTML:
            case T_ABSTRACT:
            case T_AS:
            case T_BREAK:
            case T_CLASS:
            case T_CASE:
            case T_DECLARE:
            case T_DEFAULT:
            case T_DO:
            case T_ELSE:
            case T_EXTENDS:
            case T_FINAL:
            case T_FUNCTION:
            case T_GLOBAL:
            case T_IMPLEMENTS:
            case T_INCLUDE:
            case T_INCLUDE_ONCE:
            case T_INTERFACE:
            case T_PRIVATE:
            case T_PUBLIC:
            case T_PROTECTED:
            case T_REQUIRE:
            case T_REQUIRE_ONCE:
            case T_STATIC:
            case T_TRY:
            case T_CATCH:
            case T_CONTINUE:
            case T_CURLY_OPEN:
            case T_ENCAPSED_AND_WHITESPACE:
            case T_ENDDECLARE:
            case T_ENDFOR:
            case T_ENDFOREACH:
            case T_ENDIF:
            case T_ENDSWITCH:
            case T_ENDWHILE:
            case T_RETURN:
            case T_WHILE:
            case T_WHITESPACE:
                return false;
            default:
                return true;
        }
    }

    /**
     * Whether a code is in process of a declaration.
     *
     * Since declarations are executed at compile time, they are not 'executed'
     * during normal operation. This includes class declarations, function
     * declarations, etc. A declaration has a start and end point that may span
     * multiple lines. This method tracks whether we are inside a declaration
     * at the present time.
     *
     * @param int $token  token constant
     * @return bool  whether is declaration
     */
    protected function isDeclared($token,$content)
    {
        if (is_null($this->declare_end)) {
            switch ($token) {
                case T_ABSTRACT:
                case T_CLASS:
                case T_EXTENDS:
                case T_FINAL:
                case T_FUNCTION:
                case T_IMPLEMENTS:
                case T_INTERFACE:
                case T_TRY:
                case T_CATCH:
                case T_GLOBAL:
                case T_INCLUDE:
                case T_INCLUDE_ONCE:
                case T_PRIVATE:
                case T_PUBLIC:
                case T_PROTECTED:
                case T_CONST:
                case T_REQUIRE:
                case T_REQUIRE_ONCE:
                case T_STATIC:
                    $this->declare_end = array(';','{');
                      /* all have both curly brackets and semicolon delimiter
                         because statements like 'protected function xxx' and
                         'protected $var' are very difficult to tell apart */
                    return true;
            }
            return false;
        } else {
            foreach ($this->declare_end as $char) {
                if (strpos($content,$char)!==false) {
                    $this->declare_end = null;
                    break;
                }
            }
            return true;
        }
    }

}
