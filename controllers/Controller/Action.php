<?php
/**
 * Contains the T_Controller_Action interface.
 *
 * @package controllers
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Interface for all controller actions.
 *
 * @package controllers
 * @license http://knotwerk.com/licence MIT
 */
interface T_Controller_Action extends T_Controller_Context
{

    /**
     * Handles the request.
     *
     * Method to handle the request. The function may delegate to a
     * sub-controller, or execute the request itself.
     *
     * @param T_Response $response  response to send to current request
     * @throws T_Response  alternative response in exceptional circumstances
     */
    function handleRequest($response);

    /**
     * Dispatches the request.
     *
     * Method to dispatch the request. This method begins the delegation chain
     * to sub-controllers to handle the current request. Once the chain is
     * complete, the method sends the response.
     */
    function dispatch();

}
