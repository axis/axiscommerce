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
 * @package     Axis_Layout
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Layout
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Layout extends Zend_Layout
{
    const DEFAULT_TEMPLATE = 'default';
    const DEFAULT_LAYOUT   = 'default_3columns';

    /**
     * @static
     * @var string
     */
    private static $_template;

    /**
     * Box to Block assignment
     *
     * @var array
     */
    protected $_assignments;

    protected $_tabAssignments;

    /**
     * Assoc pages array
     *
     * @var array
     */
    protected $_pages;

    /**
     *
     * @var string
     */
    protected $_layout = null;

    protected $_axisLayout = null;

    /**
     * Static method for initialization with MVC support
     *
     * @static
     * @param  string|array|Zend_Config $options
     * @return Axis_Layout
     */
    public static function startMvc($options = null)
    {
        if (null === self::$_mvcInstance) {
            self::$_mvcInstance = new self($options, true);
        } else {
            self::$_mvcInstance->setOptions($options);
        }

        return self::$_mvcInstance;
    }

    /**
     * Return current template
     *
     * @static
     * @param string ['front' || 'admin']
     * @return string
     */
    public static function getTemplate($area = 'front')
    {
        if (null !== self::$_template) {
            return self::$_template;
        }
        if ('admin' === $area) {
            $templateId = Axis::config()->design->main->adminTemplateId;
        } else {
            $templateId = Axis::config()->design->main->frontTemplateId;
        }

        $templateRow = Axis::model('core/template')
            ->find($templateId)
            ->current();

        if (!$templateRow) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    "Template %s not found in 'core_template' table. Check your template values at the 'design/main' config section", $templateId
            ));
            self::$_template = self::DEFAULT_TEMPLATE;
        } else {
            self::$_template = $templateRow->name;
        }

        return self::$_template;
    }

    public function setAssignments($assignments)
    {
        $this->_assignments = $assignments;
    }

    /**
     * Compares requests
     *
     * @param array $node
     * @param array $rewriteNode
     * @return bool
     */
    private function _catRewrite($pageId, $rewritePageId)
    {
        $node = $this->_pages[$pageId];
        $rewriteNode = $this->_pages[$rewritePageId];
        if ((0 > strcmp($node['module_name'], $rewriteNode['module_name'])) ||
            (0 > strcmp($node['controller_name'], $rewriteNode['controller_name'])) ||
            (0 > strcmp($node['action_name'], $rewriteNode['action_name'])))
        {
            return true;
        }
        return false;
    }

    public function getLayout()
    {
        if ('admin' === Zend_Registry::get('area')) {
            return 'layout';
        }

        if (null !== $this->_layout) {
            //this->helper->layout->setLayout() support
            $this->_axisLayout = 'layout' . substr($this->_layout, strpos($this->_layout, '_'));
        } elseif (null === $this->_axisLayout) {
            $pages = $this->getPagesByRequest();
            $templateId = Axis::config()->design->main->frontTemplateId;

            $rows = Axis::single('core/template_layout_page')->select()
                ->where('template_id = ?', $templateId)
                ->where('page_id IN(?)', array_keys($pages))
                ->fetchAll();

            $layout = '';
            $pageId = null;
            foreach ($rows as $row) {
                if (null !== $pageId &&
                    !$this->_catRewrite($pageId, $row['page_id'])) {

                    continue;
                }
                $pageId = $row['page_id'];
                $layout = $row['layout'];
            }

            if (empty($layout)) {
                $layout = $this->_getDefaultLayout();
            }

            $this->_axisLayout = 'layout' . substr($layout, strpos($layout, '_'));
        }

        return $this->_axisLayout;
    }

    private function _getDefaultLayout()
    {
        $template = Axis::single('core/template')
            ->find(Axis::config()->design->main->frontTemplateId)
            ->current();
        if ($template instanceof Axis_Db_Table_Row
            && !empty($template->default_layout)) {

            return $template->default_layout;
        }
        return self::DEFAULT_LAYOUT;
    }

    /**
     *
     * @return array
     */
    public function getPagesByRequest()
    {
        if (null === $this->_pages) {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            list($namespace, $module) = explode('_', $request->getModuleName(), 2);
            $this->_pages = Axis::single('core/page')->getPagesByRequest(
                strtolower($module),
                $request->getControllerName(),
                $request->getActionName()
            );
        }
        return $this->_pages;
    }

    private function _buildAssignments()
    {
        $pages = $this->getPagesByRequest();
        $assignments = array();
        $tabAssignments = array();
        if (!count($pages)) {
            return;
        }
        $templateId = Axis::config()->design->main->frontTemplateId;
        $rows = Axis::single('core/template_box')->select(
                array('id', 'class', 'block', 'config')
            )->joinInner('core_template_box_page',
                'ctbp.box_id = ctb.id',
                array('box_show',
                    'sort_order',
                    'other_block' => 'block',
                    'template',
                    'tab_container',
                    'page_id'
                )
            )->where('ctb.template_id = ?', $templateId)
            ->where('ctb.box_status = 1')
            ->where('ctbp.page_id IN(?)', array_keys($pages))
            ->order('ctb.sort_order')
            ->fetchAll()
            ;
        foreach ($rows as $row) {
            $block = !empty($row['other_block']) ? $row['other_block'] : $row['block'];

            if (isset($assignments[$block][$row['id']])) {
                $pageId = $assignments[$block][$row['id']]['page_id'];
                if (!$this->_catRewrite($pageId, $row['page_id'])) {
                    continue;
                }
            }

            list($namespace, $module, $box) = explode('_', $row['class']); // example: Axis_Locale_Currency

            if (!isset($module) || !isset($box)) {
                continue;
            }

            $assignments[$block][$row['id']] = array(
                'boxCategory'  => ucfirst($namespace),
                'boxModule'    => ucfirst($module),
                'boxName'      => ucfirst($box),
                'template'     => $row['template'],
                'tabContainer' => $row['tab_container'],
                'sort_order'   => $row['sort_order'],
                'page_id'      => $row['page_id'],
                'show'         => $row['box_show']
            );
            if (!empty($row['config'])) {
                $assignments[$block][$row['id']]['config'] = $row['config'];
            }

            if (strstr($row['class'], 'Axis_Cms_Block_')) {
                $static_block = trim(str_replace('Axis_Cms_Block_', '', $row['class']));
                if (empty($static_block)) {
                    continue;
                }
                $assignments[$block][$row['id']]['staticBlock'] = $static_block;
            }
            if (null !== $row['tab_container']) {
                $tabAssignments[$block][$row['id']] = $assignments[$block][$row['id']];
            }
        }
        $this->_assignments = &$assignments;
        $this->_tabAssignments = &$tabAssignments;
        Axis_Core_Box_Abstract::setView($this->getView());
    }

    protected function _getAssignments($blockName = '')
    {
        if (null === $this->_assignments) {
            $this->_buildAssignments();
        }

        if (empty($blockName) || !array_key_exists($blockName, $this->_assignments)) {
            return array();
        }

        return $this->_assignments[$blockName];
    }

    public function __get($key)
    {
        if ('admin' === Zend_Registry::get('area')) {
            return parent::__get($key);
        }

        $beforeContent = $afterContent = '';
        Zend_Registry::set('rendered_boxes', array());
        foreach ($this->_getAssignments($key) as $boxId => $boxConfig) {

            if (in_array($boxId, Zend_Registry::get('rendered_boxes')) ||
                !$this->_isBoxEnabled($boxConfig))
            {
                continue;
            }
            $boxContent = $this->_getBoxContent($boxConfig);

            if (!empty($boxConfig['tabContainer'])) {
                foreach ($this->_tabAssignments[$key] as $tabBoxId => $tabBoxConfig) {
                    if ($tabBoxId == $boxId
                        || $boxConfig['tabContainer'] != $tabBoxConfig['tabContainer']
                        || !$this->_isBoxEnabled($tabBoxConfig))
                    {
                        continue;
                    }

                    $boxContent .= $this->_getBoxContent($tabBoxConfig);

                    $rendered_boxes = Zend_Registry::get('rendered_boxes');
                    $rendered_boxes[] = $tabBoxId;
                    Zend_Registry::set('rendered_boxes', $rendered_boxes);
                }
                $this->_wrapContentIntoTabs($boxContent, $boxConfig['tabContainer']);
            }

            if ($boxConfig['sort_order'] < 0) {
                $beforeContent .= $boxContent;
            } else {
                $afterContent .= $boxContent;
            }
        }

        return $beforeContent . parent::__get($key) . $afterContent;
    }

    private function _getBoxContent($boxConfig)
    {
        $boxClass = $boxConfig['boxCategory'] . '_' . $boxConfig['boxModule'] . '_Box_' . $boxConfig['boxName'];
        if ($box = $this->getView()->box($boxClass, $boxConfig)) {
            $html = null;
            $obStartLevel = ob_get_level();
            try {
                $html = $box->toHtml();
            } catch (Exception $e) {
                while (ob_get_level() > $obStartLevel) {
                    $html .= ob_get_clean();
                }
                throw $e;
            }
            return $html;
        }
        return '';
    }

    private function _isBoxEnabled($boxConfig)
    {
        if (!$boxConfig['show']) {
            return false;
        }
        if (strpos($boxConfig['boxModule'], 'Payment') === 0 /*|| strpos($box['module'], 'Shipping') === 0*/) {
            $method = Axis::single(
                $boxConfig['boxModule'] . '/' . str_replace('Button', '', $boxConfig['boxName'])
            );
            return $method->isEnabled();
        }
        return true;
    }

    private function _wrapContentIntoTabs(&$content, $class)
    {
        $content = "<div class='tab-container box tabs-{$class}'>{$content}</div>";
    }

    /**
     * Render layout
     *
     * Sets internal script path as last path on script path stack, assigns
     * layout variables to view, determines layout name using inflector, and
     * renders layout view script.
     *
     * $name will be passed to the inflector as the key 'script'.
     *
     * @param  mixed $name
     * @return mixed
     */
    public function render($name = null)
    {
        if (null === $name) {
            $name = $this->getLayout();
        }

        if ($this->inflectorEnabled() && (null !== ($inflector = $this->getInflector())))
        {
            $name = $this->_inflector->filter(array('script' => $name));
        }

        $view = $this->getView();

        // if (null !== ($path = $this->getViewScriptPath())) {
        //     if (method_exists($view, 'addScriptPath')) {
        //         $view->addScriptPath($path);
        //     } else {
        //         $view->setScriptPath($path);
        //     }
        // } elseif (null !== ($path = $this->getViewBasePath())) {
        //     $view->addBasePath($path, $this->_viewBasePrefix);
        // }

        return $view->render($name);
    }
}