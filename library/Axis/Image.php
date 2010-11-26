<?php
/**
 * Axis
 *
 * This file is part of Axis.
 *
 * Axis is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Axis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Axis.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category    Axis
 * @package     Axis_Image
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Image
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Image
{
    protected $_fileType = null;
    protected $_fileName = null;
    protected $_fileMimeType = null;
    protected $_fileSrcName = null;
    protected $_fileSrcPath = null;
    protected $_imageResource = null;
    protected $_imageSrcWidth = null;
    protected $_imageSrcHeight = null;

    protected $_backgroundColor = array(255, 255, 255);

    /**
     * @param string $image_path path to the image file
     */
    public function __construct($filename)
    {
        $this->open($filename);
    }

    /**
     * Fill the image with the array
     * returned by getimagesize() function
     *
     * @param string $image_path path to the image file
     * @return void
     */
    public function open($filename)
    {
        if (!$data = @getimagesize($filename)) {
            throw new Axis_Exception(
                Axis::translate('core')->__(
                    "Cannot open file. Permission denied"
            ));
        }
        $this->_fileName = $filename;
        $this->_getFileAttributes();
        $this->getMimeType();
        $this->_imageResource = $this->createImage();
    }

    /**
     *
     * @param string $destination [optional]
     * @param string $filename [optional]
     * @return void
     */
    public function save($destination = null, $filename = null)
    {
        $destination = (null !== $destination) ? $destination : $this->_fileSrcPath;
        $filename = (null !== $filename) ? $filename : $this->_fileSrcName;

        $filename = $destination . '/' . $filename;

        if (!is_dir($destination) && !@mkdir($destination, 0777, true)) {
            throw new Axis_Exception(
                Axis::translate('core')->__(
                    'Cannot create folder. Permission denied'
            ));
        }
        if (!is_writable($destination) && !@chmod($destination, 0777)) {
            throw new Axis_Exception(
                Axis::translate('core')->__(
                    'Cannot write to folder. Permission denied'
            ));
        }

        $this->output($filename);
    }

    /**
     * This method will resize original image
     * to fill maximum space of requested dimensions.
     * Blank space will be added if necessary.
     * If height is not requested, method will return
     * square image
     *
     * @param int $canvasWidth
     * @param int $canvasHeight [optional]
     * @return Axis_Image Provides fluent interface
     */
    public function resize($canvasWidth, $canvasHeight = null)
    {
        if ($canvasWidth == 0) {
            $canvasWidth = $this->_imageSrcWidth;
            $canvasHeight = $this->_imageSrcHeight;
        } elseif (null === $canvasHeight || !$canvasHeight) {
            $canvasHeight = $canvasWidth;
        }

        $src_x = $src_y = $dst_x = $dst_y = 0;
        $newWidth = $canvasWidth;
        $newHeight = $canvasHeight;
        $canvasRation = $canvasHeight / $canvasWidth;
        $imageRatio = $this->_imageSrcHeight / $this->_imageSrcWidth;

        if ($canvasRation > $imageRatio) {
            $newHeight = round($canvasWidth * ($this->_imageSrcHeight / $this->_imageSrcWidth));
            $dst_y = ($canvasHeight - $newHeight) / 2;
        } elseif ($canvasRation < $imageRatio) {
            $newWidth = round($canvasHeight * ($this->_imageSrcWidth / $this->_imageSrcHeight));
            $dst_x = ($canvasWidth - $newWidth) / 2;
        }

        $dst_img = imagecreatetruecolor($canvasWidth, $canvasHeight);
        list($r, $g, $b) = $this->_backgroundColor;
        imagefill($dst_img, 0, 0, imagecolorallocate($dst_img, $r, $g, $b));
        imagecopyresampled($dst_img, $this->_imageResource, $dst_x, $dst_y, $src_x, $src_y, $newWidth, $newHeight, $this->_imageSrcWidth, $this->_imageSrcHeight);
        $this->_imageResource = $dst_img;
        $this->_updateImageDimensions();
        return $this;
    }

    /**
     * Draw watermark image
     *
     * @param string $filename Path to the watermark image
     * @param string $position [optional] Watermark position
     *  stretch
     *  top_left   |top_center   |top_right
     *  middle_left|middle_center|middle_right
     *  bottom_left|bottom_center|bottom_right
     *
     * @param int $opacity [optional]
     * @param boolean $repeat [optional]
     * @return Axis_Image|boolean Provides fluent interface
     */
    public function applyWatermark($filename, $position, $opacity = 50, $repeat = false)
    {
        if (!@getimagesize($filename)) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Failed to open watermark image'
            ));
            return false;
        }

        list($watermarkSrcWidth, $watermarkSrcHeight, $watermarkFileType) = getimagesize($filename);
        $watermark = $this->createImage($filename, $watermarkFileType);
        $positionX = $positionY = 0;
        $verticalAlign = 'top';
        $horizontalAlign = 'left';

        if (strstr($position, '_')) {
            list($verticalAlign, $horizontalAlign) = explode('_', $position);
        }

        switch($verticalAlign) {
            case 'middle':
                $positionY = $this->_imageSrcHeight / 2 - $watermarkSrcHeight / 2;
                break;
            case 'bottom':
                $positionY = $this->_imageSrcHeight - $watermarkSrcHeight;
                break;
            case 'top':
            default:
                $positionY = 0;
                break;
        }

        switch($horizontalAlign) {
            case 'center':
                $positionX = $this->_imageSrcWidth / 2 - $watermarkSrcWidth / 2;
                break;
            case 'right':
                $positionX = $this->_imageSrcWidth - $watermarkSrcWidth;
                break;
            case 'left':
            default:
                $positionX = 0;
                break;
        }

        if ($position == 'stretch') {
            //@todo stretch, opacity and repeat;
        }

        imagecopy($this->_imageResource, $watermark, $positionX, $positionY, 0, 0, $watermarkSrcWidth, $watermarkSrcHeight);
        imagedestroy($watermark);
    }

    /**
     * Write resource image to filename
     *
     * @param string $filename
     * @return void
     * @throws Axis_Exception if unsupported image format recieved
     */
    public function output($filename)
    {
        switch ($this->_fileType) {
            case IMAGETYPE_GIF:
                imagegif($this->_imageResource, $filename);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($this->_imageResource, $filename);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->_imageResource, $filename);
                break;
            case IMAGETYPE_XBM:
                imagexbm($this->_imageResource, $filename);
                break;
            case IMAGETYPE_WBMP:
                imagewbmp($this->_imageResource, $filename);
                break;
            default:
                throw new Axis_Exception(
                    Axis::translate('core')->__(
                        'Unsupported image format recieved'
                ));
                break;
        }
    }

    /**
     * @return imageResource
     * @throws Axis_Exception if unsupported image format recieved
     */
    public function createImage($filename = null, $filetype = null)
    {
        if (null === $filename) {
            $filename = $this->_fileName;
            $filetype = $this->_fileType;
        }
        switch ($filetype) {
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filename);
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filename);
            case IMAGETYPE_PNG:
                return imagecreatefrompng($filename);
            case IMAGETYPE_XBM:
                return imagecreatefromxbm($filename);
            case IMAGETYPE_WBMP:
                return imagecreatefromwbmp($filename);
            default:
                throw new Axis_Exception(
                    Axis::translate('core')->__(
                        '%s format is unsupported', $filetype
                    ));
                break;
        }
    }

    /**
     * @see image_type_to_mime_type
     * @return string
     */
    public function getMimeType()
    {
        if ($this->_fileType) {
            return $this->_fileType;
        }

        list($this->_imageSrcWidth, $this->_imageSrcHeight, $this->_fileType) = getimagesize($this->_fileName);
        $this->_fileMimeType = image_type_to_mime_type($this->_fileType);
        return $this->_fileMimeType;
    }

    /**
     * Update image dimensions.
     * Used after image resize.
     *
     * @return void
     */
    private function _updateImageDimensions()
    {
        $this->_imageSrcWidth = imagesx($this->_imageResource);
        $this->_imageSrcHeight = imagesy($this->_imageResource);
    }

    /**
     * Fills dirname and basename
     * @return void
     */
    private function _getFileAttributes()
    {
        $pathinfo = pathinfo($this->_fileName);
        $this->_fileSrcPath = $pathinfo['dirname'];
        $this->_fileSrcName = $pathinfo['basename'];
    }

    function __destruct()
    {
        @imagedestroy($this->_imageResource);
    }
}
