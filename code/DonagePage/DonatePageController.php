<?php

use IQnection\DonatePage\PayPalPayment;
use SilverStripe\ORM\FieldType\DBDatetime;


class DonatePageController extends FormPageController
{	
	private static $allowed_actions = [
		"paypal",
		'process_ipn_response'		
	];
	
	public function FormConfig()
	{
		$config = array(
			'sendToAll' => true,
			'useNospam' => true,
			'PageAfterSubmit' => 'paypal'
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
	
	protected $_log;
	public function logEntry($entry=null)
	{
		if (!$this->_log)
		{
			$this->_log = fopen($_SERVER['DOCUMENT_ROOT']."/paypal.transactions.log",'a');
		}
		fwrite($this->_log,"\n".date('c')."\n".$entry."\n");
		return $this->_log;
	}
	
	public function process_ipn_response()
	{
		if (!defined('PAYPAL_DEBUG_MODE'))
		{
			define("PAYPAL_DEBUG_MODE", false);
		}
	
		// this prevents some kind of error in the core
		$_SESSION = null;
		
		if ( PAYPAL_DEBUG_MODE )
		{
			$this->logEntry(str_repeat("*",20)."\nIPN Started!");
		}
	
		
		// parse post variables, reformat the data to be sent back via socket
		$data = "cmd=_notify-validate";
		foreach( $_POST as $key => $value )
		{
			$value = urlencode(stripslashes($value));
			$data .= "&".$key."=".$value;
		}
		
		// post back to PayPal system to validate
		$header =  "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Host: www.paypal.com\r\n"; 
		$header .= "Connection: close\r\n";
		$header .= "Content-Length: ".strlen($data)."\r\n\r\n";
	
		$response = "NONE";
	
		// send back the info
		$confirmURL = "ssl://www.paypal.com";
		if (defined('PAYPAL_TEST_MODE') && (PAYPAL_TEST_MODE) )
		{
			$confirmURL = "ssl://www.sandbox.paypal.com";
		}
		$socket_handle = fsockopen( $confirmURL, 443, $errno, $errstr, 30 );
		if ( PAYPAL_DEBUG_MODE )
		{
			$this->logEntry("header_debug:\n".print_r($header, true));
			$this->logEntry("data_debug:\n".print_r($data, true));
		}
		if( $socket_handle )
		{
			fputs( $socket_handle, $header.$data );
			while( !feof($socket_handle) )
			{
				$response = fgets($socket_handle, 1024);
				$response = trim($response);	
				if ( PAYPAL_DEBUG_MODE )
				{
					$this->logEntry("response_debug:\n".print_r($response, true));
				}
				if( strcmp($response, "VERIFIED") == 0 )
				{
					$response = "VERIFIED";
				}
				else if( strcmp($response, "INVALID") == 0 )
				{
					$response = "INVALID";
				}
			}
			fclose($socket_handle);
		}
		
		if( PAYPAL_DEBUG_MODE )
		{
			$this->logEntry("paypal response: ".$response);
			$this->logEntry(print_r($_POST,true));
		}
		
		if( $response != "INVALID" )
		{	// we only care about completed interactions
		
			// SUCCESS - Do something with the data		
			$Payment = PayPalPayment::create();
			$Payment->DonatePageID = $this->ID;
			$Payment->Date = date('Y-m-d h:i:s');
			$Payment->TransactionID = isset($_POST['txn_id']) ? $_POST['txn_id'] : $_POST['ipn_track_id'];
			$Payment->GatewayResponse = implode("\n",$_POST);
			$Payment->Amount = isset($_POST['amount3']) ? $_POST['amount3'] : (isset($_POST['payment_gross']) ? $_POST['payment_gross'] : (isset($_POST['mc_gross']) ? $_POST['mc_gross'] : 0) );
			$Payment->Email = isset($_POST['payer_email']) ? $_POST['payer_email'] : null;
			$Payment->Status = isset($_POST['payment_status']) ? $_POST['payment_status'] : null;
			$Payment->Name = (isset($_POST['first_name']) ? $_POST['first_name'] : null).' '.(isset($_POST['last_name']) ? $_POST['last_name'] : null);
			$Payment->Street = isset($_POST['address_street']) ? $_POST['address_street'] : null;
			$Payment->City = isset($_POST['address_city']) ? $_POST['address_city'] : null;
			$Payment->State = isset($_POST['address_state']) ? $_POST['address_state'] : null;
			$Payment->Country = isset($_POST['address_country']) ? $_POST['address_country'] : null;
			$Payment->Zip = isset($_POST['address_zip']) ? $_POST['address_zip'] : null;
			$Payment->PayerID = isset($_POST['payer_id']) ? $_POST['payer_id'] : null;
			$Payment->write();
			$Payment->OnSuccessfulPayment();
			
			if ( (isset($_POST['item_number'])) && ($submission = $this->DonatePageSubmissions()->byID($_POST['item_number'])) )
			{
				$submission->PayPalPaymentID = $Payment->ID;
				$submission->write();
			}

		}
		if ( PAYPAL_DEBUG_MODE )
		{
			fclose($this->logEntry('Complete'));
		}
		return 1;
	}	
			
}





