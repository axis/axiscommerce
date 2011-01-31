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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Core_Model_Template extends Axis_Db_Table
{
    const DEFAULT_TEMPLATE = 'default';

    protected $_name = 'core_template';

    /**
     * Retrieve the array of currently using templates
     *
     * @return array(site_id => template_id)
     */
    public function getUsed()
    {
        $frontTemplates = Axis::single('core/config_value')->getValues('design/main/frontTemplateId');
        $adminTemplates = Axis::single('core/config_value')->getValues('design/main/adminTemplateId');
        return $frontTemplates + $adminTemplates;
    }

    /**
     * Retrieve information about template
     *
     * @param int $id
     * @return array
     */
    public function getInfo($id = '')
    {
        if (!is_numeric($id) || !$template = $this->find($id)->current()) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Template not found'
            ));
            return array();
        }

        $templateAssignments = '';
        $usedTemplates = $this->getUsed();

        if (in_array($template->id, $usedTemplates)) {
            $sites = Axis_Collect_Site::collect();
            $sites[0] = 'Global';
            foreach ($usedTemplates as $siteId => $templateId){
                if ($template->id == $templateId && isset($sites[$siteId]))
                    $templateAssignments .= $sites[$siteId] . ', ';
            }
            $templateAssignments = substr($templateAssignments, 0, -2);
        }

        $result = $template->toArray();
        $result['assignments'] = $templateAssignments;

        return $result;
    }

    /**
     * Save or insert template
     *
     * @param array $data
     * @return bool
     */
    public function save($data)
    {
        if (empty($data['name']) || empty($data['default_layout'])) {
            Axis::message()->addError(
                Axis::translate('core')->__(
                    'Required fields are missing'
            ));
            return false;
        }

        if (isset($data['is_active'])) {
            $data['is_active'] = 1;
        } else {
            $data['is_active'] = 0;
        }

        if (!$row = $this->find($data['id'])->current()) {
            unset($data['id']);
            $row = $this->createRow();
        }
        $row->setFromArray($data);
        $row->save();

        Axis::message()->addSuccess(
            Axis::translate('core')->__(
                'Template was saved successfully'
        ));
        return true;
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
                $languageIds = array_keys(Axis_Collect_Language::collect());
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
     *   @copyright http://ua.php.net/manual/ru/class.dir.php#79448
     *   getDirTree(string $dir [, bool $showfiles]);
     *   $dir of the folder you want to list, be sure to have an ending /
     *   $showfiles set to 'false' if files shouldnt be listed in the output array
     *  @param string $dir
     *  @param bool $p [optional]
     *  @return array
     */
    private function _getDirTree($dir, $p = true)
    {
        $d = dir($dir);$x = array();
        while (false !== ($r = $d->read())) {
            if($r[0] != "." &&  $r != "." && $r != ".." &&
               ((false == $p && is_dir($dir.$r)) || true == $p)) {

               $x[$r] = (is_dir($dir . $r) ? array() : (is_file($dir . $r) ? true : false));
            }
        }
        foreach ($x as $key => $value) {
            if (/*is_dir($dir.$key."/")*/ is_readable($dir . $key . '.xml')) {
                $x[$key] = $dir . $key . '.xml';
                //$this->_getDirTree($dir.$key."/",$p);
            }
        }
        ksort($x);
        return $x;
    }

    /**
     *
     * @return array
     */
    public function getListXmlFiles()
    {
        $existTemplate = array();
        foreach ($this->fetchAll()->toArray() as $template) {
            $existTemplate[] =  $template['name'];
        }
        $dir = Axis::config()->system->path . '/var/templates/';
        $templates = array();


        foreach ($this->_getDirTree($dir) as $key => $value) {
            if (!in_array($key, $existTemplate))
                $templates[] = array('template' => $key/*, 'file' => $value*/);
        }
        return $templates;
    }

    /**
     *
     * @param string $templateName
     * @return string
     */
    private function  _parseXml($templateName)
    {
        if (!is_readable($templateName)) {
            $templateName = Axis::config()->system->path . '/var/templates/' . $templateName;
        }
        if (!is_readable($templateName)) {
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
        $xml->open($templateName);
        $assoc = _xml2assoc($xml);
        $xml->close();
        return current($assoc);
    }

    /**
     *
     * @param string $xmlFileName
     * @return bool
     */
    public function validateBeforeImport($xmlFileName)
    {
        $template = $this->_parseXml($xmlFileName);
        if ($templateId = $this->getIdByName($template['name'])) {
            return false;
        }
        return true;
    }

    /**
     *
     * @param string $xmlFileName
     * @return bool
     */
    public function importTemplateFromXmlFile($xmlFileName)
    {
        function _getConcatPage(array $page ) {
            return $page['module'] . '/' . $page['controller'] . '/' . $page['action'];
        }

        $template = $this->_parseXml($xmlFileName);

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
                'sort_order'  => $box['sortOrder'],
                'config'      => (string)$box['config'],
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
                        'tab_container' => $page['tab_container']
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
            $content = array();
            foreach ($cmsBlock['content'] as $row) {
                $content[$row['language_id']] = $row;
            }
            $cmsBlock['content'] = $content;
            $modelBlock->save($cmsBlock);
        }

        return true;
    }

}