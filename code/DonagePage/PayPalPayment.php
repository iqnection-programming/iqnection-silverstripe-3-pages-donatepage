<?php

namespace IQnection\Payments;
use SilverStripe\Forms;
use SilverStripe\ORM;

class PayPalPayment extends ORM\DataObject
{
	private static $table_name = 'Payment';
	
	private static $db = [
		"Amount" => "Currency",
		"TransactionID" => "Varchar(255)",
		"GatewayResponse" => "Text",
		"Date" => "Datetime",
		"Status" => "Varchar(255)",
		"Name" => "Varchar(255)",
		"Street" => "Varchar(255)",
		"City" => "Varchar(255)",
		"State" => "Varchar(255)",
		"Country" => "Varchar(255)",
		"Zip" => "Varchar(255)",
		"Email" => "Varchar(255)",
		"PayerID" => "Varchar(255)"
	];
	
	private static $has_one = array(
		"DonatePage" => \DonatePage::class
	); 		
	
	private static $summary_fields = [
		'Date.Nice' => 'Date',
		'Name' => 'Name',
		'Email' => 'Email',
		'Amount' => 'Amount'
	];
	
	private static $default_sort = 'Date DESC';
	
	public function getTitle()
	{
		return $this->TransactionID;
	}
	
	public function getCMSFields()
	{
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.GatewayResponse', Forms\TextareaField::create('GatewayResponse','Gateway Response') );

		$this->extend('updateCMSFields',$fields);
		
		return $fields;
	}
	
	public function OnSuccessfulPayment()
	{
		$this->extend('AfterSuccessfulPayment');
	}
	
	public function canCreate($member = null, $context = []) { return false; }
	public function canDelete($member = null, $context = []) { return true; }
	public function canEdit($member = null, $context = [])   { return false; }
	public function canView($member = null, $context = [])   { return true; }
}




