<?php
/** 
 * @category    Mana
 * @package     Mana_Content
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
/**
 * @author Mana Team
 *
 */
interface Mana_Content_Model_Page_Hierarchical  {
    /**
     * @param int $maxDepth
     * @return $this
     */
    public function loadChildPages();

    public function getChildPages();

    /**
     * @param string $key
     * @param null $index
     * @return mixed
     */
    public function getData($key = '', $index = null);
}