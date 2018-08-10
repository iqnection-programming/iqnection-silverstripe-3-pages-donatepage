<?php

namespace IQnection\DonatePage;

use IQnection\Payment;
use SilverStripe\ORM\FieldType\DBDatetime;
use IQnection\FormPage\FormPageController;

class DonatePageController extends FormPageController
{	
	private static $extensions = [
		\IQnection\PayPalPayment\Controller\PayPalPaymentHandler::class		
	];
	
	public function FormConfig()
	{
		$config = array(
			'sendToAll' => true,
			'useNospam' => true,
			'PageAfterSubmit' => 'paypalredirect'
		);
		$this->extend('updateFormConfig',$config);
		return $config;
	}
	
	public function FormFields()
	{
		$fields = array(
			"FirstName" => array(
				"FieldType" => "TextField",
				"Required" => true	
			),
			"LastName" => array(
				"FieldType" => "TextField",
				"Required" => true	
			),
			"Address" => array(
				"FieldType" => "TextField"
			),
			"Address2" => array(
				"FieldType" => "TextField",
				"Label" => "Address (line 2)"
			),
			"City" => array(
				"FieldType" => "TextField"
			),
			"State" => array(
				"FieldType" => "DropdownField",
				"Value" => "GetStates",
				"Default" => "PA"
			),
			"ZipCode" => array(
				"FieldType" => "TextField",
				"Label" => "Zip Code"	
			),
			"Phone" => array(
				"FieldType" => "TextField"
			),
			"Email" => array(
				"FieldType" => "EmailField",
				"Required" => true	
			),
			"Donation" => array(
				"FieldType" => "CurrencyField",
				"Required" => true
			),
			"Recipient" => $this->RecipientFieldConfig(),
		);
		$this->extend('updateFormFields',$fields);
		return $fields;
	}
	
	public function OnSuccessfulPayment($Payment,$data)
	{
		if ( (isset($_POST['item_number'])) && ($submission = FormPageSubmission()->byID($_POST['item_number'])) )
		{
			$submission->PaymentID = $Payment->ID;
			$submission->write();
		}
	}
}





