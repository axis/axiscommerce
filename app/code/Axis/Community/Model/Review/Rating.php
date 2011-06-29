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
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Community
 * @subpackage  Axis_Community_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Community_Model_Review_Rating extends Axis_Db_Table
{
    protected $_name = 'community_review_rating';
    
    /**
     * Retrieve the array of all avalable ratings
     * 
     * @param bool $enabledOnly
     * @param bool $currentLanguageOnly
     * @return array
     */
    public function getList($enabledOnly = true, $currentLanguageOnly = true)
    {
        $select = $this->select('*');
        
        $on = 'crrt.rating_id = crr.id';
        if ($currentLanguageOnly) {
            $on .= ' AND crrt.language_id = ' . Axis_Locale::getLanguageId();
        }
        $select->joinLeft('community_review_rating_title',
            $on, array('title', 'language_id')
        );
        $select->order('crrt.title DESC');
        
        if ($enabledOnly) {
            $select->where('status = ?', 'enabled');
        }
        
        $result = array();
        foreach ($select->fetchAll() as $rating) {
            if (!isset($result[$rating['id']])) {
                $result[$rating['id']] = array(
                    'id' => $rating['id'],
                    'name' => $rating['name'],
                    'status' => $rating['status'],
                    'title' => $rating['title']
                );
            }
            if (!empty($rating['language_id'])) {
                $result[$rating['id']]['title_' . $rating['language_id']] = $rating['title'];
            }
        }
        return array_values($result);
    }
    
    /**
     * Update or insert rating or array of ratings
     * 
     * @param array $data
     * id => array(
     *    id => int, //optional
     *    name => string, //required
     *    title => array( //optional
     *        language_id => title 
     *    ),
     *    status => enabled|disabled //optional
     * )
     * @return bool
     */
    public function save($data)
    {
        $count = 0;
        foreach ($data as $rating) {
            if (!$this->validate($rating)) {
                continue;
            }
            $count++;
            if (!is_numeric($rating['id']) 
                || !$row = $this->find($rating['id'])->current()) {

                $row = $this->createRow();
            }
            $row->setFromArray(array(
                'name' => $rating['name'],
                'status' => isset($rating['status']) ? $rating['status'] : 'enabled'
            ));
            $row->save();

            $ratingTitleModel = Axis::single('community/review_rating_title');
            foreach (Axis_Collect_Language::collect() as $id => $language) {
                $ratingTitleRow = $ratingTitleModel->find($row->id, $id)->current();
                if (!$ratingTitleRow) {
                    $ratingTitleRow = $ratingTitleModel->createRow(array(
                        'rating_id' => $row->id,
                        'language_id' => $id
                    ));
                }
                $ratingTitleRow->title = (isset($rating['title_' . $id]) && !empty($rating['title_' . $id]))
                    ? $rating['title_' . $id] : $rating['name'];
                
                $ratingTitleRow->save();
            }
        }
        if ($count) {
            Axis::message()->addSuccess(
                Axis::translate('community')->__(
                    "%d rating(s) was saved successfully", $count
                )
            );
        }
        return true;
    }
    
    public function validate($data)
    {
        $valid = true;
        
        if (!isset($data['name']) || empty($data['name'])) {
            Axis::message()->addError(
                Axis::translate('community')->__(
                    'Rating name is required'
            ));
            $valid = false;
        } elseif ($this->hasDuplicate($data['name'], isset($data['id']) ? $data['id'] : false)) {
            Axis::message()->addError(
                Axis::translate('community')->__(
                    "Rating with name '%s' is already exist", $data['name']
                )
            );
            $valid = false;
        }
        
        return $valid;        
    }
    
    /**
     * Check is the rating with $name already exist
     * 
     * @param string $name
     * @param int $idToExclude
     * @return bool
     */
    public function hasDuplicate($name, $idToExclude = false)
    {
        $select = $this->select('id')
            ->where('name = ?', $name);

        if (false !== $idToExclude) {
            $select->where('id <> ?', $idToExclude);
        }
        return $select->fetchOne();
    }
    
    /**
     * Remove ratings by ids
     * 
     * @param mixed $ids
     * @return void
     */
    public function remove($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $this->delete(
            $this->getAdapter()->quoteInto('id IN (?)', $ids)
        );
        Axis::message()->addSuccess(
            Axis::translate('community')->__(
                "%d rating(s) was deleted successfully", count($ids)
            )
        );
    }
}