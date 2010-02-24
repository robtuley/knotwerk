<?php
/**
 * Unit test cases for the T_Validate_ImageAspectRange class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_ImageAspectRange unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_ImageAspectRange extends T_Test_Filter_SkeletonHarness
{

   function testAcceptsAImageWithAspectWithinRange()
   {
       $filter = new T_Validate_ImageAspectRange(0.5,2);
       $img = new T_Image_Gd(8,8);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(4,8);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(16,8);
       $this->assertSame($img,$filter->transform($img));
   }

   function testRejectsImageWhereAspectGreaterThanRange()
   {
       $filter = new T_Validate_ImageAspectRange(0.5,2);
       $img = new T_Image_Gd(17,8);
       try {
           _transform($img,$filter);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testRejectsImageWhereAspectLessThanRange()
   {
       $filter = new T_Validate_ImageAspectRange(0.5,2);
       $img = new T_Image_Gd(3,8);
       try {
           _transform($img,$filter);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullLowerLimitRepresentsNoLimit()
   {
       $filter = new T_Validate_ImageAspectRange(null,2);
       $img = new T_Image_Gd(1,20);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(17,8);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullUpperLimitRepresentsNoLimit()
   {
       $filter = new T_Validate_ImageAspectRange(0.5,null);
       $img = new T_Image_Gd(100,1);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(3,8);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullForBothLimitsRepresentsNoLimitsAtAll()
   {
       $filter = new T_Validate_ImageAspectRange(null,null);
       $img = new T_Image_Gd(100,1);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(1,100);
       $this->assertSame($img,$filter->transform($img));
   }

   function testCanPipePriorFilter()
   {
       $filter = new T_Validate_ImageAspectRange(0.5,2,new T_Test_Filter_Failure());
       $img = new T_Image_Gd(8,8);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

}