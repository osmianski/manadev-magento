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

    public function __construct() {
        parent::__construct();
        $session = Mage::getSingleton('customer/session');
        $purchased = Mage::getResourceModel('downloadable/link_purchased_collection')
            ->addFieldToFilter('customer_id', $session->getCustomerId())
            ->addOrder('created_at', 'desc');
        $this->setPurchased($purchased);
        $purchasedIds = array();
        foreach ($purchased as $_item) {
            $purchasedIds[] = $_item->getId();
        }
        if (empty($purchasedIds)) {
            $purchasedIds = array(null);
        }

        $purchasedItems = Mage::getResourceModel('downloadable/link_purchased_item_collection')
            ->addFieldToFilter('purchased_id', array('in' => $purchasedIds))
            ->setOrder('item_id', 'desc')
            ->join(array('oi' => 'sales/order_item'), '`oi`.`item_id` = `main_table`.`order_item_id`', array())
            ->join(array('o' => 'sales/order'), '`oi`.`order_id` = `o`.`entity_id` AND `o`.`status` = "complete"', array());

        $purchasedItems->getSelect()->where("`main_table`.`status` NOT IN (?)", array(
            Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT,
            Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW)
        );
        $this->setItems($purchasedItems);
    }

    protected function _prepareLayout()
    {
        $this->getItems()->load();
        foreach ($this->getItems() as $item) {
            $item->setPurchased($this->getPurchased()->getItemById($item->getPurchasedId()));
        }
        return $this;
    }

    public function getStatusLabel($item) {
        /** @var Local_Manadev_Model_Download_Status $model */
        $model = Mage::getSingleton('local_manadev/download_status');
        return $model->getStatusLabel($item->getStatus(), $item);
    }

    public function getAvailableActions($item) {
        $actions = array();
        foreach($this->availableActions[$item->getStatus()] as $action) {
            switch($action) {
                case "download":
                    $actions[$action] = $this->getDownloadButton($item);
                    break;
                case "register":
                    $actions[$action] = $this->getRegisterButton($item);
                    break;
                case "open_support_ticket":
                    $actions[$action] = $this->getOpenSupportTicketButton($item);
                    break;
                case "prolong_support_period";
                    $actions[$action] = $this->getProlongSupportPeriodButton($item);
                    break;
            }
        }

        return $actions;
    }

    public function getDownloadButton($item) {
        /* @var Local_Manadev_Helper_Data $helper */
        $helper = Mage::helper('local_manadev');
        if (empty($labels = $helper->getProductBranchLabels($item->getProductId()))) {
            return null;
        }

        if (count($labels) == 1) {
            $title = Mage::helper('downloadable')->__('Start Download');
            $url = $this->getDownloadUrl($item);
            $target = '_blank';
            $text = 'Download';
            $css_class = 'btn-download';

            return compact('title', 'url', 'target', 'text', 'css_class');
        }

        $text = 'Download';
        $css_class = 'btn-download';
        $items = array();
        foreach ($labels as $branch => $label) {
            $items[] = array(
                'url' => $this->getBranchDownloadUrl($item, $branch),
                'text' => $label,
                'target' => '_blank',
            );
        }
        return compact('title', 'text', 'css_class', 'items');
    }

    public function getBranchDownloadUrl($item, $branch)
    {
        return $this->getUrl('*/download/link', array_merge(
            array(
                'id' => $item->getLinkHash(),
                '_secure' => true,
            ),
            $branch != 'master'
                ? array(
                    '_query' => array('branch' => $branch),
                )
                : array()
        ));
    }

    public function getRegisterButton($item) {
        $title = Mage::helper('downloadable')->__('Register And Download');
        $url = $this->getProductRegistrationUrl($item);
        $target = '';
        $text = $title;
        $css_class = 'btn-register';

        return compact('title', 'url', 'target', 'text', 'css_class');
    }

    public function getOpenSupportTicketButton($item) {
        $title = Mage::helper('downloadable')->__('I Need Support');
        $url = $this->getUrl('actions/support/openTicket', array('id' => $item->getLinkHash(), '_secure' => true));
        $target = '';
        $text = $title;
        $css_class = 'btn-product-support green';

        return compact('title', 'url', 'target', 'text', 'css_class');
    }

    public function getProlongSupportPeriodButton($item) {
        $title = Mage::helper('downloadable')->__('Extend Support Period');
        $url = $this->getUrl('actions/support/extend', array('id' => $item->getLinkHash(), '_secure' => true));
        $target = '';
        $text = $title;
        $css_class = 'btn-product-support';

        return compact('title', 'url', 'target', 'text', 'css_class');
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
            $title = Mage::helper('downloadable')->__('Modify URL');
            $url = $this->getUrl('actions/domain/modify', array('id' => $_item->getLinkHash()));
            $result .= "<ul class=\"add-to-links registration-buttons\"><li><a class='button btn-modify-url' href='{$url}' title='{$title}'><span><span>{$title}</span></span></a></li></ul>";

        }

        return $result;
    }

    public function getProductUrl($_item) {
        $additional = array();
        $additional['_escape'] = true;
        $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())->load($_item->getProductId());
        return $product->getUrlModel()->getUrl($product, $additional);
    }
}