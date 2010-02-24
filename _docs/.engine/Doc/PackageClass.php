<?php
/**
 * Contains the package class controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Package class controller.
 *
 * @package docs
 */
class Doc_PackageClass extends Doc_Controller
{

    /**
     * The class.
     *
     * @var T_Src_PhpClass
     */
    protected $class;

    /**
     * Handles the request.
     *
     * @param T_Response $response
     * @throws T_Response
     */
    function handleRequest($response)
    {
        $name = _end($this->getUrl()->getPath());
        $path = $this->getPackage()->getDir().
                implode('/',array_slice(explode('_',$name),1)).T_PHP_EXT;
        if (strncasecmp($name,'T_',2)!==0 ||
            !is_file($path) ||
            (!class_exists($name,true) && !interface_exists($name)) ) {
            $this->respondWithStatus(404,$response);
        }
        $this->class = new ReflectionClass($name);
        $this->like('Navigation')->appendCrumb($name,$this->url->getUrl());
        parent::handleRequest($response);
    }

    /**
     * Gets the package.
     *
     * @return Package
     */
    function getPackage()
    {
        return $this->context->getPackage();
    }

    /**
     * Gets the package gateway.
     *
     * @return Package_Gateway
     */
    function getGateway()
    {
        return $this->context->getGateway();
    }

    /**
     * Executes a GET request.
     *
     * @param T_Response $response  response to build.
     */
    function GET($response)
    {
        $view = $response->getContent();
        $view->title = $this->class->getName().' Docs';
        $view->primary = new T_Template_File($this->find('class','tpl'));
        $view->primary->package = $this->getPackage();
        $view->primary->class = $this->class;
    }

}
