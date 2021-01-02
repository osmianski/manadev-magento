<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Downloadable') . DS . 'DownloadController.php');
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Downloadable_DownloadController extends Mage_Downloadable_DownloadController
{
    public function linkAction(){
        $id = $this->getRequest()->getParam('id', 0);
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');
        if (! $linkPurchasedItem->getId() ) {
            $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__("Requested link does not exist."));
            return $this->_redirect('*/customer/products');
        }

        // MANAdev start
        /* @var Mage_Downloadable_Model_Link_Purchased_Item $linkPurchasedItem */

        $branch = Mage::app()->getRequest()->getParam('branch') ?: 'master';

        if ($filename = Mage::helper('local_manadev')->createNewZipFileWithLicense($linkPurchasedItem, $branch)) {
            $linkPurchasedItem->save();
        }
        // MANAdev end

        if (!Mage::helper('downloadable')->getIsShareable($linkPurchasedItem)) {
            $customerId = $this->_getCustomerSession()->getCustomerId();
            if (!$customerId) {
                $product = Mage::getModel('catalog/product')->load($linkPurchasedItem->getProductId());
                if ($product->getId()) {
                    $notice = Mage::helper('downloadable')->__('Please log in to download your product or purchase <a href="%s">%s</a>.', $product->getProductUrl(), $product->getName());
                } else {
                    $notice = Mage::helper('downloadable')->__('Please log in to download your product.');
                }
                $this->_getCustomerSession()->addNotice($notice);
                $this->_getCustomerSession()->authenticate($this);
                $this->_getCustomerSession()->setBeforeAuthUrl(Mage::getUrl('downloadable/customer/products/'),
                    array('_secure' => true)
                );
                return ;
            }
            $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());
            if ($linkPurchased->getCustomerId() != $customerId) {
                $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__("Requested link does not exist."));
                return $this->_redirect('*/customer/products');
            }
        }
        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought()
            - $linkPurchasedItem->getNumberOfDownloadsUsed();

        $status = $linkPurchasedItem->getStatus();

        // MANAdev start

        Mage::register("m_link_purchased_item", $linkPurchasedItem);
        $availableStatuses = array(
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL,
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE,
            Local_Manadev_Model_Download_Status::M_LINK_STATUS_PERIOD_EXPIRED
        );
        if (in_array($status, $availableStatuses)
            && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
        ) {
            if ($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
                // we don't sell URLs, we only sell files
                $this->_getCustomerSession()->addNotice(
                    Mage::helper('downloadable')->__(
                        "Downloadable URL is not available."));
                return $this->_redirect('*/customer/products');
            }

            $resource = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Link::getBasePath(),
                $filename
            );
            $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_FILE;
        // MANAdev end

            try {
                $this->_processDownload($resource, $resourceType);
                $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);

                if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1)) {
                    $linkPurchasedItem->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED);
                }
                $linkPurchasedItem->save();
                exit(0);
            }
            catch (Exception $e) {
                $this->_getCustomerSession()->addError(
                    Mage::helper('downloadable')->__('An error occurred while getting the requested content. Please contact the store owner.')
                );
            }
        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED) {
            $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__('The link has expired.'));
        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING
            || $status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW
        ) {
            $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__('The link is not available.'));
        } else {
            $this->_getCustomerSession()->addError(
                Mage::helper('downloadable')->__('An error occurred while getting the requested content. Please contact the store owner.')
            );
        }
        return $this->_redirect('*/customer/products');
    }

    protected function _processDownload($resource, $resourceType) {
        $helper = Mage::helper('downloadable/download');
        /* @var $helper Mage_Downloadable_Helper_Download */

        $helper->setResource($resource, $resourceType);

//        $fileName       = $helper->getFilename();
        /** @var Local_Manadev_Model_Downloadable_Item $linkPurchasedItem */
        $linkPurchasedItem = Mage::registry("m_link_purchased_item");
        $fileName = str_replace("-".$linkPurchasedItem->getData('m_license_verification_no'), "", $helper->getFilename());
        $contentType    = $helper->getContentType();

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $helper->getFilesize()) {
            $this->getResponse()
                ->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                ->setHeader('Content-Disposition', $contentDisposition . '; filename='.$fileName);
        }

        $this->getResponse()
            ->clearBody();
        $this->getResponse()
            ->sendHeaders();

        session_write_close();
        $helper->output();
    }
}