<?php
	class DonatePage extends FormPage
	{
	    private static $db = array(
			"RedirectText" => "HTMLText",
			"PayPalAccount" => "Varchar(255)"
		);
		
	    public function getCMSFields()
	    {
	        $fields = parent::getCMSFields();
	        $fields->addFieldToTab("Root.Main", new HTMLEditorField("RedirectText", "Text Explaining PayPal Redirect"), "Metadata");
	        if (permission::check('ADMIN')) {
	            $fields->addFieldToTab("Root.PayPalSettings", new TextField("PayPalAccount", "PayPal Account (email address)"));
	        }
	        return $fields;
	    }
	}	
	
	class DonatePageSubmission extends DataObject
	{
		
	    private static $db = array(
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
        );
		
	    private static $summary_fields = array(
			"Created" => "Date",
			"FirstName" => "First Name",
			"LastName" => "Last Name",
			"Email" => "Email Address",
			"Donation" => "Donation"
		);
		
	    private static $default_sort = "Created DESC";
		
	    public function canDelete($member = null)
	    {
	        return true;
	    }
	    public function canEdit($member = null)
	    {
	        return true;
	    }
	    public function canView($member = null)
	    {
	        return true;
	    }
	}
	
	class DonatePage_Controller extends FormPage_Controller
	{
	    private static $allowed_actions = array(
			"paypal"		
		);
		
	    public function FormConfig()
	    {
	        return array(
				'sendToAll' => true,
				'useNospam' => true,
				'PageAfterSubmit' => 'paypal'
			);
	    }
		
	    public function FormFields()
	    {
	        return array(
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
				)
			);
	    }	
				
	    public function init()
	    {
	        parent::init();
	    }
	}
?>