<?php
/**
 * XHTML Docs front controller.
 * @version SVN: $Id$
 */

require_once dirname(__FILE__).'/bootstrap.php';
$env = new T_Environment_Http();

// load packages and bootstrap docs
foreach (glob(dirname(__FILE__).'/*') as $dir) {
    $name = _end(array_filter(preg_split('@[/\\\\]@',$dir)));
    if (is_dir($dir) && ctype_alpha(substr($name,0,1))) {
        $rule = new T_Find_ClassInDir($dir,'T_');
        $env->addRule($rule);
    }
}
require_once T_ROOT_DIR.'_docs/.engine/bootstrap'.T_PHP_EXT;

// setup error handling
$env->like('T_Exception_Handler')
    ->append(new T_Exception_Handler_Debug(E_ALL|E_STRICT));

// dispatch
$app = new Doc_Root(new T_Controller_Request($env));
$app->dispatch();
