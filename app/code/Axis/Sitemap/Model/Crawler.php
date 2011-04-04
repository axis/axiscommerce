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
 * @subpackage  Axis_Sitemap_Model
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Sitemap
 * @subpackage  Axis_Sitemap_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Sitemap_Model_Crawler extends Axis_Object implements IteratorAggregate
{
    const GOOGLE_ID = 1;
    const YAHOO_ID  = 2;
    const BING_ID   = 3;

    public function  __construct($data = null)
    {
        $this->add(
            self::GOOGLE_ID, 'Google', 'http://www.google.com/webmasters/sitemaps/ping?sitemap='
        )->add(
            self::YAHOO_ID,  'Yahoo',  'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=SitemapWriter&url='
            //'http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap='
        )->add(
            self::BING_ID,   'Bing',   'http://www.bing.com/webmaster/ping.aspx?siteMap='
        );
        parent::__construct($data);
    }

    /**
     *
     * @param int $id
     * @param string $name
     * @param string $uri
     * @return Axis_Sitemap_Model_Crawler
     */
    public function add($id, $name, $uri)
    {
        $this->$id = //new Axis_Object(
            array(
            'id'   => $id,
            'name' => $name,
            'uri'  => $uri
        //)
        );
        return $this;
    }

    /**
     *
     * @return ArrayIterator 
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getData());
    }
}