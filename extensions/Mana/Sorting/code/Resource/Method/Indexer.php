<?php
/**
 * @category    Mana
 * @package     Mana_Sorting
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
class Mana_Sorting_Resource_Method_Indexer extends Mana_Sorting_Resource_Method_Abstract{

    public function process($options) {
        $this->_calculateFinalGlobalSettings($options);
        $this->_calculateFinalStoreLevelSettings($options);
    }

    public function reindexAll() {
        $this->process(array('reindex_all' => true));
    }

    protected  function _calculateFinalStoreLevelSettings($options) {
        if(!isset($options['method_id']) &&
            !isset($options['method_store_custom_settings_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all']))
        {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();

        foreach (Mage::app()->getStores() as $store) {
            /* @var $store Mage_Core_Model_Store */
            if (isset($options['store_id']) && $store->getId() != $options['store_id']) {
                continue;
            }

            $seoifyHelper = $this->coreHelper()->isManadevSeoInstalled()
            ? $this->seoHelper()
            : $dbHelper;

            $fields = array(
                'method_id' => "`m`.`id`",
                'store_id' => $store->getId(),
                'method_store_custom_settings_id' => "`msc`.`id`",
                'is_active' => "IF({$dbHelper->isCustom('msc', Mana_Sorting_Model_Method_Abstract::DM_IS_ACTIVE)},
                    `msc`.`is_active`,
                    `m`.`is_active`
                )",
                'position' => "IF({$dbHelper->isCustom('msc', Mana_Sorting_Model_Method_Abstract::DM_POSITION)},
                    `msc`.`position`,
                    `m`.`position`
                )",
                'title' => "IF({$dbHelper->isCustom('msc', Mana_Sorting_Model_Method_Abstract::DM_TITLE)},
                    `msc`.`title`,
                    `m`.`title`
                )",
                'url_key' => "IF({$dbHelper->isCustom('msc', Mana_Sorting_Model_Method_Abstract::DM_URL_KEY)},
                    `msc`.`url_key`,
                    IF({$dbHelper->isCustom('msc', Mana_Sorting_Model_Method_Abstract::DM_TITLE)},
                        {$seoifyHelper->seoifyExpr("`msc`.`title`")},
                        {$seoifyHelper->seoifyExpr("`m`.`title`")}
                    )
                )",
            );
            for($x=0;$x<=4;$x++) {
                $constant = constant("Mana_Sorting_Model_Method_Abstract::DM_ATTRIBUTE_ID_{$x}");
                $fields['attribute_id_'.$x] = "IF({$dbHelper->isCustom('msc', $constant)},
                    `msc`.`attribute_id_$x`,
                    `m`.`attribute_id_$x`
                )";
                $constant = constant("Mana_Sorting_Model_Method_Abstract::DM_ATTRIBUTE_ID_{$x}_SORTDIR");
                $fields["attribute_id_{$x}_sortdir"] = "IF({$dbHelper->isCustom('msc', $constant)},
                    `msc`.`attribute_id_{$x}_sortdir`,
                    `m`.`attribute_id_{$x}_sortdir`
                )";
                $constant = constant("Mana_Sorting_Model_Method_Abstract::DM_SORTING_METHOD_{$x}");
                $fields["sorting_method_{$x}"] = "IF({$dbHelper->isCustom('msc', $constant)},
                    `msc`.`sorting_method_{$x}`,
                    `m`.`sorting_method_{$x}`
                )";
            }

            $select = $db->select();
            $select
                ->from(array('m' => $this->getTable('mana_sorting/method')), null)
                ->joinLeft(
                    array('msc' => $this->getTable('mana_sorting/method_storeCustomSettings')),
                    $db->quoteInto("`msc`.`method_id` = `m`.`id` AND `msc`.`store_id` = ?", $store->getId()),
                    null
                );

            $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));

            if (isset($options['method_id'])) {
                $select->where("`m`.`id` = ?", $options['method_id']);
            }

            // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
            $selectSql = $select->__toString();
            $sql = $select->insertFromSelect($this->getTable('mana_sorting/method_store'), array_keys($fields));

            // run the statement
            $db->exec($sql);
        }

    }

    protected function _calculateFinalGlobalSettings($options) {
        if(!isset($options['method_id']) &&
            !isset($options['method_store_custom_settings_id']) &&
            !isset($options['store_id']) &&
            empty($options['reindex_all']))
        {
            return;
        }

        $db = $this->_getWriteAdapter();
        $dbHelper = $this->dbHelper();

        $seoifyExpr = $this->coreHelper()->isManadevSeoInstalled()
            ? $this->seoHelper()->seoifyExpr("`m`.`title`")
            : $dbHelper->seoifyExpr("`m`.`title`");

        $fields = array(
            'id' => "`m`.`id`",
            'url_key' => "IF({$dbHelper->isCustom('m', Mana_Sorting_Model_Method_Abstract::DM_URL_KEY)},
                        `m`.`url_key`,
                        {$seoifyExpr}
                    )",
        );


        $select = $db->select();
        $select->from(array('m' => $this->getTable('mana_sorting/method')), null);
        $select->columns($this->dbHelper()->wrapIntoZendDbExpr($fields));
        
        if (isset($options['method_id'])) {
            $select->where("`m`.`id` = ?", $options['method_id']);
        }

        // convert SELECT into UPDATE which acts as INSERT on DUPLICATE unique keys
        $selectSql = $select->__toString();
        $sql = $select->insertFromSelect($this->getTable('mana_sorting/method'), array_keys($fields));

        // run the statement
        $db->exec($sql);
    }

    /**
     * Resource initialization
     */
    protected function _construct() {
        $this->_setResource('mana_sorting');
    }

}