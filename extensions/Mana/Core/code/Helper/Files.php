<?php
/**
 * @category    Mana
 * @package     Mana_Core
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Core_Helper_Files extends Mage_Core_Helper_Abstract {
	public function getFilename($relativeUrl, $type, $noExistanceCheck = false) {
		$result = $this->getBasePath($type).DS.str_replace('/', DS, $relativeUrl);
        if (!is_dir(dirname($result))) {
        	mkdir(dirname($result), 0777, true);
        }
		return $noExistanceCheck || file_exists($result) ? $result : false;
	}
	public function getBaseUrl($type) {
		return Mage::getBaseUrl('media').'m-'.str_replace(DS, '/', $type);
	}
	public function getBasePath($type) {
		return Mage::getConfig()->getOptions()->getMediaDir().DS.'m-'.str_replace('/', DS, $type);
	}
	public function getType($relativeUrl, $types) {
		if (!is_array($types)) {
			$types = array($types);
		}
		foreach ($types as $candidate) {
			if ($filename = $this->getFilename($relativeUrl, $candidate)) {
				return $candidate;
			}
		}
		return false;
	}
	public function getUrl($relativeUrl, $type) {
		if (is_array($type)) {
			foreach ($type as $candidate) {
				if ($url = $this->getUrl($relativeUrl, $candidate)) {
					return $url;
				}
			}
			return false;
		}
		else {
			if ($this->getFilename($relativeUrl, $type)) {
				return $this->getBaseUrl($type).'/'.str_replace(DS, '/', $relativeUrl);
			}
			else {
				return false;
			}
		}
	}
	public function getNewUrl($filename, $type) {
		$fileinfo = pathinfo(strtolower($filename));
		$hash = sha1($fileinfo['filename']);
		$first = substr($hash, strlen($hash) - 2, 1);
		$second = substr($hash, strlen($hash) - 1);
		$resultTemplate = "$first/$second/{$fileinfo['filename']}%s.{$fileinfo['extension']}";
		$checkTemplate = $this->getFileName($resultTemplate, $type, true);
		if (!file_exists(sprintf($checkTemplate, ''))) {
			return sprintf($resultTemplate, '');
		}
		$i = 1;
		while (true) {
			if (!file_exists(sprintf($checkTemplate, '-'.$i))) {
				return sprintf($resultTemplate, '-'.$i);
			}
			$i++;
		}
	}
}