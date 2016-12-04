<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer edit block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Local_Manadev_Block_Customer_Edit extends Mage_Adminhtml_Block_Customer_Edit
{
    public function __construct()
    {
        parent::__construct();

        $filter = 'customer_name=' . Mage::registry('current_customer')->getName();
        $filter = base64_encode($filter);
        $url = Mage::helper('adminhtml')->getUrl('adminhtml/license/issuedLicenses', array('filter' => $filter));

        $this->_addButton('view_licenses', array(
            'label' => Mage::helper('local_manadev')->__("View Licenses"),
            'onclick' => 'setLocation(\''. $url .'\')',
        ));
    }
}
