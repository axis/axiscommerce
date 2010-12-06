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
 * @package     Axis_Pdf
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

define("DOMPDF_UNICODE_ENABLED", true);
require_once('dompdf/dompdf_config.inc.php');

/**
 *
 * @category    Axis
 * @package     Axis_Pdf
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Pdf
{
    private $_DOMPDF;
  
    public function __construct()
    {
        //Zend_Loader::registerAutoload('Axis_Pdf_Loader');
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->pushAutoloader(array('Axis_Pdf_Loader', 'autoload'));

        $memoryLimit = intval(ini_get('memory_limit'));
        if ($memoryLimit < 64){
            ini_set("memory_limit", "64M");
        }
        $this->_DOMPDF = new DOMPDF();
    }
    
    public function setContent($content)
    {   // next page is <div style="page-break-before:always"></div>
        $this->_DOMPDF->load_html($content);
    }
    
    public function getPdf($returnFileName = "sample.pdf")
    {
        $this->_DOMPDF->render();
        $this->_DOMPDF->stream($returnFileName);
    }   
}