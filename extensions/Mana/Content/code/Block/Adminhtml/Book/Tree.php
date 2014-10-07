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
class Mana_Content_Block_Adminhtml_Book_Tree extends Mage_Adminhtml_Block_Template {
    protected function _construct() {
        $this->setTemplate('mana/content/book/tree.phtml');
    }

    public function getOptions() {
        $page = $this->getHierarchicalFlatModel();
        $page->loadChildPages(Mage::getStoreConfig('mana_content/general/opened_tree_depth'));

        return $this->jsonHelper()->encodeAttribute(array(
            'core' => array(
                'data' => $this->_convertPageTreeToArrayRecursively($page, 0),
                'check_callback' => true,
                'multiple' => false,
            ),
            'dnd' => array(
                'copy' => false,
            ),
            'plugins' => array (
                'dnd',
            ),
        ));
    }

    /**
     * @param Mana_Content_Model_Page_Hierarchical $page
     * @param $depth
     * @return array
     */
    protected function _convertPageTreeToArrayRecursively($page, $depth) {
        if($this->adminHelper()->isGlobal()) {
            $id = $page->getData('id');
        } else {
            $id = $page->getData('page_global_id');
        }
        if(strlen($page->getData('title')) > Mage::getStoreConfig('mana_content/general/visible_title_char')) {
            $text = substr($page->getData('title'), 0, Mage::getStoreConfig('mana_content/general/visible_title_char'));
            $text .= "...";
        } else {
            $text = $page->getData('title');
        }

        $opened_nodes = Mage::getSingleton('admin/session')->getData('tree_state')['core']['open'];
        $state = array(
            'opened' => in_array($id, $opened_nodes),
            'selected' => $depth == 0,
        );
        $children = array();
        foreach ($page->getChildPages() as $childPage) {
            $children[] = $this->_convertPageTreeToArrayRecursively($childPage, $depth + 1);
        }
        $children = (count($children) != 0) ? $children: null;

        $li_attr['title'] = $page->getData('title');
        return compact('id', 'text', 'state', 'children', 'li_attr');
    }

    #region dependencies

    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Core_Helper_Json
     */
    public function jsonHelper() {
        return Mage::helper('mana_core/json');
    }

    /**
     * @throws Exception
     * @return Mana_Content_Model_Page_Hierarchical
     */
    public function getHierarchicalFlatModel() {
        if (($result = Mage::registry('m_flat_model')) instanceof Mana_Content_Model_Page_Hierarchical) {
            return $result;
        }
        else {
            throw new Exception(sprintf('%s does not implement %s', get_class($result)), 'Mana_Content_Model_Page_Hierarchical');
        }

    }

    #endregion
}