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
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Template extends Axis_Db_Table implements Axis_Config_Option_Array_Interface
{
    const DEFAULT_TEMPLATE = 'default';

    protected $_name = 'core_template';

    /**
     * Retrieve the array of currently using templates
     * @param int $templateId
     * @return bool
     */
    public function isUsed($templateId)
    {
        return (bool) Axis::single('core/config_value')->select()
            ->where("path = 'design/main/frontTemplateId' OR path = 'design/main/adminTemplateId'")
            ->where('value = ?', $templateId)
            ->fetchOne()
            ;
    }

    /**
     * Save or insert template
     *
     * @param array $data
     * @return Axis_Db_Table_Row
     */
    public function save(array $data)
    {
        $row = $this->getRow($data);
        $row->save();
        return $row;
    }

    /**
     * Prepare to generate xml
     * @param int $templateId
     * @return array
     */
    public function getFullInfo($templateId)
    {
        if (!$template = $this->find($templateId)->current()) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Template not found'
            ));
            return false;
        }
        $template = $template->toArray();
        $pages = array();
        $orderBy = array('module_name', 'controller_name', 'action_name');
        foreach (Axis::single('core/page')->fetchAll(null, $orderBy) as $row) {
            $pages[$row->id] = array(
                'id'         => $row->id,
                'module'     => $row->module_name,
                'controller' => $row->controller_name,
                'action'     => $row->action_name
            );
        }

        $boxes = Axis::single('core/template_box')->select()
            ->where('template_id = ?', $templateId)
            ->fetchAll();

        $cmsBlocks = array();
        foreach ($boxes as &$box) {
            $box['pages'] = array();
            $boxToPage = Axis::single('core/template_box_page')->fetchAll(array(
                'box_id = ' . $box['id']
            ));

            if ('Axis_Cms_Block_' === substr($box['class'], 0, strlen('Axis_Cms_Block_'))) {
                $name = substr($box['class'], strlen('Axis_Cms_Block_'));

                $rowset = Axis::single('cms/block')->select('*')
                    ->join('cms_block_content', 'cb.id = cbc.block_id', '*')
                    ->where('cb.name = ?', $name)
                    ->fetchAll();
                $languageIds = array_keys(Axis_Locale_Model_Language::getConfigOptionsArray());
//                $cmsBlocks = array();
                foreach ($rowset as $row) {

                    if (isset($cmsBlocks[$row['id']]['content'])) {
                        $content = $cmsBlocks[$row['id']]['content'];
                    } else {
                        $content = array_fill_keys($languageIds , null);
                    }
                    $content[$row['language_id']] = $row['content'];
                    $row['content'] = $content;
                    unset($row['language_id']);
                    $cmsBlocks[$row['id']] = $row;
                }

//                $cmsBlocks[] = $cmsBlocks;
            }
            foreach ($boxToPage as $item) {
                $box['pages'][] = array(
                    'id'            => $item->page_id,
                    'module'        => $pages[$item->page_id]['module'],
                    'controller'    => $pages[$item->page_id]['controller'],
                    'action'        => $pages[$item->page_id]['action'],
                    'box_show'      => $item->box_show,
                    'block'         => $item->block,
                    'sort_order'    => $item->sort_order,
                    'template'      => $item->template,
                    'tab_container' => $item->tab_container
                );
            }
        }
        $template['boxes'] = $boxes;

        $layouts = Axis::single('core/template_page')->select()
            ->where('template_id = ?', $templateId)
            ->fetchAll();
        foreach ($layouts as &$layout) {
            $layout['page'] = $pages[$layout['page_id']];
            if (isset($pages[$layout['parent_page_id']])) {
                $layout['parent_page'] = $pages[$layout['parent_page_id']];
            }
        }
        $template['layouts'] = $layouts;
        $template['cms_block'] = $cmsBlocks;
        return $template;
    }

    /**
     *
     * @param string $xmlFilePath Path to the xml file
     * @return string
     */
    private function  _parseXml($xmlFilePath)
    {
        if (!is_readable($xmlFilePath)) {
            $xmlFilePath = Axis::config('system/path') . '/var/templates/' . $xmlFilePath;
        }
        if (!is_readable($xmlFilePath)) {
            return false;
        }

        if (!function_exists('_xml2assoc')) {
            /**
             *
             * @copyright http://ua2.php.net/manual/ru/class.xmlreader.php#83929
             * @param string $xml
             * @return array
             */
            function _xml2assoc($xml)
            {
                $assoc = null;
                while($xml->read()){
                    switch ($xml->nodeType) {
                    case XMLReader::END_ELEMENT:
                        return $assoc;
                    case XMLReader::ELEMENT:
                        if($xml->hasAttributes && $xml->getAttribute('multiple')){
                            $assoc[$xml->name ][] = $xml->isEmptyElement ? '' : _xml2assoc($xml);
                        } else {
                            $assoc[$xml->name] = $xml->isEmptyElement ? '' : _xml2assoc($xml);
                        }
                        break;
                    case XMLReader::TEXT:
                    case XMLReader::CDATA:
                        $assoc .= $xml->value;
                    }
                }
                return $assoc;
            }
        }
        $xml = new XMLReader();
        $xml->open($xmlFilePath);
        $assoc = _xml2assoc($xml);
        $xml->close();
        return current($assoc);
    }

    /**
     * @param string $xmlFilePath Path to imported xml file
     * @return bool
     */
    public function validateBeforeImport($xmlFilePath)
    {
        $template = $this->_parseXml($xmlFilePath);
        if ($templateId = $this->getIdByName($template['name'])) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param string $xmlFilePath
     * @return bool
     */
    public function importTemplateFromXmlFile($xmlFilePath)
    {
        function _getConcatPage(array $page) {
            return $page['module'] . '/' . $page['controller'] . '/' . $page['action'];
        }

        $template = $this->_parseXml($xmlFilePath);

        $template['id'] = $this->getIdByName($template['name']);
        $templateRow = $this->getRow($template);
        $templateRow->save();
        $template['id'] = $templateRow->id;

        $modelPage = Axis::model('core/page');
        $select = $modelPage->select(array(
            'id', 'page' => "CONCAT(module_name, '/', controller_name, '/', action_name)"
        ))->order(array('module_name', 'controller_name', 'action_name'));
        $existPages = $select->fetchPairs();
        $pages      = array();
        $boxes      = $template['box'];
        $layouts    = $template['layout'];
        $cmsBlocks  = $template['cms_block'];

        // import new pages
        foreach ($boxes as $box) {
            foreach ($box['page'] as $page) {
                $pages[] = _getConcatPage($page);
            }
        }
        foreach ($layouts as $_row) {
            $pages[] = _getConcatPage($_row['page']);

        }
        $pages = array_diff(array_unique($pages), $existPages);

        foreach ($pages as $page) {
            $modelPage->add($page);
        }
        $existPages = array_flip($select->fetchPairs());

        //import boxes
        $modelTemplateBox = Axis::model('core/template_box');
        $modelTemplateBoxPage = Axis::model('core/template_box_page');
        foreach ($boxes as $box) {
            $boxId = $modelTemplateBox->insert(array(
                'template_id' => $template['id'],
                'block'       => $box['block'],
                'class'       => $box['class'],
                'sort_order'  => $box['sort_order'],
                'config'      => empty($box['config']) ?
                    '{}' : $box['config'],
                'box_status'  => $box['status']
            ));
            $pages = $box['page'];
            foreach ($pages as $page) {
                $modelTemplateBoxPage->insert(array(
                    'box_id'   => $boxId,
                    'page_id'  => $existPages[_getConcatPage($page)],
                    'box_show' => $page['show'],
                    'block'    => $page['block'],
                    'template' => $page['template'],
                    'tab_container' => $page['tab_container'],
                    'sort_order'    => empty($page['sort_order']) ?
                        new Zend_Db_Expr('NULL') : $page['sort_order']
                ));
            }
        }
        //import layouts
        $modelTemplatePage = Axis::model('core/template_page');
        foreach ($layouts as $_row) {
            $layout = is_null($_row['layout']) ? '' : $_row['layout'];
            $page = _getConcatPage($_row['page']);
            $parentPage = null;
            if (isset($_row['parent_page'])) {
                $parentPage = _getConcatPage($_row['parent_page']);
            }
            $modelTemplatePage->add(
                $layout,
                $page,
                $template['id'],
                $parentPage,
                $_row['priority']
            );
        }
        //import cms blocks
        $modelBlock = Axis::model('cms/block');
        $languageIds  = array_keys(Axis_Locale_Model_Language::getConfigOptionsArray());
        $modelContent = Axis::model('cms/block_content');
        foreach ($cmsBlocks as $cmsBlock) {
            $cmsBlockId = $modelBlock->getIdByName($cmsBlock['name']);
            if ($cmsBlockId) {
                Axis::message()->addNotice(
                    Axis::translate('core')->__(
                        'Cms block %s already exist', $cmsBlock['name']
                    )
                );
                continue;
            }

            $row = $modelBlock->save($cmsBlock);
            
            //save cms block content
            $content = array();
            foreach ($cmsBlock['content'] as $rowContent) {
                $content[$rowContent['language_id']] = $rowContent;
            }
            foreach ($languageIds as $languageId) {
                if (!isset($content[$languageId])) {
                    continue;
                }
                $modelContent->getRow($row->id, $languageId)
                    ->setFromArray($content[$languageId])
                    ->save();
            }
        }

        return true;
    }

    public function getTemplateNameById($id)
    {
        if (!$row = $this->find($id)->current()) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    "Template %s not found in 'core_template' table. Check your template values at the 'design/main' config section", $templateId
            ));
            return Axis_Core_Model_Template::DEFAULT_TEMPLATE;
        }
        return $row->name;
    }

    /**
     * Duplicates boxes and layout preferences from one theme to another
     *
     * @param string $from
     * @param string $to    Must be unique value
     * @return mixed Axis_Db_Table_Row|false
     */
    public function duplicate($from, $to)
    {
        $fromId = $this->getIdByName($from);
        if (!$fromId) {
            return false;
        }

        $data = $this->getFullInfo($fromId);
        if ($toId = $this->getIdByName($to)) {
            $template = $this->find($toId)->current();
        } else {
            $template = $this->createRow();
            $template->name = $to;
        }
        $template->default_layout = $data['default_layout'];
        $template->save();

        //import boxes
        $mTemplateBox       = Axis::model('core/template_box');
        $mTemplateBoxPage   = Axis::model('core/template_box_page');
        foreach ($data['boxes'] as $boxData) {
            unset($boxData['id']);
            $box = $mTemplateBox->createRow($boxData);
            $box->template_id = $template->id;
            $box->save();
            foreach ($boxData['pages'] as $pageData) {
                $page = $mTemplateBoxPage->createRow($pageData);
                $page->box_id    = $box->id;
                $page->page_id   = $pageData['id'];
                $page->save();
            }
        }

        //import layouts
        $mTemplatePage = Axis::model('core/template_page');
        foreach ($data['layouts'] as $layoutData) {
            unset($layoutData['id']);
            $layout = $mTemplatePage->createRow($layoutData);
            $layout->template_id = $template->id;
            $layout->save();
        }

        return $template;
    }
    
    /**
     *
     * @static
     * @return array
     */
    public static function getConfigOptionsArray()
    {
        return Axis::single('core/template')
                ->select(array('id', 'name'))
                ->fetchPairs();
    }

    /**
     *
     * @static
     * @param int $key
     * @return string
     */
    public static function getConfigOptionValue($key)
    {
        return Axis::single('core/template')->getNameById($key);
    }
}
