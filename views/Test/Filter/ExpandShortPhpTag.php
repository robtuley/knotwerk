<?php
/**
 * Unit test cases for the T_Filter_ExpandShortPhpTag class.
 *
 * @package viewTests
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * T_Filter_ExpandShortPhpTag test cases.
 *
 * @package viewTests
 */
class T_Test_Filter_ExpandShortPhpTag extends T_Test_Filter_SkeletonHarness
{

    function testHasNoEffectOnFullPhpTag()
    {
        $filter = new T_Filter_ExpandShortPhpTag();
        $expected = '<?php echo "normal PHP"; ?>';
        $this->assertSame($filter->transform($expected),$expected);
    }

    function testExpandsShortPhpTag()
    {
        $filter = new T_Filter_ExpandShortPhpTag();
        if (!ini_get('short_open_tag')) {
            $this->assertSame($filter->transform('<? echo "normal PHP"; ?>'),
                              '<?php echo "normal PHP"; ?>');
        } else {
            $this->assertSame($filter->transform('<? echo "normal PHP"; ?>'),
                              '<? echo "normal PHP"; ?>');
        }
    }

    function testExpandsShortPhpTagWhenEmbeddedInOtherCode()
    {
        $filter = new T_Filter_ExpandShortPhpTag();
        $code = 'Some embedded <? echo "PHP"; ?> and <? echo "tags"; ?>';
        if (!ini_get('short_open_tag')) {
            $this->assertSame($filter->transform($code),
                              'Some embedded <?php echo "PHP"; ?> and <?php echo "tags"; ?>');
        } else {
            $this->assertSame($filter->transform($code),$code);
        }
    }

    function testExpandsShortPhpTagWithUnixLineEndings()
    {
        $filter = new T_Filter_ExpandShortPhpTag();
        $code = '<?'."\n".'echo "normal PHP"; ?>';
        if (!ini_get('short_open_tag')) {
            $this->assertSame($filter->transform($code),
                              '<?php echo "normal PHP"; ?>');
        } else {
            $this->assertSame($filter->transform($code),$code);
        }
    }

    function testExpandsShortPhpTagWithWindowsLineEndings()
    {
        $filter = new T_Filter_ExpandShortPhpTag();
        $code = '<?'."\r\n".'echo "normal PHP"; ?>';
        if (!ini_get('short_open_tag')) {
            $this->assertSame($filter->transform($code),
                              '<?php echo "normal PHP"; ?>');
        } else {
            $this->assertSame($filter->transform($code),$code);
        }
    }

    function testExpandsShortPhpAndEchoTag()
    {
        $filter = new T_Filter_ExpandShortPhpTag();
        if (!ini_get('short_open_tag')) {
            $this->assertSame($filter->transform('<?="normal PHP"; ?>'),
                              '<?php echo "normal PHP"; ?>');
        } else {
            $this->assertSame($filter->transform('<?="normal PHP"; ?>'),
                              '<?="normal PHP"; ?>');
        }
    }

    function testPipePriorFilter()
    {
        $prior = new T_Test_Filter_Suffix();
        $filter = new T_Filter_ExpandShortPhpTag($prior);
        if (!ini_get('short_open_tag')) {
            $expected = $prior->transform('<?php echo "normal PHP"; ?>');
            $this->assertSame($filter->transform('<?="normal PHP"; ?>'),$expected);
        } else {
            $expected = $prior->transform('<?="normal PHP"; ?>');
            $this->assertSame($filter->transform('<?="normal PHP"; ?>'),$expected);
        }
    }

}