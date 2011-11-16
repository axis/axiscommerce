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
 * @package     Axis_Account
 * @copyright   Copyright 2008-2011 Axis
 * @license     GNU Public License V3.0
 */

class Axis_Account_Upgrade_0_2_5 extends Axis_Core_Model_Migration_Abstract
{
    protected $_version = '0.2.5';
    protected $_info = 'Foreign keys added to default billing and shipping addresses in account_customer table';

    public function up()
    {
        /**
         * we didn't have the foreign keys between account_customer_address and default addresses,
         * so before applying the upgrade, have to cleanup possible issues
         */
        $customerRowset = Axis::model('account/customer')
            ->select()
            ->where('default_billing_address_id IS NOT NULL')
            ->orWhere('default_shipping_address_id IS NOT NULL')
            ->fetchRowset();

        $addressIds = Axis::model('account/customer_address')
            ->select(array('id', 'customer_id'))
            ->fetchPairs();

        foreach ($customerRowset as $customerRow) {
            $save = false;
            if (!isset($addressIds[$customerRow->default_billing_address_id])) {
                $customerRow->default_billing_address_id = new Zend_Db_Expr('NULL');
                $save = true;
            }
            if (!isset($addressIds[$customerRow->default_shipping_address_id])) {
                $customerRow->default_shipping_address_id = new Zend_Db_Expr('NULL');
                $save = true;
            }
            if ($save) {
                $customerRow->save();
            }
        }

        // add foreign keys
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('account_customer')}`
            ADD CONSTRAINT `FK_ACCOUNT_CUSTOMER_SHIPPING_ADDRESS` FOREIGN KEY `FK_ACCOUNT_CUSTOMER_SHIPPING_ADDRESS` (`default_shipping_address_id`)
                REFERENCES `{$installer->getTable('account_customer_address')}` (`id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            ADD CONSTRAINT `FK_ACCOUNT_CUSTOMER_BILLING_ADDRESS` FOREIGN KEY `FK_ACCOUNT_CUSTOMER_BILLING_ADDRESS` (`default_billing_address_id`)
                REFERENCES `{$installer->getTable('account_customer_address')}` (`id`)
                ON DELETE CASCADE
                ON UPDATE CASCADE;

        ");
    }

    public function down()
    {
        $installer = $this->getInstaller();

        $installer->run("

        ALTER TABLE `{$installer->getTable('account_customer')}`
            DROP FOREIGN KEY `FK_ACCOUNT_CUSTOMER_SHIPPING_ADDRESS`,
            DROP FOREIGN KEY `FK_ACCOUNT_CUSTOMER_BILLING_ADDRESS`;

        ");
    }
}
