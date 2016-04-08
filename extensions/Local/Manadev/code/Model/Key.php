<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Model_Key {

    public function getPublicKeyByRandom() {
        $keyDir = $this->_getManaCoreDir() . DS . 'key' . DS . 'public';
        $availableKeys = array();

        if (is_dir($keyDir)) {
            foreach (scandir($keyDir) as $file) {
                if (in_array($file, array('.', '..'))) {
                    continue;
                }

                $availableKeys[] = $file;
            }
        }

        $randomSelectedKey = microtime() % count($availableKeys);

        return $availableKeys[$randomSelectedKey];
    }

    public function generateSignature($data, $key) {
        $availableKeysDir = $this->_getManaCoreDir() . DS . 'key';
        $privateKeyFile = $availableKeysDir . DS . 'private' . DS . $key;
        $privateKey = openssl_pkey_get_private("file://" . $privateKeyFile);

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signature;
    }

    public function verifySignature($data, $signature, $key) {
        $availableKeysDir = $this->_getManaCoreDir() . DS . 'key';

        $publicKeyFile = $availableKeysDir . DS . 'public' . DS . $key;
        $publicKey = file_get_contents($publicKeyFile);

        return openssl_verify($data, $signature, $publicKey, "sha256WithRSAEncryption");
    }

    public function generateSignatureFromAvailableKeys($data, $key) {
        $availableKeysDir = $this->_getAvailableKeysDir();
        $privateKeyFile = $availableKeysDir . DS . 'private' . DS . $key;
        $privateKey = openssl_pkey_get_private("file://" . $privateKeyFile);

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signature;
    }

    public function verifySignatureFromAvailableKeys($data, $signature, $key) {
        $availableKeysDir = $this->_getAvailableKeysDir();

        $publicKeyFile = $availableKeysDir . DS . 'public' . DS . $key;
        $publicKey = file_get_contents($publicKeyFile);

        return openssl_verify($data, $signature, $publicKey, "sha256WithRSAEncryption");
    }

    public function dataToString($data) {
        return base64_encode(json_encode($data));
    }

    public function stringToData($string) {
        return json_decode(base64_decode($string));
    }

    public function dataToStringSerialize($data) {
        return base64_encode(serialize($data));
    }

    public function stringToDataSerialize($string) {
        return unserialize(base64_decode($string));
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
    protected function _getManaCoreDir() {
        return Mage::getModuleDir(null, 'Mana_Core');
    }

    /**
     * @return string
     */
    protected function _getAvailableKeysDir() {
        return Mage::getBaseDir() . DS . 'available_keys';
    }

    private function ssl_encrypt($source, $type, $key) {
		$maxlength = 256;
		$output = '';
		while ($source) {
			$input = substr($source, 0, $maxlength);
			$source = substr($source, $maxlength);
			if ($type == 'private') {
				$ok = openssl_private_encrypt($input, $encrypted, $key);
			} else {
				$ok = openssl_public_encrypt($input, $encrypted, $key);
			}

			$output .= $encrypted;
		}

		return $output;
	}

	private function ssl_decrypt($source, $type, $key) {
		$maxlength = 512;
		$output = '';
		while ($source) {
			$input = substr($source, 0, $maxlength);
			$source = substr($source, $maxlength);
			if ($type == 'private') {
				$ok = openssl_private_decrypt($input, $out, $key);
			} else {
				$ok = openssl_public_decrypt($input, $out, $key);
			}

			$output .= $out;
		}

		return $output;
	}
}