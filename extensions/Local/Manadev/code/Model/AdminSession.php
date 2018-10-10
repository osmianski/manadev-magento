<?php

class Local_Manadev_Model_AdminSession extends Mage_Admin_Model_Session
{
    public function isAllowed($resource, $privilege = null) {
        if ($resource == 'newsletter/subscriber') {
            return false;
        }

        return parent::isAllowed($resource, $privilege);
    }
}