<?php
/**
 * An interface for agent inspection parsers.
 *
 * @package client
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */
interface T_Agent_Parser
{

    // no fixed parser methods, any method can be added and as long as it
    // returns null or an instance of T_Agent_Parser it will be proxied
    // OK by the T_Agent_Inspection container.

}