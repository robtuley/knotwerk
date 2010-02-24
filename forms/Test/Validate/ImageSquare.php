<?php
/**
 * Unit test cases for the T_Validate_ImageSquare class.
 *
 * @package formTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Validate_ImageSquare unit test cases.
 *
 * @package formTests
 */
class T_Test_Validate_ImageSquare extends T_Test_Filter_SkeletonHarness
{

   function testAcceptsASquareImage()
   {
       $img = new T_Image_Gd(20,20);
       $filter = new T_Validate_ImageSquare();
       $this->assertSame($img,$filter->transform($img));
   }

   function testRejectsImageWhereWidthLessThanHeight()
   {
       $img = new T_Image_Gd(19,20);
       $filter = new T_Validate_ImageSquare();
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testRejectsImageWhereWidthGreaterThanHeight()
   {
       $img = new T_Image_Gd(21,20);
       $filter = new T_Validate_ImageSquare();
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

   function testCanPipePriorFilter()
   {
       $img = new T_Image_Gd(20,20);
       $filter = new T_Validate_ImageSquare(new T_Test_Filter_Failure());
       try {
           $filter->transform($img);
           $this->fail();
       } catch (T_Exception_Filter $e) { }
   }

}