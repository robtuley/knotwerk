<?php
/**
 * Contains the Package controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * Package controller.
 *
 * @package docs
 */
class Doc_Package extends Doc_Controller
{

    /**
     * The package.
     *
     * @var Package
     */
    protected $package;

    /**
     * Handles the request.
     *
     * @param T_Response $response
     * @throws T_Response
     */
    function handleRequest($response)
    {
        $name = _end($this->getUrl()->getPath());
        try {
            $this->package = $this->getGateway()->getByName($name);
        } catch (Exception $e) {
            $this->respondWithStatus(404,$response);
        }
        $nav = $this->like('Navigation');
        $nav->get()->ref->{$this->package->getAlias()}->setActive();
        $nav->appendCrumb($this->package->getName(),$this->url->getUrl());
        parent::handleRequest($response);
    }

    /**
     * Gets the package.
     *
     * @return Package
     */
    function getPackage()
    {
        return $this->package;
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
        $view->title = $this->getPackage()->getName().' Package Docs';
        $view->primary = new T_Template_File($this->find('package','tpl'));
        $view->primary->package = ($p=$this->getPackage());
        $others = $this->getGateway()->getAll();
        unset($others[$p->getAlias()]);
        $view->primary->others = $others;
    }

    /**
     * Always map to package controller.
     *
     * @param string $name  URL segment to map to a classname
     * @return string   controller classname
     */
    function mapToClassname($name)
    {
        return 'Doc_PackageClass';
    }

}
