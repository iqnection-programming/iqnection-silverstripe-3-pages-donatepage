<?php

use IQnection\Payments\PayPalPayment;
use SilverStripe\ORM\FieldType\DBField;

class DonatePageSubmission extends FormPageSubmission 
{
	private static $db = [
		'FirstName' => 'Varchar(255)',
		'LastName' => 'Varchar(255)',
		'Address' => 'Varchar(255)',
		'Address2' => 'Varchar(255)',
		'City' => 'Varchar(255)',
		'State' => 'Varchar(255)',
		'ZipCode' => 'Varchar(255)',
		'Phone' => 'Varchar(255)',
		'Email' => 'Varchar(255)',
		'Donation' => 'Currency'
	];
	
	private static $has_one = [
		"PayPalPayment" => PayPalPayment::class
	];
	
	private static $summary_fields = [
		"Created" => "Date",
		"FirstName" => "First Name",
		"LastName" => "Last Name",
		"Email" => "Email Address",
		"Donation" => "Donation",
		"PaymentCompleted.Nice" => "Completed"
	];
	
	private static $casting = [
		'PaymentCompleted' => 'Boolean'
	];
	
	private static $default_sort = "Created DESC";
	
	public function canCreate($member = null,$context = []) { return false; }
	public function canDelete($member = null,$context = []) { return true; }
	public function canEdit($member = null,$context = [])   { return false; }
	public function canView($member = null,$context = [])   { return true; }
	
	public function PaymentCompleted()
	{
		return DBField::create_field('SilverStripe\ORM\FieldType\DBBoolean',$this->PayPalPayment()->Exists());
	}
}

