<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
abstract class Mana_Content_Model_Page_Abstract extends Mage_Core_Model_Abstract {
    const DM_IS_ACTIVE = 0;
    const DM_URL_KEY = 1;
    const DM_TITLE = 2;
    const DM_CONTENT = 3;
    const DM_PAGE_LAYOUT = 4;
    const DM_LAYOUT_XML = 5;
    const DM_CUSTOM_DESIGN_ACTIVE_FROM = 6;
    const DM_CUSTOM_DESIGN_ACTIVE_TO = 7;
    const DM_CUSTOM_DESIGN = 8;
    const DM_CUSTOM_LAYOUT_XML = 9;
    const DM_META_TITLE = 10;
    const DM_META_KEYWORDS = 11;
    const DM_META_DESCRIPTION = 12;
    const DM_POSITION = 13;
    const DM_LEVEL = 14;
    const DM_TAGS = 15;

    protected $rules = array(
        'title' => 'required',
        'url_key' => 'required',
        'content' => 'required',
    );
    protected $captions = array(
        'url_key' => "URL Key",
    );

    protected $_validator;

    public function validate() {
        $global = Mage::registry('m_global_flat_model');

        $validator = $this->getValidator();

        if(!$validator->passes()) {
            throw new Mana_Core_Exception_Validation($validator->getMessages());
        }
        if(Mage::app()->getRequest()->getParam('rootPageId') == $this->getData('page_global_id') || Mage::app()->getRequest()->getParam('rootPageId') === "false") {
            $this->_validateUrlKey();
        }
    }

    public function setDefaults() {
        $this->getResource()->setDefaults($this);

        return $this;
    }

    public function getCustomSettingId($globalId) {
        $model = Mage::getModel('mana_content/page_global');
        $model->load($globalId);
        return $model->getData('page_global_custom_settings_id');
    }

    public function getGlobalId($customSettingsId) {
        $model = Mage::getModel('mana_content/page_global');
        $model->load($customSettingsId, 'page_global_custom_settings_id');

        return $model->getData('id');
    }

    /**
     * Retrieve model resource
     *
     * @return Mana_Content_Resource_Page_Abstract
     */
    public function getResource() {
        return parent::getResource();
    }


    public function afterCommitCallback() {
        parent::afterCommitCallback();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, static::ENTITY,
                Mage_Index_Model_Event::TYPE_SAVE);
        }
        return $this;
    }

    protected function _afterDeleteCommit() {
        parent::_afterDeleteCommit();
        if (!Mage::registry('m_prevent_indexing_on_save')) {
            $this->getIndexerSingleton()->processEntityAction($this, static::ENTITY,
                Mage_Index_Model_Event::TYPE_DELETE);
        }
        return $this;
    }

    /**
     * @return Mana_Admin_Model_Validator
     */
    public function getValidator() {
        if(!$this->_validator) {
            $this->_validator = Mage::getModel('mana_admin/validator', array($this->rules, $this->getData(), $this->captions, $this, array()));
        }
        return $this->_validator;
    }

    public function getReferencePages($root_id) {
        $this->getResource()->getReferencePages($root_id);
    }

    public function getIdentifier() {
        $route = "mana_content/book/view";
        if($this->adminHelper()->isGlobal()) {
            $pageGlobalId = $this->getData('page_global_id');
            $model = Mage::getModel('mana_content/page_store');
            $model->setData('store_id', Mage::app()->getDefaultStoreView()->getId());
            $model->load($pageGlobalId, 'page_global_id');
            $id = $model->getId();
        } else {
            $id = $this->getId();
        }
        $url = Mage::getUrl($route, array('_use_rewrite' => true, 'id' => $id));
        $currentUrl = Mage::getUrl();
        $url = str_replace($currentUrl, '', $url);
        return $url;
    }

    #region Dependencies
    /**
     * @return Mage_Index_Model_Indexer
     */
    public function getIndexerSingleton() {
        return Mage::getSingleton('index/indexer');
    }

    /**
     * @return Mana_Core_Helper_Db
     */
    public function dbHelper() {
        return Mage::helper('mana_core/db');
    }

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Content_Helper_Data
     */
    public function contentHelper() {
        return Mage::helper('mana_content');
    }

    protected function _validateUrlKey() {
        $resource = $this->getResource();
        $read = $resource->getReadConnection();
        $select = $read->select();
        $select->from(array('mps' => $resource->getTable('mana_content/page_store')), array('COUNT(*)'))
            ->joinInner(array('mpg' => $resource->getTable('mana_content/page_global')), '`mpg`.`id` = `mps`.`page_global_id`', array())
            ->joinInner(array('mpgcs' => $resource->getTable('mana_content/page_globalCustomSettings')), '`mpgcs`.`id` = `mpg`.`page_global_custom_settings_id`', array())
            ->where('`mpgcs`.`parent_id` IS NULL')
            ->where('`mps`.`url_key` = ?', $this->getUrlKey());

        if($this->getId()) {
            $select->where('`mps`.`page_global_id` <> ?', $this->getData('page_global_id'));
        }
        if (!$this->adminHelper()->isGlobal()) {
            $select->where('`mps`.`store_id` = ?', $this->adminHelper()->getStore()->getId());
        }

        $count = $read->fetchOne($select);
        if($count) {
            throw new Exception($this->contentHelper()->__('Root book page with URL Key `%s` already exists', $this->getUrlKey()));
        }
    }

    #endregion
}