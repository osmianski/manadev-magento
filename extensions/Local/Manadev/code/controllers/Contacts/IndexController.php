<?php

include_once 'app/code/core/Mage/Contacts/controllers/IndexController.php';

class Local_Manadev_Contacts_IndexController extends Mage_Contacts_IndexController
{
    public function indexAction() {
        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }

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

    public function postAction() {
        // spam protection
        if (!$this->_validateFormKey()) {
            $this->_redirectReferer();
            return;
        }

        parent::postAction();
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}