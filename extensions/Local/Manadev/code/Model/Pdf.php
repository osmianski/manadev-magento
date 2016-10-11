<?php

class Local_Manadev_Model_Pdf extends Mage_Sales_Model_Order_Pdf_Abstract
{
	protected function __($message) {
		static $messages = array(
			'Price' => 'Kaina',
			'Invoice # ' => 'Sąskaita-faktūra Nr. ',
			'Invoice Date: ' => 'Sąskaitos išrašymo data: ',
			'Services' => 'Paslaugos',
			'Qty' => 'Kiekis',
			'Subtotal' => 'Suma',
			'Supplier' => 'Tiekėjas',
			'Customer' => 'Pirkėjas',
			'pdf' => 'pdf_lt',
			'sales/identity/address' => 'sales/identity/address_lt',
			'Magento development services' => 'Magento programavimo paslaugos',
			'According to order #%s, created at %s' => 'Pagal užsakymą Nr. %s, sukurtą %s',
			'Total:' => 'VISO:',
			'Total with VAT:' => 'VISO su PVM:',
			'Total without VAT:' => 'VISO be PVM:',
			'%s%% VAT:' => '%s%% PVM:',
			'%s-LTL exchange rate:' => '%s-LTL keitimo kursas:',
			'Paid by:' => 'Apmokėjo:',
			'Issued by: director Vladislav Ošmianskij' => 'Išrašė: direktorius Vladislav Ošmianskij',
			'Credit Memo # ' => 'Kreditinė sąskaita Nr. ',
			'Date: ' => 'Išrašymo data: ',
			'Refunded to:' => 'Mokėjimas gražintas:',
		);
		$args = func_get_args();
		if ($this->getLanguage() == 'lt') {
			$args[0] = $messages[$message];
		}
		return call_user_func_array('sprintf', $args);
	}
	
