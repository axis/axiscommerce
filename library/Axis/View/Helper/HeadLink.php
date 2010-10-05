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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */

class Axis_View_Helper_HeadLink extends Zend_View_Helper_HeadLink
{
    private $_proxy = null;

    /**
     * headLink() - View Helper Method
     *
     * Returns current object instance. Optionally, allows passing array of
     * values to build link.
     *
     * @return Zend_View_Helper_HeadLink
     */
    public function headLink(
        array $attributes = null,
        $placement = Zend_View_Helper_Placeholder_Container_Abstract::APPEND)
    {
        $this->_proxy = null;
        return parent::headLink($attributes, $placement);
    }

    /**
     * @param String $proxy
     * @return Axis_View_Helper_HeadLink Provides fluent interface
     */
    public function setProxy($proxy)
    {
        $this->_proxy = $proxy;
        return $this;
    }

    /**
     * Search algorithm:
     *  - from current template
     *  - from default template
     *  - from ECART_ROOT
     *
     * @param string $css
     * @param bool $absolute [optional]
     * @return string
     */
    public function getCss($css, $absolute = true)
    {
        if (strstr($css, 'http://') || strstr($css, 'https://')) {
            return $css;
        }

        $file = '/skin/' . $this->view->area . '/'
            . $this->view->templateName . '/css/' . $css;

        if (!is_readable($this->view->path . $file)) {
            $file = '/skin/' . $this->view->area
                  . '/default/css/' . $css;
        }
        if (!is_readable($this->view->path . $file)) {
            $file = $css;
        }
        $baseUrl = $absolute ?
            $this->view->resourceUrl : Zend_Controller_Front::getInstance()->getBaseUrl();

        return $baseUrl . '/' . trim($file, '/');
    }

    /**
     * Create HTML link element from data item
     *
     * @param  stdClass $item
     * @return string
     */
    public function itemToString(stdClass $item)
    {
        $attributes = (array) $item;
        $link       = '<link ';

        if (isset($attributes['href'])) {
            $href = ($attributes['rel'] == 'stylesheet') ?
                $this->getCss($attributes['href']) : $attributes['href'];

            $link .= 'href="'.$href.'" ';
        }

        $this->_completeItem($link, $attributes);

        return $link;
    }

    /**
     * Create HTML link element from data item
     *
     * @param array $group
     * @param string $indent
     * @param bool $disableProxy
     * @return string
     */
    public function groupToString($group, $indent, $useProxy)
    {
        if (!$useProxy) {
            $items = array();
            foreach ($group as $item) {
                $items[] = $this->itemToString($item);
            }
            $html = $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
        } else {
            $item = current($group);
            $attributes = (array) $item;
            $html = '<link ';

            foreach ($group as $item) {
                $hrefs[] = trim($this->getCss($item->href, false), '/');
            }
            $html .= 'href="'.$this->view->resourceUrl.'/min.php?f=' . implode(',', $hrefs) . '" ';

            $this->_completeItem($html, $attributes);
        }

        return $html;
    }

    /**
     * Completes HTML link element
     *
     * @param string $item
     * @param array $attributes
     * @return void
     */
    private function _completeItem(&$item, $attributes)
    {
        foreach ($this->_itemKeys as $itemKey) {
            if ($itemKey == 'href') {
                continue;
            }
            if (isset($attributes[$itemKey])) {
                if(is_array($attributes[$itemKey])) {
                    foreach($attributes[$itemKey] as $key => $value) {
                        $item .= sprintf('%s="%s" ', $key, ($this->_autoEscape) ?
                            $this->_escape($value) : $value);
                    }
                } else {
                    $item .= sprintf('%s="%s" ', $itemKey, ($this->_autoEscape) ?
                        $this->_escape($attributes[$itemKey]) : $attributes[$itemKey]);
                }
            }
        }

        if ($this->view instanceof Zend_View_Abstract) {
            $item .= ($this->view->doctype()->isXhtml()) ? '/>' : '>';
        } else {
            $item .= '/>';
        }

        if (($item == '<link />') || ($item == '<link >')) {
            $item = '';
        } elseif (isset($attributes['conditionalStylesheet'])
            && !empty($attributes['conditionalStylesheet'])
            && is_string($attributes['conditionalStylesheet'])) {

            $item = '<!--[if ' . $attributes['conditionalStylesheet'] . ']> '
                  . $item
            . '<![endif]-->';
        }
    }

    /**
     * Render link elements as string
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $items = array();
        $this->getContainer()->ksort();
        $groups = array();
        $mi = $ci = 0;
        foreach ($this as &$item) {
            if (null !== $item->proxy && Axis::config('core/minify/css_' . $this->view->area, true)) {
                $groups['proxy_' . $item->proxy][] =& $item;
                $ci++; // break conditionals array
                $mi++; // break media array
            } else {
                if ($item->rel == 'stylesheet') {
                    if (isset($item->conditionalStylesheet)
                        && !empty($item->conditionalStylesheet)
                        && is_string($item->conditionalStylesheet)) {

                        $groups[$item->conditionalStylesheet . $ci][] =& $item;
                        $mi++; // break media array
                    } elseif (isset($item->media)
                        && !empty($item->media)
                        && is_string($item->media)) {

                        $groups[$item->media . $mi][] =& $item;
                        $ci++; // break conditionals array
                    }
                } else {
                    $items[] = $this->itemToString($item);
                }
            }
        }

        foreach ($groups as $key => $group) {
            $items[] = $this->groupToString($group, $indent, strpos($key, 'proxy_') === 0);
        }

        return $indent . implode($this->_escape($this->getSeparator()) . $indent, $items);
    }

    /**
     * Create data item for stack
     *
     * @param  array $attributes
     * @return stdClass
     */
    public function createData(array $attributes)
    {
        $attributes['proxy'] = $this->_proxy;
        $data = (object) $attributes;
        return $data;
    }

    /**
     * offsetSet()
     *
     * @param  string|int $index
     * @param  array $value
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if (!$this->_isValid($value)) {
            #require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('offsetSet() expects a data token; please use one of the custom offsetSet*() methods');
        }

        // we don't need to overwrite existing files - will shift them by one
        if ($this->getContainer()->offsetExists($index)) {
            $values = $this->getContainer()->getArrayCopy();
            $keys   = $this->getContainer()->getKeys();

            // insert value to required position. Keys will be restored later
            $offset = array_search($index, $keys);
            array_splice($values, $offset, 0, array($value));

            // rebuild array keys
            $arrKeys = array_fill_keys($keys, null);
            $result = array();
            foreach ($arrKeys as $key => $dummy) {
                if ($key === $index) {
                    do {
                        $result[$key] = $dummy;
                        $key++;
                    } while (isset($keys[$key]));
                }
                $result[$key] = $dummy;
            }

            // restore keys in result array
            $i = 0;
            foreach ($result as $key => $dummy) {
                $result[$key] = $values[$i++];
            }

            return $this->getContainer()->exchangeArray($result);
        }

        return $this->getContainer()->offsetSet($index, $value);
    }
}
