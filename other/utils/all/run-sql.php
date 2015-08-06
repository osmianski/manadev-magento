<?php

require_once '../app/Mage.php';
umask(0);
Mage::app('', 'store');

$sql = array();
foreach (glob('run/*.sql') as $filename) {
    $sql[$filename] = array(
        'query' => file_get_contents($filename),
        'timings' => array()
    );
}

/* @var $db Varien_Db_Adapter_Pdo_Mysql */
$db = Mage::getSingleton('core/resource')->getConnection('read');

for ($i = 0; $i < 5; $i++) {
    foreach (array_keys($sql) as $filename) {
        $startedAt = microtime(true);
        $db->query($sql[$filename]['query']);
        $sql[$filename]['timings'][] = microtime(true) - $startedAt;
    }
}

foreach ($sql as $filename => $data) {
    echo sprintf("%s: %.4f\n", $filename, array_sum($data['timings']) / count($data['timings']));
}