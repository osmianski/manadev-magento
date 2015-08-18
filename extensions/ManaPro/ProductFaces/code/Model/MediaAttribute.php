<?php
/**
 * @category    Mana
 * @package     ManaPro_ProductFaces
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

class ManaPro_ProductFaces_Model_MediaAttribute extends Mage_Catalog_Model_Product_Attribute_Backend_Media
{
    public function beforeSave($object) {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!is_array($value) || !isset($value['images'])) {
            return;
        }

        if (!is_array($value['images']) && strlen($value['images']) > 0) {
            $value['images'] = Mage::helper('core')->jsonDecode($value['images']);
        }

        if (!is_array($value['images'])) {
            $value['images'] = array();
        }


        $clearImages = array();
        $newImages = array();
        $existImages = array();
        if ($object->getIsDuplicate() != true) {
            foreach ($value['images'] as &$image) {
                if (!empty($image['removed'])) {
                    $clearImages[] = $image['file'];
                } else {
                    if (!isset($image['value_id'])) {
                        $newFile = $this->_moveImageFromTmp($image['file']);
                        $image['new_file'] = $newFile;
                        $newImages[$image['file']] = $image;
                        $this->_renamedImages[$image['file']] = $newFile;
                        $image['file'] = $newFile;
                    } else {
                        $existImages[$image['file']] = $image;
                    }
                }
            }
        } else {
            // For duplicating we need copy original images.
            $duplicate = array();
            foreach ($value['images'] as &$image) {
                if (!isset($image['value_id'])) {
                    continue;
                }
                $duplicate[$image['value_id']] = $this->_copyImage($image['file']);
                // MANAdev Start:
                //$newImages[$image['file']] = $duplicate[$image['value_id']];
                $newImages[$image['file']] = array();
                $newImages[$image['file']]['new_file'] = $duplicate[$image['value_id']];
                $newImages[$image['file']]['label'] = $image['label'];
                // MANAdev End
            }

            $value['duplicate'] = $duplicate;
        }

        foreach ($object->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $attrData = $object->getData($mediaAttrCode);

            if (in_array($attrData, $clearImages)) {
                $object->setData($mediaAttrCode, false);
            }

            if (in_array($attrData, array_keys($newImages))) {
                $object->setData($mediaAttrCode, $newImages[$attrData]['new_file']);
                $object->setData($mediaAttrCode . '_label', $newImages[$attrData]['label']);
            }

            if (in_array($attrData, array_keys($existImages))) {
                $object->setData($mediaAttrCode . '_label', $existImages[$attrData]['label']);
            }
        }

        $object->setData($attrCode, $value);

        return $this;
    }
}