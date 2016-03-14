<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Customer_Products_List extends Mage_Downloadable_Block_Customer_Products_List
{
    // Magento 2 Products
    // not_registered -> available_til -> expired -> available_til (optional)
    //
    // Magento 1.x Products
    // not_registered -> available_til -> period_expired

    protected $availableActions = array(
        Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE => array('download', 'open_support_ticket'),
        Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE => array(),
        Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL => array('download', 'open_support_ticket'),
        Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED => array('download', 'prolong_support_period'),
        Local_Manadev_Model_Download_Status::M_LINK_STATUS_DOWNLOAD_EXPIRED => array('prolong_support_period'),
        Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED => array('register'),
    );

    protected $actionTemplates = array(
        'register' => 'Register',
        'download' => 'Download',
        'open_support_ticket' => 'Open Support Ticket',
        'prolong_support_period' => 'Extend Support Period',
    );

    public function getStatusLabel($item) {
        return Local_Manadev_Model_Download_Status::getStatusLabel($item->getStatus(), $item);
    }

    public function getAvailableActions($item) {
        $actions = '';
        foreach($this->availableActions[$item->getStatus()] as $action) {
            $actions .= " <br>";
            switch($action) {
                case "download":
                    $actions .= $this->getDownloadButton($item);
                    break;
                case "register":
                    $actions .= $this->getRegisterButton($item);
                    break;
                default:
                    $actions .= "<a href='#'>{$this->actionTemplates[$action]}</a>";
            }
        }

        return $actions;
    }

    public function getDownloadButton($item) {
        $title = Mage::helper('downloadable')->__('Start Download');
        $url = $this->getDownloadUrl($item);
        $template = "<a href='{$url}' title='{$title}' {$this->_openNewWindow()}>{$item->getLinkTitle()}</a>";

        return $template;
    }

    public function getRegisterButton($item) {
        $title = Mage::helper('downloadable')->__('Register And Download');
        $url = $this->getProductRegistrationUrl($item);
        $template = "<a href='{$url}' title='{$title}' {$this->_openNewWindow()}>{$title}</a>";

        return $template;
    }

    /**
     * @return string
     */
    protected function _openNewWindow() {
        $openNewWindow = $this->getIsOpenInNewWindow() ? 'onclick="this.target=\'_blank\'"' : '';

        return $openNewWindow;
    }

    public function getProductRegistrationUrl($item) {
        return $this->getUrl('actions/domain/register', array('id' => $item->getLinkHash(), '_secure' => true));
    }

    public function getRegisteredDomain($_item) {
        return implode("<br/>", explode(",", $_item->getData('m_registered_domain')));
    }
}