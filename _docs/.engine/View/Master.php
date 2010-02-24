<?php
/**
 * Defines the View_Master class.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Master view.
 *
 * @package docs
 */
class View_Master extends T_Template_File
{

    function __construct(T_Environment $env)
    {
        parent::__construct($env->find('master','tpl'));
        $nav = $env->like('Navigation');

        // setup default
        $this->title = 'Title Placeholder';
        $this->primary = 'Primary Content Placeholder';
        $this->nav = $nav;

        // setup helpers
        $this->addHelper(array($nav,'url'),'url');
        $p = new T_Template_Helper_Placeholder();
        $this->addHelper(array($p,'get'),'placeholder');
        $this->addHelper(array($env,'getAppRoot'),'root');
        $this->addHelper(array(new T_Xhtml_Php,'transform'),'php');
    }

}
