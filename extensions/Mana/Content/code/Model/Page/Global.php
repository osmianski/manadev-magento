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
class Mana_Content_Model_Page_Global extends Mana_Content_Model_Page_Abstract implements Mana_Content_Model_Page_Hierarchical {
    const ENTITY = 'mana_content/page_global';

    protected $_children = array();

    protected function _construct() {
        $this->_init(self::ENTITY);
    }

    /**
     * Retrieve model resource
     *
     * @return Mana_Content_Resource_Page_Global
     */
    public function getResource() {
        return parent::getResource();
    }

    /**
     * @param int $maxDepth
     * @return $this
     */
    public function loadChildPages() {
        $models = array($this);
        while(true == true) {
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
     * @param Mana_Content_Model_Page_Global[] $models
     * @param Mana_Content_Model_Page_Global[] $childModels
     */
    protected function _addChildPagesToTheirParents($models, $childModels) {
        foreach ($childModels as $childModel) {
            foreach ($models as $model) {
                if ($childModel->getData('parent_id') == $model->getData('page_global_custom_settings_id')) {
                    $model->addChild($childModel);
                    break;
                }
            }
        }
    }

    public function addChild($childPage) {
        $this->_children[] = $childPage;
    }
}