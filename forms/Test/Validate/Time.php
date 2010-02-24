<?php
/**
 * Unit test cases for the T_Validate_Time class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_Time test cases.
 *
 * @package formTests
 */
class T_Test_Validate_Time extends T_Test_Filter_SkeletonHarness
{

    function testAcceptsTrue24HrTimes()
    {
        $f = new T_Validate_Time();
        $this->assertSame($f->transform('0100'),60*60);
        $this->assertSame($f->transform('0000'),0);
        $this->assertSame($f->transform('0832'),8*60*60+32*60);
        $this->assertSame($f->transform('1200'),12*60*60);
        $this->assertSame($f->transform('1534'),15*60*60+34*60);
        $this->assertSame($f->transform('2359'),23*60*60+59*60);
    }

    function testAcceptsLoose24HrTimes()
    {
        $f = new T_Validate_Time();
        $this->assertSame($f->transform('1:00'),60*60);
        $this->assertSame($f->transform('0.00'),0);
        $this->assertSame($f->transform('8:32'),8*60*60+32*60);
        $this->assertSame($f->transform('12:00'),12*60*60);
        $this->assertSame($f->transform('15:34'),15*60*60+34*60);
        $this->assertSame($f->transform('23.59'),23*60*60+59*60);
    }

    function testAccepts12HrTimes()
    {
        $f = new T_Validate_Time();
        $this->assertSame($f->transform('12:00am'),0,'12:00am');
        $this->assertSame($f->transform('12.32 AM'),32*60,'12.32 AM');
        $this->assertSame($f->transform('1.00am'),60*60,'1.00am');
        $this->assertSame($f->transform('8:32 AM'),8*60*60+32*60,'8:32 AM');
        $this->assertSame($f->transform('12:00pm'),12*60*60,'12:00pm');
        $this->assertSame($f->transform('3:34PM '),15*60*60+34*60,'3:34PM ');
        $this->assertSame($f->transform('11.59 pm'),23*60*60+59*60,'11.59 pm');
    }

    function testAccepts12HrTimesWithNoMinutes()
    {
        $f = new T_Validate_Time();
        $this->assertSame($f->transform('12am'),0,'12am');
        $this->assertSame($f->transform('1 AM'),60*60,'1 AM');
        $this->assertSame($f->transform('8am'),8*60*60,'8am');
        $this->assertSame($f->transform('12pm'),12*60*60,'12pm');
        $this->assertSame($f->transform('3PM '),15*60*60,'3PM ');
        $this->assertSame($f->transform('11 pm'),23*60*60,'11 pm');
    }

    function testRejectsInvalidTimes()
    {
        $invalid = array('3400','2399','-1200','13.45 afterwards','8','45',
                         'pre13.00','12.13yu','14.00pm','notatime');
        $f = new T_Validate_Time();
        foreach ($invalid as $term) {
            try {
                $f->transform($term);
                $this->fail("Accepted $term as a valid time");
            } catch (T_Exception_Filter $e) { }
        }
    }

}