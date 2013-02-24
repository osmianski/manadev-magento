<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Block_Column_Select extends Mana_Admin_Block_Column {
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
    <select name="<?php echo $this->getColumn()->getId() ?>"
           class="ct-container <?php echo $this->getColumn()->getInlineCss() ?>"
           id="<?php echo $cellId ?>"
           <?php if ($this->getIsDisabled()) : ?>disabled="disabled"<?php endif; ?> >
        <option value="" <?php if ('' == $row->getData($this->getColumn()->getIndex())) : ?>selected="selected"<?php endif; ?> ></option>
        <?php foreach ($this->getColumn()->getOptions() as $value => $label) : ?>
            <option value="<?php echo $value ?>" <?php if ($value == $row->getData($this->getColumn()->getIndex())) : ?>selected="selected"<?php endif; ?> ><?php echo $label ?></option>
        <?php endforeach; ?>
    </select>
    <?php
        $html = ob_get_clean();
        return $html;
    }
    public function renderCss() {
        return parent::renderCss()
                . ' ct-select'
                . ' c-' . $this->getColumn()->getId();
    }
}