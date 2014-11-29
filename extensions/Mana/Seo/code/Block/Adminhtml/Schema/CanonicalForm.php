<?php
/** 
 * @category    Mana
 * @package     Mana_Seo
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Seo_Block_Adminhtml_Schema_CanonicalForm extends Mana_Admin_Block_V2_Form {
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id' => 'mf_canonical',
            'html_id_prefix' => 'mf_canonical_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'flat_model' => $this->getFlatModel(),
            'edit_model' => $this->getEditModel(),
        ));

        $fieldset = $this->addFieldset($form, 'mfs_canonical', array(
            'title' => $this->__('Canonical URL'),
            'legend' => $this->__('Canonical URL'),
        ));

        $this->addField($fieldset, 'canonical_category', 'select', array(
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'label' => $this->__('Canonical Link Meta Tag on Category Pages'),
            'note' => $this->__("If 'No', canonical URL would be rendered as specified in %s", implode('->',
                array($this->__('System'), $this->__('Configuration'), $this->__('Catalog'),
                $this->__('Search Engine Optimizations'), $this->__('Use Canonical Link Meta Tag For Categories')))),
            'name' => 'canonical_category',
            'required' => true,
        ));

        $this->addField($fieldset, 'canonical_search', 'select', array(
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'label' => $this->__('Canonical Link Meta Tag on Quick Search Page'),
            'name' => 'canonical_search',
            'required' => true,
        ));

        $this->addField($fieldset, 'canonical_cms', 'select', array(
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'label' => $this->__('Canonical Link Meta Tag on CMS Pages'),
            'name' => 'canonical_cms',
            'required' => true,
        ));

        if ($this->coreHelper()->isManadevAttributePageInstalled()) {
            $this->addField($fieldset, 'canonical_option_page', 'select', array(
                'options' => $this->getYesNoSourceModel()->getOptionArray(),
                'label' => $this->__('Canonical Link Meta Tag on Option Pages'),
                'name' => 'canonical_option_page',
                'required' => true,
            ));
        }

        $this->addField($fieldset, 'canonical_filters', 'select', array(
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'label' => $this->__('Canonical URL Contains All Applied Filters'),
            'name' => 'canonical_filters',
            'required' => true,
        ));

        $this->addField($fieldset, 'canonical_limit_all', 'select', array(
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'label' => $this->__('Canonical URL Points to the Page with All Items'),
            'name' => 'canonical_limit_all',
            'required' => true,
        ));

        $fieldset = $this->addFieldset($form, 'mfs_prev_next', array(
            'title' => $this->__('rel=prev and rel=next Paging Hints'),
            'legend' => $this->__('rel=prev and rel=next Paging Hints'),
        ));

        $this->addField($fieldset, 'prev_next_product_list', 'select', array(
            'options' => $this->getYesNoSourceModel()->getOptionArray(),
            'label' => $this->__('rel=prev and rel=next URLs on Paginated Product List Pages'),
            'name' => 'prev_next_product_list',
            'required' => true,
        ));

//        $this->addField($fieldset, 'prev_next_other_lists', 'select', array(
//            'options' => $this->getYesNoSourceModel()->getOptionArray(),
//            'label' => $this->__('rel=prev and rel=next URLs on All Pages Containing Product List Toolbar'),
//            'name' => 'prev_next_other_lists',
//            'required' => true,
//        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    #region Dependencies
    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getFlatModel() {
        return Mage::registry('m_flat_model');
    }

    /**
     * @return Mana_Seo_Model_Schema
     */
    public function getEditModel() {
        return Mage::registry('m_edit_model');
    }

    /**
     * @return Mana_Seo_Model_Source_Canonical
     */
    public function getCanonicalSourceModel() {
        return Mage::getSingleton('mana_seo/source_canonical');
    }

    #endregion
}