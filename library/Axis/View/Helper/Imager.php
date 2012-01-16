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
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_Imager
{
    private $_imagesPath;
    private $_imagesWsPath;
    private $_cachePath;
    private $_cacheWsPath;
    private $_watermarkDisplay;
    private $_watermarkImage;
    private $_watermarkPosition;
    private $_watermarkOpacity;

    public function __construct()
    {
        $config = Axis::config();
        $this->_imagesPath = $config->system->path . '/media';
        $this->_imagesWsPath = '/media';
        $this->_cachePath = $config->system->path . $config->image->main->cachePath;
        $this->_cacheWsPath = $config->image->main->cachePath;
        $this->_watermarkDisplay = (boolean)$config->get('image/watermark/enabled', 1);
        $this->_watermarkImage = trim($config->get('image/watermark/image', 'catalog/watermark.png'), '/\\ ');
        $this->_watermarkPosition = $config->get('image/watermark/position', 'bottom_right');
        $this->_watermarkOpacity = $config->get('image/watermark/opacity', 50);
    }

    private function _getImgTag($src, $config = array())
    {
        if (isset($config['getUrl'])) {
            return $this->view->resourceUrl . $this->view->basePath . $src;
        }
        unset($config['seo'], $config['disableWatermark']);
        if (empty($this->view->resourceUrl) || strpos($src, $this->view->resourceUrl) === 0) {
            $config['src'] = $src;
        } else {
            $config['src'] = $this->view->resourceUrl . $src;
        }
        $html = '<img ';
        foreach ($config as $name => $value) {
            $html .= $name . '="' . $value . '" ';
        }
        $html .= '/>';
        return $html;
    }

    /**
     * Image resizer, cacher, seo, watermark
     * Possible config keys:
     * width  => image width
     * height => image height
     * alt    => alternative text & title attribute
     * seo    => seo image name
     * disableWatermark => false
     *
     * @param string $src
     * @param array $config
     * @return string image tag
     */
    public function imager($src, $config = array())
    {
        $config = array_merge(array(
            'width' => 0,
            'height' => 0,
            'seo' => '',
            'alt' => '',
            'disableWatermark' => false
        ), array_filter($config));

        $src = trim($src, '/\\');
        if (empty($src) || !is_file($this->_imagesPath . '/' . $src)) {
            $src = $this->view->skinPath('images/no_image.gif');
            $filename = 'no_image';
        } else {
            $src = $this->_imagesPath. '/' . $src;

            $imagename = substr($src, strrpos($src, '/') + 1);
            $imagename = substr($imagename, 0, strrpos($imagename, '.'));
            if (empty($config['seo'])) {
                $config['seo'] = $imagename;
            } else {
                $config['seo'] .= '_' . $imagename;
            }

            $filename = $config['seo'];
        }

        // remove all cyrillic symbols to support $filename[$i]
        $filename = strtolower(preg_replace('/[^a-zA-Z0-9_\.]/', '_', $filename));

        if ($config['width'] > 0) {
            $config['height'] = $config['height'] > 0 ? $config['height'] : $config['width'];
            $filename .= '_' . $config['width'] . 'x' . $config['height'];
        }
        $filename .= substr($src, strrpos($src, '.')); // add extension

        $wsDesination = $this->_cacheWsPath . '/' . $filename[0] . '/' . $filename[1];
        $destination  = $this->_cachePath . '/' . $filename[0] . '/' . $filename[1];

        if (file_exists($destination . '/' . $filename) && Axis::config('image/product/cache')) {
            return $this->_getImgTag($wsDesination . '/' . $filename, $config);
        }

        try {
            $image = new Axis_Image($src);
            $image->resize($config['width'], $config['height']);
            if ($this->_watermarkDisplay && !$config['disableWatermark']) {
                $image->applyWatermark(
                    $this->view->skinPath('images/' . $this->_watermarkImage),
                    $this->_watermarkPosition,
                    $this->_watermarkOpacity
                );
            }
            $image->save($destination, $filename);
        } catch (Axis_Exception $e) {
            Axis::message()->addError($e->getMessage());
            return $this->_getImgTag($this->view->skinUrl('images/no_image.gif'), $config);
        }

        return $this->_getImgTag($wsDesination . '/' . $filename, $config);
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}