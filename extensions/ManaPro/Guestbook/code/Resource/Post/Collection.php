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
class ManaPro_Guestbook_Resource_Post_Collection extends Mana_Db_Resource_Object_Collection {
    /**
     * Invoked during resource collection model creation process, this method associates this
     * resource collection model with model class and with resource model class
     */
    protected function _construct()
    {
        $this->_init('manapro_guestbook/post');
    }
}