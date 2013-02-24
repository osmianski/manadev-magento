<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
abstract class Mana_Admin_Block_Column extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function generateId()
    {
		return Mage::helper('core')->uniqHash('id_');
    }
	public function isDisabled($row) {
		return $this->getColumn()->getIsDefaultDisabled();
	}
	public function renderCellOptions($cellId, $row) {
		$options = new Varien_Object();
		$this->_renderCellOptions($options, $row);
		if (count($options->getData())) {
			ob_start();
			?>
<script type="text/javascript">(function($) {
$('#<?php echo $cellId ?>').data(<?php echo $options->toJson() ?>);	
})(jQuery);</script>
			<?php 
			return ob_get_clean();
		}
		else {
			return '';
		}
	}
	public function renderColumnOptions() {
		$options = new Varien_Object();
		$cellId = '#'.$this->getColumn()->getGrid()->getId().' th.c-'.$this->getColumn()->getId();
		$this->_renderColumnOptions($options);
		if (count($options->getData())) {
			ob_start();
			?>
<script type="text/javascript">(function($) {
$('<?php echo $cellId ?>').data(<?php echo $options->toJson() ?>);	
})(jQuery);</script>
			<?php 
			return ob_get_clean();
		}
		else {
			return '';
		}
	}
	protected function _renderColumnOptions($options) {
		if ($this->getColumn()->hasDefaultBit()) {
			$options
				->setShowHelper(true)
		    	->setDefaultLabel($this->getColumn()->getDefaultLabel())
		    	->setIsDefaultDisabled($this->getColumn()->getIsDefaultDisabled());
		}
		else {
			$options->setShowHelper(false);
		}
	}
	protected function _renderCellOptions($options, $row) {
		if ($this->getColumn()->hasDefaultBit()) {
			$options->setIsDefault(!Mage::helper('mana_db')->hasOverriddenValue($row, null,
		    		$this->getColumn()->getDefaultBit()));
		}
		else {
		    $options->setIsDefault(false);
		}
		$options->setValue($row->getData($this->getColumn()->getIndex()));
	}
	public function renderHeader() {
		return parent::renderHeader();//.$this->renderColumnOptions();
	}
	public function getColumnInfo() {
		$options = new Varien_Object();
		$this->_renderColumnOptions($options);
		return $options->getData();
	}
	public function getCellInfo($row) {
		$options = new Varien_Object();
		$this->_renderCellOptions($options, $row);
		return $options->getData();
	}
}