<?php
/**
 * Unit test cases for the T_Validate_ImageWidthRange class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_ImageWidthRange unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_ImageWidthRange extends T_Test_Filter_SkeletonHarness
{

   function testAcceptsAImageWithWidthWithinRange()
   {
       $filter = new T_Validate_ImageWidthRange(30,50);
       $img = new T_Image_Gd(40,20);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(30,20);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(50,20);
       $this->assertSame($img,$filter->transform($img));
   }

   function testRejectsImageWhereWidthGreaterThanRange()
   {
       $filter = new T_Validate_ImageWidthRange(30,50);
       $img = new T_Image_Gd(51,20);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testRejectsImageWhereWidthLessThanRange()
   {
       $filter = new T_Validate_ImageWidthRange(30,50);
       $img = new T_Image_Gd(29,20);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullLowerLimitRepresentsNoLimit()
   {
       $filter = new T_Validate_ImageWidthRange(null,50);
       $img = new T_Image_Gd(1,20);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(51,20);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullUpperLimitRepresentsNoLimit()
   {
       $filter = new T_Validate_ImageWidthRange(30,null);
       $img = new T_Image_Gd(100,20);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(29,20);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullForBothLimitsRepresentsNoLimitsAtAll()
   {
       $filter = new T_Validate_ImageWidthRange(null,null);
       $img = new T_Image_Gd(100,20);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(1,20);
       $this->assertSame($img,$filter->transform($img));
   }

   function testCanPipePriorFilter()
   {
       $filter = new T_Validate_ImageWidthRange(30,50,new T_Test_Filter_Failure());
       $img = new T_Image_Gd(40,20);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

}