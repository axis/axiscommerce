<?php

require_once '_init.php';

try {
    $opts = new Zend_Console_Getopt(array(
        'install|i-w' => 'Install module',
        'up|u-w' => 'Update module',
        'down|d-w' => 'Downgrade module',
        'remove|r-w' => 'Remove module',
        'create|c-w' => 'Create module',
        'version|v=w' => 'Version',
    ));
    $opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    exit;
}


/**
 * @var Axis_Core_Model_Module module object
 */
$moduleModel = Axis::model('core/module');

if (isset($opts->install)) {
    $install = $opts->getOption('install');

    $module = $moduleModel->getByCode('Axis_Core');
    $module->install();

    $module = $moduleModel->getByCode('Axis_Locale');
    $module->install();

    $modulesList = $moduleModel->getListFromFilesystem('Axis');
    foreach ($modulesList as $code) {
        $module = $moduleModel->getByCode($code);
        $module->install();
    }
}

if (isset($opts->remove)) {
    $remove = $opts->getOption('remove');
}

if (isset($opts->up)) {
    $up = $opts->getOption('up');
    $module = $moduleModel->getByCode($up);
    $module->upgradeAll();
}

if (isset($opts->down)) {
    $down = $opts->getOption('down');
    $module = $moduleModel->getByCode($down);
    $upgradeRow = $module->getLastUpgrade();
    $upgrade = $module->getUpgradeObject($upgradeRow->version);
    $module->downgrade($upgrade);
}

if (isset($opts->create)) {
    $create = $opts->getOption('create');
}