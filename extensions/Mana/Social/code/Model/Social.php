<?php
/**
 * @category    Mana
 * @package     Mana_Social
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Social_Model_Social
{
    /**
     * @return Mana_Social_Model_Link[]
     */
    public function getLinks() {
        $result = array();
        foreach ($this->getSites() as $site) {
            if ($link = $site->getLink()) {
                $result[] = $link;
            }
        }
        usort($result, array($this, '_compareLinks'));
        return $result;
    }

    /**
     * @return Mana_Social_Model_Share[]
     */
    public function getSharingActions() {
        $result = array();
        foreach ($this->getSites() as $site) {
            foreach ($site->getSharingActions() as $action) {
                $result[] = $action;
            }
        }
        return $result;
    }

    /**
     * @param Mana_Social_Model_Link $a
     * @param Mana_Social_Model_Link $b
     * @return int
     */
    public function _compareLinks($a, $b)
    {
        if ($a->getSortOrder() < $b->getSortOrder()) return -1;
        if ($a->getSortOrder() > $b->getSortOrder()) return 1;
        return 0;
    }
    /**
     * @return Mana_Social_Model_Site_Abstract[]
     */
    public function getSites()
    {
        /* @var $core Mana_Core_Helper_Data */
        $core = Mage::helper(strtolower('Mana_Core'));
        $result = array();

        foreach ($core->getSortedXmlChildren(Mage::getConfig()->getNode('mana_social'), 'sites') as $siteCode => $siteConfig) {
            /* @var $site Mana_Social_Model_Site_Abstract */
            $site = Mage::getModel((string)$siteConfig->model);
            $site->setCode($siteCode);
            foreach ($siteConfig->children() as $siteField => $siteValue) {
                if ($siteField == 'sharing_actions') {
                    foreach ($siteValue->children() as $sharingActionCode => $sharingActionConfig) {
                        /* @var $action Mana_Social_Model_Share */
                        $action = Mage::getModel((string)$sharingActionConfig->model);
                        $action->setCode($sharingActionCode);
                        $action->setSite($site);
                        foreach ($sharingActionConfig->children() as $actionField => $actionValue) {
                            if ($actionField != 'model') {
                                $action->setData($actionField, (string)$actionValue);
                            }
                        }
                        $site->addSharingAction($action);
                    }
                }
                elseif ($siteField != 'model') {
                    $site->setData($siteField, (string)$siteValue);
                }
            }
            $result[$siteCode] = $site;
        }

        return $result;
    }
}