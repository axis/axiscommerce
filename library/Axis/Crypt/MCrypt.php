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
 * @package     Axis_Crypt
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Crypt
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Crypt_MCrypt
{
    protected $_cipher;
    protected $_mode;
    protected $_handler; 
    
    public function __construct($key = null)
    {
        if (null === $key) {
            $key = Axis::config()->crypt->key;
        }
        
        if (null === $this->_cipher) {
            $this->_cipher = MCRYPT_BLOWFISH;
        }

        if (null === $this->_mode) {
            $this->_mode = MCRYPT_MODE_ECB;
        }

        $this->_handler = mcrypt_module_open($this->_cipher, '', $this->_mode, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->_handler), MCRYPT_RAND);

        $maxKeySize = mcrypt_enc_get_key_size($this->_handler);

        if (iconv_strlen($key, 'UTF-8') > $maxKeySize) {
            $this->_handler = null;
            throw new Axis_Exception('Maximum key size must should be smaller ' . $maxKeySize);
        }

        mcrypt_generic_init($this->_handler, $key, $iv);
    }
    
    /**
     * Encrypt
     * @return mixed string|null
     * @param object $data
     */
    public function encrypt($data)
    {
        if (empty($data)) {
            return $data;
        }
        return base64_encode(mcrypt_generic($this->_handler, (string) $data));
    }
    
    /**
     * Decrypt
     * @return mixed string|null
     * @param object $data
     */
    public function decrypt($data)
    {
        if (empty($data)) {
            return $data;
        }
        return str_replace("\x0", '', trim(
            mdecrypt_generic($this->_handler, base64_decode((string) $data))
        ));
    }
    
    public function __destruct()
    {
        if ($this->_handler) {
            mcrypt_generic_deinit($this->_handler);
            mcrypt_module_close($this->_handler);
        }
    }
    
}