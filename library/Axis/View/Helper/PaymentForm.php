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
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @copyright   Copyright 2008-2010 Axis
 * @license     GNU Public License V3.0
 */

/**
 *
 * @category    Axis
 * @package     Axis_View
 * @subpackage  Axis_View_Helper
 * @author      Axis Core Team <core@axiscommerce.com>
 */
class Axis_View_Helper_PaymentForm
{
    /**
     * Return additional paymet form
     * @return string
     * @param object|string $payment
     * @param string $template example: 'view', 'form', 'process' etc
     * @param string $app[optional] 'front' | 'admin'
     */
    public function paymentForm($paymentCode, $template, $area = null)
    {
        if ($paymentCode instanceof Axis_Method_Payment_Model_Abstract) {
            $this->view->payment = $paymentCode;
            $paymentCode = $this->view->payment->getCode();
        } else {
            $this->view->payment = Axis_Payment::getMethod($paymentCode);
        }

        if (null === $area) {
            $area = $this->view->area;
        }

        $templatePath = $this->view->path . '/app/design/' . $area . '/'
                      . $this->view->templateName . '/templates';

        $shortPath = 'payment' . str_replace('_', '/', strtolower($paymentCode))
                   . '/' . $template . '.phtml';
       
        if (is_readable($templatePath . '/' . $shortPath)) {
            return $this->view->render($shortPath);
        }

        return '';
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}