	protected $_locales;
	/**
	 * Enter description here ...
	 * @return Zend_Locale
	 */
	protected function _locale() {
		if (!$this->_locales) {
			$this->_locales = array(
				'lt' => new Zend_Locale('lt_LT'),
				'en' => new Zend_Locale('en_US'),
			);
		}
		if (isset($this->_locales[$this->getLanguage()])) {
			return $this->_locales[$this->getLanguage()];
		}
		else {
			throw new Exception('Not implemented');
		}
	}
	protected $_currencies = array();
	/**
	 * Enter description here ...
	 * @param unknown_type $currency
	 * @return Zend_Currency
	 */
	protected function _currency($currency) {
		if (!isset($this->_currencies[$currency])) {
			$this->_currencies[$currency] = new Zend_Currency($currency, $this->_locale());
		}
		return $this->_currencies[$currency];
	}
	protected function _date($date, $timezone = false) {
		$date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT, $this->_locale(), $timezone);
		$format = $this->_locale()->getTranslation(Mage_Core_Model_Locale::FORMAT_TYPE_LONG, 'date', $this->_locale());
		return $date->toString($format, $this->_locale());
	}
	protected function _price($price, $currency) {
		return $this->_currency($currency)->toCurrency($price);
	}
	protected function _number($number) {
		return Zend_Locale_Format::toNumber($number, array('locale' => $this->_locale()));
	}
	protected function _words($price, $currency) {
		$result = '';
		static $hiLabels = array(
			1000000000 => array('milijardas', 'milijardai', 'milijardų'),
			1000000 => array('milijonas', 'milijonai', 'milijonų'),
			1000 => array('tūkstantis', 'tūkstančiai', 'tūkstančių'),
		);
		static $currencyLabels = array(
			'USD' => array('doleris', 'doleriai', 'dolerių'),
			'LTL' => array('litas', 'litai', 'litų'),
		); 
		$value = (int) floor($price);
		foreach ($hiLabels as $threshold => $labels) {
			if ($value >= $threshold) {
				list($loResult, $hiIndex) = $this->_loWords((int) floor($value / $threshold) % 1000);
				if ($loResult) {
					if ($result) $result .= ' ';
					$result .= $loResult . ' ' . $labels[$hiIndex];
				}
			}
		}
		
		list($loResult, $hiIndex) = $this->_loWords($value % 1000);
		if (!$loResult) $loResult = 'nulis';
		if ($result) $result .= ' ';
		$result .= $loResult . ' ' . $currencyLabels[$currency][$hiIndex] . sprintf(' %d ct.', ((int) floor($price * 100)) % 100);
		return $result;
	}
	protected function _loWords($value) {
		$hiIndex = 2;
		$loResult = '';
		switch ((int) floor($value / 100)) {
			case 0: break;
			case 1: $loResult .= 'vienas šimtas'; break;
			case 2: $loResult .= 'du šimtai'; break;
			case 3: $loResult .= 'trys šimtai'; break;
			case 4: $loResult .= 'keturi šimtai'; break;
			case 5: $loResult .= 'penki šimtai'; break;
			case 6: $loResult .= 'šeši šimtai'; break;
			case 7: $loResult .= 'septyni šimtai'; break;
			case 8: $loResult .= 'aštuoni šimtai'; break;
			case 9: $loResult .= 'devyni šimtai'; break;
		}
		$handled = false;
		$value = $value % 100;
		switch ((int) floor($value / 10)) {
			case 0: break;
			case 1: {
					switch ($value) {
						case 10: if ($loResult) {$loResult .= ' '; } $loResult .= 'dešimt'; break;
						case 11: if ($loResult) {$loResult .= ' '; } $loResult .= 'vienuolika'; break;
						case 12: if ($loResult) {$loResult .= ' '; } $loResult .= 'dvylika'; break;
						case 13: if ($loResult) {$loResult .= ' '; } $loResult .= 'trylika'; break;
						case 14: if ($loResult) {$loResult .= ' '; } $loResult .= 'keturiolika'; break;
						case 15: if ($loResult) {$loResult .= ' '; } $loResult .= 'penkiolika'; break;
						case 16: if ($loResult) {$loResult .= ' '; } $loResult .= 'šešiolika'; break;
						case 17: if ($loResult) {$loResult .= ' '; } $loResult .= 'septyniolika'; break;
						case 18: if ($loResult) {$loResult .= ' '; } $loResult .= 'aštuoniolika'; break;
						case 19: if ($loResult) {$loResult .= ' '; } $loResult .= 'devyniolika'; break;
					}
					$handled = true;
				} 
				break;
			case 2: if ($loResult) {$loResult .= ' '; } $loResult .= 'dvidešimt'; break;
			case 3: if ($loResult) {$loResult .= ' '; } $loResult .= 'trisdešimt'; break;
			case 4: if ($loResult) {$loResult .= ' '; } $loResult .= 'keturiasdešimt'; break;
			case 5: if ($loResult) {$loResult .= ' '; } $loResult .= 'penkiasdešimt'; break;
			case 6: if ($loResult) {$loResult .= ' '; } $loResult .= 'šešiasdešimt'; break;
			case 7: if ($loResult) {$loResult .= ' '; } $loResult .= 'septyniasdešimt'; break;
			case 8: if ($loResult) {$loResult .= ' '; } $loResult .= 'aštuoniasdešimt'; break;
			case 9: if ($loResult) {$loResult .= ' '; } $loResult .= 'devyniasdešimt'; break;
		}
		if (!$handled) {
			switch ($value % 10) {
				case 0: break;
				case 1: if ($loResult) {$loResult .= ' '; } $loResult .= 'vienas'; $hiIndex = 0; break;
				case 2: if ($loResult) {$loResult .= ' '; } $loResult .= 'du'; $hiIndex = 1; break;
				case 3: if ($loResult) {$loResult .= ' '; } $loResult .= 'trys'; $hiIndex = 1; break;
				case 4: if ($loResult) {$loResult .= ' '; } $loResult .= 'keturi'; $hiIndex = 1; break;
				case 5: if ($loResult) {$loResult .= ' '; } $loResult .= 'penki'; $hiIndex = 1; break;
				case 6: if ($loResult) {$loResult .= ' '; } $loResult .= 'šeši'; $hiIndex = 1; break;
				case 7: if ($loResult) {$loResult .= ' '; } $loResult .= 'septyni'; $hiIndex = 1; break;
				case 8: if ($loResult) {$loResult .= ' '; } $loResult .= 'aštuoni'; $hiIndex = 1; break;
				case 9: if ($loResult) {$loResult .= ' '; } $loResult .= 'devyni'; $hiIndex = 1; break;
			}
		}
		return array($loResult, $hiIndex);
	}

    protected function _print($document) {
        $templates = explode("\n", Mage::getStoreConfig('local_manadev/accounting/invoice_template'));
        $template = array_shift($templates);
        foreach ($templates as $oldTemplate) {
            list($templateDate, $templateName) = explode(':', $oldTemplate);
            $templateDate = trim($templateDate);
            $templateName = trim($templateName);
            if ($this->date > $templateDate) {
                break;
            }
            $template = $templateName;
        }
        $method = '_print'.$template;
        $this->$method($document);
    }
    /**
     * @param $document
     *
     * One line (no matter how many actual products are in invoice) - development services
     * Row and subtotal are with VAT
     * VAT is extracted and shown below
     */
    protected function _printOld($document) {
        // invoice caption
        $vatCaption = (((int)$document->getMVatPercent()) && $this->getLanguage() == 'lt' && $this->getDocumentType() == 'invoice') ? 'PVM ' : '';
        $this->text($vatCaption . $this->__($this->caption) . $document->getIncrementId(), 85, 715, 567, '#808080', 'Bold', 20, 'center', $this->page);
        $this->text($this->__($this->dateLabel) . $this->_date($document->getMDate()), 85, 687, 567, '#000000', 'Regular', 11, 'center', $this->page);

        // supplier info
        $this->text($this->__('Supplier'), 85, 650, 322, '#000000', 'Bold', 16, 'left', $this->page);
        foreach (explode("\n", Mage::getStoreConfig($this->__('sales/identity/address'), $this->store)) as $i => $value) {
            $this->text($value, 85, 628 - $i * 14, 322, '#000000', 'Regular', 11, 'left', $this->page);
        }

        // customer info
        $this->text($this->__('Customer'), 330, 650, 567, '#000000', 'Bold', 16, 'left', $this->page);
        foreach ($this->_formatAddress($this->order->getBillingAddress()->format($this->__('pdf'))) as $i => $value) {
            $this->text($value, 330, 628 - $i * 14, 567, '#000000', 'Regular', 11, 'left', $this->page);
        }

        // payment info
        $this->text($this->__($this->paidLabel), 330, 520, 567, '#000000', 'Bold', 16, 'left', $this->page);
        $y = 506;
        foreach (explode('{{pdf_row_separator}}', Mage::helper('payment')->getInfoBlock($this->order->getPayment())->setIsSecureMode(true)->toPdf()) as $value) {
            if (strip_tags(trim($value))) {
                $this->text(strip_tags(trim($value)), 330, $y, 567, '#000000', 'Regular', 11, 'left', $this->page);
                $y -= 14;
            }
        }

        // print header line
        $this->rectangle(85, 425, 403, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(403, 425, 483, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(483, 425, 567, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->__('Services'), 87, 430, 400, '#000000', 'Bold', 11, 'left', $this->page);
        $this->text($this->__('Price'), 405, 430, 480, '#000000', 'Bold', 11, 'center', $this->page);
        $this->text($this->__('Subtotal'), 485, 430, 565, '#000000', 'Bold', 11, 'center', $this->page);
        $this->line(85, 442, 567, 442, 1, '#000000', $this->page);

        // print service line
        $this->rectangle(85, 381, 403, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(403, 381, 483, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(483, 381, 567, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->__('Magento development services'), 87, 414, 400, '#000000', 'Regular', 9, 'left', $this->page);
        $this->text($this->__('According to order #%s, created at %s', $this->order->getIncrementId(), $this->_date($document->getCreatedAt(), true)), 100, 400, 400, '#000000', 'Regular', 9, 'left', $this->page);
        $this->text($this->_price($document->getGrandTotal(), $this->curr), 405, 414, 480, '#000000', 'Regular', 9, 'center', $this->page);
        $this->text($this->_price($document->getGrandTotal(), $this->curr), 485, 414, 565, '#000000', 'Regular', 9, 'center', $this->page);
        $y = 364;

        if ((int)$document->getMVatPercent()) {
            // print USD with VAT
            $this->text($this->__('Total with VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->text($this->_price($document->getGrandTotal(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;

            // print USD without VAT
            $this->text($this->__('Total without VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->text($this->_price($document->getMUsdTotal(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;

            // print USD VAT
            $this->text($this->__('%s%% VAT:', $document->getMVatPercent() * 1), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->text($this->_price($document->getMUsdVat(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;
        } else {
            // print USD with VAT
            $this->text($this->__('Total:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->text($this->_price($document->getGrandTotal(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;
        }

        if ($this->getLanguage() == 'lt') {
            // print exchange rate
            $this->text($this->__('%s-LTL exchange rate:', $this->curr), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->line(330, $y + 17, 567, $y + 17, 0.5, '#000000', $this->page);
            $this->line(330, $y, 567, $y, 0.5, '#000000', $this->page);
            $this->text($this->_number($document->getMExchangeRate()), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;

            if ((int)$document->getMVatPercent()) {
                // print LTL with VAT
                $this->text($this->__('Total with VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
                $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
                $this->text($this->_price($document->getMGrandTotal(), 'LTL'), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
                $y -= 17;

                // print LTL without VAT
                $this->text($this->__('Total without VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
                $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
                $this->text($this->_price($document->getMTotal(), 'LTL'), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
                $y -= 17;

                // print LTL VAT
                $this->text($this->__('%s%% VAT:', $document->getMVatPercent() * 1), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
                $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
                $this->text($this->_price($document->getMVat(), 'LTL'), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
                $y -= 17;
            } else {
                // print LTL with VAT
                $this->text($this->__('Total:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
                $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
                $this->text($this->_price($document->getMGrandTotal(), 'LTL'), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
                $y -= 17;

            }
        }
        // words
        $y -= 50;
        if ($this->getLanguage() == 'lt') {
            $this->text($this->_words($document->getGrandTotal(), $this->curr), 85, $y + 5, 567, '#000000', 'Regular', 11, 'left', $this->page);
        }
        $y -= 34;
        $this->text($this->__('Issued by: director Vladislav Ošmianskij'), 85, $y + 5, 500, '#000000', 'Regular', 11, 'right', $this->page);
    }

    /**
     * @param $document
     *
     * One line (no matter how many actual products are in invoice) - development services
     * Row and subtotal are without VAT
     * Then total VAT and total with VAT are shown
     */
    protected function _printVatExcluded($document) {
        // invoice caption
        $vatCaption = ($this->getLanguage() == 'lt' && $this->getDocumentType() == 'invoice') ? 'PVM ' : '';
        $this->text($vatCaption . $this->__($this->caption) . $document->getIncrementId(), 85, 715, 567, '#808080', 'Bold', 20, 'center', $this->page);
        $this->text($this->__($this->dateLabel) . $this->_date($document->getMDate()), 85, 687, 567, '#000000', 'Regular', 11, 'center', $this->page);

        // supplier info
        $this->text($this->__('Supplier'), 85, 650, 322, '#000000', 'Bold', 16, 'left', $this->page);
        foreach (explode("\n", Mage::getStoreConfig($this->__('sales/identity/address'), $this->store)) as $i => $value) {
            $this->text($value, 85, 628 - $i * 14, 322, '#000000', 'Regular', 11, 'left', $this->page);
        }

        // customer info
        $this->text($this->__('Customer'), 330, 650, 567, '#000000', 'Bold', 16, 'left', $this->page);
        foreach ($this->_formatAddress($this->order->getBillingAddress()->format($this->__('pdf'))) as $i => $value) {
            $this->text($value, 330, 628 - $i * 14, 567, '#000000', 'Regular', 11, 'left', $this->page);
        }

        // payment info
        $this->text($this->__($this->paidLabel), 330, 520, 567, '#000000', 'Bold', 16, 'left', $this->page);
        $y = 506;
        foreach (explode('{{pdf_row_separator}}', Mage::helper('payment')->getInfoBlock($this->order->getPayment())->setIsSecureMode(true)->toPdf()) as $value) {
            if (strip_tags(trim($value))) {
                $this->text(strip_tags(trim($value)), 330, $y, 567, '#000000', 'Regular', 11, 'left', $this->page);
                $y -= 14;
            }
        }

        // print header line
        $this->rectangle(85, 425, 403, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(403, 425, 483, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(483, 425, 567, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->__('Services'), 87, 430, 400, '#000000', 'Bold', 11, 'left', $this->page);
        $this->text($this->__('Price'), 405, 430, 480, '#000000', 'Bold', 11, 'center', $this->page);
        $this->text($this->__('Subtotal'), 485, 430, 565, '#000000', 'Bold', 11, 'center', $this->page);
        $this->line(85, 442, 567, 442, 1, '#000000', $this->page);

        // print service line
        $this->rectangle(85, 381, 403, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(403, 381, 483, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(483, 381, 567, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->__('Magento development services'), 87, 414, 400, '#000000', 'Regular', 9, 'left', $this->page);
        $this->text($this->__('According to order #%s, created at %s', $this->order->getIncrementId(), $this->_date($document->getCreatedAt(), true)), 100, 400, 400, '#000000', 'Regular', 9, 'left', $this->page);
        $this->text($this->_price($document->getMUsdTotal(), $this->curr), 405, 414, 480, '#000000', 'Regular', 9, 'center', $this->page);
        $this->text($this->_price($document->getMUsdTotal(), $this->curr), 485, 414, 565, '#000000', 'Regular', 9, 'center', $this->page);
        $y = 364;

        // print USD without VAT
        $this->text($this->__('Total without VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
        $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->_price($document->getMUsdTotal(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
        $y -= 17;

        // print USD VAT
        $this->text($this->__('%s%% VAT:', $document->getMVatPercent() * 1), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
        $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->_price($document->getMUsdVat(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
        $y -= 17;

        // print USD with VAT
        $this->text($this->__('Total with VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
        $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->_price($document->getGrandTotal(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
        $y -= 17;

        if ($this->getLanguage() == 'lt') {
            // print exchange rate
            $this->text($this->__('%s-LTL exchange rate:', $this->curr), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->line(330, $y + 17, 567, $y + 17, 0.5, '#000000', $this->page);
            $this->line(330, $y, 567, $y, 0.5, '#000000', $this->page);
            $this->text($this->_number($document->getMExchangeRate()), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;

            // print LTL without VAT
            $this->text($this->__('Total without VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->text($this->_price($document->getMTotal(), 'LTL'), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;

            // print LTL VAT
            $this->text($this->__('%s%% VAT:', $document->getMVatPercent() * 1), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->text($this->_price($document->getMVat(), 'LTL'), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;

            // print LTL with VAT
            $this->text($this->__('Total with VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
            $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
            $this->text($this->_price($document->getMGrandTotal(), 'LTL'), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
            $y -= 17;
        }
        // words
        $y -= 50;
        if ($this->getLanguage() == 'lt') {
            $this->text($this->_words($document->getGrandTotal(), $this->curr), 85, $y + 5, 567, '#000000', 'Regular', 11, 'left', $this->page);
        }
        $y -= 34;
        $this->text($this->__('Issued by: director Vladislav Ošmianskij'), 85, $y + 5, 500, '#000000', 'Regular', 11, 'right', $this->page);
    }

    /**
     * @param $document
     *
     * One line (no matter how many actual products are in invoice) - development services
     * Row and subtotal are without VAT
     * Then total VAT and total with VAT are shown
     */
    protected function _printEuroOnly($document) {
        // invoice caption
        $vatCaption = ($this->getLanguage() == 'lt' && $this->getDocumentType() == 'invoice') ? 'PVM ' : '';
        $this->text($vatCaption . $this->__($this->caption) . $document->getIncrementId(), 85, 715, 567, '#808080', 'Bold', 20, 'center', $this->page);
        $this->text($this->__($this->dateLabel) . $this->_date($document->getMDate()), 85, 687, 567, '#000000', 'Regular', 11, 'center', $this->page);

        // supplier info
        $this->text($this->__('Supplier'), 85, 650, 322, '#000000', 'Bold', 16, 'left', $this->page);
        foreach (explode("\n", Mage::getStoreConfig($this->__('sales/identity/address'), $this->store)) as $i => $value) {
            $this->text($value, 85, 628 - $i * 14, 322, '#000000', 'Regular', 11, 'left', $this->page);
        }

        // customer info
        $this->text($this->__('Customer'), 330, 650, 567, '#000000', 'Bold', 16, 'left', $this->page);
        foreach ($this->_formatAddress($this->order->getBillingAddress()->format($this->__('pdf'))) as $i => $value) {
            $this->text($value, 330, 628 - $i * 14, 567, '#000000', 'Regular', 11, 'left', $this->page);
        }

        // payment info
        $this->text($this->__($this->paidLabel), 330, 520, 567, '#000000', 'Bold', 16, 'left', $this->page);
        $y = 506;
        foreach (explode('{{pdf_row_separator}}', Mage::helper('payment')->getInfoBlock($this->order->getPayment())->setIsSecureMode(true)->toPdf()) as $value) {
            if (strip_tags(trim($value))) {
                $this->text(strip_tags(trim($value)), 330, $y, 567, '#000000', 'Regular', 11, 'left', $this->page);
                $y -= 14;
            }
        }

        // print header line
        $this->rectangle(85, 425, 403, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(403, 425, 483, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(483, 425, 567, 442, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->__('Services'), 87, 430, 400, '#000000', 'Bold', 11, 'left', $this->page);
        $this->text($this->__('Price'), 405, 430, 480, '#000000', 'Bold', 11, 'center', $this->page);
        $this->text($this->__('Subtotal'), 485, 430, 565, '#000000', 'Bold', 11, 'center', $this->page);
        $this->line(85, 442, 567, 442, 1, '#000000', $this->page);

        // print service line
        $this->rectangle(85, 381, 403, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(403, 381, 483, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->rectangle(483, 381, 567, 425, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->__('Magento development services'), 87, 414, 400, '#000000', 'Regular', 9, 'left', $this->page);
        $this->text($this->__('According to order #%s, created at %s', $this->order->getIncrementId(), $this->_date($document->getCreatedAt(), true)), 100, 400, 400, '#000000', 'Regular', 9, 'left', $this->page);
        $this->text($this->_price($document->getMUsdTotal(), $this->curr), 405, 414, 480, '#000000', 'Regular', 9, 'center', $this->page);
        $this->text($this->_price($document->getMUsdTotal(), $this->curr), 485, 414, 565, '#000000', 'Regular', 9, 'center', $this->page);
        $y = 364;

        // print USD without VAT
        $this->text($this->__('Total without VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
        $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->_price($document->getMUsdTotal(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
        $y -= 17;

        // print USD VAT
        $this->text($this->__('%s%% VAT:', $document->getMVatPercent() * 1), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
        $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->_price($document->getMUsdVat(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
        $y -= 17;

        // print USD with VAT
        $this->text($this->__('Total with VAT:'), 330, $y + 5, 475, '#000000', 'Bold', 11, 'right', $this->page);
        $this->rectangle(483, $y + 17, 567, $y, 0.5, '#000000', '#FFFFFF', $this->page);
        $this->text($this->_price($document->getGrandTotal(), $this->curr), 485, $y + 5, 565, '#000000', 'Bold', 9, 'center', $this->page);
        $y -= 17;

        // words
        $y -= 50;
        if ($this->getLanguage() == 'lt') {
            $this->text($this->_words($document->getGrandTotal(), $this->curr), 85, $y + 5, 567, '#000000', 'Regular', 11, 'left', $this->page);
        }
        $y -= 34;
        $this->text($this->__('Issued by: director Vladislav Ošmianskij'), 85, $y + 5, 500, '#000000', 'Regular', 11, 'right', $this->page);
    }

    public $caption;
    public $dateLabel;
    public $paidLabel;
    public $page;
    public $order;
    public $curr;
    public $store;
    public $date;
    public $timezone;

    public function getPdf($documents = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer($this->getDocumentType());
        if ($this->getDocumentType() == 'invoice') {
        	$this->caption = 'Invoice # ';
        	$this->dateLabel = 'Invoice Date: ';
        	$this->paidLabel = 'Paid by:';
        }
        elseif ($this->getDocumentType() == 'creditmemo') {
        	$this->caption = 'Credit Memo # ';
        	$this->dateLabel = 'Date: ';
        	$this->paidLabel = 'Refunded to:';
        }
        else {
        	throw new Exception('Not implemented');
        }

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($documents as $document) {
            if ($document->getStoreId()) {
                Mage::app()->getLocale()->emulate($document->getStoreId());
                Mage::app()->setCurrentStore($document->getStoreId());
            }
            $this->page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $this->page;

            $this->order = $document->getOrder();
			$this->curr = $document->getOrderCurrencyCode();
			$this->store = $document->getStoreId();

            $date = Mage::helper('local_manadev')->getDocumentEffectiveDate($document);
            $this->date = $date['date'];
            $this->timezone = $date['timezone'];

            $this->_print($document);

	        if ($document->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;

            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $page->drawText(Mage::helper('sales')->__('Product'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'), 255, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 430, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -=20;
        }

        return $page;
    }
    protected function _setFontRegular($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/app/code/local/Local/Manadev/fonts/calibri.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/app/code/local/Local/Manadev/fonts/calibrib.ttf');
        $object->setFont($font, $size);
        return $font;
    }
    protected function _setFontItalic($object, $size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/app/code/local/Local/Manadev/fonts/calibrii.ttf');
    	$object->setFont($font, $size);
        return $font;
    }
    /**
     * @param $page Zend_Pdf_Page
     */
    protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, 85, 757, 257, 813);
            }
        }
        //return $page;
    }
    
	/**
	* Return length of generated string in points
	*
	* @param string $string
	* @param Zend_Pdf_Resource_Font $font
	* @param int $font_size
	* @return double
	*/
	public function getTextWidth($text, Zend_Pdf_Resource_Font $font, $font_size)
	{
	 $drawing_text = iconv('UTF-8', 'UTF-16BE', $text);
	 $characters    = array();
	 for ($i = 0; $i < strlen($drawing_text); $i++) {
	  	$characters[] = (ord($drawing_text[$i++]) << 8) | ord ($drawing_text[$i]);
	 }
	 $glyphs        = $font->glyphNumbersForCharacters($characters);
	 $widths        = $font->widthsForGlyphs($glyphs);
	 $text_width   = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;
	 return $text_width;
	}
	public function text($text, $x1, $y, $x2, $color, $typeface, $size, $alignment, $page) {
		$method = '_setFont'.$typeface;
    	$font = $this->$method($page, $size);
        $page->setFillColor(Zend_Pdf_Color_Html::color($color));
        $width = $this->getTextWidth($text, $font, $size);
        $x = $alignment == 'left' ? $x1 : ($alignment == 'center' ? $x1 + ($x2 - $x1 - $width) / 2 : $x2 - $width);
        $page->drawText($text, round($x), $y, 'UTF-8');
	}
	public function rectangle($x1, $y1, $x2, $y2, $lineWidth, $lineColor, $fillColor, $page) {
		$page->setFillColor(Zend_Pdf_Color_Html::color($fillColor));
		$page->setLineColor(Zend_Pdf_Color_Html::color($lineColor));
		$page->setLineWidth($lineWidth);

		$page->drawRectangle($x1, $y1, $x2, $y2);
	}	
	public function line($x1, $y1, $x2, $y2, $lineWidth, $lineColor, $page) {
		$page->setLineColor(Zend_Pdf_Color_Html::color($lineColor));
		$page->setLineWidth($lineWidth);

		$page->drawLine($x1, $y1, $x2, $y2);
	}	
}