<?php

include_once 'app/code/core/Mage/Contacts/controllers/IndexController.php';

class Local_Manadev_Contacts_IndexController extends Mage_Contacts_IndexController
{
    public function indexAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('contactForm')
            ->setFormAction(Mage::getUrl('*/*/post', array('_secure' => $this->getRequest()->isSecure())));

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');

        if (Mage::getStoreConfig('web/default/show_cms_breadcrumbs')
            && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))
        ) {
            $breadcrumbs->addCrumb('home', array(
                'label' => Mage::helper('cms')->__('Home'),
                'title' => Mage::helper('cms')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ));
            $breadcrumbs->addCrumb('presale', array(
                'label' => Mage::helper('cms')->__('Presale Questions'),
                'title' => Mage::helper('cms')->__('Presale Questions')
            ));
        }

        $this->renderLayout();
    }
}