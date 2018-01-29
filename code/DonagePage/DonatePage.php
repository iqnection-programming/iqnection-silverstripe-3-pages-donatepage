<?php

use SilverStripe\Forms;
use IQnection\DonatePage\PayPalPayment;

class DonatePage extends FormPage
{
	private static $paypal_url = "https://www.paypal.com/cgi-bin/webscr";
	private static $paypal_sandbox_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	
	private static $db = [
		"RedirectText" => "HTMLText",
		"PayPalAccount" => "Varchar(255)"
	];
	
	private static $has_many = [
		"DonatePageSubmissions" => DonatePageSubmission::class,
		"PayPalPayments" => PayPalPayment::class
	];
		
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Main", Forms\HTMLEditor\HTMLEditorField::create("RedirectText", "Text Explaining PayPal Redirect")->addExtraClass('stacked'),"Metadata");

		if ( (defined('PAYPAL_TEST_MODE')) && (PAYPAL_TEST_MODE) )
		{
			$fields->addFieldToTab('Root.PayPalSettings', Forms\HeaderField::create('test-mode','Test Mode: Enabled',4) );
		}
		if ( (defined('PAYPAL_DEBUG_MODE')) && (PAYPAL_DEBUG_MODE) )
		{
			$fields->addFieldToTab('Root.PayPalSettings', Forms\HeaderField::create('debug-mode','Debug Mode: Enabled',4) );
		}
		$fields->addFieldToTab("Root.PayPalSettings", Forms\TextField::create("PayPalAccount", "PayPal Account (email address)"));
		
		// Paypal Payments
		$fields->addFieldToTab('Root.Payments', Forms\GridField\GridField::create(
			'PayPalPayments',
			'PayPal Payments',
			$this->PayPalPayments(),
			Forms\GridField\GridFieldConfig_RecordEditor::create()
		));
		
		$this->extend('updateCMSFields',$fields);
		return $fields;
	}
	
	public function PayPalUrl()
	{
		if (defined('PAYPAL_TEST_MODE') && (PAYPAL_TEST_MODE) )	
		{
			return $this->config()->get('paypal_sandbox_url');
		}
		return $this->config()->get('paypal_url');
	}
	
	public function IPNLink()
	{
		$Link = $this->AbsoluteLink('process_ipn_response');
		$this->extend('updateIPNLink',$Link);
		return $Link;
	}	
}	


