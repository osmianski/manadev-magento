<?php
/**
 * @category    Mana
 * @package     ManaProduct_Review
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaProduct_Review_Block_Helper extends Mage_Review_Block_Helper {
    protected $_availableTemplates = array(
        'default' => 'manaproduct/review/summary.phtml',
        'short' => 'manaproduct/review/summary_short.phtml'
    );
    public function getReviewsUrl() {
        return $this->getProduct()->getProductUrl();
    }
}