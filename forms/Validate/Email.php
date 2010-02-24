<?php
/**
 * Defines the T_Validate_Email class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that string is an email format.
 *
 * @package forms
 * @license http://knotwerk.com/licence MIT
 */
class T_Validate_Email extends T_Filter_Skeleton
{

    /**
     * Checks string is an email.
     *
     * This does NOT allow all valid email addresses through. An email regex that
     * only enforces the spec is discussed in
     * http://iamcal.com/publish/articles/php/parsing_email/ but for practical
     * purposes this is too loose without a domain MX check.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
                          $regex = '/^[a-z\&\d\.\+_\'%-]+'.   // user
                                                       '@'.   // at
                      '(?:(?=[a-z\d])[a-z\d-]*[a-z\d]\.)+'.   // domain.subdomain
                                           '[a-z]{2,6}$/i';   // top level domain
        if (!preg_match($regex,$value)) {
            throw new T_Exception_Filter('must be a valid email address');
        }
        return $value;
    }

}