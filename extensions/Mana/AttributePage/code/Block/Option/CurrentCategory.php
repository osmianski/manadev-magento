<?php

class Mana_AttributePage_Block_Option_CurrentCategory extends Mana_AttributePage_Block_Option_Images
{
    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    public function getCollection() {
        /* @var Mana_AttributePage_Resource_OptionPage_Store_Collection $collection */
        $collection = $this->createOptionPageCollection()
            ->addStoreFilter($this->getData('store_id'))
            ->setOrder('position', 'ASC');

        return $this->addCategoryFilterToCollection($collection);

    }

    /**
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection|Object
     */
    public function createOptionPageCollection() {
        return Mage::getResourceModel('mana_attributepage/optionPage_store_collection');
    }

    /**
     * @param Mana_AttributePage_Resource_OptionPage_Store_Collection $collection
     * @return Mana_AttributePage_Resource_OptionPage_Store_Collection
     */
    protected function addCategoryFilterToCollection($collection) {
    }

}