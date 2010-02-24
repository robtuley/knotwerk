#!/usr/bin/php -q
<?php
/**
 * Export Library.
 *
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */
require_once dirname(__FILE__).'/../bootstrap.php';

// define export environment
class T_Environment_Export extends T_Environment_Terminal
{

    function __construct()
    {
        parent::__construct();
        // all classes required (reflection is needed)
        foreach (glob(dirname(__FILE__).'/../*') as $dir) {
            $name = _end(array_filter(preg_split('@[/\\\\]@',$dir)));
            if (is_dir($dir) && ctype_alpha(substr($name,0,1))) {
                $rule = new T_Find_ClassInDir($dir,'T_');
                $this->addRule($rule);
            }
        }
        // setup error handling
        $this->like('T_Exception_Handler')
             ->append(new T_Exception_Handler_Terminal(E_ALL|E_STRICT));
    }

    function parseInput()
    {
        $data = array('add'=>array(),'target'=>null);
        for ($i=0; $i<$_SERVER["argc"]; $i++) {
            switch($_SERVER["argv"][$i]) {
                case '-a':
                    $i++;
                    if (isset($_SERVER["argv"][$i])) {
                        $data['add'][] = $_SERVER["argv"][$i];
                    } else {
                        $msg = 'All -a flags must be followed by a dir or filename';
                        throw new T_Exception_Arg($msg);
                    }
                    break;
                case '-t':
                    $i++;
                    if (isset($_SERVER["argv"][$i])) {
                        $data['target'] = $_SERVER["argv"][$i];
                    } else {
                        $msg = 'The -t flag must be followed by the target';
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
    $env  = new T_Environment_Export();
    $args = $env->input('OPT');

    // items to include
    $add = $args->asArray('add')->uncage();
    foreach ($add as $f) {
        if (!is_dir($f) && !is_file($f)) {
            throw new T_Exception_Arg("$f does not exist (must be directory or file).");
        }
    }

    // target (stdout by default)
    $target = $args->asScalar('target')->uncage();
    $format = 'php';
    if (is_dir($target)) $format = 'dir';
    if ($format==='php') {
        if (is_null($target)) {
            $target = STDOUT;
        } else {
            $file = $target;
            $target = @fopen($file,'wb');
            if (!$target) {
                throw new T_Exception_Arg("Could not open target $file for output");
            }
        }
    } elseif ($format==='dir') {
        // normalise directory target
        $target = str_replace('/',DIRECTORY_SEPARATOR,$target);
        $target = rtrim($target,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

} catch (T_Exception_Arg $e) {
    fwrite(STDERR,$e->getMessage().EOL.EOL);
    $err = true;
}
if ($err || $args->exists('help')) {
    $text = <<<USAGE
Usage: export.php [-t <target>] [-a <dir_or_file>] [-f <format>]
 -t <target>      target (stdout by default), existing dir to create autoload tree
 -a <dir or file> add this file or dir
 -h               display usage instructions
USAGE;
    if ($err) {
        fwrite(STDERR,$text);
        exit(1);
    } else {
        echo $text;
        exit(0);
    }
}

// load files that need to be processed
$src = array();
foreach ($add as $f) {

    // form iterators for dir or single file
    if ($is_dir=is_dir($f)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($f));
    } else {
        $iterator = array($f);
    }

    foreach ($iterator as $f) {
        // check is a PHP file.
        $ext = strrchr($f,'.');
        if (strcmp(T_PHP_EXT,$ext)!==0) continue;
        // exclude hidden dir (start with a .), sample dirs (starts with _),
        // and exclude the Test directory..
        if ( $is_dir && (
                strpos($f,DIRECTORY_SEPARATOR.'.')!==false ||
                strpos($f,DIRECTORY_SEPARATOR.'_')!==false ||
                strpos($f,DIRECTORY_SEPARATOR.'Test')!==false
                        )
           ) continue;
        // now load actual source, and process it.
        $src[] = new T_Code_Php(file_get_contents($f));
    }

}

// order files by dependencies
$filter = new T_Code_Sort();
$src = _transform($src,$filter);

// now package files
$main = null;
$prefix = null;
$svn = 0;
foreach ($src as $php) {

    // process source.
    $php = $php->expand(); // expand includes
    if (($v=$php->getVersion())>$svn) $svn = $v;
    $php = $php->compress(); // compress code

    // export
    switch ($format) {
        case 'dir' :
            $class = $php->getClasses();
            if (count($class)!=1) {
                $class = implode(', ',$class);
                $msg = "All sources must be one class per file to create an autoload tree ($class).";
                echo $php->getInline();
                fwrite(STDERR,$msg);
                exit(2);
            }
            $class = _first($class);

            $dir = explode('_',$class);
            $file = array_pop($dir).T_PHP_EXT;
            $dir = $target.implode(DIRECTORY_SEPARATOR,$dir).DIRECTORY_SEPARATOR;
            if (!is_dir($dir)) {
                $ok = @mkdir($dir,0777,true);
                if (!$ok) {
                    $msg = "Could not create dir $dir";
                    fwrite(STDERR,$msg);
                    exit(3);
                }
            }
            $content = '<?php // @see http://knotwerk.com'.EOL.
                       '      // @version SVN: '.$v.EOL.
                       $php->getInline();
            $ok = @file_put_contents($dir.$file,$content,FILE_TEXT);
            if (!$ok) {
                $msg = "Could not write to file {$dir}{$file}";
                fwrite(STDERR,$msg);
                exit(4);
            }
            break;

        case 'php' :
            $main .= $php->getInline().EOL;
            break;

        default :
            throw new RuntimeException("Could not handle export to $format");
    }

}

// create prefix if PHP export
if ($format==='php') {
    $prefix = '<?php // @see http://knotwerk.com'.EOL.
              '      // @version SVN: '.$svn.EOL;
}

// write out data if it has been cached
if ($prefix)  {
    $ok = @fwrite($target,$prefix);
    if (!$ok) {
        $msg = "Could not write to file $target";
        fwrite(STDERR,$msg);
        exit(4);
    }
}
if ($main) {
    $ok = @fwrite($target,$main);
    if (!$ok) {
        $msg = "Could not write to file $target";
        fwrite(STDERR,$msg);
        exit(4);
    }
}

exit(0); // exit code all OK
