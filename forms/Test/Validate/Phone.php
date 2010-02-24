<?php
class T_Test_Validate_Phone extends T_Test_Filter_SkeletonHarness
{

    function testFilterHasNoEffectOnValidPhoneNumbers()
    {
        $valid = array( '123456',
                        '12345678901234567890',
                        '(01234) 3456657',
                        '+44 89 78 67 54',
                        '01234 545635 x 4353',
                        '06347121 ext 3456',
                        '06347121 EXT 3456',
                        '123-123-123',
                        '+44 (0) 1234 543545' );
        $filter = new T_Validate_Phone;
        foreach ($valid as $phone) {
        	$this->assertSame($phone,$filter->transform($phone));
        }
    }

    function testFilterFailsWithAnInvalidPhone()
    {
        $invalid = array( '',
                          '12345',
                          '123456789012345678901',
                          'not a phone',
                          '123 @ 12312',
                          '(12)   -   - --- - ',
                          'ext');
        $filter = new T_Validate_Phone;
        foreach ($invalid as $phone) {
            try {
                $filter->transform($phone);
                $this->fail("$phone is not a valid phone");
            } catch (T_Exception_Filter $e) { }
        }
    }

    function testPipePriorFilter()
    {
        $filter = new T_Validate_Phone('mb_strtolower');
        $this->assertSame($filter->transform('12345 EXT 123'),'12345 ext 123');
    }

}