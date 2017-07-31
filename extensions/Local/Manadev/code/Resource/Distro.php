<?php

class Local_Manadev_Resource_Distro {
    const XML_PATH_PLATFORM_DIR = 'local_manadev/downloads/platform_%s_dir';

    public function load($platform, $sku) {
        $path = Mage::getStoreConfig(sprintf(self::XML_PATH_PLATFORM_DIR, $platform));

        foreach (glob(BP . "/$path/manadev_{$sku}_*.zip") as $filename) {
            if (($pos = strrpos(pathinfo($filename, PATHINFO_FILENAME), '_')) === false) {
                continue;
            }

            $version = substr(pathinfo($filename, PATHINFO_FILENAME), $pos + 1);

            return compact('version', 'filename');
        }

        return null;
    }
}