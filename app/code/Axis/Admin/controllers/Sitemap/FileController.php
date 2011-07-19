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
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Admin
 * @subpackage  Axis_Admin_Controller
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Admin_Sitemap_FileController extends Axis_Admin_Controller_Back
{
    public function createAction()
    {
        $this->layout->disableLayout();

        $filename = $this->_getParam('filename');
        $alpha = new Zend_Filter_Alnum();
        $filename = $alpha->filter($filename);
        $filename .= '.xml';

        $siteId = $this->_getParam('site_id');

        $menu = new Zend_Navigation();
        $gmDate = gmdate('Y-m-d');
        $oldLocale = Axis_Locale::getLocale();
        $locales = Axis::single('locale/language')
                ->select(array('id', 'locale'))
                ->fetchPairs();
        foreach ($locales as $languageId => &$_locale) {
            Axis_Locale::setLocale($_locale);
            $_locale = Axis_Locale::getLanguageUrl();
        }
        Axis_Locale::setLocale($oldLocale);

        $categories = Axis::single('catalog/category')->select('*')
            ->addName()
            ->addKeyWord()
            ->order('cc.lft')
            ->addSiteFilter($siteId)
            ->addDisabledFilter()
            ->fetchAll()
            ;
        $config = Axis::config()->sitemap;

        $changefreq = $config->categories->frequency ;
        $priority   = $config->categories->priority ;

        $_container = $menu;
        $lvl = 0;
        foreach ($categories as $_category) {
            if (!isset($locales[$_category['language_id']])) {
                continue;
            }
            $uri = $this->view->hurl(array(
                'cat' => array(
                    'value' => $_category['id'],
                    'seo'   => $_category['key_word']
                ),
                'locale'     => $locales[$_category['language_id']],
                'controller' => 'catalog',
                'action'     => 'view'
            ), false, true);


            $page = new Zend_Navigation_Page_Uri(array(
                'label'      => $_category['name'],
                'title'      => $_category['name'],
                'uri'        => $uri,
                'order'      => $_category['lft'],
                'visible'    => 'enabled' === $_category['status'] ? true : false,
                'lastmod'    => $gmDate,
                'changefreq' => $changefreq,
                'priority'   => $priority,
                'id'         => $_category['id'] . $_category['language_id']
            ));

            $lvl = $lvl - $_category['lvl'] + 1;
            for ($i = 0; $i < $lvl; $i++) {
                $_container = $_container->getParent();
            }

            $lvl = $_category['lvl'];
            $_container->addPage($page);
            $_container = $page;
        }

        $products = Axis::single('catalog/product_category')->select()
            ->distinct()
            ->from('catalog_product_category', array())
            ->joinLeft('catalog_product',
                'cp.id = cpc.product_id',
                array('id'))
            ->addName()
            ->addKeyWord()
            ->addActiveFilter()
            ->addDateAvailableFilter()
            ->addSiteFilter($siteId)
            ->columns(array('category_id' => 'cc.id'))
            ->fetchAll()
            ;

        $changefreq = $config->products->frequency;
        $priority   = $config->products->priority;

        foreach($products as $_product) {
            if (!isset($locales[$_product['language_id']])) {
                continue;
            }
            $uri = $this->view->hurl(array(
                'cat' => array(
                    'value' => $_product['id'],
                    'seo' => $_product['key_word']
                ),
                'locale'     => $locales[$_product['language_id']],
                'controller' => 'catalog',
                'action'     => 'view'
            ), false, true);

            $page = new Zend_Navigation_Page_Uri(array(
                'label'      => $_product['name'],
                'title'      => $_product['name'],
                'uri'        => $uri,
                'lastmod'    => $gmDate,
                'changefreq' => $changefreq,
                'priority'   => $priority,
            ));

            $_container = $menu->findBy(
                'id', $_product['category_id'] . $_product['language_id']
            );
            if (null !== $_container) {
                $_container->addPage($page);
            }
        }

        $categories = Axis::single('cms/category')->select(array('id', 'parent_id'))
            ->addCategoryContentTable()
            ->columns(array('ccc.link', 'ccc.title', 'ccc.language_id'))
            ->addActiveFilter()
            ->addSiteFilter($siteId)
            ->where('ccc.link IS NOT NULL')
            ->fetchAll();

        $changefreq = $config->cms->frequency;
        $priority   = $config->cms->priority;

        foreach ($categories as $_category) {
            if (!isset($locales[$_category['language_id']])) {
                continue;
            }
            $title = empty($_category['title']) ?
                $_category['link'] : $_category['title'];

            $page = new Zend_Navigation_Page_Mvc(array(
                'label'      => $title,
                'title'      => $title,
                'route'      => 'cms_category',
                'params'     => array(
                    'cat'    => $_category['link'],
                    'locale' => $locales[$_category['language_id']]
                ),
                'id' => 'cms' . $_category['id'] . $_category['language_id'],
                'lastmod'    => $gmDate,
                'changefreq' => $changefreq,
                'priority'   => $priority
            ));
            $_container = $menu->findBy(
                'id', 'cms' . $_category['parent_id'] . $_category['language_id']
            );
            if (null === $_container) {
                $_container = $menu;
            }
            $_container->addPage($page);
        }
        $pages = array();
        if ($config->cms->showPages && !empty($categories)) {
            $pages = Axis::single('cms/page')->select(array('id', 'name'))
                ->join(array('cpca' => 'cms_page_category'),
                    'cp.id = cpca.cms_page_id',
                    'cms_category_id')
                ->join('cms_page_content',
                    'cp.id = cpc.cms_page_id',
                    array('link', 'title', 'language_id'))
                ->where('cp.is_active = 1')
                ->where('cpca.cms_category_id IN (?)', array_keys($categories))
                ->fetchAll()
                ;

            foreach($pages as $_page) {
                $title = empty($_page['title']) ? $_page['link'] : $_page['title'];

                $page = new Zend_Navigation_Page_Mvc(array(
                    'label'  => $title,
                    'title'  => $title,
                    'route'  => 'cms_page',
                    'params' => array(
                        'page'   => $_page['link'],
                        'locale' => $locales[$_page['language_id']]
                    ),
                    'lastmod'    => $gmDate,
                    'changefreq' => $changefreq,
                    'priority'   => $priority
                ));
                $_container = $menu->findBy(
                    'id', 'cms' . $_page['cms_category_id'] . $_page['language_id']
                );

                if (null !== $_container) {
                    $_container->addPage($page);
                }
            }
        }
        $content = $this->view->navigation()->sitemap($menu)
            ->setUseSitemapValidators(false)
            ->render();

        $this->getResponse()
            ->clearAllHeaders()
            ->setHeader('Content-Description', 'File Transfer', true)
            ->setHeader('Content-Type', 'application/octet-stream', true)
            ->setHeader('Content-Disposition', 'attachment; filename=' . $filename, true)
            ->setHeader('Content-Transfer-Encoding', 'binary', true)
            ->setHeader('Expires', '0', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Pragma', 'public', true)
//            ->setHeader('Content-Length: ', filesize($content), true)
            ;
        $this->getResponse()->setBody($content);
    }

    public function listAction()
    {
        $this->layout->disableLayout();
        $data = array();
        $handler = opendir(Axis::config('system/path'));
        while ($file = readdir($handler)) {
            if (is_dir($file)) {
                continue;
            }
            $pathinfo = pathinfo($file);
            if ('xml' !== $pathinfo['extension']) {
                continue;
            }
            $data[] = array('filename' => $file);
        }
        closedir($handler);
        return $this->_helper->json->setData($data)->sendSuccess();
    }
}