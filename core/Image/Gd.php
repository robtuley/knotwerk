<?php
/**
 * Defines the class T_Image_Gd.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Image uses GD library.
 *
 * @package core
 * @license http://knotwerk.com/licence MIT
 */
class T_Image_Gd
{

    /**
     * GD2 image handle.
     *
     * This resource handle is left public, but should be accessed with care.
     *
     * @var resource
     */
    public $handle;

    /**
     * Image width.
     *
     * @var int
     */
    protected $width;

    /**
     * Image height.
     *
     * @var int
     */
    protected $height;

    /**
     * Image quality.
     *
     * Image quality sets the quality of the image when saved as PNG or JPG.
     * A qiality of 0 is poor, and a quality of 100 is very high.
     *
     * @var int
     */
    protected $quality = 75;

    /**
     * MIME type.
     *
     * @var unknown_type
     */
    protected $mime;

    /**
     * Load the image from file.
     *
     * @param string $path  path to image file
     */
    function __construct($width,$height,$type=T_Mime::PNG)
    {
        $this->width = (int) $width;
        $this->height = (int) $height;
        $this->handle = @imagecreatetruecolor($this->width,$this->height);
        if ($this->handle === false) {
            throw new T_Exception_Image('Image creation failed');
        }
        $this->mime = new T_File_Mime('png');
        $this->mime->setType($type);
    }

    /**
     * Get image width.
     *
     * @return int  image width in pixels
     */
    function getWidth()
    {
        return $this->width;
    }

    /**
     * Get image height.
     *
     * @return int  image width in pixels
     */
    function getHeight()
    {
        return $this->height;
    }

    /**
     * Get image MIME type.
     *
     * @return T_File_Mime  image MIME type
     */
    function getMime()
    {
        return $this->mime;
    }

    /**
     * Convert image type.
     *
     * @param int $type
     * @return T_Image_Gd  fluent interface
     */
    function convertTo($type)
    {
        $this->mime->setType($type);
        return $this;
    }

    /**
     * Resize image to new size.
     *
     * This function resizes any image down to fit within a maximum width and
     * height while maintaining aspect. It will never enlarge an image.
     *
     * @param int $max_width  maximum width in pixels
     * @param int $max_height  maximum height in pixels
     * @return T_Image_Gd  resized image
     */
    function resize($max_width,$max_height)
    {
        /* work out new size, maintaining aspect */
        $ratio = min($max_width/$this->getWidth(),
                        $max_height/$this->getHeight() );
        if ($ratio >= 1) {
            return clone($this);
        }
        $new_width = ceil($this->getWidth()*$ratio);
        $new_height = ceil($this->getHeight()*$ratio);
        /* create new image object */
        $im = new T_Image_Gd($new_width,$new_height,$this->mime->getType());
        $ok = imagecopyresampled($im->handle,$this->handle,0,0,0,0,
                   $new_width,$new_height,$this->getWidth(),$this->getHeight());
        if ($ok === false) {
            throw new T_Exception_Image('Resize failed');
        }
        return $im;
    }

    /**
     * Set the quality.
     *
     * @param int $quality  Quality of output (in %, 1==worst, 100 == best)
     * @return T_Image_Gd  fluent interface
     */
    function setQuality($percent)
    {
        if ($percent > 100 || $percent < 1) {
            throw new T_Exception_Image('quality value out of bounds');
        }
        $this->quality = (int) $percent;
        return $this;
    }

    /**
     * Export image to file or buffer.
     *
     * @param string|null $filename  Filename to dave to, or null for buffer
     * @return T_Image_Gd  fluent interface
     */
    protected function export($filename=null)
    {
        switch ($this->mime->getType()) {
            case T_Mime::JPEG :
                @imageinterlace($this->handle,1); /* progressive JPEG on */
                $ok = @imagejpeg($this->handle,$filename,$this->quality);
                break;
            case T_Mime::PNG :
                $q = 9-ceil($this->quality*(9/100));
                  /* quality 0-9, 0==best, 9==maximum compression */
                $ok = @imagepng($this->handle,$filename,$q);
                break;
            case T_Mime::GIF :
                /* requires explicit separation of 2nd argument */
                if (is_null($filename)) {
                    $ok = @imagegif($this->handle);
                } else {
                    $ok = @imagegif($this->handle,$filename);
                }
                break;
            default :
                $msg = 'Illegal image MIME type '.$this->mime->__toString();
                throw new ImageException($msg);
        }
        if (!$ok) {
            throw new T_Exception_Image("Export $filename failed");
        }
    }

    /**
     * Save to file.
     *
     * @param string $dir  directory
     * @param string $filename  filename
     * @return T_File_Path   filepath to file (normalized extension)
     */
    function toFile($dir,$filename)
    {
        $path = new T_File_Path($dir,$filename,$this->getMime()->getExt());
        if ($path->exists()) {
            /* atomic swap-based replace performed so no possibility of
               mid-write read of partial image by another process */
            $tmp = new T_File_Path($dir,
                                   uniqid(rand(),true),
                                   $this->getMime()->getExt());
            $this->export($tmp->__toString());
            $tmp->rename($path);
        } else {
            $this->export($path->__toString());
        }
        return $path;
    }

    /**
     * Outputs image to output buffer.
     *
     * @return null
     */
    function toBuffer()
    {
        $this->export();
    }

    /**
     * Convert image to binary string.
     *
     * @return string  binary image string
     */
    function __toString()
    {
        ob_start();
        $this->toBuffer();
        return ob_get_clean();
    }

}
