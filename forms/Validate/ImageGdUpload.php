<?php
/**
 * Defines the OKT_AsImageUpload class.
 *
 * @package forms
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Test that an uploaded file is an image.
 *
 * @package forms
 */
class T_Validate_ImageGdUpload extends T_Filter_Skeleton
{

    /**
     * Asserts an image is of the type claimed.
     *
     * @param T_File_Uploaded $file
     * @param int $type  one of the IMAGETYPE_XX constants
     * @param string $name  name to reference image type
     */
    protected function assertType($file,$type,$name)
    {
        $msg = "is not a valid $name file";
        if (function_exists('exif_imagesize')) {
            if (exif_imagetype($file->__toString())!==$type) {
                 throw new T_Exception_Filter($msg);
            }
        } else {
            /* slower but more widely available */
            $data = getimagesize($file->__toString());
            if (!is_array($data)) throw new T_Exception_Filter($msg);
            if ($data[2]!==$type) throw new T_Exception_Filter($msg);
        }
    }

    /**
     * Checks uploaded file is an image.
     *
     * @param mixed $value  data to filter
     * @return mixed  filtered value
     */
    protected function doTransform($value)
    {
        if (!$value->exists()) {
            throw new T_Exception_Filter('is not a valid file');
        }
        switch ($value->getMime()->getType()) {
            case T_Mime::JPEG :
                $this->assertType($value,IMAGETYPE_JPEG,'JPEG');
                break;
            case T_Mime::GIF :
                $this->assertType($value,IMAGETYPE_GIF,'GIF');
                break;
            case T_Mime::PNG :
                $this->assertType($value,IMAGETYPE_PNG,'PNG');
                break;
            default:
                throw new T_Exception_Filter('must be a JPEG, GIF or PNG image');
        }
        try {
            $img = new T_Image_GdFile($value);
        } catch (Exception $e) {
            throw new T_Exception_Filter('could not be processed (probably too big!)');
        }
        // normalize extension
        $img->convertTo($img->getMime()->getType());
        return $img;
    }

}