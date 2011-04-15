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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_HeadScript extends Zend_View_Helper_HeadScript
{
    private $_proxy = null;

    /**
     * Return headScript object
     *
     * Returns headScript helper object; optionally, allows specifying a script
     * or script file to include.
     *
     * @param  string $mode Script or file
     * @param  string $spec Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array $attributes Array of script attributes
     * @param  string $type Script type and/or array of script attributes
     * @return Zend_View_Helper_HeadScript
     */
    public function headScript(
        $mode = Zend_View_Helper_HeadScript::FILE,
        $spec = null,
        $placement = 'APPEND',
        array $attributes = array(),
        $type = 'text/javascript')
    {
        $this->_proxy = null;
        return parent::headScript($mode, $spec, $placement, $attributes, $type);
    }

    /**
     * @param String $proxy
     * @return Axis_View_Helper_HeadScript Provides fluent interface
     */
    public function setProxy($proxy)
    {
        $this->_proxy = $proxy;
        return $this;
    }

    /**
     * Search algorithm:
     *  - from current template
     *  - from fallback template
     *  - from default template
     *  - from AXIS_ROOT
     *
     * @param string $css
     * @param bool $absolute [optional]
     * @return string
     */
    public function getJs($js, $absolute = true)
    {
        if (strstr($js, 'http://') || strstr($js, 'https://')) {
            return $js;
        }

        $fallbackList = array_unique(array(
            $this->view->templateName,
            /* $this->view->defaultTemplate */
            'fallback',
            'default'
        ));
        $find = false;
        foreach ($fallbackList as $fallback) {
            $file = '/skin/' . $this->view->area . '/' . $fallback . '/js/' . $js;
            if (is_readable($this->view->path . $file)) {
                $find = true;
                break;
            }
        }

        if (!$find) {
            $file = $js;
        }

        $baseUrl = $absolute ?
            $this->view->resourceUrl : Zend_Controller_Front::getInstance()->getBaseUrl();

        return $baseUrl . '/' . trim($file, '/');
    }

    /**
     * Create script HTML
     */
    public function itemToString($item, $indent, $escapeStart, $escapeEnd)
    {
        $type = ($this->_autoEscape) ? $this->_escape($item->type) : $item->type;
        $html = $indent . '<script type="' . $type . '"';

        if (isset($item->attributes['src'])) {
            $html .= ' src="' . $this->getJs($item->attributes['src']) . '"';
        }

        $this->_completeItem($html, $item, $indent, $escapeStart, $escapeEnd);

        return $html;
    }

    /**
     * Create script HTML
     */
    public function groupToString($group, $indent, $escapeStart, $escapeEnd, $useProxy)
    {
        if (!$useProxy) {
            $items = array();
            foreach ($group as $item) {
                $items[] = $this->itemToString($item, $indent, $escapeStart, $escapeEnd);
            }
            $html = implode($this->getSeparator(), $items);
        } else {
            $item = current($group);
            $type = ($this->_autoEscape) ? $this->_escape($item->type) : $item->type;
            $html = $indent . '<script type="' . $type . '"';

            if (isset($item->attributes['src'])) {
                foreach ($group as $item) {
                    $srcs[] = trim($this->getJs($item->attributes['src'], false), '/');
                }
                $html .= ' src="' . $this->view->resourceUrl . '/min.php?f=' . implode(',', $srcs) . '"';
            }

            $this->_completeItem($html, $item, $indent, $escapeStart, $escapeEnd);
        }

        return $html;
    }

    /**
     * Completes HTML link element
     */
    private function _completeItem(&$html, $item, $indent, $escapeStart, $escapeEnd)
    {
        $attrString = '';
        if (!empty($item->attributes)) {
            foreach ($item->attributes as $key => $value) {
                if ('src' == $key) {
                    continue;
                }
                if (!$this->arbitraryAttributesAllowed()
                    && !in_array($key, $this->_optionalAttributes)) {

                    continue;
                }
                if ('defer' == $key) {
                    $value = 'defer';
                }
                $attrString .= sprintf(' %s="%s"', $key, ($this->_autoEscape) ? $this->_escape($value) : $value);
            }
        }

        $html .= $attrString . '>';
        if (!empty($item->source)) {
            $html .= PHP_EOL . $indent . '    ' . $escapeStart . PHP_EOL . $item->source . $indent . '    ' . $escapeEnd . PHP_EOL . $indent;
        }
        $html .= '</script>';

        if (isset($item->attributes['conditional'])
            && !empty($item->attributes['conditional'])
            && is_string($item->attributes['conditional'])) {

            $html = '<!--[if ' . $conditional . ']> ' . $html . '<![endif]-->';
        }
    }

    /**
     * Retrieve string representation
     *
     * @param  string|int $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        if ($this->view) {
            $useCdata = $this->view->doctype()->isXhtml() ? true : false;
        } else {
            $useCdata = $this->useCdata ? true : false;
        }
        $escapeStart = ($useCdata) ? '//<![CDATA[' : '//<!--';
        $escapeEnd   = ($useCdata) ? '//]]>'       : '//-->';

        $items = array();
        $this->getContainer()->ksort();
        $groups = array();
        $gi = $si = 0;
        foreach ($this as $offset => &$item) {
            if (!$this->_isValid($item)) {
                continue;
            }

            $item->offset = $offset;

            if (null !== $item->attributes['proxy'] && Axis::config('core/minify/js_' . $this->view->area)) {
                $proxy = $item->attributes['proxy'];
                if (isset($item->attributes['conditional'])
                    && !empty($item->attributes['conditional'])
                    && is_string($item->attributes['conditional'])) {

                    $proxy .= $item->attributes['conditional'];
                }
                $groups['proxy_' . $proxy][] =& $item;
                $si++; // break scripts array
                $gi++; // break general array
            } else {
                if (isset($item->attributes['conditional'])
                    && !empty($item->attributes['conditional'])
                    && is_string($item->attributes['conditional'])) {

                    $groups[$item->attributes['conditional']][] =& $item;
                } elseif (empty($item->source)) {
                    $groups['general_' . $gi][] =& $item;
                    $si++; // break scripts array
                } else {
                    $groups['script_' . $si][] =& $item;
                    $gi++; // break general array
                }
            }
        }

        foreach ($groups as $key => $group) {
            $items[] = $this->groupToString(
                            $group,
                            $indent,
                            $escapeStart,
                            $escapeEnd,
                            strpos($key, 'proxy_') === 0
                       );
        }

        $return = implode($this->getSeparator(), $items);
        return $return;
    }

    /**
     * Create data item containing all necessary components of script
     *
     * @param  string $type
     * @param  array $attributes
     * @param  string $content
     * @return stdClass
     */
    public function createData($type, array $attributes, $content = null)
    {
        $data             = new stdClass();
        $data->type       = $type;
        $data->attributes = $attributes;
        $data->attributes['proxy'] = $this->_proxy;
        $data->source     = $content;
        return $data;
    }

    /**
     * Override offsetSet
     *
     * @param  string|int $index
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($index, $value)
    {
        if (!$this->_isValid($value)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Invalid argument passed to offsetSet(); please use one of the helper methods, offsetSetScript() or offsetSetFile()');
            $e->setView($this->view);
            throw $e;
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
