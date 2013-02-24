<?php
/** 
 * @category    Mana
 * @package     ManaPro_Guestbook
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Guestbook_BookController  extends Mage_Core_Controller_Front_Action {
    const XML_PATH_EMAIL_RECIPIENT  = 'contacts/email/recipient_email';
    const XML_PATH_EMAIL_SENDER     = 'contacts/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE   = 'contacts/email/email_template';

    public function indexAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('form')
            ->setFormAction( Mage::getUrl('*/*/post') );
        $this->_title($this->__('Guest Book'));
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
        $this->renderLayout();
    }
    public function postAction() {
        $post = $this->getRequest()->getPost();
        if ( $post ) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (Mage::getStoreConfigFlag('manapro_guestbook/name/is_required')) {
                    if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                        $error = true;
                    }
                }
                if (Mage::getStoreConfigFlag('manapro_guestbook/text/is_required')) {
                    if (!Zend_Validate::is(trim($post['text']) , 'NotEmpty')) {
                        $error = true;
                    }
                }

                if (Mage::getStoreConfigFlag('manapro_guestbook/email/is_required')) {
                    if (!Zend_Validate::is(trim($post['email']) , 'NotEmpty')) {
                        $error = true;
                    }
                }

                if (Mage::getStoreConfigFlag('manapro_guestbook/url/is_required')) {
                    if (!Zend_Validate::is(trim($post['url']) , 'NotEmpty')) {
                        $error = true;
                    }
                }

                if (Mage::getStoreConfigFlag('manapro_guestbook/region/is_required')) {
                    if (Mage::getStoreConfigFlag('manapro_guestbook/region/is_freeform')) {
                        if (!Zend_Validate::is(trim($post['region']) , 'NotEmpty')) {
                            $error = true;
                        }
                    }
                    else {
                        if (empty($post['region_id'])) {
                            $error = true;
                        }
                    }
                }

                //if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                //    $error = true;
                //}

                if ($error) {
                    throw new Exception();
                }

                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

                //if (!$mailTemplate->getSentSuccess()) {
                //    throw new Exception();
                //}

                $post = Mage::getModel('manapro_guestbook/post')->addData($post);
                if (!Mage::getStoreConfigFlag('manapro_guestbook/region/is_freeform')) {
                    $post->setRegion(Mage::getModel('directory/region')->load($post->getRegionId())->getName());
                }
                $post
                    ->setCreatedAt(strftime('%Y-%m-%d', time()))
                    //->setText(str_replace("\n", '<br />', $post->getText()))
                    ->setStatus(Mage::getStoreConfigFlag('manapro_guestbook/general/is_moderated')
                    ? ManaPro_Guestbook_Model_Post_Status::PENDING
                    : ManaPro_Guestbook_Model_Post_Status::APPROVED);

                $post->save();
                
                $translate->setTranslateInline(true);

                if (Mage::getStoreConfigFlag('manapro_guestbook/general/is_moderated')) {
                    Mage::getSingleton('customer/session')->addSuccess($this->__('Your post has been submitted for moderation. Thank you for your opinion.'));
                }
                else {
                    Mage::getSingleton('customer/session')->addSuccess($this->__('Your post has been submitted. Thank you for your opinion.'));
                }

                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                Mage::logException($e);
                $translate->setTranslateInline(true);

                Mage::getSingleton('customer/session')->addError($this->__('Unable to submit your post. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
            $this->_redirect('*/*/');
        }
    }
}