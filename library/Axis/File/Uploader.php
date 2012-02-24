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
 * @package     Axis_File
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_File
 * @author      Axis Core Team <core@axiscommerce.com>
 */

//@todo use Zend_File_Transfer 
class Axis_File_Uploader
{
    /**
     * Copy of $_FILES[$fileName]
     *
     * @var array
     */
    protected $_file = array();

    /**
     * @var bool
     */
    protected $_useDispersion = false;

    /**
     * @var array
     */
    protected $_allowedExtensions = null;

    /**
     * Php error codes
     *
     * @var array
     */
    protected $_errorCode = array(
        0 => "There is no error, the file uploaded with success",
        1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
        3 => "The uploaded file was only partially uploaded",
        4 => "No file was uploaded",
        6 => "Missing a temporary folder"
    );

    /**
     * @param string $fileName
     * @throws Axis_Exception
     */
    public function __construct($fileName)
    {
        if (!isset($_FILES[$fileName])) {
            throw new Axis_Exception(
                sprintf("File was not uploaded correctly. %s array doesn't have '%s' file", '$_FILES', $fileName)
            );
        }
        $this->_file = $_FILES[$fileName];
    }

    /**
     * @param string $desinationFolder
     * @param string $fileName
     * @return array|false
     * @throws Axis_Exception
     */
    public function save($destination, $fileName = null)
    {
        $this->_validateFile();

        $destination = rtrim($destination, '/\\');

        if (null === $fileName) {
            $fileName = $this->_file['name'];
        }

        $fileName = $this->_getCorrectFileName($fileName);
        $dispersion = $this->_getDispersionPath($fileName);

        if (!is_dir($destination . $dispersion)
            && !@mkdir($destination . $dispersion, 0777, true)) {

            throw new Axis_Exception("Unable to create directory '{$destination}'");
        }

        $fileName = $this->_getNewFileName($destination . $dispersion . $fileName);
        $fileName = $dispersion . $fileName;

        $result = @move_uploaded_file(
            $this->_file['tmp_name'],
            $destination . $fileName
        );

        if (!$result) {
            throw new Axis_Exception($this->_errorCode[$this->_file['error']]);
        }

        chmod($destination . $fileName, 0777);

        return array(
            'path' => $destination,
            'file' => $fileName
        );
    }

    /**
     * Set allowed extensions for uploader instance
     *
     * @param array $extensions
     * @return Axis_File_Uploader Provides fluent interface
     */
    public function setAllowedExtensions($extensions)
    {
        foreach ($extensions as $extension) {
            $this->_allowedExtensions[strtolower($extension)] = strtolower($extension);
        }
        return $this;
    }

    /**
     * Validate file extension
     *
     * @param string $extension
     * @return bool
     */
    public function isAllowedExtension($extension)
    {
        if (null === $this->_allowedExtensions) {
            return true;
        }
        if (in_array(strtolower($extension), $this->_allowedExtensions)) {
            return true;
        }
        return false;
    }

    /**
     * @param bool $flag
     * @return Axis_File_Uploader Provides fluent interface
     */
    public function setUseDispersion($flag)
    {
        $this->_useDispersion = (bool) $flag;
        return $this;
    }

    /**
     * Validate file before save
     *
     * @return void
     * @throws Axis_Exception
     */
    protected function _validateFile()
    {
        $pathinfo = pathinfo($this->_file['name']);

        if (!isset($pathinfo['extension'])) {
            $pathinfo['extension'] = '';
        }

        if (!$this->isAllowedExtension($pathinfo['extension'])) {
            throw new Axis_Exception("Disallowed file type.");
        }
    }

    /**
     * Retrieve unique file name within destination folder
     * Adds number to the end of fileName if same fileName found
     *
     * @param string $filePath
     * @return string
     */
    protected function _getNewFileName($filePath)
    {
        $i = 1;
        $fileInfo = pathinfo($filePath);
        while (file_exists($filePath)) {
            $filePath = $fileInfo['dirname']
                        . DIRECTORY_SEPARATOR
                        . $fileInfo['filename']
                        . '-'
                        . $i++
                        . '.'
                        . $fileInfo['extension'];
        }
        return basename($filePath);
    }

    /**
     * Removes invalid symbols from the fileName
     *
     * @param string $fileName
     * @return string
     */
    protected function _getCorrectFileName($fileName)
    {
        return preg_replace('/[^a-zA-Z0-9_\.]/', '_', $fileName);
    }

    /**
     * Retrieve additional folders to move file to it
     *
     * @param string $fileName
     * @return string
     */
    protected function _getDispersionPath($fileName)
    {
        $dispersionPath = '/';

        if (!$this->_useDispersion) {
            return $dispersionPath;
        }

        for ($i = 0; $i < 2; $i++) {
            if (!preg_match('/[a-zA-Z0-9_]{1}/', $fileName[$i])) {
                break;
            }
            $dispersionPath .= strtolower($fileName[$i]) . '/';
        }
        return $dispersionPath;
    }
}