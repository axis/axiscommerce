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
 * @subpackage  Axis_Core_Admin_Controller
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Core
 * @subpackage  Axis_Core_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Admin_SiteController extends Axis_Admin_Controller_Back
{
    public function indexAction()
    {
        $this->view->pageTitle = Axis::translate('core')->__('Sites');
        $this->render();
    }

    public function listAction()
    {
        $data = Axis::single('core/site')->getList();
        return $this->_helper->json
            ->setData($data)
            ->sendSuccess()
        ;
    }

    public function batchSaveAction()
    {
        $dataset = Zend_Json::decode($this->_getParam('data'));
        
        $model         = Axis::model('core/site');
        $modelCategory = Axis::model('catalog/category'); 
        
        foreach ($dataset as $data) {
            $row = $model->save($data);
            
            //save root category
            if (empty($data['root_category'])) {
                $modelCategory->addNewRootCategory($row);
                continue;
            }
            // it's not safe if there are another categories linked to this site
            $isSiteHaveCategory = (bool) $modelCategory->select()
                ->where('site_id = ?', $row->id)
                ->fetchOne();

            if ($isSiteHaveCategory) {
                Axis::message()->addNotice(
                    Axis::translate('core')->__(
                        "Root category wasn't changed. Some categories already linked with the site %s. Unlink them from the site first",
                        $row->name
                    )
                );
                continue;
            }
            // update site_id for category and all of child nodes
            $oldSiteId = $modelCategory->select('site_id')
                ->where('id = ?', $data['root_category'])
                ->fetchOne();

            if ($oldSiteId != $row->id) {
                $modelCategory->update(array(
                    'site_id' => $row->id
                ), 'site_id = ' . $oldSiteId);
            }
        }

        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Site was saved successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }

    public function removeAction()
    {
        $data = Zend_Json::decode($this->_getParam('data'));

        Axis::model('core/site')->delete(
            $this->db->quoteInto('id IN(?)', $data)
        );
        Axis::dispatch('core_site_delete_success', array('site_ids' => $data));
        Axis::message()->addSuccess(
            Axis::translate('admin')->__(
                'Site was deleted successfully'
            )
        );
        return $this->_helper->json->sendSuccess();
    }
}