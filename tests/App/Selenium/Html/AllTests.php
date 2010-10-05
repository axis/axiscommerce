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
 * @package     Axis_Test
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

require_once dirname(__FILE__) . '../../../bootstrap.php';

 /*
  *  //pear channel-discover pear.phpunit.de
  *  //pear install phpunit/PHPUnit
  *  pear install Testing_Selenium-beta
  *
  *  http://release.seleniumhq.org/selenium-remote-control/1.0.1/selenium-remote-control-1.0.1-dist.zip
  *  unpack on $somedir
  *  cd $somedir/selenium-server-1.0.1/
  *  java -jar selenium-server.jar
  */
/**
 *
 * @category    Axis
 * @package     Axis_Test
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class App_Selenium_Html_AllTests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('App_Selenium_Html_AllTests');
        $suite->addTestSuite('App_Selenium_Html_CreateAccount');
        return $suite;
    }
}