<?php
/**
 * @category    Mana
 * @package     Local_Manadev
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * This block appears on home page. In underlying markup, person can apply for fee-free feature, theme, etc
 * @author Mana Team
 *
 */
class Local_Manadev_Block_Request extends Mage_Core_Block_Template {
	public function getSendUrl() {
		return $this->getUrl('actions/request/send');
	}
}