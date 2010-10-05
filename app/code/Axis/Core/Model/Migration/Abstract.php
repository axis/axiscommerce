<?php

abstract class Axis_Core_Model_Migration_Abstract
{
    /**
     * @var Axis_Install_Model_Installer
     */
    protected $_installer;
    protected $_info = '';
    protected $_version;

    public function  __construct()
    {
        $this->_installer = Axis::single('install/installer');
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function getInfo()
    {
        return $this->_info;
    }

    abstract public function up();

    abstract public function down();

}