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
 * @package     Axis_Controller
 * @subpackage  Plugin
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Controller
 * @subpackage  Plugin
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Controller_Plugin_Layout extends Zend_Layout_Controller_Plugin_Layout
{
    /**
     * Assoc pages array
     *
     * @var array
     */
    protected $_pages;

    /**
     *
     * @return array
     */
    public function getPages()
    {
        return $this->_pages;
    }

    /**
     * @param array $pages
     * @return void
     */
    public function setPages($pages)
    {
        $this->_pages = $pages;
    }

    /**
     *
     * @param array $node
     * @param array $rewriteNode
     * @return bool
     */
    private function _sortPages($node, $rewriteNode)
    {
        if ((0 > strcmp($node['module_name'], $rewriteNode['module_name'])) ||
            (0 > strcmp($node['controller_name'], $rewriteNode['controller_name'])) ||
            (0 > strcmp($node['action_name'], $rewriteNode['action_name'])))
        {
            return true;
        }
        return false;
    }
    protected function _initPages()
    {
        $request = $this->getRequest();
        list($namespace, $module) = explode('_', $request->getModuleName(), 2);
        $pages = Axis::single('core/page')->getPagesByRequest(
            strtolower($module),
            $request->getControllerName(),
            $request->getActionName()
        );

        uasort($pages, array($this, '_sortPages'));
        $this->setPages($pages);
    }

    protected function _initLayout()
    {
        $layout = $this->getLayout();
        $layoutName = $layout->getLayout();
        if (!empty($layoutName)) {
            return;
        }
        if (Axis_Area::isBackend()) {
            $layoutName = 'layout';
            $layout->setLayout($layoutName, false);
            return;
        }
        $pages = $this->getPages();
        $themeId = Axis::config('design/main/frontTemplateId');

        $dataset = Axis::single('core/template_page')->select()
            ->where('template_id = ?', $themeId)
            ->where('page_id IN(?)', array_keys($pages))
            ->order('priority DESC')
            ->fetchAll();

        $pageId = null;
        foreach ($dataset as $row) {
            if (null !== $pageId &&
                !$this->_sortPages($pages[$pageId], $pages[$row['page_id']])) {

                continue;
            }
            $pageId       = $row['page_id'];
            $layoutName   = $row['layout'];
            $parentPageId = $row['parent_page_id'];
        }

        if (empty($layoutName)) {
            $layoutName = Axis::single('core/template_page')->select('layout')
                ->where('template_id = ?', $themeId)
                ->where('page_id = ? ', $parentPageId)
                ->fetchOne();
        }

        if (empty($layoutName)) {
            $layoutName = Axis_Layout::DEFAULT_LAYOUT;
            $theme = Axis::single('core/template')->find($themeId)->current();
            if ($theme && !empty($theme->default_layout)) {

                $layoutName = $theme->default_layout;
            }
        }

        $layout->setLayout($layoutName, false);
    }
    
    protected function _initBlockAssigns()
    {
        $pages = $this->getPages();
        if (!count($pages)) {
            return;
        }
        $assigns = array();
        $tabAssigns = array();

        // add parent page
        $strongPage = current($pages);
        $themeId = Axis::config('design/main/frontTemplateId');
        
        $parentPage = Axis::single('core/page')->select('*')
            ->join('core_template_page', 'cp.id = ctp.parent_page_id')
            ->where('ctp.template_id = ?', $themeId)
            ->where('ctp.page_id = ?', $strongPage['id'])
            ->fetchRow();
        if ($parentPage) {
            $pages[$parentPage['id']] = $parentPage;
        }
        $blockset = Axis::single('core/template_box')->select(
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
            )->where('ctb.template_id = ?', $themeId)
            ->where('ctb.box_status = 1')
            ->where('ctbp.page_id IN(?)', array_keys($pages))
            ->order('ctb.sort_order')
            ->fetchAll()
            ;
        foreach ($blockset as $block) {

            $container = empty($block['other_block']) ?
                $block['block'] : $block['other_block'];
            $blockId = $block['id'];

            if (isset($assigns[$container][$blockId])) {
                $oldPage = $pages[$assigns[$container][$blockId]['page_id']];
                $newPage = $pages[$block['page_id']];
                if (!$this->_sortPages($oldPage, $newPage)) {
                    continue;
                }
            }

            // example: Axis_Locale_Currency
            list($namespace, $module, $box) = explode('_', $block['class']);

            if (!isset($module) || !isset($box)) {
                continue;
            }
            $assign = array(
                'boxNamespace'  => ucfirst($namespace),
                'boxModule'     => ucfirst($module),
                'boxName'       => ucfirst($box),
                'template'      => $block['template'],
                'tab_container' => $block['tab_container'],
                'sort_order'    => $block['sort_order'],
                'page_id'       => $block['page_id'],
                'box_show'      => $block['box_show']
            );
            if (!empty($block['config'])) {
                $assign['config'] = $block['config'];
            }

            if (strstr($block['class'], 'Axis_Cms_Block_')) {
                $staticBlock = trim(str_replace('Axis_Cms_Block_', '', $block['class']));
                if (empty($staticBlock)) {
                    continue;
                }
                $assign['staticBlock'] = $staticBlock;
            }
            $tabContainer = $block['tab_container'];
            if (!empty($tabContainer)) {
                $assigns[$container][$tabContainer][$blockId] = $assign;
            } else {
                $assigns[$container][$blockId] = $assign;
            }
        }

        $this->getLayout()->setAssigments($assigns);
    }

    /**
     * postDispatch() plugin hook -- render layout
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_initPages();
        $this->_initLayout();
        $this->_initBlockAssigns();
        return parent::postDispatch($request);
    }
}
