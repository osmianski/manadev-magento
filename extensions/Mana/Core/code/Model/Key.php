<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Mana_Core_Model_Key {

    public function getPublicKeyByRandom() {
        $keyDir = Mage::getModuleDir(null, 'Mana_Core') . DS . 'key' . DS . 'public';
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
        $privateKey = $this->getPrivateKeyResource($key);

        openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return $signature;
    }

    public function getPublicKeyResource($keyName) {
        $availableKeysDir = Mage::getModuleDir(null, 'Mana_Core') . DS . 'key';
        $publicKeyFile = $availableKeysDir . DS . 'public' . DS . $keyName;
        if (!file_exists($publicKeyFile)) {
            throw new Exception("Key not found.");
        }
        return file_get_contents($publicKeyFile);
    }

    public function getPrivateKeyResource($keyName) {
        $availableKeysDir = Mage::getModuleDir(null, 'Mana_Core') . DS . 'key';
        $privateKeyFile = $availableKeysDir . DS . 'private' . DS . $keyName;
        if (!file_exists($privateKeyFile)) {
            throw new Exception("Key not found.");
        }
        return openssl_pkey_get_private("file://" . $privateKeyFile);
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

    public function ssl_encrypt($source, $type, $key) {
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

	public function ssl_decrypt($source, $type, $key) {
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