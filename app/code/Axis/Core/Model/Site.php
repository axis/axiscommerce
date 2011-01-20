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
class Axis_Core_Model_Site extends Axis_Db_Table
{
    protected $_name = 'core_site';

    /**
     * @return array
     */
    public function getList()
    {
        $languageId = Axis_Locale::getLanguageId();

        return $this->select('*')
            ->joinLeft(
                'catalog_category',
                'cs.id = cc.site_id AND cc.lvl = 0',
                array('root_category' => 'id')
            )
            ->joinLeft(
                'catalog_category_description',
                $this->getAdapter()->quoteInto(
                    'cc.id = ccd.category_id AND ccd.language_id = ?', $languageId
                ), array('category_name' => 'name')
            )
            ->fetchAll()
            ;
    }

    /**
     * @param string $url
     * @return Axis_DB_Table_Row|false
     */
    public function getByUrl($url)
    {
        $sites  = $this->fetchAll(null, 'length(base) DESC'); // order for correct site detection
        $scheme = 'https://';
        $base   = 'secure';
        if (0 !== strpos($url, $scheme)) {
            $base   = 'base';
            $scheme = 'http://';
        }

        if (0 === strpos($url, $scheme . 'www.')) {
            $secondaryUrl = str_replace($scheme . 'www.', $scheme , $url);
        } else {
            $secondaryUrl = str_replace($scheme, $scheme . 'www.' , $url);
        }

        foreach (array($url, $secondaryUrl) as $url) {
            foreach ($sites as $site) {
                if (empty($site->base) || empty($site->secure)) {
                    continue;
                }
                $baseMath    = (0 === strpos($url, $site->base));
                $secureMatch = (0 === strpos($url, $site->secure));
                if ($baseMath || $secureMatch) {
                    // check for similar urls:
                    // example.com/axis vs example.com/axis2
                    // example.com/axis vs example.com/axis/axis
                    $matchedUrl = $baseMath ? $site->base : $site->secure;
                    $matchedUrlLegth = strlen($matchedUrl);
                    if ($matchedUrlLegth > strlen($url)) {
                        continue;
                    }
                    $requestUri = substr($url, $matchedUrlLegth);
                    if (!empty($requestUri) && $requestUri[0] !== '/') {
                        continue;
                    }
                    return $site;
                }
            }
        }
        return false;
    }

    /**
     * @param mixed (int|bool) $siteId[optional]
     * @return array
     */
    public function getCompanyInfo($siteId = null)
    {
        $company = Axis::config('core/company', $siteId)->toArray();
        //@todo Use Axis_Object
        return array(
            'email'     => Axis_Collect_MailBoxes::getName($company['administratorEmail']),
            'city'      => $company['city'],
            'country'   => Axis_Collect_Country::getName($company['country']),
            'fax'       => $company['fax'],
            'name'      => $company['name'],
            'phone'     => $company['phone'],
            'postcode'  => $company['zip'],
            'site'      => $company['site'],
            'zone'      => Axis_Collect_Zone::getName($company['zone']),
            'street_address' => $company['street'],
            'customer_relation_email' => Axis_Collect_MailBoxes::getName(
                $company['customerRelationEmail']
            ),
            'sales_email' => Axis_Collect_MailBoxes::getName(
                $company['salesDepartmentEmail']
            ),
            'support_email' => Axis_Collect_MailBoxes::getName(
                $company['supportEmail']
            )
        );
    }

    /**
     *
     * @param array $data
     * @return void
     */
    public function save($data)
    {
        $languages = Axis_Collect_Language::collect();

        foreach ($data as $id => $values) {
            if ($values['id'] == 'new') {
                $siteId = $this->insert(array(
                    'name' => $values['name'],
                    'base' => $values['base'],
                    'secure' => $values['secure']
                ));
                $categoryId = Axis::single('catalog/category')->insert(array(
                    'site_id'    => $siteId,
                    'lft'        => 1,
                    'rgt'        => 2,
                    'lvl'        => 0,
                    'created_on' => Axis_Date::now()->toSQLString(),
                    'status'     => 'enabled'
                ));

                foreach ($languages as $languageId => $name) {
                    Axis::single('catalog/category_description')->insert(array(
                        'category_id' => $categoryId,
                        'language_id' => $languageId,
                        'name'        => $values['name'],
                        'description' => 'Root Category'
                    ));
                }
            } else {
                $this->update(array(
                    'name'   => $values['name'],
                    'base'   => $values['base'],
                    'secure' => $values['secure']
                ), "id = $values[id]");

                // link another root_category for site if nessesary
                if (!$values['root_category']) {
                    continue;
                }

                $oldSite = Axis::single('catalog/category')
                    ->select('site_id')
                    ->where('id = ?', $values['root_category'])
                    ->fetchOne();

                if ($oldSite == $values['id']) {
                    continue;
                }

                // it's not safe if there are another categories linked to this site
                if (Axis::single('catalog/category')
                        ->select('id')
                        ->where('site_id = ?', $values['id'])
                        ->fetchOne()) {

                    Axis::message()->addNotice(
                        Axis::translate('core')->__(
                            "Root category wasn't changed. Some categories already linked with the site %s. Unlink them from the site first",
                            $values['name']
                        )
                    );
                } else {
                    // update site_id for category and all of child nodes
                    Axis::single('catalog/category')
                        ->update(array(
                            'site_id' => $values['id']
                        ), 'site_id = ' . $oldSite);
                }
            }
        }
    }
}