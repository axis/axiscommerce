<?php

function get_content($filename)
{
    $content = file_get_contents($filename);
    $content = str_replace('<?php', '', $content);
    $content = preg_replace('/\/\*\*.+\*\//Us', '', $content);
    $content = str_replace('$installer->startSetup();', '', $content);
    $content = str_replace('$installer->endSetup();', '', $content);
    $content = preg_replace('/\$installer->registerModule\(.+\)\;/', '', $content);
    $content = preg_replace('/\$installer->unregisterModule\(.+\)\;/', '', $content);
    return $content;
}

$tpl = <<<HEREDOC
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


class {code}_Upgrade_{upgrade_name} extends Axis_Core_Model_Migration_Abstract
{
    protected \$_version = '{version}';
    protected \$_info = 'install';

    public function up()
    {
{install_content}
    }

    public function down()
    {
{uninstall_content}
    }
}
HEREDOC;

$path = "../app/code/Axis";
$modules = glob($path . "/*");
foreach ($modules as $module_path) {
    $install = "{$module_path}/sql/install.php";
    if (!file_exists($install)) {
        continue;
    }
    require_once "{$module_path}/etc/config.php";

    $code = key($config);
    $config = current($config);

    $vars = array(
        'code' => $code,
        'upgrade_name' => str_replace('.', '_', $config['version']),
        'version' => $config['version'],
        'install_content' => get_content($install),
        'uninstall_content' => ''
    );

    $uninstall = "{$module_path}/sql/uninstall.php";
    if (file_exists($uninstall)) {
        $vars['uninstall_content'] = get_content($uninstall);
    }

    $content = $tpl;
    foreach ($vars as $key => $value) {
        $content = str_replace('{' . $key . '}', $value, $content);
    }

    $upgrade_file = "{$module_path}/sql/{$config['version']}.php";
    file_put_contents($upgrade_file, $content);
}