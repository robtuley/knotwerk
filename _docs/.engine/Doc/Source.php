<?php
/**
 * Contains the Source controller.
 *
 * @package docs
 * @author Rob Tuley
 * @version SVN: $Id$
 */

/**
 * HTTP Source index controller.
 *
 * @package docs
 */
class Doc_Source extends Doc_Controller
{

    /**
     * Package gateway.
     *
     * @var Package_Gateway
     */
    protected $gateway = false;

    /**
     * Handles the request.
     *
     * @param T_Response $response
     * @throws T_Response
     */
    function handleRequest($response)
    {
        $nav = $this->like('Navigation');
        $section = $nav->get()->ref->setActive();
        $nav->appendCrumb($section->getTitle(),$this->url->getUrl());
        parent::handleRequest($response);
    }

    /**
     * Gets the package gateway.
     *
     * @return Package_Gateway
     */
    function getGateway()
    {
        if ($this->gateway===false) {
            $this->gateway = $this->like('Package_Gateway');
        }
        return $this->gateway;
    }

    /**
     * Executes a GET request.
     *
     * @param T_Response $response  response to build.
     */
    function GET($response)
    {
        $packages = $this->getGateway()->getAll();

        $view = $response->getContent();
        $view->title = 'Knotwerk Documentation';
        $view->primary = new T_Template_File($this->find('ref','tpl'));
        $view->primary->packages = $packages;
    }

    /**
     * Always map to package controller.
     *
     * @param string $name  URL segment to map to a classname
     * @return string   controller classname
     */
    function mapToClassname($name)
    {
        return 'Doc_Package';
    }

}
