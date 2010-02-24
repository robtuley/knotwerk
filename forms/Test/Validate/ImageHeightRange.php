<?php
/**
 * Unit test cases for the T_Validate_ImageHeightRange class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_ImageHeightRange unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_ImageHeightRange extends T_Test_Filter_SkeletonHarness
{

   function testAcceptsAImageWithHeightWithinRange()
   {
       $filter = new T_Validate_ImageHeightRange(30,50);
       $img = new T_Image_Gd(20,40);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(20,30);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(20,50);
       $this->assertSame($img,$filter->transform($img));
   }

   function testRejectsImageWhereHeightGreaterThanRange()
   {
       $filter = new T_Validate_ImageHeightRange(30,50);
       $img = new T_Image_Gd(20,51);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testRejectsImageWhereHeightLessThanRange()
   {
       $filter = new T_Validate_ImageHeightRange(30,50);
       $img = new T_Image_Gd(20,29);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullLowerLimitRepresentsNoLimit()
   {
       $filter = new T_Validate_ImageHeightRange(null,50);
       $img = new T_Image_Gd(20,1);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(20,51);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullUpperLimitRepresentsNoLimit()
   {
       $filter = new T_Validate_ImageHeightRange(30,null);
       $img = new T_Image_Gd(20,100);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(20,29);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testNullForBothLimitsRepresentsNoLimitsAtAll()
   {
       $filter = new T_Validate_ImageHeightRange(null,null);
       $img = new T_Image_Gd(20,100);
       $this->assertSame($img,$filter->transform($img));
       $img = new T_Image_Gd(20,1);
       $this->assertSame($img,$filter->transform($img));
   }

   function testCanPipePriorFilter()
   {
       $filter = new T_Validate_ImageHeightRange(30,50,new T_Test_Filter_Failure());
       $img = new T_Image_Gd(20,40);
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

}