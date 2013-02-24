<?php
/**
 * @category    Mana
 * @package     ManaPro_Featured
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class ManaPro_Featured_Block_Carousel extends Mage_Catalog_Block_Product_Abstract {
    protected $_helperBlock = null;
    protected function _prepareLayout() {
        // add helper block
        if (($template = Mage::getStoreConfig($this->getConfigSource() . '_carousel_helper/template')) == 'none') {
            $child = null;
        }
        elseif ($template == 'custom') {
            $child = $this->getLayout()->createBlock('core/template', $this->getNameInLayout() . '.helper', array(
                'collection' => $this->getCollection(),
                'config_source' => $this->getConfigSource(),
            ));
            $child->setTemplate(Mage::getStoreConfig($this->getConfigSource() . '_carousel_helper/custom'));
        }
        else {
            $xml = Mage::getConfig()->getNode('mana_featured/carousel_helper/' . $template);
            $blockType = isset($xml->block) ? (string)$xml->block : 'core/template';
            $child = $this->getLayout()->createBlock($blockType, $this->getNameInLayout() . '.helper', array(
                'collection' => $this->getCollection(),
                'config_source' => $this->getConfigSource(),
            ));
            if (isset($xml->template)) {
                $child->setTemplate((string)$xml->template);
            }
        }
        if ($child) {
            $this->append($child, $this->getNameInLayout() . '.helper');
            $child->addToParentGroup(Mage::getStoreConfig($this->getConfigSource() . '_carousel_helper/position'));
            $this->_helperBlock = $child;
        }

        // add navigation blocks
        if (($template = Mage::getStoreConfig($this->getConfigSource() . '_carousel_navigation/template')) == 'none') {
            $prevChild = $nextChild = null;
        }
        elseif ($template == 'custom') {
            $prevChild = $this->getLayout()->createBlock('core/template', $this->getNameInLayout() . '.prev', array(
                'collection' => $this->getCollection(),
                'config_source' => $this->getConfigSource(),
            ));
            $prevChild->setTemplate(Mage::getStoreConfig($this->getConfigSource() . '_carousel_navigation/custom_prev'));

            $nextChild = $this->getLayout()->createBlock('core/template', $this->getNameInLayout() . '.next', array(
                'collection' => $this->getCollection(),
                'config_source' => $this->getConfigSource(),
            ));
            $nextChild->setTemplate(Mage::getStoreConfig($this->getConfigSource() . '_carousel_navigation/custom_next'));
        }
        else {
            $xml = Mage::getConfig()->getNode('mana_featured/carousel_navigation/' . $template);
            $blockType = isset($xml->prev_block) ? (string)$xml->prev_block : 'core/template';
            $prevChild = $this->getLayout()->createBlock($blockType, $this->getNameInLayout() . '.prev', array(
                'collection' => $this->getCollection(),
                'config_source' => $this->getConfigSource(),
            ));
            if (isset($xml->template_prev)) {
                $prevChild->setTemplate((string)$xml->template_prev);
            }
            $blockType = isset($xml->next_block) ? (string)$xml->next_block : 'core/template';
            $nextChild = $this->getLayout()->createBlock($blockType, $this->getNameInLayout() . '.next', array(
                'collection' => $this->getCollection(),
                'config_source' => $this->getConfigSource(),
            ));
            if (isset($xml->template_next)) {
                $nextChild->setTemplate((string)$xml->template_next);
            }
        }
        if ($prevChild) {
            $this->append($prevChild, $this->getNameInLayout() . '.prev');
            $prevChild->addToParentGroup('floating');
        }
        if ($nextChild) {
            $this->append($nextChild, $this->getNameInLayout() . '.next');
            $nextChild->addToParentGroup('floating');
        }

        return parent::_prepareLayout();
    }
    public function getConfigJson() {
        $result = array();
        $result['rotationInterval'] = Mage::getStoreConfig($this->getConfigSource() . '_carousel_effect/rotation_interval');

        foreach (array('hide', 'show') as $op) {
            $effect = Mage::getStoreConfig($this->getConfigSource() . '_carousel_effect/'. $op);
            switch ($effect) {
                case 'none': $result[$op.'Effect'] = false; break;
                case 'custom': $result[$op.'Effect'] = Mage::getStoreConfig($this->getConfigSource() . '_carousel_effect/'. $op.'_custom'); break;
                case 'random':
                    $result[$op.'Effect'] = false;
                    $result[$op.'RandomEffect'] = true;
                    $result[$op.'RandomEffects'] = explode(',', Mage::getStoreConfig($this->getConfigSource() . '_carousel_effect/'. $op.'_random'));
                    break;
                default:
                    $xml = Mage::getConfig()->getNode('mana_featured/carousel_effect/' . $effect);
                    if (isset($xml->model)) {
                        $model = Mage::getModel((string)$xml->model);
                        $result[$op.'Effect'] = $model->getEffect($effect);
                        $result[$op.'EffectOptions'] = $model->getEffectOptions($effect, $this->getConfigSource(), $op);
                    }
                    else {
                        $result[$op.'Effect'] = $effect;
                    }
                    break;
            }
            $result[$op.'EffectTimer'] = Mage::getStoreConfig($this->getConfigSource() . '_carousel_effect/'. $op.'_interval');
        }

        if ((Mage::getStoreConfig($this->getConfigSource() . '_carousel_helper/template')) != 'none') {
            $result['helper'] = '.m-featured .m-carousel-helper ol';
            $result['helperInteraction'] = Mage::getStoreConfig($this->getConfigSource() . '_carousel_helper/interaction');
        }
        if ((Mage::getStoreConfig($this->getConfigSource() . '_carousel_navigation/template')) != 'none') {
            $result['previousItemElement'] = '.m-featured .m-carousel-navigation-prev';
            $result['nextItemElement'] = '.m-featured .m-carousel-navigation-next';
        }
        $result['autoStart'] = Mage::getStoreConfigFlag($this->getConfigSource() . '_carousel_other/auto_start');
        $result['randomStart'] = Mage::getStoreConfigFlag($this->getConfigSource() . '_carousel_other/random_start');
        $result['shuffle'] = Mage::getStoreConfigFlag($this->getConfigSource() . '_carousel_other/shuffle');

        return json_encode($result);
    }
    public function getCss() {
        return '';
    }
    public function getDecorationCssClasses($position) {
        if (($decoration = Mage::getStoreConfig($this->getConfigSource().'_carousel_decoration/'.$position)) == 'none') {
            return false;
        }
        else {
            $css = 'm-'.str_replace('_', '-', $position).' ';
            if ($decoration == 'custom') {
                if ($custom = Mage::getStoreConfig($this->getConfigSource() . '_carousel_decoration/' . $position . '_custom')) {
                    $css .= $custom;
                }
                else {
                    return false;
                }
            }
            elseif ($node = Mage::getConfig()->getNode('mana_featured/carousel_decoration/' . $position . '/' . $decoration)) {
                $css .= (string)$node->css_class;
            }
            return $css;
        }
    }
    protected function _beforeToHtml() {
        if ($this->_helperBlock) {
            $this->_helperBlock->setItems($this->getItems());
        }

        return parent::_beforeToHtml();
    }
}