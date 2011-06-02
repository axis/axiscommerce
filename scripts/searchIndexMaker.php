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
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 * JavaScript localization search
 * This script will find all language sensitive statements, that are thanslated via js function l() and output them to $output
 */
require_once '_init.php';
//set_time_limit(4 * 60);
$prefix = $config->db->prefix;
$sites = $db->fetchAll("SELECT * FROM {$prefix}core_site");
$languages = $db->fetchAll("SELECT * FROM {$prefix}locale_language");

/* indexing products */
$indexPath = $root . '/var/index';
foreach ($sites as $site) {
    $siteName = str_replace(
        array('http://', 'https://'), '',
        urlencode($site['name'] . '_'. $site['id'])
    );
    $indexDir = $indexPath . '/' . $siteName;
    @removeDir($indexDir);
    /* create index dirs if not exists */
    if (!is_dir($indexDir)) {
        mkdir($indexDir, 0777);
    }
    foreach ($languages as $language) {

        $indexLocale = $indexDir . '/' . $language['locale'];
        if (!is_dir($indexLocale)) {
            mkdir($indexLocale, 0777);
        }

        $log = new Zend_Log(new Zend_Log_Writer_Stream(
            $root . '/var/logs/indexing.log'
        ));
        $log->info('Starting up');

        if (@preg_match('/\pL/u', 'a') != 1) {
            $log->err("PCRE unicode support is turned off.\n");
        }
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive()
        );

        try {
            $index = Zend_Search_Lucene::open($indexLocale);
            $log->info("Opened existing index in $indexLocale");
        } catch (Zend_Search_Lucene_Exception $e) {
            try {
                $index = Zend_Search_Lucene::create($indexLocale);
                $log->info("Created new index in $indexLocale");
            } catch(Zend_Search_Lucene_Exception $e) {
                $log->err("Failed opening or creating index in $indexLocale");
                $log->err($e->getMessage());
                echo "Unable to open or create index: {$e->getMessage()}";
                exit(1);
            }
        }

        // Indexing products
        $queryCount = "SELECT COUNT(*)
            FROM {$prefix}catalog_product p
            INNER JOIN {$prefix}catalog_product_category ptc ON p.id = ptc.product_id
            INNER JOIN {$prefix}catalog_category c ON c.id = ptc.category_id
            WHERE site_id = ?";
        $cnt = $db->fetchOne($queryCount, $site['id']);

        $query = "SELECT DISTINCT p.id, p.sku, pi.path AS image_thumbnail, pit.title AS image_title, pd.*, hu.key_word
            FROM {$prefix}catalog_product p
            INNER JOIN {$prefix}catalog_product_description pd ON p.id = pd.product_id
            LEFT JOIN {$prefix}catalog_product_image pi ON p.image_thumbnail = pi.id
            LEFT JOIN {$prefix}catalog_product_image_title pit ON pit.image_id = pi.id
            INNER JOIN {$prefix}catalog_product_category ptc ON p.id = ptc.product_id
            INNER JOIN {$prefix}catalog_category c ON c.id = ptc.category_id
            LEFT  JOIN {$prefix}catalog_hurl hu ON hu.key_id = p.id AND hu.key_type='p'
            WHERE pd.language_id = ? AND c.site_id = ?";

        $step = 30;
        $offset = 0;
        while ($offset < $cnt) {
            $products = $db->fetchAssoc(
                $query . " LIMIT $offset, $step",
                array($language['id'], $site['id'])
            );

            foreach ($products as $product) {
                /* INDEXING HERE */
                $index->addDocument(createDoc(
                    'product',
                    $product['name'],
                    $product['description'],
                    $product['key_word'],
                    $product['image_thumbnail'],
                    $product['image_title']
                ));

                $log->info('Added document ' . $product['key_word']);
            }

            $offset += $step;
        }

        // INDEXING CMS PAGES
        $query = "SELECT * FROM {$prefix}cms_page_content cpco
            INNER JOIN {$prefix}cms_page_category cpca ON cpca.cms_page_id = cpco.cms_page_id
            INNER JOIN {$prefix}cms_category cc ON cc.id = cpca.cms_category_id
            WHERE cpco.language_id = ? AND cc.site_id = ?";
        $cmsPages = $db->fetchAssoc(
            $query, array($language['id'], $site['id'])
        );
        foreach ($cmsPages as $page) {
            $index->addDocument(createDoc(
                'page',
                $page['title'],
                addBlocksContent($page['content']),
                $page['link']
            ));

            $log->info('Added document ' . $page['link']);
        }

        $log->info("Optimizing index...");
        $index->optimize();
        $index->commit();
        $log->info("Done. Index now contains " . $index->numDocs() . " documents");
    }
}
$log->info("Index Maker shutting down");

function addBlocksContent($content)
{
    //inserting blocks in content
    $matches = array();
    preg_match_all('/{{\w+}}/', $content, $matches);
    $i = 0;

    foreach ($matches[0] as $block) {
        $blockName = str_replace(array('{', '}'), '', $block);
        list($tagType, $tagKey) = explode('_', $blockName, 2);
        $blockContent = '';
        if  ('static' === $tagType) {
           $blockContent = Axis::single('cms/block')->getContentByName($tagKey);
        }

       $content = str_replace($block, $blockContent, $content);
    }
    return $content;
}

function createDoc($type, $name, $content, $url, $imagePath = null, $imageTitle = null)
{
    $doc = new Zend_Search_Lucene_Document();
    $doc->addField(Zend_Search_Lucene_Field::UnIndexed(
        'type', $type, 'utf-8'
    ));
    $doc->addField(Zend_Search_Lucene_Field::Text(
        'name', $name, 'utf-8'
    ));
    $doc->addField(Zend_Search_Lucene_Field::Text(
        'contents', $content, 'utf-8'
    ));
    $doc->addField(Zend_Search_Lucene_Field::UnIndexed(
        'url', $url, 'utf-8'
    ));
    if (null !== $imagePath || $type == 'product') {
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(
            'image', $imagePath, 'utf-8'
        ));
        $doc->addField(Zend_Search_Lucene_Field::UnIndexed(
            'image_title', $imageTitle, 'utf-8'
        ));
    }
    return $doc;
}

function removeDir($path) {
    $index_path = rtrim(
        str_replace(
            array('\\', 'scripts'),
            array('/', ''),
            dirname(realpath(__FILE__))
        ), '/') . '/var/index';

    /* check are we in 'ROOT/var/index' */
    if (false === strpos($path, $index_path))
        return false;

    if (is_dir($path)) {
        $path = rtrim($path, '/');
        $dir = dir($path);
        while (false !== ($file = $dir->read())) {
            if ($file != '.' && $file != '..') {
                (!is_link("$path/$file") && is_dir("$path/$file")) ?
                    RemoveDir("$path/$file") : unlink("$path/$file");
            }
        }
        $dir->close();
        rmdir($path);
        return true;
    }
    return false;
}