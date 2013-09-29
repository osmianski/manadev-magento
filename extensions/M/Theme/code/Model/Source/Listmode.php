<?php
/**
 * @category    Mana
 * @package     M_Theme
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class M_Theme_Model_Source_Listmode extends Mage_Adminhtml_Model_System_Config_Source_Catalog_ListMode {
    protected $_themeName;
    public function toOptionArray() {
        /* @var $t M_Theme_Helper_Data */
        $t = Mage::helper(strtolower('M_Theme'));
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));

        $config = $t->getConfig($this->_themeName)->getNode();
        if (isset($config->catalog->product->list_modes)) {
            $listModes = array();
            $result = array();
            foreach ($core->getSortedXmlChildren($config->catalog->product, 'list_modes') as $listMode) {
                $listModes[] = array(
                    'value' => $listMode->getName(),
                    'label' => (string)$listMode->label
                );
                $result[] = array(
                    'value' => $listMode->getName(),
                    'label' => $t->__('%s Only', (string)$listMode->label)
                );
            }
            for ($count = 2; $count <= count($listModes); $count++) {
                $indexes = array_fill(0, $count, 0);
                $finished = false;
                while (!$finished) {
                    // check if all indexes are unique
                    $unique = true;
                    $uniqueIndexes = array();
                    $value = array();
                    $label = array();
                    foreach ($indexes as $index) {
                        if (isset($uniqueIndexes[$index])) {
                            $unique = false;
                            break;
                        }
                        $uniqueIndexes[$index] = $index;
                        $value[] = $listModes[$index]['value'];
                        $label[] = $listModes[$index]['label'];
                    }

                    // if unique add to result
                    if ($unique) {
                        $result[] = array(
                            'value' => implode('-', $value),
                            'label' => implode(' / ', $label),
                        );
                    }

                    // increment indexes
                    for ($i = $count - 1; $i >= 0; $i--) {
                        $indexes[$i]++;
                        if ($indexes[$i] < count($listModes)) {
                            break;
                        }
                        else {
                            $indexes[$i] = 0;
                        }
                    }

                    // check is all combinations are iterated
                    $finished = true;
                    foreach ($indexes as $index) {
                        if ($index) {
                            $finished = false;
                            break;
                        }
                    }
                }
            }
            return $result;
        }
        else {
            return parent::toOptionArray();
        }
    }
}