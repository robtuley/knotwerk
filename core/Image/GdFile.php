<?php
/**
 * Defines the class T_Image_GdFile.
 *
 * @package core
 * @author Rob Tuley
 * @version SVN: $Id$
 * @license http://knotwerk.com/licence MIT
 */

/**
 * Image file using GD library.
 *
 * @package core
 */
class T_Image_GdFile extends T_Image_Gd
{

    /**
     * Load the image from file.
     *
     * @param T_File_Path $path  file path
     */
    function __construct($path)
    {
        $this->mime = $path->getMime();
        if (!$path->exists()) {
            throw new T_Exception_Image($path->__toString().' not exists');
        }
        $pathstr = $path->__toString();
        list($this->width,$this->height,$type) = @getimagesize($pathstr);
        switch ($this->mime->getType()) {
            case T_Mime::JPEG :
                if ($type !== IMAGETYPE_JPEG) {
                    throw new T_Exception_Image($pathstr.' is not a JPEG');
                }
                $this->handle = @imagecreatefromjpeg($pathstr);
                break;
            case T_Mime::PNG :
                if ($type !== IMAGETYPE_PNG) {
                    throw new T_Exception_Image($pathstr.' is not a PNG');
                }
                $this->handle = @imagecreatefrompng($pathstr);
                break;
            case T_Mime::GIF :
                if ($type !== IMAGETYPE_GIF) {
                    throw new T_Exception_Image($pathstr.' is not a GIF');
                }
                $this->handle = @imagecreatefromgif($pathstr);
                break;
            default :
                $msg = 'Illegal image MIME type '.$mime->__toString();
                throw new T_Exception_Image($msg);
        }
        if ($this->handle === false) {
            throw new T_Exception_Image('Error loading '.$pathstr);
        }
    }

}