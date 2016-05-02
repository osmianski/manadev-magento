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

    public function shaToLicenseNo($sha) {
        return $this->convBase($sha, '0123456789abcdef', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function convBase($numberInput, $fromBaseInput, $toBaseInput) {
        if ($fromBaseInput == $toBaseInput) {
            return $numberInput;
        }
        $fromBase = str_split($fromBaseInput, 1);
        $toBase = str_split($toBaseInput, 1);
        $number = str_split($numberInput, 1);
        $fromLen = strlen($fromBaseInput);
        $toLen = strlen($toBaseInput);
        $numberLen = strlen($numberInput);
        $retval = '';
        if ($toBaseInput == '0123456789') {
            $retval = 0;
            for ($i = 1; $i <= $numberLen; $i++) {
                $retval = bcadd($retval, bcmul(array_search($number[$i - 1], $fromBase), bcpow($fromLen, $numberLen - $i)));
            }

            return $retval;
        }
        if ($fromBaseInput != '0123456789') {
            $base10 = $this->convBase($numberInput, $fromBaseInput, '0123456789');
        } else {
            $base10 = $numberInput;
        }
        if ($base10 < strlen($toBaseInput)) {
            return $toBase[$base10];
        }
        while ($base10 != '0') {
            $retval = $toBase[bcmod($base10, $toLen)] . $retval;
            $base10 = bcdiv($base10, $toLen, 0);
        }

        return $retval;
    }

    /**
     * @return string
     */
    protected function _getAvailableKeysDir() {
        return Mage::getBaseDir() . DS . 'available_keys';
    }
}