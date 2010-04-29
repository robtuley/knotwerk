#!/usr/bin/php -q
<?php
/**
 * Execute unit tests from the terminal.
 *
 * @package test
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/*
 BOOTSTRAP LIBRARY
 -----------------
 This test script can be used to test the library under both autoload
 conditions and compiled packages. By default the library bootstraps and
 sets up all packages for autoload, unless manual -b bootstrapping has been
 specified. We search for manual bootstrap:
*/
$bootstrap = array();
for ($i=0; $i<$_SERVER["argc"]; $i++) {
    if (strcmp($_SERVER["argv"][$i],'-b')===0) {
        $i++;
        if (isset($_SERVER["argv"][$i])) {
            $bootstrap[] = $_SERVER["argv"][$i];
        } else {
            fwrite(STDERR,'All -b flags must be followed by a bootstrap file name');
            exit(1);
        }
    }
}
if (count($bootstrap)==0) {
    require dirname(__FILE__).'/bootstrap.php';  // default bootstrap
} else {
    foreach ($bootstrap as $b) if (is_file($b)) require $b;
      // ^ directly bootstrap any files
}

/*
 DEFINE TEST ENVIRONMENT
 -----------------------
 The test environment is created
*/
class T_Environment_Test extends T_Environment_Terminal
{

    function __construct($bootstrap)
    {
        parent::__construct();
        // autoload ALL packages if no custom bootstrap
        if (count($bootstrap)==0) {
            foreach (glob(dirname(__FILE__).'/*') as $dir) {
                $name = _end(array_filter(preg_split('@[/\\\\]@',$dir)));
                if (is_dir($dir) && ctype_alpha(substr($name,0,1))) {
                    $rule = new T_Find_ClassInDir($dir,'T_');
                    $this->addRule($rule);
                }
            }
        } else {
            foreach ($bootstrap as $dir) {
                if (is_dir($dir)) {
                    $rule = new T_Find_ClassInDir($dir,'T_');
                    $this->addRule($rule);
                }
            }
            // make sure unit package is autoloaded
            $this->addRule(new T_Find_ClassInDir(T_ROOT_DIR.'unit','T_'));
        }
        // setup error handling
        $this->like('T_Exception_Handler')
             ->append(new T_Exception_Handler_Terminal(E_ALL|E_STRICT));
    }

    function parseInput()
    {
        $data = array('package'=>array(),'class'=>array());
        for ($i=0; $i<$_SERVER["argc"]; $i++) {
            switch($_SERVER["argv"][$i]) {
                case '-p':
                    $i++;
                    if (isset($_SERVER["argv"][$i])) {
                        $data['package'][] = $_SERVER["argv"][$i];
                    } else {
                        $msg = 'All -p flags must be followed by a package name';
                        throw new T_Exception_Arg($msg);
                    }
                    break;
                case '-c':
                    $i++;
                    if (isset($_SERVER["argv"][$i])) {
                        $data['class'][] = $_SERVER["argv"][$i];
                    } else {
                        $msg = 'All -c flags must be followed by a class name';
                        throw new T_Exception_Arg($msg);
                    }
                    break;
                case "-?":
                case "-h":
                case "--help":
                    $data['help'] = true;
            }
        }
        return array('OPT'=>new T_Cage_Array($data));
    }
}

// create environment & validate args
$err = false;
try {
    $env  = new T_Environment_Test($bootstrap);
    $args = $env->input('OPT');
    $classes = $args->asArray('class')->uncage();
    foreach ($classes as $c) {
        if (!class_exists($c)) throw new T_Exception_Arg("$c is not a class.");
    }
    $packages = $args->asArray('package')->uncage();
    foreach ($packages as $p) {
        if (!is_dir(T_ROOT_DIR.$p)) {
            throw new T_Exception_Arg("$p is not a package.");
        }
        // make sure autoload setup for test dir
        $env->addRule(new T_Find_ClassInDir(T_ROOT_DIR.$p.'/Test','T_Test_'));
    }
} catch (T_Exception_Arg $e) {
    fwrite(STDERR,$e->getMessage().EOL.EOL);
    $err = true;
}
if ($err || $args->exists('help')) {
    echo <<<USAGE
Usage: test [-p <package>] [-c <class>]
 -p <package>   include all tests from this package.
 -c <class>     includes all tests for a specific class.
 -b <bootstrap>
 -h             display usage instructions

If no args are supplied, all available tests will be executed. The -c and -p
args can be used together, or multiple times to construct a custom test suite.
USAGE;
    exit($err ? 1 : 0);
}

// display intro
echo 'Knotwerk Unit Tests'.EOL;
if (count($packages)) echo '[package: '.implode(',',$packages).']'.EOL;
if (count($classes)) echo '[class: '.implode(',',$classes).']'.EOL;

// if no packages, include all!
if (count($classes)==0 && count($packages)==0) {
    $dir = new T_File_Dir(T_ROOT_DIR);
    foreach ($dir as $sub) {
        if (!($sub instanceof T_File_Dir)) continue;
        $ds = DIRECTORY_SEPARATOR;
        $name = _end(explode($ds,rtrim($sub->__toString(),$ds)));
        if (ctype_alpha(substr($name,0,1))) {
            $packages[] = $name;
        }
    }
    // @todo add docs
}

// build test suite
$suite = new T_Unit_Suite();
foreach ($packages as $p) {
    if (strpos($p,'_docs')!==false) {
        // bootstraps docs area
        require_once T_ROOT_DIR.'_docs/.engine/bootstrap'.T_PHP_EXT;
        $suite->addChild( new T_Unit_Directory(ENGINE_DIR.'Test','Test') );
    } else {
        $suite->addChild( new T_Unit_PackageSuite($p) );
    }
}
foreach ($classes as $c) {
    $test = 'T_Test_'.substr($c,2);
    if (class_exists($test)) {
        $suite->addChild( new $test() );
    }
}

// attach observers and renderers
$suite->attach(new T_Unit_TerminalDisplay())
      ->attach(new T_Unit_XmlLog(T_CACHE_DIR.'unit.log.xml'));
/*
if (T_Unit_CodeCoverage::isAvailable()) {
    $coverage = new T_Unit_CodeCoverage();
    $coverage->scanDir(dirname(__FILE__))
             ->addRender(new T_Unit_CoverageForTerminal());
    $suite->attach($coverage);
}
*/

// run tests
$suite->execute();

exit(0); // exit code all OK
