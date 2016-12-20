<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Social_Block_Facebook_Meta extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('mana/social/facebook/meta.phtml');
    }

    protected function _beforeToHtml()
    {
        if ($_product = Mage::registry('current_product')) {
            $canEmail = Mage::registry('send_to_friend_model');
            $canEmail = $canEmail && $canEmail->canEmailToFriend();
            $url = Mage::getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true, '_nosid' => true, '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()));
            $_images = $_product->getMediaGalleryImages();
            $mediaUrl = '';
            if (count($_images)) {
              foreach ($_images as $_image) {
                $mediaUrl = Mage::getBaseUrl('media').'catalog/product'.str_replace(DS, '/', $_image->getFile());
                break;
              }
            }
            else {
              $mediaUrl = '';
            }

            $this
                ->setIsRelevant(true)
                ->setTitle($_product->getName())
                ->setType('website')
                ->setFbUrl($url)
                ->setSiteName(Mage::getStoreConfig('general/store_information/name'))
                ->setDescription($_product->getMetaDescription())
                ->setMediaUrl($mediaUrl);
        }
        else {
            $this->setIsRelevant(false);
        }
        return parent::_beforeToHtml();
    }
}