<?php
/**
 * Bootstraps the HTML documentation.
 */

// add docs constants
define('DOC_DIR',T_ROOT_DIR.'_docs/');
define('ENGINE_DIR',DOC_DIR.'.engine/');
define('ASSET_DIR',DOC_DIR.'.assets/');
define('DEMO_DIR',T_ROOT_DIR.'_demo/');

// add find rules
$env->addRule(new T_Find_ClassInDir(ENGINE_DIR))
    ->addRule(new T_Find_FileInDir(ENGINE_DIR.'_tpl/','tpl'));
