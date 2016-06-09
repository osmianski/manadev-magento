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
        /** @var Local_Manadev_Model_Download_Status $model */
        $model = Mage::getSingleton('local_manadev/download_status');
        return $model->getStatusLabel($item->getStatus(), $item);
    }

    public function getAvailableActions($item) {
        $actions = '';
        foreach($this->availableActions[$item->getStatus()] as $action) {
            switch($action) {
                case "download":
                    $actions .= $this->getDownloadButton($item);
                    break;
                case "register":
                    $actions .= $this->getRegisterButton($item);
                    break;
                case "open_support_ticket":
                    $actions .= $this->getOpenSupportTicketButton($item);
                    break;
                case "prolong_support_period";
                    $actions .= $this->getProlongSupportPeriodButton($item);
                    break;
            }
        }

        return $actions;
    }

    public function getDownloadButton($item) {
        $title = Mage::helper('downloadable')->__('Start Download');
        $url = $this->getDownloadUrl($item);
        $template = "<a class='button' href='{$url}' title='{$title}' {$this->_openNewWindow()}><span><span>Download</span></span></a>";

        return $template;
    }

    public function getRegisterButton($item) {
        $title = Mage::helper('downloadable')->__('Register And Download');
        $url = $this->getProductRegistrationUrl($item);
        $template = "<a class='button' href='{$url}' title='{$title}'><span><span>{$title}</span></span></a>";

        return $template;
    }

    public function getOpenSupportTicketButton($item) {
        $title = Mage::helper('downloadable')->__('Open Support Ticket');
        $url = $this->getUrl('actions/support/openTicket', array('id' => $item->getLinkHash(), '_secure' => true));
        $template = "<a class='button' href='{$url}' title='{$title}'><span><span>{$title}</span></span></a>";

        return $template;
    }

    public function getProlongSupportPeriodButton($item) {
        $title = Mage::helper('downloadable')->__('Extend Support Period');
        $url = $this->getUrl('actions/support/extend', array('id' => $item->getLinkHash(), '_secure' => true));
        $template = "<a class='button' href='{$url}' title='{$title}'><span><span>{$title}</span></span></a>";

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
        if($domain = $_item->getData('m_registered_domain')) {
            $result = $domain;
        } else {
            $storeInfo = $_item->getData('m_store_info');
            if(strlen($storeInfo) < 256) {
                $result = $storeInfo;
            } else {
                $result = substr($storeInfo, 0, 255);
            }
        }
        $result = htmlentities($result);
        if(!in_array($_item->getStatus(),
            array(Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_AVAILABLE, Local_Manadev_Model_Download_Status::M_LINK_STATUS_NOT_REGISTERED))
        ) {
            $result .= "<br/>";
            $title = Mage::helper('downloadable')->__('Modify');
            $url = $this->getUrl('actions/domain/modify', array('id' => $_item->getLinkHash()));
            $result .= "<a class='button' href='{$url}' title='{$title}'><span><span>{$title}</span></span></a>";

        }

        return $result;
    }
}