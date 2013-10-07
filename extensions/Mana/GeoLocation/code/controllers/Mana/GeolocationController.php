<?php
/**
 * @category    Mana
 * @package     Mana_GeoLocation
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_GeoLocation_Mana_GeolocationController extends Mage_Adminhtml_Controller_Action {
    public function indexAction() {
        // page
        $this->_title('Mana')->_title($this->__('Geo Location'));

        // layout
        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();
        $this->generateLayoutXml()->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;
        $this->_initLayoutMessages('adminhtml/session');

        // rendering
        $this->_setActiveMenu('mana/geolocation');
        $this->renderLayout();
    }

    public function importDomainAction() {
        if ($this->getRequest()->isPost() && !empty($_FILES['file']['tmp_name'])) {
            try {
               $this->_importDomain($_FILES['file']['tmp_name']);


                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mana_geolocation')->__('Domains successfully imported.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid file upload attempt'));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/');
    }

    protected function _importDomain($filename) {
        /* @var $resource Mana_GeoLocation_Resource_Domain */
        $resource = Mage::getResourceModel('mana_geolocation/domain');
        $resource->beginTransaction();
        $resource->truncate();
        $sql = "INSERT INTO `{$resource->getMainTable()}` (`domain`, `country_id`) \n";
        $sqlEmpty = true;
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $fh = fopen($filename, 'r');
        $lineNo = 0;
        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($core->startsWith($line, '<!DOCTYPE html PUBLIC')) {
                fclose($fh);
                unlink($filename);
                throw new Mage_Core_Exception(mage::helper('mana_geolocation')->__('Too many downloads. Try again later.'));
            }
            if ($line && !$core->startsWith($line, '#')) {
                $data = str_getcsv($line);
                if ($sqlEmpty) {
                    $sqlEmpty = false;
                    $sql .= "VALUES ";
                } else {
                    $sql .= ", ";
                }
                $sql .= "('{$data[1]}','{$data[2]}')\n";
            }
        }
        $resource->run($sql);
        fclose($fh);
        unlink($filename);
        $resource->commit();
    }

    public function importV4Action() {
        if ($this->getRequest()->isPost()) {
            try {
                if (!empty($_FILES['file']['tmp_name'])) {
                    $this->_importV4($_FILES['file']['tmp_name']);
                }
                else {
                    $filename = Mage::getBaseDir('var').'/m_geolocation.csv';
                    if (file_exists($filename)) {
                        unlink($filename);
                    }
                    $this->_get('http://software77.net/geo-ip/?DL=1', $filename);
                    $this->_importV4($filename);
                }


                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mana_geolocation')->__('IP Addresses successfully imported.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid file upload attempt'));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/');
    }

    protected function _importV4($filename) {
        /* @var $resource Mana_GeoLocation_Resource_Ip4 */
        $resource = Mage::getResourceModel('mana_geolocation/ip4');
        $resource->beginTransaction();
        $resource->truncate();
        $sql = "INSERT INTO `{$resource->getMainTable()}` (`ip_from`, `ip_to`, `registry`, `date_assigned`, `country_id`) \n";
        $sqlEmpty = true;
        /* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
        $fh = fopen($filename, 'r');
        $lineNo = 0;
        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($core->startsWith($line, '<!DOCTYPE html PUBLIC')) {
                fclose($fh);
                unlink($filename);
                throw new Mage_Core_Exception(mage::helper('mana_geolocation')->__('Too many downloads. Try again later.'));
            }
            if ($line && !$core->startsWith($line, '#')) {
                $data = str_getcsv($line);
                if ($sqlEmpty) {
                    $sqlEmpty = false;
                    $sql .= "VALUES ";
                }
                else {
                    $sql .= ", ";
                }
                $sql .= "({$data[0]},{$data[1]},'{$data[2]}',{$data[3]},'{$data[4]}')\n";
            }
        }
        $resource->run($sql);
        fclose($fh);
        unlink($filename);
        $resource->commit();
    }

    public function importV6Action() {
        if ($this->getRequest()->isPost()) {
            try {
                if (!empty($_FILES['file']['tmp_name'])) {
                    $this->_importV4($_FILES['file']['tmp_name']);
                } else {
                    $filename = Mage::getBaseDir('var') . '/m_geolocation.csv';
                    if (file_exists($filename)) {
                        unlink($filename);
                    }
                    $this->_get('http://software77.net/geo-ip/?DL=7', $filename);
                    $this->_importV6($filename);
                }


                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mana_geolocation')->__('IP Addresses successfully imported.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid file upload attempt'));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/');
    }

    protected function _importV6($filename) {
        /* @var $resource Mana_GeoLocation_Resource_Ip6 */
        $resource = Mage::getResourceModel('mana_geolocation/ip6');
        $resource->beginTransaction();
        $resource->truncate();
        $sql = "INSERT INTO `{$resource->getMainTable()}` (`ip_from`, `ip_to`, `registry`, `date_assigned`, `country_id`) \n";
        $sqlEmpty = true;
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $fh = fopen($filename, 'r');
        $lineNo = 0;
        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($core->startsWith($line, '<!DOCTYPE html PUBLIC')) {
                fclose($fh);
                unlink($filename);
                throw new Mage_Core_Exception(mage::helper('mana_geolocation')->__('Too many downloads. Try again later.'));
            }
            if ($line && !$core->startsWith($line, '#')) {
                $data = str_getcsv($line);
                if ($sqlEmpty) {
                    $sqlEmpty = false;
                    $sql .= "VALUES ";
                } else {
                    $sql .= ", ";
                }
                list($from, $to) = explode('-', $data[0]);
                $from = $this->_expandV6Adddress($from);
                $to = $this->_expandV6Adddress($to);
                $sql .= "('{$from}','{$to}','{$data[2]}',{$data[3]},'{$data[1]}')\n";
            }
        }
        $resource->run($sql);
        fclose($fh);
        unlink($filename);
        $resource->commit();
    }

    protected function _get($url, $filename) {
        $gzfh = gzopen($url, 'rb');
        $fh = fopen($filename, 'wb');
        stream_copy_to_stream($gzfh, $fh);
        gzclose($gzfh);
        fclose($fh);
    }

    protected function _expandV6Adddress($ip) {
        $result = array();
        $ip = explode(':', $ip);
        for ($i = 0; $i < 8; $i++) {
            $result[] = isset($ip[$i]) ? str_pad($ip[$i], 4, '0', STR_PAD_LEFT): '0000';
        }
        return implode('', $result);
    }

    public function searchAction() {
        if ($this->getRequest()->isPost() && ($search = $this->getRequest()->getParam('search'))) {
            try {
                if (($countryId = Mage::helper('mana_geolocation')->find($search)) && $countryId != 'ZZ') {
                    $country = Mage::getModel('directory/country')->loadByCode($countryId);
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mana_geolocation')->__('Country of %s is %s', $search, $country->getName()));
                }
                else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Country of %s not found', $search));
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid search attempt'));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mana_geolocation')->__('Invalid search attempt'));
        }
        $this->_redirect('*/*/');
    }
}