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
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Cms
 * @subpackage  Axis_Cms_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Cms_Model_Block extends Axis_Db_Table
{
    protected $_name = 'cms_block';

    public function getContentByName($name)
    {
        return $this->select('cbc.content')
            ->joinLeft('cms_block_content', 'cbc.block_id = cb.id')
            ->where('cb.name = ?', $name)
            ->where('cbc.language_id = ?', Axis_Locale::getLanguageId())
            ->where('cb.is_active = ?', 1)
            ->fetchOne();
    }

    /**
     * Inserts or update cms_block
     *
     * @param array $data
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
    public function save(array $data)
    {
        if (isset($data['id']) && !$data['id']) {
            unset($data['id']);
        }
        $row = $this->getRow($data);
        $row->save();
        $languages = Axis_Collect_Language::collect();
        $model = Axis::model('cms/block_content');
        foreach ($languages as $languageId => $language) {
            if (!isset($data['content'][$languageId])) {
                continue;
            }
            $model->getRow($row->id, $languageId)
                ->setFromArray($data['content'][$languageId])
                ->save();
        }

        return $row->id;
    }
}