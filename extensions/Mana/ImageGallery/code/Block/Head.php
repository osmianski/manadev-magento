<?php

class Mana_ImageGallery_Block_Head extends Mage_Core_Block_Template
{
	public function getLocale()
	{
		return Mage::app()->getTranslator()->getLocale();
	}
//	public function getLocalizedSkinUrl($path, $fileName)
//	{
//		$relativeFileNames = array(
//			$path.$this->getLocale().'/'.$fileName, 
//			$path.'en-US/'.$fileName, 
//			$path.$fileName);
//		foreach ($relativeFileNames as $relativeFileName) {
//			if (Mage::getDesign()->validateFile($relativeFileName, array(
//				'_type' => 'skin'))) {
//				return $this->getSkinUrl($relativeFileName);
//			}
//		}
//		return false;
//	}
}