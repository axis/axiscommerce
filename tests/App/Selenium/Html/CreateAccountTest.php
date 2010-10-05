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
require_once dirname(__FILE__) . '/../../../bootstrap.php';

/**
 *
 * @category    Axis
 * @package     Axis_Test
 * @author      Axis Core Team <core@axiscommerce.com>
 */
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

class App_Selenium_Html_CreateAccountTest extends PHPUnit_Extensions_SeleniumTestCase
{
    function setUp()
    {
        $this->setBrowser("*firefox");
        $this->setBrowserUrl("http://demo.axiscommerce.com/axis/");
    }

    function testMyTestCase()
    {
        $this->open("/axis/");
        $this->click("//img[@alt='Axiscommerce']");
//        $this->click("link=Sign In / Create Account");
//        $this->click("link=Create an Account");
//        $this->type("email", "test" . rand() . "@domain.com");
//        $this->type("password", "123654");
//        $this->type("password_confirm", "123654");
//        $this->type("firstname", "Jonh");
//        $this->type("lastname", "Doe");
//        $this->type("firstname", "John");
//        $this->type("field_nickname", "joe");
//        $this->click("submit");
//        try {
//            $this->assertTrue($this->isTextPresent("Hello John Doe"));
//        } catch (PHPUnit_Framework_AssertionFailedError $e) {
//            array_push($this->verificationErrors, $e->toString());
//        }
//        try {
//            $this->assertTrue($this->isTextPresent("Mail was sended successfully"));
//        } catch (PHPUnit_Framework_AssertionFailedError $e) {
//            array_push($this->verificationErrors, $e->toString());
//        }
//        try {
//            $this->assertTrue($this->isTextPresent("My Account"));
//        } catch (PHPUnit_Framework_AssertionFailedError $e) {
//            array_push($this->verificationErrors, $e->toString());
//        }
//        $this->click("link=Logout");
        $this->waitForPageToLoad("30000");
    }
}