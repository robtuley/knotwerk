<?php
class T_Test_Validate_Email extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectOnValidEmailAddresses()
    {
        $valid = array( 'mail@example.com',
                        'mail@domain.co.uk',
                        'mail@domain.subdomain.museum',
                        'some.name@domain.com',
                        'some_name-here@domain.subdomain.com',
                        'mail@domain-domain.co.uk',
                        'mail@domain.fr',
                        'user+addressing@domain.com',
                        'user_with.and-with@domain.com',
                        'user123@domain-with-dashes.jp',
                        '123user@domain.com',
                        '123456@just-digits.com',
                        'user@single.a.subdomain.co.uk',
                        '1234+56@domain.com',
                        'per%cent@is-also-valid.com',
                        'user@digits1234indomain.com',
                        'user@o2.com',
                        'k&k@example.net',
                        'o\'connor@example.com',
                        'a@example.com',
                        'user_@example.com',
                        '_user@example.com',
                        '_@example.com' );
        $filter = new T_Validate_Email();
        foreach ($valid as $email) {
        	$this->assertSame($email,$filter->transform($email));
        }
    }

    function testFilterComparesInCaseInsensitiveManner()
    {
        $filter = new T_Validate_Email();
        $this->assertSame($filter->transform('rOb@eXample.Com'),'rOb@eXample.Com');
    }

    function testFilterFailsWithAnInvalidEmailAddress()
    {
        $invalid = array( 'not.an.email',
                          'mail at example dot com',
                          'mail@ example.com',
                          'mail@example_underscore.com',
                          'mail@exam@ple.com',
                          'mail@example+noplus.com',
                          'mail@example.c',
                          'mail@-example.com',
                          'mail@com',
                          'mail@example.com*&^%$',
                          'email is mail@example.com',
                          'us er@example.com',
                          'ghet£jh@example.com',
                          true,
                          123675,
                          1.4345364,
                          '',
                          'rob@example.com'."\n".'invalid bit',
                          'rob@example.com'."\r\n".'invalid bit',
                          'invalid bit'."\n".'rob@example.com',
                          'user@example-.com',
                          '@example.com',
                          'user@example.c',
                          'user@example.toolong' );
        $filter = new T_Validate_Email();
        foreach ($invalid as $email) {
            try {
                $filter->transform($email);
                $this->fail("$email is not a valid email");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testPipePriorFilter()
    {
        $filter = new T_Validate_Email('mb_strtolower');
        $this->assertSame($filter->transform('rOb@eXample.Com'),'rob@example.com');
    }

}