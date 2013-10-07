<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

/**
 * This block show button link to page with reviews.
 * @author Mana Team
 *
 */
class Local_Manadev_Block_Review extends Mage_Review_Block_Helper {
    protected $_availableTemplates = array(
        'default' => 'review/helper/summary.phtml',
        'short'   => 'review/helper/summary_short.phtml',
    	'button' => 'local/manadev/review/button.phtml',
    );
}