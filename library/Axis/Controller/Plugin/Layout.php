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

    protected function _getLayoutName()
    {
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

        if (empty($layoutName) && !empty($parentPageId)) {
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
        return $layoutName;
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
            $pages[$parentPage->id] = $parentPage->toArray();
        }
        $blockset = Axis::single('core/template_box')->select(array(
                'id', 'class', 'block', 'config',
                'sort_order' => 'IF (ctbp.sort_order IS NOT NULL, ctbp.sort_order, ctb.sort_order)'
            ))
            ->joinInner(
                'core_template_box_page',
                'ctbp.box_id = ctb.id',
                array(
                    'box_show',
                    'other_block' => 'block',
                    'template',
                    'tab_container',
                    'page_id'
                )
            )
            ->where('ctb.template_id = ?', $themeId)
            ->where('ctb.box_status = 1')
            ->where('ctbp.page_id IN (?)', array_keys($pages))
            ->order('sort_order')
            ->fetchAll();

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
            $assign = array_merge(array(
                'box_namespace' => ucfirst($namespace),
                'box_module'    => ucfirst($module),
                'box_name'      => ucfirst($box),
                'template'      => $block['template'],
                'tab_container' => $block['tab_container'],
                'sort_order'    => $block['sort_order'],
                'page_id'       => $block['page_id'],
                'box_show'      => $block['box_show']
            ), Zend_Json::decode($block['config']));

            $perPageRules = array('tab_container', 'template', 'sort_order');
            foreach ($perPageRules as $ruleKey) {
                if (empty($block[$ruleKey])) {
                    continue; // use value from config
                }
                $assign[$ruleKey] = $block[$ruleKey];
            }

            if (strstr($block['class'], 'Axis_Cms_Block_')) {
                $staticBlock = trim(str_replace('Axis_Cms_Block_', '', $block['class']));
                if (empty($staticBlock)) {
                    continue;
                }
                $assign['static_block'] = $staticBlock;
            }

            $tabContainer = $assign['tab_container'];
            if (!empty($tabContainer)) {
                if (isset($assigns[$container][$tabContainer][$blockId])) {
                    $oldPage = $pages[$assigns[$container]
                        [$tabContainer][$blockId]['page_id']];
                    $newPage = $pages[$block['page_id']];
                    if (!$this->_sortPages($oldPage, $newPage)) {
                        continue;
                    }
                }
                $assigns[$container][$tabContainer][$blockId] = $assign;
                if (isset($assigns[$container][$blockId])) {
                    unset($assigns[$container][$blockId]);
                }
            } else {
                $assigns[$container][$blockId] = $assign;
                if (isset($assigns[$container][$tabContainer][$blockId])) {
                    unset($assigns[$container][$tabContainer][$blockId]);
                }
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
        $layout = $this->getLayout();
        $helper = $this->getLayoutActionHelper();

        // Return early if forward detected
        if (!$request->isDispatched()
            || $this->getResponse()->isRedirect()
            || ($layout->getMvcSuccessfulActionOnly()
                && (!empty($helper) && !$helper->isActionControllerSuccessful())))
        {
            return;
        }

        // Return early if layout has been disabled
        if (!$layout->isEnabled()) {
            return;
        }

        // two logic mix
        $layoutName = $layout->getLayout();
        if (Axis_Area::isFrontend()) {
            $this->_initPages();
            if (empty($layoutName)) {
                $layoutName = $this->_getLayoutName();
            }
            $this->_initBlockAssigns();
        } elseif (empty($layoutName)) {
            $layoutName = 'layout';
        }
        $layout->setLayout($layoutName, false);

        $response   = $this->getResponse();
        $content    = $response->getBody(true);
        $contentKey = $layout->getContentKey();

        if (isset($content['default'])) {
            $content[$contentKey] = $content['default'];
        }
        if ('default' != $contentKey) {
            unset($content['default']);
        }

        $layout->assign($content);

        $fullContent = null;
        $obStartLevel = ob_get_level();
        try {
            $fullContent = $layout->render();
            $response->setBody($fullContent);
        } catch (Exception $e) {
            while (ob_get_level() > $obStartLevel) {
                $fullContent .= ob_get_clean();
            }
            $request->setParam('layoutFullContent', $fullContent);
            $request->setParam('layoutContent', $layout->content);
            $response->setBody(null);
            throw $e;
        }

    }
}
