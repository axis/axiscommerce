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
 * @package     Axis_Sitemap
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * 
 * @category    Axis
 * @package     Axis_Sitemap
 * @subpackage  Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sitemap_Model_File_Engine extends Axis_Db_Table
{
    protected $_name = 'sitemap_file_engine';

    /**
     *
     * @return array
     */
    public function getEngines()
    {
        return array (
            1 => array(
                'id'   => '1',
                'name' => 'Google',
                'url'  => Axis::config()->sitemap->main->googlePingUrl
            ),
            2 => array(
                'id'   => '2',
                'name' => 'Yahoo',
                'url'  => Axis::config()->sitemap->main->yahooPingUrl
            ),
            3 => array(
                'id'   => '3',
                'name' => 'Ask',
                'url'  => Axis::config()->sitemap->main->askPingUrl
            ),
            4 => array(
                'id'   => '4',
                'name' => 'MS',
                'url'  => Axis::config()->sitemap->main->msnPingUrl
            )
        );
    }

    /**
     *
     * @param array $engineIds
     * @param int $sitemapId
     * @return void
     */
    public function save($engineIds, $sitemapId)
    {
        $this->delete($this->getAdapter()
            ->quoteInto('sitemap_file_id = ?', $sitemapId));
        foreach ($engineIds as $engineId ) {
            if ($engineId != '' 
                && in_array($engineId, array_keys($this->getEngines()))) {

                $this->insert(array(
                    'sitemap_file_id'   => $sitemapId,
                    'sitemap_engine_id' => $engineId
                ));
            }
        }
    }

    /**
     *
     * @return array
     */
    public function getEnginesNamesAssigns()
    {
        $engines = $this->getEngines();

        $assigns = array();
        foreach ($this->select()->fetchAll() as $row) {
            $assigns[$row['sitemap_file_id']][$row['sitemap_engine_id']] =
                $engines[$row['sitemap_engine_id']]['name'];
        }
        return $assigns;
    }
}