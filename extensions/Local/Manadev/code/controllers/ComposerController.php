<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_ComposerController extends Mage_Core_Controller_Front_Action
{
    const XML_PATH_ENABLED = 'local_manadev/downloads/composer';
    const XML_PATH_LOGGING_ENABLED = 'local_manadev/downloads/composer_log';

    public function preDispatch() {
        parent::preDispatch();

        if (!Mage::getStoreConfigFlag(self::XML_PATH_ENABLED)) {
            $this->norouteAction();
        }
    }

    public function repoAction() {
        if (!($key = Mage::app()->getRequest()->getParam('key'))) {
            return $this->renderErrorResponse('Tried to access composer repo without specifying a key');
        }

        if (!preg_match('/[0-9a-z]+/', $key)) {
            return $this->renderErrorResponse("Invalid repo key '$key'");
        }

        if (!($repo = $this->getRepo($key))) {
            return $this->renderErrorResponse();
        }

        $json = json_encode(array(
            'packages' => array(
                'manadev/' . $repo->getData('title') => array(
                    'stable' => array(
                        'name' => 'manadev/' . $repo->getData('title'),
                        'version' => $repo->getVersion(),
                        'dist' => array(
                            'url' => Mage::getUrl("actions/composer/file/key/$key"),
                            'type' => 'zip',
                        ),
                        'autoload' => array(
                            'files' => $this->scanRegistrationPhpFiles($repo),
                            'psr-4' => array(
                                "" => 'app/code/'
                            ),
                        )
                    ),
                ),
            ),
        ));
        $this->getResponse()->appendBody($json);
    }

    public function fileAction() {
        if (!($key = Mage::app()->getRequest()->getParam('key'))) {
            return $this->renderErrorResponse('Tried to access composer repo without specifying a key');
        }

        if (!preg_match('/[0-9a-z]+/', $key)) {
            return $this->renderErrorResponse("Invalid repo key '$key'");
        }

        if (!($repo = $this->getRepo($key))) {
            return $this->renderErrorResponse();
        }

        $dir = $this->prepareTemp();

        $filename = $dir . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($filename, true) !== true) {
           return $this->renderErrorResponse("Couldn't create ZIP file for repo '$key'");
        }

        foreach ($repo->getExtensions() as $extension) {
            if (!$this->addFiles($dir, $extension->getData('filename'))) {
                return $this->renderErrorResponse();
            }
        }

        if (!$this->addLicenseFiles($dir, $repo)) {
           return $this->renderErrorResponse("Couldn't add licenses to ZIP file for repo '$key'");
        }

        $this->fillInZipFile($zip, $dir);
        $zip->close();
        $this->removeDir($dir);
        $this->sendFile($filename);

    }

    protected function getRepo($key) {
        if (!($data = $this->getRepoResource()->loadByKey($key))) {
            $this->log("Repo with key '$key' not found");
            return null;
        }

        $repo = $this->createRepo($data);
        $isDownloadSubscriptionActive = false;
        foreach ($this->getDownloadableItemResource()->getItemsByComposerRepoId($repo->getId()) as $itemData) {
            if ($itemData['platform'] != '2') {
                continue;
            }

            if ($itemData['status'] == Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE_TIL ||
                $itemData['status'] == Local_Manadev_Model_Download_Status::M_LINK_STATUS_AVAILABLE)
            {
                $isDownloadSubscriptionActive = true;
            }

            if (!$fileData = $this->getDistroResource()->load($itemData['platform'], $itemData['sku'])) {
                $this->log("Extension '{$itemData['sku']}' for repo with key '$key': file not found");
                return null;
            }

            $repo->addExtension($this->createExtension(array_merge($itemData, $fileData)));
        }

        if (!$isDownloadSubscriptionActive) {
                $this->log("No extension in repo with key '$key' has active download subscription");

                return null;
        }

        return $repo;
    }

    protected function renderErrorResponse($message = null) {
        if ($message) {
            $this->log($message);
        }

        $shortError = "Repo not found or download/support subscription expired.";
        $error = "Composer repository with specified key doesn't exist or " .
            "one of extensions list in specified composer repository is not available for download due to " .
            "expired support subscription. Please make sure repository key is correct and support " .
            "subscription is active.";

        $this->getResponse()->setHeader('HTTP/1.1', "404 $shortError");
        $this->getResponse()->setHeader('Status', "404 $shortError");

        $this->getResponse()->appendBody($error);
    }

    protected function log($message) {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_LOGGING_ENABLED)) {
            return;
        }

        Mage::log($_SERVER['REMOTE_ADDR'] . ": " . $message, Zend_Log::DEBUG, 'composer.log');
    }

    protected function prepareTemp() {
        $dir = BP . '/var/downloads';

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        foreach (glob($dir.'/*.zip') as $filename) {
            if (time() - filemtime($filename) > 24 * 3600) {
                unlink($filename);
            }
        }

        $dir .= '/' . bin2hex(openssl_random_pseudo_bytes(20));
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        return $dir;
    }

    protected function sendFile($filename) {
        $this->getResponse()
            ->setHttpResponseCode(200)->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', mime_content_type($filename), true)
            ->setHeader('Content-Length', filesize($filename))
            ->setHeader('Content-Disposition', 'inline; filename="'. basename($filename) . '"', true);

        // send headers and raw file bytes
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        readfile($filename);
    }

    protected function addFiles($dir, $sourceZipFilename) {
        $zip = new ZipArchive();

        if ($zip->open($sourceZipFilename) !== true) {
            $this->log("Couldn't open ZIP '$sourceZipFilename'");
            return false;
        }
        if ($zip->extractTo($dir) !== true) {
            $this->log("Couldn't extract ZIP '$sourceZipFilename' to '$dir'");

            return false;
        }

        $zip->close();
        return true;
    }

    /**
     * @param string $dir
     * @param Local_Manadev_Model_Composer_Repo $repo
     * @return bool
     */
    protected function addLicenseFiles($dir, $repo) {
        $result = false;
        $dir .= '/app/code/Manadev/Core/license/';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        foreach ($repo->getExtensions() as $extension) {
            $licenseText = "{$extension->getData('name')}\n" .
                "Version {$extension->getData('version')}\n" .
                "\nLicensed to {$extension->getData('customer_firstname')}\n";

            $s = "{$extension->getData('sku')}|{$extension->getData('m_license_verification_no')}";
            $r = '';
            for ($i = 0; $i < strlen($s); $i++) {
                $r .= ($i + 1 == strlen($s) && $i % 2 == 0) ? $s[$i] : ($i % 2 == 0 ? $s[$i + 1] : $s[$i - 1]);
            }
            $s = base64_encode(implode(array_map(function ($r) { return chr(ord($r) + 1); }, str_split($r))));
            $licenseText .= "\n$s\n";

            file_put_contents("{$dir}/{$extension->getData('m_license_verification_no')}.license", $licenseText);

            if ($extension->getData('m_key_public')) {
                $publicKey = $extension->getData('m_key_public');
                $privateKey = $extension->getData('m_key_private');
            } else {
                $keys = $this->helper()->generateKeys();
                $publicKey = $keys['public'];
                $privateKey = $keys['private'];
            }
            file_put_contents("{$dir}/{$extension->getData('m_license_verification_no')}.public.pem", $publicKey);
            file_put_contents("{$dir}/{$extension->getData('m_license_verification_no')}.private.pem", $privateKey);
            $result = true;
        }

        return $result;
    }

    protected function fillInZipFile($zip, $sourcePath, $targetPath = '') {
        if ($targetPath) {
            $targetPath .= '/';
        }
        foreach (new \DirectoryIterator($sourcePath) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->fillInZipFile($zip, $sourcePath . '/' . $fileInfo->getBasename(),
                    $targetPath . $fileInfo->getBasename());
            }
            else {
                $zip->addFile($fileInfo->getPathname(), str_replace('\\', '/',
                    $targetPath . $fileInfo->getBasename()));
            }
        }
    }

    protected function removeDir($path) {
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->removeDir($path . '/' . $fileInfo->getBasename());
            }
            else {
                unlink($path . '/' . $fileInfo->getBasename());
            }
        }
        rmdir($path);
    }

    /**
     * @param Local_Manadev_Model_Composer_Repo $repo
     * @return array
     */
    protected function scanRegistrationPhpFiles($repo) {
        $result = array();

        foreach ($repo->getExtensions() as $extension) {
            $zip = new ZipArchive();
            if ($zip->open($extension->getData('filename')) !== true) {
                $this->log("Couldn't open ZIP '{$extension->getData('filename')}'");

                continue;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (basename($filename) == 'registration.php') {
                    $result[$filename] = true;
                }
            }

            $zip->close();
        }

        return array_keys($result);
    }

    /**
     * @return Local_Manadev_Resource_Composer_Repo
     */
    protected function getRepoResource() {
        return Mage::getResourceSingleton('local_manadev/composer_repo');
    }

    /**
     * @return Local_Manadev_Model_Composer_Repo
     */
    protected function createRepo($data) {
        $result = Mage::getModel('local_manadev/composer_repo');
        $result->setData($data);
        return $result;
    }

    /**
     * @return Local_Manadev_Resource_Downloadable_Item
     */
    protected function getDownloadableItemResource() {
        return Mage::getResourceSingleton('downloadable/link_purchased_item');
    }

    /**
     * @return Local_Manadev_Resource_Distro
     */
    protected function getDistroResource() {
        return Mage::getResourceSingleton('local_manadev/distro');
    }

    /**
     * @return Local_Manadev_Model_Composer_Extension
     */
    protected function createExtension($data) {
        $result = Mage::getModel('local_manadev/composer_extension');
        $result->setData($data);

        return $result;
    }

    /**
     * @return Local_Manadev_Helper_Data
     */
    protected function helper() {
        return Mage::helper('local_manadev');
    }
}