<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Key {

    public function generateSignature($data, $key) {
        $privateKey = $this->getPrivateKeyResource($key);

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signature;
    }

    public function verifySignature($data, $signature, $key) {
        $publicKey = $this->getPublicKeyResource($key);

        return openssl_verify($data, $signature, $publicKey, "sha256WithRSAEncryption");
    }

    public function generateSignatureFromAvailableKeys($data, $key) {
        $privateKey = $this->getPrivateKeyResource($key);

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signature;
    }

    public function verifySignatureFromAvailableKeys($data, $signature, $key) {
        $publicKey = $this->getPublicKeyResource($key);

        return openssl_verify($data, $signature, $publicKey, "sha256WithRSAEncryption");
    }

    public function getPublicKeyResource($keyName) {
        $publicKeyFile = $this->_getAvailableKeysDir() . DS . 'public' . DS . $keyName;
        if (!file_exists($publicKeyFile)) {
            throw new Exception("Key not found.");
        }
        return file_get_contents($publicKeyFile);
    }

    public function getPrivateKeyResource($keyName) {
        $privateKeyFile = $this->_getAvailableKeysDir() . DS . 'private' . DS . $keyName;
        if (!file_exists($privateKeyFile)) {
            throw new Exception("Key not found.");
        }
        return openssl_pkey_get_private("file://" . $privateKeyFile);
    }

    /**
     * @param $linkFile
     * @return string
     */
    public function getVersionFromZipFile($linkFile) {
        preg_match('/\d{2}.\d{2}.\d{2}.\d{2}/', $linkFile, $matches);
        if(count($matches)) {
            return reset($matches);
        }

        throw new Exception("Could not determine extension version from zip file name");
    }

    /**
     * @return string
     */
    protected function _getAvailableKeysDir() {
        return Mage::getBaseDir() . DS . 'available_keys';
    }
}