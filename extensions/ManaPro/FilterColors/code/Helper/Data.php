<?php
/**
 * @category    Mana
 * @package     ManaPro_FilterColors
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/* BASED ON SNIPPET: New Module/Helper/Data.php */
/**
 * Generic helper functions for ManaPro_FilterColors module. This class is a must for any module even if empty.
 * @author Mana Team
 */
class ManaPro_FilterColors_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getCssRelativeUrl($storeId) {
	    return 'm-filter-'. $storeId . '.css';
	}
    public function getFilterClass($filterOptions) {
        return 'mf-'.$filterOptions->getStoreId().'-'.$filterOptions->getGlobalId();
    }
    public function getFilterValueClass($filterOptions, $optionId) {
        return 'mfv-'.$optionId;
    }
    protected function _renderBackgrounds() {
        $backgrounds = func_get_args();
        $filterOptions = array_shift($backgrounds);
        $selector = array_shift($backgrounds);
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
        $layerIndex = 0;
        $result = "\n";
        foreach (array_reverse($backgrounds) as $background) {
            $layerSelector = ".$selector" . ($layerIndex ? " .m-layer$layerIndex" : '');
            $layerIndex++;
            if ($background && ($url = $files->getUrl($background, 'image', '../'))) {
                $result .= "$layerSelector { background-image: url($url); }\n";
            }
            else {
                $result .= "$layerSelector { background-image: none; }\n";
            }
        }
        return $result;
    }
    public function generateCss($storeId) {
        /* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
        $filters = Mage::getResourceModel('mana_filters/filter2_store_collection');
        $filters
            ->addColorsFilter()
            ->addFieldToFilter('store_id', $storeId)
            ;

        ob_start();

        foreach ($filters as $filterOptions) {
            /* @var $filterOptions Mana_Filters_Model_Filter2_Store */

            $values = Mage::getResourceModel('mana_filters/filter2_value_store_collection');
            $values
                ->addFieldToFilter('filter_id', $filterOptions->getId())
                ->setEditFilter(true);
?>

<?php foreach ($values as $value) : ?>
.<?php echo $this->getFilterValueClass($filterOptions, $value->getOptionId()) ?> .m-layer1 {
    width: <?php echo $filterOptions->getImageWidth() ?>px;
    height: <?php echo $filterOptions->getImageHeight() ?>px;
    background-repeat: no-repeat;
}
.<?php echo $this->getFilterValueClass($filterOptions, $value->getOptionId())?> {
    -webkit-border-radius: <?php echo $filterOptions->getImageBorderRadius() ?>px;
    -moz-border-radius: <?php echo $filterOptions->getImageBorderRadius() ?>px;
    border-radius: <?php echo $filterOptions->getImageBorderRadius() ?>px;
    width: <?php echo $filterOptions->getImageWidth() ?>px;
    height: <?php echo $filterOptions->getImageHeight() ?>px;
    background-repeat: no-repeat;
<?php if ($color = $value->getColor()) : ?>
    background-color: <?php echo $color ?>;
<?php endif; ?>
}
<?php echo $this->_renderBackgrounds($filterOptions, $this->getFilterValueClass($filterOptions, $value->getOptionId()),
    $filterOptions->getImageNormal(), $value->getNormalImage()) ?>
<?php echo $this->_renderBackgrounds($filterOptions, $this->getFilterValueClass($filterOptions, $value->getOptionId()) . '.hovered',
    $filterOptions->getImageNormalHovered(), $value->getNormalHoveredImage() ? $value->getNormalHoveredImage() : $value->getNormalImage()) ?>
<?php echo $this->_renderBackgrounds($filterOptions, $this->getFilterValueClass($filterOptions, $value->getOptionId()) . '.selected',
    $filterOptions->getImageSelected(), $value->getSelectedImage() ? $value->getSelectedImage() :  $value->getNormalImage()) ?>
<?php echo $this->_renderBackgrounds($filterOptions, $this->getFilterValueClass($filterOptions, $value->getOptionId()) . '.selected.hovered',
    $filterOptions->getImageSelectedHovered(), $value->getSelectedHoveredImage() ? $value->getSelectedHoveredImage() :  $value->getNormalImage()) ?>
.<?php echo $this->getFilterValueClass($filterOptions, $value->getOptionId()) ?>-state,
.<?php echo $this->getFilterValueClass($filterOptions, $value->getOptionId()) ?>-state .m-layer1 {
    width: <?php echo $filterOptions->getStateWidth() ?>px;
    height: <?php echo $filterOptions->getStateHeight() ?>px;
}
.<?php echo $this->getFilterValueClass($filterOptions, $value->getOptionId())?>-state {
    -webkit-border-radius: <?php echo $filterOptions->getStateBorderRadius() ?>px;
    -moz-border-radius: <?php echo $filterOptions->getStateBorderRadius() ?>px;
    border-radius: <?php echo $filterOptions->getStateBorderRadius() ?>px;
<?php if ($color = $value->getColor()) : ?>
    background-color: <?php echo $color ?>;
<?php endif; ?>
}
<?php echo $this->_renderBackgrounds($filterOptions, $this->getFilterValueClass($filterOptions, $value->getOptionId()) . '-state',
    $filterOptions->getStateImage(), $value->getStateImage()) ?>

<?php endforeach; ?>
<?php
        }
        $css = ob_get_clean();
        $filename = $files->getFilename($this->getCssRelativeUrl($storeId), 'css', true);
        $fh = fopen($filename, 'w');
        fwrite($fh, $css);
        fclose($fh);
    }
}