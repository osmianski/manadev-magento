<?php
/** 
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 * @method bool getUseAjax()
 * @method Mana_Admin_Block_V2_Grid setUseAjax(bool $value)
 * @method bool getStoreDataInBrowser()
 * @method Mana_Admin_Block_V2_Grid setStoreDataInBrowser(bool $value)
 * @method array getRaw()
 * @method Mana_Admin_Block_V2_Grid setRaw(array $value)
 * @method array getEdit()
 * @method Mana_Admin_Block_V2_Grid setEdit(array $value)
 * @method bool getUseDefaultEnabled()
 * @method Mana_Admin_Block_V2_Grid setUseDefaultEnabled(bool $value)
 */
class Mana_Admin_Block_V2_Grid extends Mage_Adminhtml_Block_Widget_Grid  {
    protected $_multipleRowColumns = array();
    protected $_multipleRowColumnsPrepared = false;
    protected $_currentMultipleRowItem;

    protected function _prepareLayout() {
        $this->setTemplate('mana/admin/v2/grid.phtml');
        $this->setId(str_replace('_', '-', str_replace('.', '-', $this->getNameInLayout())));

        /* @var $button Mana_Admin_Block_Grid_Action */
        $button = $this->getLayout()->createBlock('mana_admin/v2_grid_action', "{$this->getNameInLayout()}.export")
            ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Export'),
                'class' => 'task',
                'readonly_action' => true,
            ));

        $this->setChild('export_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/v2_grid_action', "{$this->getNameInLayout()}.reset")
            ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Reset Filter'),
                'readonly_action' => true,
            ));
        $this->setChild('reset_filter_button', $button);

        $button = $this->getLayout()->createBlock('mana_admin/v2_grid_action', "{$this->getNameInLayout()}.search")
            ->setData(array(
                'label' => Mage::helper('adminhtml')->__('Search'),
                'class' => 'task',

                'readonly_action' => true,
            ));
        $this->setChild('search_button', $button);

        $this->checkGridDisable();
        $this->_prepareClientSideBlock();
        return $this;
    }

    protected function _prepareCollection() {
        Mage::dispatchEvent('m_crud_grid_collection', array('grid' => $this));
        $block = $this->getData('m_client_side_block');
        if ($this->getStoreDataInBrowser()) {
            /* @var $collection Mana_Db_Resource_Entity_JsonCollection */
            $collection = $this->getCollection();
            $block['raw'] = $this->jsonHelper()->encodeAttribute($collection->getRawData());
        }

        if ($this->getEdit()) {
            $block['edit'] = $this->jsonHelper()->encodeAttribute($this->getEdit(),
                array('force_object' => array('pending' => true, 'saved' => true, 'deleted' => true)));
        }
        $this->setData('m_client_side_block', $block);

        if ($this->getCollection()) {

            $this->_preparePage();

            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);

            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }

            if (is_string($filter)) {
                $data = $this->helper('adminhtml')->prepareFilterString($filter);
                $this->_setFilterValues($data);
            } else if ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            } else if (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }

            if (isset($this->_columns[$columnId]) && $this->_columns[$columnId]->getIndex()) {
                $dir = (strtolower($dir) == 'desc') ? 'desc' : 'asc';
                $this->_columns[$columnId]->setDir($dir);
                $column = $this->_columns[$columnId]->getFilterIndex() ?
                    $this->_columns[$columnId]->getFilterIndex() : $this->_columns[$columnId]->getIndex();
                $this->getCollection()->setOrder($column, $dir);
                if ($secondaryOrder = $this->getData('secondary_order')) {
                    $this->getCollection()->setOrder($secondaryOrder['column'], $secondaryOrder['dir']);
                }
            }

            if (!$this->_isExport) {
                $this->getCollection()->load();
                $this->_afterLoadCollection();
            }
        }

        return $this;
    }

    protected function _prepareColumns() {
        Mage::dispatchEvent('m_crud_grid_columns', array('grid' => $this));
        parent::_prepareColumns();

        return $this;
    }

    protected function _prepareClientSideBlock() {
        /* @var $urlTemplate Mana_Core_Helper_UrlTemplate */
        $urlTemplate = Mage::helper('mana_core/urlTemplate');

        $block = array(
            'type' => 'Mana/Admin/Grid',
            'url' => $urlTemplate->encodeAttribute($this->getGridUrl()),
            'html' => array(
                'id' => $this->getId(),
            ),
        );

        if ($this->getData('readonly')) {
            $block['readonly'] = 1;
        }

        $this->setData('m_client_side_block', $block);

        return $this;
    }

    /**
     * @param int $index
     * @param Mage_Core_Model_Abstract $row
     * @return array
     */
    public function getRowClientSideBlock(/** @noinspection PhpUnusedParameterInspection */$index, $row) {
        return array_merge(array(
            'type' => 'Mana/Admin/Grid/Row',
            'row_id' => $row->getId(),
        ), $this->getRowUrl($row) ? array('url' => $this->getRowUrl($row)) : array());
    }

    /**
     * @param Mana_Db_Model_Entity $row
     * @param Mana_Admin_Block_Grid_Column $column
     * @return array
     */
    public function getCellClientSideBlock(/** @noinspection PhpUnusedParameterInspection */$row, $column) {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper('mana_core');
        $standardPrefix = 'adminhtml/widget_grid_column_renderer_';
        $editablePrefix = 'mana_admin/v2_grid_column_';

        $rendererClass = $column->getData('renderer');
        if (!$rendererClass) {
            $rendererClass = $column->getRendererClass();
        }

        if (!($type = $column->getData('cell_client_side_block_type'))) {
            $type = 'Mana/Admin/Grid/Cell';
            if ($core->startsWith($rendererClass, $standardPrefix)) {
                $type .= '/' . ucfirst(substr($rendererClass, strlen($standardPrefix)));
            }
            elseif ($core->startsWith($rendererClass, $editablePrefix)) {
                $type .= '/' . ucfirst(substr($rendererClass, strlen($editablePrefix)));
            }
        }

        $result = compact('type');
        if ($column->getData('readonly')) {
            $result['readonly'] = 1;
        }
        return $result;
    }

    /**
     * Add column to grid
     *
     * @param   string $columnId
     * @param   array | Varien_Object $column <p>
     *      Available options:
     *      <table>
     *      <tr valign="top"><td><i>header</i></td><td>Column header text</td></tr>
     *      <tr valign="top"><td><i>index</i></td><td>Underlying DB column name</td></tr>
     *      <tr valign="top"><td><i>width</i></td><td>Width, like '50px'</td></tr>
     *      <tr valign="top"><td><i>align</i></td><td>Horizontal alignment: 'center', 'left'</td></tr>
     *      </table>
     *      </p>
     * @throws Exception
     * @return  Mana_Admin_Block_V2_Grid
     */
    public function addColumn($columnId, $column) {
        if (is_array($column)) {
            $this->_columns[$columnId] = $this->createColumn($columnId, $column);
            $this->_lastColumnId = $columnId;
        }
        else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format.'));
        }

        return $this;
    }

    /**
     * Add multiple row column to grid
     *
     * @param   string $columnId
     * @param   array | Varien_Object $column <p>
     *      Available options:
     *      <table>
     *      <tr valign="top"><td><i>header</i></td><td>Column header text</td></tr>
     *      <tr valign="top"><td><i>index</i></td><td>Underlying DB column name</td></tr>
     *      <tr valign="top"><td><i>width</i></td><td>Width, like '50px'</td></tr>
     *      <tr valign="top"><td><i>align</i></td><td>Horizontal alignment: 'center', 'left'</td></tr>
     *      </table>
     *      </p>
     * @throws Exception
     * @return  Mana_Admin_Block_V2_Grid
     */
    public function addMultipleRowColumn($columnId, $column) {
        if (is_array($column)) {
            $this->_multipleRowColumns[$columnId] = $this->createColumn($columnId, $column);
        }
        else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format.'));
        }

        return $this;
    }

    public function getCurrentMultipleRowItem() {
        return $this->_currentMultipleRowItem;
    }

    public function setCurrentMultipleRowItem($value) {
        $this->_currentMultipleRowItem = $value;

        return $this;
    }

    public function getMultipleRowColumns() {
        $this->setCurrentMultipleRowItem(func_get_arg(0));
        if (!$this->_multipleRowColumnsPrepared) {
            $this->_prepareMultipleRowColumns();
            $this->_multipleRowColumnsPrepared = true;
        }
        return $this->_multipleRowColumns;
    }

    public function getMultipleRowColSpan($parentItem, $childItem, $columnIndex, $column) {
        return 0;
    }

    public function getMultipleRowClass($childItem) {
        return '';
    }

    protected function _prepareMultipleRowColumns() {
    }

    public function createColumn($columnId, $column) {
        if (is_array($column)) {
            /* @var $columnBlock Mana_Admin_Block_V2_Grid_Column */
            $columnBlock = $this->getLayout()->createBlock('mana_admin/v2_grid_column');
            $columnBlock->setData($column);
            $columnBlock
                ->setGrid($this)
                ->setId($columnId);
            $columnBlock->prepareClientSideBlock();
        }
        else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format.'));
        }

        return $columnBlock;
    }

    public function getUsedDefault() {
        if ($edit = $this->getEdit()) {
            return !empty($edit['useDefault']);
        }
        else {
            return $this->getEditModel()->isUsingDefaultData($this->getFieldName());
        }
    }

    public function checkGridDisable() {
        if ($this->getDisplayUseDefault() && $this->getUsedDefault()) {
            $this->setData('readonly', true);
        }

        return $this;
    }

    public function getDisplayUseDefault() {
        return $this->adminHelper()->getDefaultFormula($this->getFlatModel(), $this->getFieldName());
    }

    public function getDefaultLabel() {
        return $this->adminHelper()->getDefaultLabel($this->getFlatModel(), $this->getFieldName());
    }

    public function getUseDefaultHtmlId() {
        return $this->getId().'-use-default';
    }
    /**
     * @return bool|Mana_Db_Model_Entity
     */
    public function getFlatModel() {
        return false;
    }

    /**
     * @return bool|Mana_Db_Model_Entity
     */
    public function getEditModel() {
        return false;
    }

    public function getFieldName() {
        return false;
    }

    #region Dependencies
    /**
     * @return Mana_Admin_Helper_Data
     */
    public function adminHelper() {
        return Mage::helper('mana_admin');
    }

    /**
     * @return Mana_Core_Helper_Data
     */
    public function coreHelper() {
        return Mage::helper('mana_core');
    }

    /**
     * @return Mana_Db_Helper_Data
     */
    public function dbHelper() {
        return Mage::helper('mana_db');
    }

    /**
     * @return Mana_Core_Helper_Json
     */
    public function jsonHelper() {
        return Mage::helper('mana_core/json');
    }

    #endregion
}