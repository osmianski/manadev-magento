<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Lock extends Mage_Core_Helper_Abstract {
	protected $_locks = array(); 
	protected $_isLocked = array();
    /**
     * Get lock file resource
     *
     * @return resource
     */
    protected function _getLockFile($name)
    {
        if (!isset($this->_locks[$name])) {
            $varDir = Mage::getConfig()->getVarDir('locks');
            $file = $varDir . DS . $name.'.lock';
            if (is_file($file)) {
                $this->_locks[$name] = fopen($file, 'w');
            } else {
                $this->_locks[$name] = fopen($file, 'x');
            }
            fwrite($this->_locks[$name], date('r'));
        }
        return $this->_locks[$name];
    }

    /**
     * Lock process without blocking.
     * This method allow protect multiple process runing and fast lock validation.
     *
     * @return Mage_Index_Model_Process
     */
    public function lock($name)
    {
        $this->_isLocked[$name] = true;
        flock($this->_getLockFile($name), LOCK_EX | LOCK_NB);
        return $this;
    }

    /**
     * Lock and block process.
     * If new instance of the process will try validate locking state
     * script will wait until process will be unlocked
     *
     * @return Mage_Index_Model_Process
     */
    public function lockAndBlock($name)
    {
        $this->_isLocked[$name] = true;
        flock($this->_getLockFile($name), LOCK_EX);
        return $this;
    }

    /**
     * Unlock process
     *
     * @return Mage_Index_Model_Process
     */
    public function unlock($name)
    {
        unset($this->_isLocked[$name]);
        flock($this->_getLockFile($name), LOCK_UN);
        fclose($this->_getLockFile($name));
        unset($this->_locks[$name]);
        return $this;
    }

    /**
     * Check if process is locked
     *
     * @return bool
     */
    public function isLocked($name)
    {
        if (isset($this->_isLocked[$name])) {
            return $this->_isLocked[$name];
        } else {
            $fp = $this->_getLockFile($name);
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                flock($fp, LOCK_UN);
                return false;
            }
            return true;
        }
    }
    public function __destruct()
    {
    	foreach ($this->_locks as $fh) {
    		fclose($fh);
    	}
    }
}