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
 * @package     Axis_Install
 * @subpackage  Axis_Install_Model
 * @copyright   Copyright 2008-2012 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_Install
 * @subpackage  Axis_Install_Model
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_Install_Model_Installer
{
    /**
     * Execute multiqueries
     *
     * @param string $sql
     * @return Axis_Install_Model_Installer provides fluent interface
     * @throws Exception
     */
    public function run($sql)
    {
        $tries = 0;
        $stmts = $this->_splitMultiQuery($sql);
        foreach ($stmts as $stmt) {
            do {
                $retry = false;
                try {
                    // skip commented queries
                    if (0 === strpos($stmt, '--') || 0 === strpos($stmt, '/*')) {
                        continue;
                    }
                    Axis::db()->getConnection()->exec($stmt);
                } catch (Exception $e) {
                    if ($e->getMessage() == 'SQLSTATE[HY000]: General error: 2013 Lost connection to MySQL server during query'
                        && $tries < 10) {

                        $retry = true;
                        $tries++;
                    } else {
//                        Axis::message()->addError($e->getTraceAsString());
                        Axis::message()->addError($e->getMessage());
                        throw $e;
                    }
                }
            } while ($retry);
        }

        return $this;
    }

    /**
     * Split multi statement query
     *
     * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
     * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     * @param $sql string
     * @return array
     */
    private function _splitMultiQuery($sql)
    {
        $parts = preg_split(
            '#(;|\'|"|\\\\|//|--|\n|/\*|\*/)#',
            $sql,
            null,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        $q = false;
        $c = false;
        $stmts = array();
        $s = '';

        foreach ($parts as $i=>$part) {
            // strings
            if (($part==="'" || $part==='"') && ($i===0 || $parts[$i-1]!=='\\')) {
                if ($q===false) {
                    $q = $part;
                } elseif ($q===$part) {
                    $q = false;
                }
            }

            // single line comments
            if (($part==='//' || $part==='--') && ($i===0 || $parts[$i-1]==="\n")) {
                $c = $part;
            } elseif ($part==="\n" && ($c==='//' || $c==='--')) {
                $c = false;
            }

            // multi line comments
            if ($part==='/*' && $c===false) {
                $c = '/*';
            } elseif ($part==='*/' && $c==='/*') {
                $c = false;
            }

            // statements
            if ($part===';' && $q===false && $c===false) {
                if (trim($s)!=='') {
                    $stmts[] = trim($s);
                    $s = '';
                }
            } else {
                $s .= $part;
            }
        }
        if (trim($s)!=='') {
            $stmts[] = trim($s);
        }

        return $stmts;
    }

    /**
     * Retrieve table name with table prefix
     *
     * @param string $table
     * @return string
     */
    public function getTable($table)
    {
        return Axis::config()->db->prefix . $table;
    }

    /**
     * Disable foreign keys check while install is running
     *
     * @return Axis_Install_Model_Installer provides fluent interface
     */
    public function startSetup()
    {
        $this->run("
            SET SQL_MODE='';
            SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
            SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
        ");

        return $this;
    }

    /**
     * Revert changed mysql options to previous
     *
     * @return Axis_Install_Model_Installer provides fluent interface
     */
    public function endSetup()
    {
        $this->run("
            SET SQL_MODE=IFNULL(@OLD_SQL_MODE,'');
            SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS,0);
        ");

        return $this;
    }

}