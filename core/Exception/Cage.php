<?php
/**
 * Contains the class T_Exception_Cage.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Cage Exception.
 *
 * A T_Exception_Cage error is thrown during illegal use or access of caged
 * data. In production code, such an exception usually means someone is messing
 * around with illegal input parameters.
 *
 * @package core
 */
class T_Exception_Cage extends Exception { }