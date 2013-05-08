<?php
/** 
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Core_Test_Setup  {
    protected $_module;
    protected $_definedVersion;
    protected $_installedVersion;
    protected $_files = array();

    public function __construct($module, $definedVersion, $installedVersion) {
        $this->_module = $module;
        $this->_definedVersion = $definedVersion;
        $this->_installedVersion = $installedVersion;
    }
    public function install() {
        /* @var $fileHelper Mana_Core_Helper_Files */
        $fileHelper = Mage::helper('mana_core/files');
        $fileHelper->walkRecursively(Mage::getBaseDir() . '/tests/data/' . str_replace('_', '/', $this->_module),
            array($this, '_addInstallFile'));
        uksort($this->_files, 'version_compare');

        foreach ($this->_files as $filename) {
            /** @noinspection PhpIncludeInspection */
            include $filename;
        }
    }

    public function _addInstallFile($filename, $isDir) {
        if (!$isDir) {
            $version = pathinfo($filename, PATHINFO_FILENAME);
            if ((!$this->_installedVersion || version_compare($version, $this->_installedVersion) > 0) &&
                version_compare($version, $this->_definedVersion) <= 0)
            {
                $this->_files[$version] = $filename;
            }
        }

        return true;
    }
}