<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Admin_Helper_Event extends Mage_Core_Helper_Abstract {
    /**
     * @param Mage_Core_Block_Abstract $target
     * @param string[] $handlers
     * @param array $params
     * @return mixed
     */
    public function raise($target, $handlers, $params = array()) {
        if (!empty($handlers)) {
            $event = new Varien_Object(array_merge($params, array(
                'target' => $target, 'result' => false, 'stop_event_handling' => false)));
            foreach ($handlers as $handler) {
                $handler = explode('::', $handler);
                if ($object = $target->getLayout()->getBlock($handler[0])) {
                    $method = $handler[1];
                    $event->setResult($object->$method($event));
                    if ($event->getStopEventHandling()) {
                        break;
                    }
                }
            }
            return $event->getResult();
        }
        else {
            return false;
        }
    }
}