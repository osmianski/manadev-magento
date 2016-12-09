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
class Mana_Content_Model_Page_Store extends Mana_Content_Model_Page_Abstract implements Mana_Content_Model_Page_Hierarchical {
    const ENTITY = 'mana_content/page_store';

    protected $_childrenLoaded = false;
    protected $_children = array();

    /**
     * @return bool
     */
    public function isChildrenLoaded() {
        return $this->_childrenLoaded;
    }

    /**
     * @param bool $childrenLoaded
     */
    public function setChildrenLoaded($childrenLoaded) {
        $this->_childrenLoaded = $childrenLoaded;
    }

    protected function _construct() {
        $this->_init(self::ENTITY);
    }

    public function canShow() {
        return $this->getData('is_active');
    }

    /**
     * @param int $maxDepth
     * @return $this
     */
    public function loadChildPages() {
        $models = array($this);
        while (true == true) {
            $childModels = $this->getResource()->getChildPages($models);
            if (empty($childModels)) {
                break;
            }

            $this->_addChildPagesToTheirParents($models, $childModels);
            $models = $childModels;
        }
    }

    public function getChildPages() {
        return $this->_children;
    }

    /**
     * @param Mana_Content_Model_Page_Store[] $models
     * @param Mana_Content_Model_Page_Store[] $childModels
     */
    protected function _addChildPagesToTheirParents($models, $childModels) {
        foreach ($childModels as $childModel) {
            foreach ($models as $model) {
                if ($model->isChildrenLoaded()) {
                    continue;
                }

                if ($childModel->getData('parent_id') == $model->getData('page_global_custom_settings_id')) {
                    $model->addChild($childModel);
                    break;
                }
            }
        }
        foreach ($models as $model) {
            $model->setChildrenLoaded(true);
        }
    }

    public function addChild($childPage) {
        $this->_children[] = $childPage;
    }

    /**
     * @return Mana_Content_Resource_Page_Store
     */
    public function getResource() {
        return parent::getResource();
    }

    public function getParentPages() {
        if (!is_array($this->_parentPages)) {
            $this->_parentPages = $this->getResource()->getParentPages($this);
        }

        return $this->_parentPages;
    }
}