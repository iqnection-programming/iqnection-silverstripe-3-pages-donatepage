<?php

namespace IQnection\DonatePage;

use SilverStripe\Forms;
use IQnection\PayPalPayment\PayPalPayment;
use IQnection\FormPage\FormPage;

class DonatePage extends FormPage
{
	private static $table_name = 'DonatePage';
	
	private static $extensions = [
		\IQnection\PayPalPayment\PayPalPage\PageExtension::class
	];
	
	private static $has_many = [
		"DonatePageSubmissions" => Model\DonatePageSubmission::class,
	];
		
}	


