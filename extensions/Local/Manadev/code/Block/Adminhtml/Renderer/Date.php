<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Local_Manadev_Block_Adminhtml_Renderer_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {
        $htmlId = $this->_getHtmlId() . microtime(true);

        $format = Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $html = '
            <img src="' . Mage::getDesign()->getSkinUrl('images/grid-cal.gif') . '" alt="" class="v-middle m-date-trigger"'
                . ' id="'.$htmlId.'_trig"'
                . ' title="' . $this->escapeHtml(Mage::helper('adminhtml')->__('Date selector')) . '"/>
            <input type="text" id="' . $htmlId . '" value="' . $this->_getValue($row) . '" class="input-text no-changes m-save-on-change m-date"/>
        ';

        $html.= '<script type="text/javascript">
            Calendar.setup({
                inputField : "'.$htmlId.'",
                ifFormat : "'.$format.'",
                button : "'.$htmlId.'_trig",
                align : "Bl",
                singleClick : true,
                electric: false
            });

            $("'.$htmlId.'_trig").observe("click", showCalendar);

            function showCalendar(event){
                var element = event.element(event);
                var offset = $(element).viewportOffset();
                var scrollOffset = $(element).cumulativeScrollOffset();
                var dimensionsButton = $(element).getDimensions();
                var index = $("widget-chooser") ? $("widget-choser").getStyle("zIndex") : 0;

                $$("div.calendar").each(function(item){
                    if ($(item).visible()) {
                        var dimensionsCalendar = $(item).getDimensions();

                        $(item).setStyle({
                            "zIndex" : index + 1,
                            "left" : offset[0] + scrollOffset[0] - dimensionsCalendar.width
                                + dimensionsButton.width + "px",
                            "top" : offset[1] + scrollOffset[1] + dimensionsButton.height + "px"
                        });
                    };
                });
            };
        </script>';

        return $html;
    }

    protected function _getValue(Varien_Object $row) {
        $date = parent::_getValue($row);

        if($date) {
            $date = new Zend_Date(strtotime($date));

            $date = $this->_convertDate($date);
            $date = $date->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
        }

        return $date;
    }

    protected function _convertDate($date)
    {
        try {
            $dateObj = Mage::app()->getLocale()->date(null, null, null, false);

            //set default timezone for store (admin)
            $dateObj->setTimezone(
                Mage::app()->getStore()->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_TIMEZONE)
            );

            //set begining of day
            $dateObj->setHour(00);
            $dateObj->setMinute(00);
            $dateObj->setSecond(00);

            //set date with applying timezone of store
            $dateObj->set($date, Zend_Date::DATETIME_FULL);

            //convert store date to default date in UTC timezone without DST
            $dateObj->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);

            return $dateObj;
        }
        catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retrieve html id of filter
     *
     * @return string
     */
    protected function _getHtmlId()
    {
        return $this->getColumn()->getGrid()->getId() . '_'. $this->getColumn()->getId();
    }

    /**
     * @return Local_Manadev_Model_Download_Status
     */
    protected function _getDownloadStatusModel() {
        $model = Mage::getSingleton('local_manadev/download_status');

        return $model;
    }
}