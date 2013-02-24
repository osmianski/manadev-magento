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
class Mana_Admin_Block_Column_Color extends Mana_Admin_Block_Column {
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row) {
    	ob_start();
    	$cellId = $this->generateId();
    	?>
<div style="<?php echo $this->_getStyle($row)?>"
    class="ct-container input-color <?php echo $this->getColumn()->getInlineCss() ?>"
    id="<?php echo $cellId ?>">&nbsp;
</div>
    	<?php 
    	$html = ob_get_clean();//.$this->renderCellOptions($cellId, $row);
        return $html;
    }
    public function renderCss() {
    	return parent::renderCss()
    		.' ct-color'
    		.' c-'.$this->getColumn()->getId();
    }
    protected function _getStyle($row) {
        if (!($color = $row->getData($this->getColumn()->getIndex()))) {
            $color = 'transparent';
        }
        $width = $height = $this->getColumn()->getWidth();
        return "background: {$color}; width: {$width}; height: {$height}; ";
    }
    protected function _renderColumnOptions($options) {
        parent::_renderColumnOptions($options);
        $options
            ->setShowUseDefault($options->getShowHelper())
            ->setShowHelper(true);
    }
}