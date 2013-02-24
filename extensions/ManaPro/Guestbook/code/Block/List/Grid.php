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
class ManaPro_Guestbook_Block_List_Grid extends Mana_Admin_Block_Crud_List_Grid  {
    public function __construct() {
        parent::__construct();
        $this->setId('guestbookGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->setUseAjax(false);
    }
    protected function _prepareCollection() {
        /* @var $collection ManaPro_Guestbook_Resource_Post_Collection */
        $collection = Mage::getResourceModel('manapro_guestbook/post_collection');

        $collection->addColumnToSelect(array('id', 'store_id', 'status', 'created_at'));
        foreach (Mage::helper('manapro_guestbook')->getGridColumns() as $column) {
            $method = "_prepareCollection_$column";
            $this->$method($collection);
        }

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
    /**
     * @param ManaPro_Guestbook_Resource_Post_Collection $collection
     * @return void
     */
    protected function _prepareCollection_email($collection) {
        $collection->addColumnToSelect('email');
    }
    /**
     * @param ManaPro_Guestbook_Resource_Post_Collection $collection
     * @return void
     */
    protected function _prepareCollection_url($collection) {
        $collection->addColumnToSelect('url');
    }
    /**
     * @param ManaPro_Guestbook_Resource_Post_Collection $collection
     * @return void
     */
    protected function _prepareCollection_name($collection) {
        $collection->addColumnToSelect('name');
    }
    /**
     * @param ManaPro_Guestbook_Resource_Post_Collection $collection
     * @return void
     */
    protected function _prepareCollection_text($collection) {
        $collection->addColumnToSelect('text');
    }
    /**
     * @param ManaPro_Guestbook_Resource_Post_Collection $collection
     * @return void
     */
    protected function _prepareCollection_country($collection) {
        $collection->addColumnToSelect('country_id');
    }
    /**
     * @param ManaPro_Guestbook_Resource_Post_Collection $collection
     * @return void
     */
    protected function _prepareCollection_region($collection) {
        $collection->addColumnToSelect('region');
    }

    protected function _prepareColumns() {
        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Date'),
            'index'     => 'created_at',
            'width' => '100px',
            'type'      => 'datetime',
        ));

        foreach (Mage::helper('manapro_guestbook')->getGridColumns() as $column) {
            $method = "_prepareColumn_$column";
            $this->$method();
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => $this->__('Store'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'width' => '150px',
            ));
        }
        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '100px',
            'options' => Mage::getSingleton('manapro_guestbook/source_status')->getOptionArray(),
        ));

        parent::_prepareColumns();
        return $this;
    }
    protected function _prepareColumn_email() {
        $this->addColumn('email', array(
            'header' => $this->__('Email'),
            'index' => 'email',
            'width' => '150px',
        ));
    }
    protected function _prepareColumn_url() {
        $this->addColumn('url', array(
            'header' => $this->__('Website'),
            'index' => 'url',
            'width' => '150px',
        ));
    }
    protected function _prepareColumn_name() {
        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'index' => 'name',
            'width' => '150px',
        ));
    }
    protected function _prepareColumn_text() {
        $this->addColumn('text', array(
            'header' => $this->__('Text'),
            'index' => 'text',
            'type' => 'text',
            'nl2br' => true,
            'escape' => true,
        ));
    }
    protected function _prepareColumn_country() {
        $this->addColumn('country', array(
            'header' => $this->__('Country'),
            'index' => 'country_id',
            'width' => '150px',
            'type'  => 'options',
            'options' => Mage::getSingleton('mana_core/source_country')->getOptionArray(),
        ));
    }
    protected function _prepareColumn_region() {
        $this->addColumn('region', array(
            'header' => $this->__('Region'),
            'index' => 'region',
            'width' => '150px',
        ));
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->setUseSelectAll(true);

        $this->getMassactionBlock()->addItem('approve', array(
             'label'=> $this->__('Approve'),
             'url'  => $this->getUrl('*/*/approve'),
        ));
        $this->getMassactionBlock()->addItem('reject', array(
             'label'=> $this->__('Reject'),
             'url'  => $this->getUrl('*/*/reject'),
        ));
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/delete'),
            'confirm' => $this->__('Are you sure?'),
        ));

        return $this;
    }
    public function getGridUrl() {
        return $this->getUrl('*/*/*');
    }
    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}