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
    protected $skip = array('.', '..');

    public function getFilename($relativeUrl, $type, $noExistanceCheck = false) {
		$result = $this->getBasePath($type).DS.str_replace('/', DS, $relativeUrl);
        if (!is_dir(dirname($result))) {
        	mkdir(dirname($result), 0777, true);
        }
		return $noExistanceCheck || file_exists($result) ? $result : false;
	}
	public function getBaseUrl($type, $baseUrl = null, $storeId = null) {
	    if (!$baseUrl) {
            $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl('media');
	    }
		return $baseUrl.'m-'.str_replace(DS, '/', $type);
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
	public function getUrl($relativeUrl, $type, $baseUrl = null) {
		if (is_array($type)) {
			foreach ($type as $candidate) {
				if ($url = $this->getUrl($relativeUrl, $candidate, $baseUrl)) {
					return $url;
				}
			}
			return false;
		}
		else {
			if ($this->getFilename($relativeUrl, $type)) {
				return $this->getBaseUrl($type, $baseUrl).'/'.str_replace(DS, '/', $relativeUrl);
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
		if (!$this->_fileExists(sprintf($checkTemplate, ''))) {
			return sprintf($resultTemplate, '');
		}
		$i = 1;
		while (true) {
			if (!$this->_fileExists(sprintf($checkTemplate, '-'.$i))) {
				return sprintf($resultTemplate, '-'.$i);
			}
			$i++;
		}
	}

    protected function _fileExists($filename) {
        $filename = str_replace('\\', '/', $filename);
        if (file_exists($filename)) {
            return true;
        }
        if (($pos = strpos($filename, '/m-temp/')) !== false) {
            $filename = substr($filename, 0, $pos) . '/m-' . substr($filename, $pos + strlen('/m-temp/'));
            return file_exists($filename);
        }
        else {
            return false;
        }
    }
    public function walkRecursively($dir, $callback) {
        if (file_exists($dir)) {
            $this->_walkRecursively($dir, $callback);
        }
    }

    protected function _walkRecursively($dir, $callback) {
        if ($handle = opendir($dir)) {
            $files = array();
            while (false !== ($file = readdir($handle))) {
                $files[] = $file;
            }
            closedir($handle);
            foreach ($files as $file) {
                if (!in_array($file, $this->skip)) {
                    $filename = $dir . '/' . $file;
                    $isDir = is_dir($filename);
                    if (call_user_func($callback, $dir . '/' . $file, $isDir) && $isDir) {
                        $this->_walkRecursively($filename, $callback);
                    }
                }
            }
        }
    }

    public function shouldRenderImage($relativeUrl) {
        return $this->getFilename($relativeUrl, 'image') !== false;
    }
    public function renderImageAttributes($relativeUrl, $width = null, $height = null) {
        if ($filename = $this->getFilename($relativeUrl, 'image')) {
            if ($width || $height) {
                $processor = new Varien_Image($filename);
                $newRelativeUrl = 'w'.($width ? $width : 'x').'h'.($height ? $height : 'x').'/'.$relativeUrl;
                if (!$width) {
                    $width = $processor->getOriginalWidth();
                }
                if (!$height) {
                    $height = $processor->getOriginalHeight();
                }
                $processor->keepAspectRatio(true);
                $processor->resize($width, $height);
                $processor->save($this->getFilename($newRelativeUrl, 'image', true));
                return "src=\"{$this->getUrl($newRelativeUrl, 'image')}\" ".
                    "width=\"{$processor->getOriginalWidth()}\" ".
                    "height=\"{$processor->getOriginalHeight()}\"";
            }
            else {
                return "src=\"{$this->getUrl($relativeUrl, 'image')}\"";
            }
        }
    }
}