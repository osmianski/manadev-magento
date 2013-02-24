<?php
/**
 * @category    Mana
 * @package     Mana_Admin
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Enter description here ...
 * @author Mana Team
 *
 */
class Mana_Admin_UploadController extends Mage_Adminhtml_Controller_Action {
	protected function _saveFile($type, $extensions = array()) {
		global $_FILES;
		/* @var $core Mana_Core_Helper_Data */ $core = Mage::helper(strtolower('Mana_Core'));
		/* @var $files Mana_Core_Helper_Files */ $files = Mage::helper(strtolower('Mana_Core/Files'));
		try {
			$isXhr = $this->getRequest()->getParam('qqfile') != null;
			if (!$isXhr && !isset($_FILES['qqfile'])) {
				throw new Exception($this->__('No files were uploaded.'));
			}
			$filename = $isXhr ? $this->getRequest()->getParam('qqfile') : $_FILES['qqfile']['name'];
			$size = $isXhr ? (int)$_SERVER["CONTENT_LENGTH"] : $_FILES['qqfile']['size'];
			$fileinfo = pathinfo($filename);
			if (count($extensions) && !in_array(strtolower($fileinfo['extension']), $extensions)) {
				throw new Exception($this->__('Invalid file extension %s.', $fileinfo['extension']));	
			}
			if ($size == 0) {
				throw new Exception($this->__('File is empty.'));	
			}
			if ($size > min($core->getIniByteValue('post_max_size'), $core->getIniByteValue('upload_max_filesize'))) {
				throw new Exception($this->__('File is too large. Current maximum size is %d bytes.'));	
			}
			
			$relativeUrl = $files->getNewUrl($filename, $type);
			$targetFileName = $files->getFilename($relativeUrl, $type, true);
			if ($isXhr) {
		        // save upload as a temp file
		        $input = fopen("php://input", "r");
		        $temp = tmpfile();
		        $realSize = stream_copy_to_stream($input, $temp);
		        fclose($input);
		        if ($realSize != $size) {
		        	throw new Exception($this->__("File size %d was expected, but %d was actually sent", $size, $realSize));	
		        }
		        
		        // move temp file to target location
		        $target = fopen($targetFileName, "w");        
		        fseek($temp, 0, SEEK_SET);
		        stream_copy_to_stream($temp, $target);
		        fclose($target);
	        }
	        else {
        		if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $targetFileName)) {
        			throw new Exception("The upload was cancelled, or server error encountered.");
        		}
	        }
			
	        $url = $files->getUrl($relativeUrl, $type);
	        $id = $this->getRequest()->getParam('id');
			$response = new Varien_Object(compact('relativeUrl', 'url', 'id'));
			$this->getResponse()->setBody($response->toJson());
		}
		catch (Exception $e) {
			$response = new Varien_Object(array('error' => $e->getMessage()));
			$this->getResponse()->setBody($response->toJson());
		}
	}
	public function startAction() {
		switch ($this->getRequest()->getParam('type')) {
			case 'image':
				$this->_saveFile('temp/image', array('jpg', 'jpeg', 'gif', 'png'));
				break;
			default:
				throw new Exception('Not implemented');
		}
		
	}
}