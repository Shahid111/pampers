<?php
/**
 * Contact Class
 *
 * This class handles Contacts (individuals/couples) fro the CARE database
 * allowing them to be editted and loaded
 *
 * @author James Keasley <james.keasley@nct.org.uk>
 * @version 1.0
 * @package NCT
 */
/**
 * Make Care Webservices available 
 */
require_once('care_webservice.class.php');
/**
 * Make the Address class available
 */
require_once('address.class.php');
/**
 * Make the Role Class available
 */
require_once('role.class.php');
/**
 * make the Communication Class available
 */
require_once('communication.class.php');
/**
 * Make the Branch Class Available
 */
require_once('branch.class.php');

/**
 * Make the utility Lib available
 */
require_once('utility.lib.php');

/**
 * This is the Class that Handles Contacts from CARE
 */
class Contact
{
  /**
   * The unique ID of the instance of Contact 
   * @type int 
   */
  protected $contactNumber;
  
  /**
   * The Title string (is Mr, Mrs, Ms) 
   * @type string 
   */
  private $title;
  /**
   * The Contact's forenames 
   * @type string 
   */
  private $forenames;
  /**
   * The forename that should be used in commmunications with the Contact 
   * @type string 
   */
  private $preferredForename;
  /**
   * The Contact's surname 
   * @type string
   */
  private $surname;
  /**
   * The sex of the Contact 
   * @type string
   */
  private $sex;  
  /**
   * The Contact's Initials 
   * @type string
   */
  private $initials;
  /**
   * the salutation to be used in any communicatiosn with the contact 
   * @type string
   */
  private $salutation;
  /**
   * The Contact's associated Branch ID 
   * @type int
   */
  private $branchCode = -1;
  /**
   * an array of Role objects 
   * @type array
   */
  private $roles = array();
  /**
   * an array of Address objects 
   * @type array
   */
  private $addresses = array();
  /**
   * an array of Communication objects 
   * @type array
   */
  private $communications = array();
  /**
   * an associative array of Communication objects 
   * @type array
   */
  private $coreCommunications = array();
  /**
   * an instance of the Branch Class 
   * @type Branch
   */
  private $branch = NULL;
  
  /**
   * true for successful loading of Contact ]
   * @type bool
   */
  private $success = false;
  /**
   * an array of error strings, if the class fails to load 
   * @type array
   */
  private $reason = array();
  /**
   * Whether this is a test or live instance 
   * @type bool
   */
  private $istest = false;
  /**
   * DEPRECATED 
   * @type void
   */
  private $client;
  /**
   * Whether the Branches have been loaded yet
   * @type bool
   */
  private $branchLoaded = false;
  /**
   * Whether Roles have been loaded
   * type bool
   */
  private $rolesLoaded = false;
  /**
   * Whether the Addresses have been loaded yet
   * @type bool
   */
  private $addressesLoaded = false;

  /**
   * The passwordHard value
   * @type string
   */
  private $passwordHash = '';

  /**
   * This is the Class constuctor
   *
   * This takes the input parameters and loads or creates an instance of Contact.
   * 
   * Generally, this method is called in one of three scenarios:
   * 
   * 1. Creating a new contact by using all parameters except $contactNumber (which would be 'null')
   * 2. Loading an existing contact (use first two parameters with $contactNumber as an integer)
   * 3. When a contact is initialy created the constructor gets called a second time to finish adding 
   * communications (not possible in the first call). 
   * In this case the first two and last three parameters are used 
   * but all others are set to 'null' (see line 991).
   *
   * @param int|null $contactNumber the unique ID of the Contact to be loaded
   * @param bool $istest Whether to use the test or live instance
   * @param string|null $title e.g. 'Mrs'
   * @param string|null $forenames e.g. 'Doris Edna'
   * @param string|null $preferredForename e.g. 'Furious'
   * @param string|null $surname e.g. 'McPhee'
   * @param string|null $sex 'F', 'M' or 'U' (Female, Male or Undefined)
   * @param string|null $address e.g. '29 Acacia Avenue'
   * @param string|null $town e.g. 'Bristol'
   * @param string|null $county e.g. 'North Somerset'
   * @param string|null $postcode e.g. 'BS8 1AS'
   * @param string|null $telephone e.g. '+44 (01633) 675 909'
   * @param string|null $mobile e.g. '07831 354 204'
   * @param string|null $email e.g. 'furious.mcphee@cagefight.com'
   *
   * @return void|int void for success, -1 for failure
   */
  public function Contact ($contactNumber = null,
  							$istest = false,
  							$title = null,
  							$forenames = null,
  							$preferredForename = null,
  							$surname = null,
  							$sex = null,
  							$address = null,
  							$town = null,
  							$county = null,
  							$postcode = null,
  							$telephone = null,
  							$mobile = null,
  							$email = null
  							)
  {
  	$this->branchCode = -1;
	$this->roles = array();
	$this->addresses = array();
	$this->communications = array();
	$this->coreCommunications = array(); // key value array
	$this->branch = NULL; 
	$this->success = false;
	$this->reason = array(); 
  
  	$this->istest = $istest;
  	
    if ($contactNumber == null) // assume new contact registering - manual 
    { 	
    	if ($title != null &&
    		$forenames != null &&
    		$preferredForename != null &&
    		$surname != null &&
    		$sex != null &&
    		$address != null &&
    		$town != null &&
    		$county != null &&
    		$postcode != null &&
    		$telephone != null &&
    		$mobile != null &&
    		$email != null)
    	{
    		// set personal details
    		$this->set_title($title);
    		$this->set_forenames($forenames);
    		$this->set_preferredForename($preferredForename);
    		$this->set_surname($surname);
    		$this->set_sex($sex);
    		// set address
    		$this->addAddress($address, $town, $county, $postcode, true);
    		// set communications
    		$this->coreCommunications['T'] = $this->getRelevantCommunication('T');
	    	$this->coreCommunications['MB'] = $this->getRelevantCommunication('MB');
		if ($this->getRelevantCommunication('E')->get_Number() != NULL) {
		  $this->coreCommunications['E'] = $this->getRelevantCommunication('E');
		} else if ($this->getRelevantCommunication('EM')->get_Number() != NULL) {
		  $this->coreCommunications['E'] = $this->getRelevantCommunication('EM');
		} else if ($this->getRelevantCommunication('EB')->get_Number() != NULL) {
                  $this->coreCommunications['E'] = $this->getRelevantCommunication('EB');
                } else if ($this->getRelevantCommunication('ES')->get_Number() != NULL) {
                  $this->coreCommunications['E'] = $this->getRelevantCommunication('ES');
                } else if ($this->getRelevantCommunication('ET')->get_Number() != NULL) {
                  $this->coreCommunications['E'] = $this->getRelevantCommunication('ET');
                } else {
		  $this->coreCommunications['E'] = $this->getRelevantCommunication('E');
		}
		$this->coreCommunications['T']->set_Number($telephone);
		$this->coreCommunications['MB']->set_Number($mobile);
		$this->coreCommunications['E']->set_Number($email);
    	}
    	else
    	{
    		$this->success = FALSE;
		    $this->reason[] = "Not enough data to make a valid new record";
		    return -1;
    	}
    }
	else // assume population of an existing contact from the CARE database
	{
	    $client = Care_Webservice::getInstance($istest);
	    
	    // CONTACT INFORMATION ->
	    $contact_method = 'SelectContactData';  
	    $contact_params = array('pSelectDataType'=>'xcdtContactInformation',
				    'params' => array('ContactNumber' => $contactNumber));
	    $contact_retVals = $client->doMethod($contact_method, $contact_params);
	
	    if ($contact_retVals['SelectContactDataResult'] != ''
		&& array_key_exists('DataRow', $contact_retVals['SelectContactDataResult'])
		&& array_key_exists('ContactNumber', $contact_retVals['SelectContactDataResult']['DataRow']))
	    {
	    	$contact_info = $contact_retVals['SelectContactDataResult']['DataRow'];
	    	
	    	// Populate private variables for contact from CARE ->
	    	$this->title = $contact_info['Title'];
	    	$this->forenames = $contact_info['Forenames'];
	    	$this->preferredForename = $contact_info['PreferredForename'];
	    	$this->surname = $contact_info['Surname'];
	    	$this->sex = $contact_info['Sex'];
	    	$this->initials = $contact_info['Initials'];
	    	$this->salutation = $contact_info['Salutation'];
	    	$this->branchCode = $contact_info['BranchCode'];

	    	$this->success = true;
	    	$this->contactNumber = $contactNumber;
		$passwords = getHashedPasswords($contactNumber, $istest);
		$this->passwordHash = $passwords[0];
	    }
	    else
	    {
	      $this->success = false;
	      $this->reason[] = 'Invalid Return';
	    }
	    // CONTACT INFORMATION <-

	    // COMMUNICATIONS INFORMATION ->
	    $communications_method = 'SelectContactData';
	    $communications_params = array('pSelectDataType'=>'xcdtContactCommsNumbers',
				    'params' => array('ContactNumber' => $contactNumber));
	    $communications_retVals = $client->doMethod($communications_method, $communications_params);
	    if (isset($communications_retVals['SelectContactDataResult']['DataRow']))
	    {
		  	if (count($communications_retVals['SelectContactDataResult']['DataRow']) > 0)
		  	{
			  if (array_key_exists('CommunicationNumber', 
					       $communications_retVals['SelectContactDataResult']['DataRow'])) {
			    $data = $communications_retVals['SelectContactDataResult']['DataRow'];
			    $this->communications[] = new Communication($data['CommunicationNumber'],
		                                                        $data['DeviceCode'],
		                                                        $data['DeviceDefault'],
		                                                        $data['Number'],
		                                                        $data['AmendedOn'],
		                                                        $istest
		                                                        );
			  } else {
			    foreach ($communications_retVals['SelectContactDataResult']['DataRow'] as $data) {
				$this->communications[] = new Communication($data['CommunicationNumber'],
									    $data['DeviceCode'],
									    $data['DeviceDefault'],
									    $data['Number'],
									    $data['AmendedOn'],
									    $istest
									    );
			    }
			  } 
			} else {
			  $this->success = false;
			  $this->reason[] = 'Failed to get communications data';
			}
	    }
	    
	    // '$this->communications should now be an array of objects for all communications that are 
	    // stored in CARE for this user
	    // What we really need though is just the most pertinant home phone, mobile and email addresses
	    //
	    $this->coreCommunications['T'] = $this->getRelevantCommunication('T');
	    $this->coreCommunications['MB'] = $this->getRelevantCommunication('MB');
	    if ($this->getRelevantCommunication('E')->get_Number() != NULL) {
	      $this->coreCommunications['E'] = $this->getRelevantCommunication('E');
	    } else if ($this->getRelevantCommunication('EM')->get_Number() != NULL) {
	      $this->coreCommunications['E'] = $this->getRelevantCommunication('EM');
	    } else if ($this->getRelevantCommunication('EB')->get_Number() != NULL) {
	      $this->coreCommunications['E'] = $this->getRelevantCommunication('EB');
	    } else if ($this->getRelevantCommunication('ES')->get_Number() != NULL) {
	      $this->coreCommunications['E'] = $this->getRelevantCommunication('ES');
	    } else if ($this->getRelevantCommunication('ET')->get_Number() != NULL) {
	      $this->coreCommunications['E'] = $this->getRelevantCommunication('ET');
	    } else {
	      $this->coreCommunications['E'] = $this->getRelevantCommunication('E');
	    }

	    //$this->coreCommunications['E'] = $this->getRelevantCommunication('E');
	    // COMMUNICATIONS INFORMATION <-
	    
	    // COMMUNICATION WORKAROUND ->
	    // Can't seem to add comms as part of creating a new user so am adding them here 
	    // from session data instead
	    if ($this->contactNumber != null && $telephone != null && $mobile != null && $email != null)
    	{
    		$this->coreCommunications['T']->set_Number($telephone);
    		$this->coreCommunications['MB']->set_Number($mobile);
    		$this->coreCommunications['E']->set_Number($email);
    		$this->coreCommunications['T']->savePrivateVariables($this->contactNumber);
    		$this->coreCommunications['MB']->savePrivateVariables($this->contactNumber);
    		$this->coreCommunications['E']->savePrivateVariables($this->contactNumber);
    	}
	    // COMMUNICATION WORKAROUND <-
	}
  }
  
  /* LOAD FUNCTIONS - CALLED 'ON DEMAND' -> */
  /**
   * Load the Barnch object and associated information of the current Contact
   *
   * This method will Load the Branch object associated with the current contact on demand
   * so that the Branch doesn't need to be loaded on the initial load, minimising the initial 
   * load time and size of the Contact object, by only loading the Branch as needed
   *
   * @access Private 
   * @return Branch an instance of class Branch
   */
  private function loadBranchData()
  {
	  if ($this->branchCode != -1 && !$this->branchLoaded)
	  {
	  	$this->branch = new Branch($this->branchCode, $this->istest);
	  	$this->branchLoaded = true;
	  }
  }

  /**
   * Load the Roles of the current Contact
   *
   * This method loads the roles data of the current Contact on an as needed basis
   * This reduces the initial load time and size of the Contact Object of initial
   * loading by only making the call to load the Roles when they are actually needed 
   * by the system
   *
   * @access Private
   * @return array an arry of Role objects, empty if no Roles apply
   */
  private function loadRoleData()
  {
  	if ($this->contactNumber != null && !$this->rolesLoaded)
  	{
  		$contact_method = 'SelectContactData';
  		$client = Care_Webservice::getInstance($this->istest);
	 	$roles_params = array('pSelectDataType'=>'xcdtContactRoles',
					      'params' => array('ContactNumber' => $this->contactNumber));
		
		
		$roles = $client->doMethod($contact_method, $roles_params);
		$this->roles = array();
		if (isset($roles['SelectContactDataResult']['DataRow']))
		{
			if (count($roles) > 0) {
			  if (array_key_exists('ContactRoleNumber', $roles['SelectContactDataResult']['DataRow'])) {
			    $this->roles[] = new Role($roles['SelectContactDataResult']['DataRow']);
			  } else {
			    foreach ($roles['SelectContactDataResult']['DataRow'] as $role) {
			      $this->roles[] = new Role($role);
			    }
			  }
			}
		}
		
		$this->rolesLoaded = true;
  	}
  }

  /**
   * Load the Address Data for the Contact
   *
   * This method decreases the initial loading time and size of the Contact object
   * by loading up that Contact's address data only when it is needed.
   *
   * @access Private
   * @return array an Array of Address objects
   */
  private function loadAddressData()
  {
  	if ($this->contactNumber != null && !$this->addressesLoaded)
  	{
  		$client = Care_Webservice::getInstance($this->istest);
	  	$address_method = 'SelectContactData';  
	    $address_params = array('pSelectDataType'=>'xcdtContactAddresses',
				    'params' => array('ContactNumber' => $this->contactNumber));
	    $address_retVals = $client->doMethod($address_method, $address_params);
	
	    if (@count($address_retVals['SelectContactDataResult']['DataRow']) > 0) {
	      if (array_key_exists('AddressNumber', $address_retVals['SelectContactDataResult']['DataRow'])) {
		$data = $address_retVals['SelectContactDataResult']['DataRow'];
		$isDefault = $data['Default'] == 'Yes' ? true : false;
	        $this->addresses[] = new Address($data['AddressNumber'], $isDefault, $this->istest);
	      } else {
		foreach ($address_retVals['SelectContactDataResult']['DataRow'] as $data) {
		  $isDefault = $data['Default'] == 'Yes' ? true : false;
		  $this->addresses[] = new Address($data['AddressNumber'], $isDefault, $this->istest);
		}
	      }
	    }
	    else
	    {
	    	$this->success = false;
	      	$this->reason[] = 'Failed to get address data';
	    }
	    $this->addressesLoaded = true;
  	}
  }

  /* LOAD FUNCTIONS - CALLED 'ON DEMAND' -> */
  
  /* UTILITY FUNCTIONS -> */
  /**
   * Get the default Communication object for a given type of device
   *
   * checks whether any of that type of device is present
   * returns the most appropriate Communication object if possible
   * Logic is:
   *
   * 1. Do we any communication objects of type X (where X is either 'home phone', 'mobile phone' or 'email'
   * 2. Are any of them marked as device deaults?
   * 3. If not, choose the one with the most recent amended on date
   * 4. If none at all then make a new one for that type.
   *
   * @param string $type the device type to find
   *
   * @return Communication an instance of Communication
   */
  protected function getRelevantCommunication($type)
  {
  	$result = NULL;
  	$typeMatches = array();
  	for ($i = 0; $i < count($this->communications); $i++)
  	{
  		if ($this->communications[$i]->get_DeviceCode() == $type)
  		{
  			$typeMatches[] = $this->communications[$i];
  		}
  	}
  	if (count($typeMatches) != 0)
  	{
  		// more than one of the requested type
  		if (count($typeMatches) > 1)
  		{
  			// try and find a match with a positive device default
  			$typeMatchesWithDefault = array();
  			for ($i = 0; $i < count($typeMatches); $i++)
  			{
  				if ($typeMatches[$i]->get_DeviceDefault() == 'Yes')
  				{
  					$typeMatchesWithDefault[] = $typeMatches[$i];
  				}
  			}
  			if (count($typeMatchesWithDefault) < 1)
  			{
  				// more than 1 type match but no defaults:
  				// try and find the most recent of the multiple matches
	  			$largestDateUnix = 0;
		  		for ($i = 0; $i < count($typeMatches); $i++)
		  		{
		  			$amendedDate = $typeMatches[$i]->get_AmendedOn();
		  			$amendedDateUnix = strtotime(str_replace('/', '-', $amendedDate).' 12:00');
		  			if ($amendedDateUnix > $largestDateUnix)
		  			{
		  				$largestDateUnix = $amendedDateUnix;
		  				$result = $typeMatches[$i];
		  			}
		  		}
  			}
  			else
  			{
	  			if (count($typeMatchesWithDefault) > 1)
	  			{
		  			// try and find the most recent of the multiple matches
		  			$largestDateUnix = 0;
			  		for ($i = 0; $i < count($typeMatchesWithDefault); $i++)
			  		{
			  			$amendedDate = $typeMatchesWithDefault[$i]->get_AmendedOn();
			  			$amendedDateUnix = strtotime(str_replace('/', '-', $amendedDate).' 12:00');
			  			if ($amendedDateUnix > $largestDateUnix)
			  			{
			  				$largestDateUnix = $amendedDateUnix;
			  				$result = $typeMatchesWithDefault[$i];
			  			}
			  		}
	  			}
	  			else
	  			{
	  				$result = $typeMatchesWithDefault[0];
	  			}
  			}
  		}
  		else
  		{
  			$result = $typeMatches[0];
  		}
  	}
  	else
  	{
  		// none of the requested type, going to have to make a new one
  		$result = new Communication(-1, $type, 'Yes', '', '', $this->istest);
  	}
  	
  	return $result; // this will be an instance of the Communications object, possibly a new one
  }
  
  /**
   * Checks whether a Contact has a given role
   *
   * Takes a role code as input, and checks whether the Contact has that Role
   *
   * @param string $input a Role Code to be checked for
   *
   * @return bool True if the Role is found, false otherwise
   */
	public function checkRole($input)
	  {
	  	$this->loadRoleData();
	  	if (is_string($input))
	  	{
	  		for($i = 0; $i < count($this->roles); $i++)
	  		{
	  			if ($this->roles[$i]->get_role_code() == $input && $this->roles[$i]->get_is_current())
	  			{
	  				return true;
	  			}	
	  		}
	  		return false;
	  	}
	  	else
	  	{
	  		return false;
	  	}
	  }	
  
  /* UTILITY FUNCTIONS <- */

  /* GETS -> */
  /**
   * Gets the value of isTest
   *
   * @return bool true for test instance, false for live
   */
  public function getIsTest()
  {
  	return $this->istest;
  }
  
  /**
   * Get the Contact Branch code
   *
   * @return int the unique ID of the Branch associated with the Contact
   */
  public function get_branchCode()
  {
  	return $this->branchCode;	
  }
  
  /**
   * Get the contact Number
   *
   * @return int The unique ID of this instance of the Contact object
   */
  public function get_contactNumber()
  {
    return $this->contactNumber;
  }
  
  /**
   * Get the title
   *
   * @return string the Value set for title
   */
  public function get_title()
  {
    return $this->title;
  }
  
  /**
   * Get the Forenames
   *
   * @return string The value set for the Contact's forenames
   */
  public function get_forenames()
  {
    return $this->forenames;
  }
  
  /**
   * Get the Preferred forename
   *
   * @return string the Contact's preferred forename
   */
  public function get_preferredForename()
  {
    return $this->preferredForename;
  }
  
  /**
   * Get the Contact's surname
   *
   * @return the Surname
   */
  public function get_surname()
  {
    return $this->surname;
  }
  
  /**
   * Get sex
   *
   * Yes please
   *
   * @return string the value set for the Contact's sex (M or F)
   */
  public function get_sex()
  {
    return $this->sex;
  }
  
  /**
   * Get the Contact's Initials
   *
   * @return string The Contact's initials
   */
  public function get_initials()
  {
    return $this->initials;
  }
  
  /**
   * Get the salutation to be used
   *
   * @return string the value of salutation
   */
  public function get_salutation()
  {
    return $this->salutation;
  }

  /**
   * get reason for failure to load object
   *
   * @return null|string null if object loaded successfully, the reason string for failure otherwise
   */
  public function get_reason()
  {
    if ($this->success)
    {
      return null;
    }
    else
    {
    	return $this->reason;
    }
  }

  /**
   * Get a Contact's associated Address objects
   *
   * Get the array of Addresses for the Contact
   * optionally only returning the address set as default for that contact
   *
   * @param bool $default set to true to only get default address, false for all
   *
   * @return array An array of Address objects
   */
  public function get_addresses($default = true)
  {
  	$this->loadAddressData();
    if ($default) {
      $add = array();
      foreach ($this->addresses as $address) {
	if ($address->get_isDefault()) {
	  $add[] = $address;
	}
      }
      return $add;
    }
    return $this->addresses;
  } 
  /**
   * Get a Contact's associated Role Objects
   *
   * Return an array of Role objects,optionally only returning those Roles that are currently active
   *
   * @param bool $default true to get only active Roles, False for all Roles
   *
   * @return array an array of Role objects
   */
  public function get_roles($default = true)
  {
  	$this->loadRoleData();
    if ($default) {
      $roles = array();
      foreach ($this->roles as $role) {
	if ($role->get_is_current()) {
	  $roles[] = $role;
	}
      }
      return $roles;
    }
    return $this->roles;
  }
  
  /**
   * Get the Communications array
   *
   * @return array an array of Communication Objects
   */
  public function get_communications()
  {
    return $this->communications;
  }
  
  /**
   * Get the Core Communications
   *
   * @return array an associative array of Communication Objects
   */
  public function get_coreCommunications()
  {
    return $this->coreCommunications;
  }
  
  /**
   * Get the Contact's default email
   *
   * @return Communication an instance of Communication
   */
  public function get_coreEmail()
  {
    return $this->coreCommunications['E']->get_Number();
  }
  
  /**
   * Get the Contact's default phone number
   *
   * @return Communication an instance of Communication
   */
  public function get_coreTelephone()
  {
    return $this->coreCommunications['T']->get_Number();
  }
  
  /**
   * Get the Contact's default mobile number
   *
   * @return Communication an instance of Communication
   */
  public function get_coreMobile()
  {
    return $this->coreCommunications['MB']->get_Number();
  }
  
  /**
   * Get the Branch associated with the Contact
   *
   * @return Branch an instance of class Branch 
   */
  public function get_branch()
  {
  	$this->loadBranchData();
    return $this->branch;
  }
  
  /**
   * Get the hashed password
   *
   * @return string a password hash value
   */
  public function get_password()
  {
    return $this->passwordHash;
  }
  /* GETS <- */
  
  /* SETS -> */
  /**
   * Set the Contact's Title
   *
   * Takes a valid input and sets the title to that value
   * valid input is a non-null string less that 40 characters long
   *
   * @param string $input The title to be set
   *
   * @return int 0 for success, -1 otherwise
   */
  public function set_title($input)
  {
    if ($input == null || !is_string($input) || strlen($input) > 40) {
      return -1;
    }
    $this->title = $input;
    return 0;
  }
  
  /**
   * Set the Contact Forenames
   *
   * sets the value of the forenames to a valid input 
   * input is valid if it is a non-null string less than 60 characters long
   * The Contact Initials are also automatically set here
   *
   * @param string the forenames to be set
   *
   * @return int 0 for success, -1 otherwise
   */
  public function set_forenames($input)
  {
    if ($input == null || !is_string($input) || strlen($input) > 60) {
      return -1;
    }
    $this->forenames = $input;

    // Also needs to change 'initials' field
    // Explode into seperate strings, take the first letter of each string
    $initialsArray = explode(' ', $this->forenames);
    $initialsString = '';
    for ($i = 0; $i < count($initialsArray); $i++) {
      $strToAdd = strtoupper(substr($initialsArray[$i], 0, 1));
      if ($i == 0) {
	$initialsString .= $strToAdd;
      }	else {
	$initialsString .= ' '.$strToAdd;
      }
    }
    if (strlen($initialsString) > 7) {
      return -1;
    } else {
      $this->initials = $initialsString;
    }
    return 0;
  }
  
  /**
   * Set the Contact's preferred Forename
   *
   * Takes a valid input and sets the value as the Contact preferredForename
   * Input is valid if it is a non-null string less than 50 characters long
   *
   * @param string $input The string to be set as the preferredForename
   *
   * @return int 0 for success, -1 for failure
   */
  public function set_preferredForename($input)
  {
    if ($input == null || !is_string($input) || strlen($input) > 50) {
      return -1;
    }
    $this->preferredForename = $input;
    // Also needs to change 'salutation' field
    $salutationString = 'Dear '.$input;
    if (strlen($salutationString) > 80)	{
      return -1;
    } else {
      $this->salutation = $salutationString;
    }
    return 0;
  }

  /**
   * Set the Contact's surname
   *
   * sets the surname if the input string is valid
   * input is valid if it is a non-null string of less than 50 characters
   *
   * @param string $input The surname to be set
   *
   * @return int 0 for success, -1 for failure
   */
  public function set_surname($input)
  {
    if ($input == null || !is_string($input) || strlen($input) > 50) {
      return -1;
    }
    $this->surname = $input;
    return 0;
  }
  
  /**
   * Sets the value of sex
   *
   * Takes a valid input and sets sex to that value
   * input is valid if it is a string of the following values
   * * 'M' = male
   * * 'F' = female
   * * 'U' = undefined (a couple contact for example)
   *
   * @param string $input the value to be set
   *
   * @return int 0 for success, -1 otherwise
   */
  public function set_sex($input)
  {
    if ($input != 'M' && $input != 'F' && $input != 'U') {
      return -1;
    }
    $this->sex = $input;
    return 0;
  }
  /* SETS <- */
  
  /* ADDS -> */
  /**
   * Add an address
   *
   * Adds a new Address object to the Contact
   *
   * @param string $address the first line of the Address
   * @param string $town The town art of the address
   * @param string $county the county part of the address
   * @param string $postcode the postcode for the address
   * @param bool $isDefault should this address be set as the default for the contact
   *
   * @return int 0 for success, -1 for failure
   */
  public function addAddress($address, $town, $county, $postcode, $isDefault)
  {
  	$this->loadAddressData();
  	if (is_string($address) && is_string($town) && is_string($county) && is_string($postcode) && is_bool($isDefault))
  	{
	  	$this->addresses[] = new Address(null, null, $this->istest);
	  	$this->addresses[count($this->addresses)-1]->set_Address($address);
	  	$this->addresses[count($this->addresses)-1]->set_Town($town);
	  	$this->addresses[count($this->addresses)-1]->set_County($county);
	  	$this->addresses[count($this->addresses)-1]->set_Postcode($postcode);
	  	$this->addresses[count($this->addresses)-1]->set_isDefault($isDefault);
	  	return 0;
  	}
  	else
  	{
  		return -1;
  	}
  }
  
  /**
   * Add a Communication Log
   *
   * Add a record of a communication to the Contact
   *
   * @param string $addresseeContactNumber Contact number of the person recieving the communication
   * @param string $addresseeAddressNumber Address number of the person recieving the communication
   * @param string $direction The direction of communication ('I' = incoming)
   * @param string $documentType ONWF = Online Web Form
   * @param string $topic ONLQ = Online Question
   * @param string $subTopic COU = Course, GEN = General, MEM = Membership, WEB = Website
   * @param string $documentClass R = Restricted
   * @param string $documentSubject 80 char max string for the subject
   * @param string $precis The actual body of the communication
   *
   * @return int 0 for success, -1 for failure
   */
  public function addCommunicationsLog($addresseeContactNumber,
  										$addresseeAddressNumber,
  										$direction,
  										$documentType,
  										$topic,
  										$subTopic,
  										$documentClass,
  										$documentSubject,
  										$precis
  										)
  {
  		// Trim the $documentSubject down to 80 chars max
  		$documentSubject = substr($documentSubject, 0 , 79);
  		// Grab a logical address number for this contact
  		$addressNumber = -1;
  		if (!$this->addressesLoaded)
  		{
  			$this->get_addresses();
  		}
  		for ($i = 0; $i < count($this->addresses); $i++)
  		{
  			$thisAddress = $this->addresses[$i];
  			if ($thisAddress->get_isDefault())
  			{
  				$addressNumber = $thisAddress->get_Address_Number();
  			}
  		}
  		if ($addressNumber == -1)
  		{
  			return -1; // not much point doing more if we don't have a default address number
  		}  		
  		$client = Care_Webservice::getInstance($this->istest);
	  	$method = 'AddCommunicationsLog';

	  	$params = array('params' => array(
	  	// details of recipient (could be anybody)
	  	'AddresseeContactNumber' => $addresseeContactNumber,
	  	'AddresseeAddressNumber' => $addresseeAddressNumber,
	  	// details of sender (this contact)
	  	'SenderContactNumber' => $this->contactNumber,
	  	'SenderAddressNumber' => $addressNumber,
	  	// current date in the appropriate format
	  	'Dated' => date('d/m/Y H:i:s'),
	  	
	  	'Direction' => $direction,
	  	'DocumentType' => $documentType,
	  	'Topic' => $topic,
	  	'SubTopic' => $subTopic,
	  	'DocumentClass' => $documentClass,
	  	'DocumentSubject' => $documentSubject,
	  	'Precis' => $precis,

	  	'Source' => 'SLFSRV' // standard for this framework
	  	));
  	
  	$return = $client->doMethod($method, $params);
  	
  	if (isset($return['AddCommunicationsLogResult']['DocumentNumber']))
  	{
  		return 0;
  	}
  	else
  	{
  		return -1;
  	}
  }
  /* ADDS <- */
  
  /* DATABASE SAVES -> */
  /**
   * Saves data to the CARE database
   *
   * This method handles all the calls required to save a Contact and its associated data
   * into the care db
   *
   * @return int 0 for success, -01 for failure
   */
  public function saveChanges()
  {
  	// Save private vars for this instance
  	$savePrivateVariablesReturn = $this->savePrivateVariables();
  	
  	if ($this->contactNumber != null)
  	{
	  	// Save addresses
	  	$this->loadAddressData();
	  	$saveAddressesReturn = 0;
	  	for ($i = 0; $i < count($this->addresses); $i++)
	  	{
	  		$return = $this->addresses[$i]->savePrivateVariables($this->contactNumber);
	  		if ($return == -1 && $saveAddressesReturn != -1)
	  		{
	  			$saveAddressesReturn = -1;
	  		}
	  	}
	  	// Save communications - just core types for now TODO: Expand for others at some point?
	  	$saveTelephoneReturn = $this->coreCommunications['T']->savePrivateVariables($this->contactNumber);
	  	$saveMobileReturn = $this->coreCommunications['MB']->savePrivateVariables($this->contactNumber);
	  	$saveEmailReturn = $this->coreCommunications['E']->savePrivateVariables($this->contactNumber);
	  	// Deal with fails
	  	if ($savePrivateVariablesReturn == -1 || $saveAddressesReturn == -1)
	  	{
	  		return -1;  // TODO: Add error logging to a file or email notification? (Generic error)
	  	}
	  	else
	  	{
	  		return 0;
	  	}
  	}
  }
  
  /**
   * Save a Contact to CARE
   *
   * This is the private method called by saveChanges to handle
   * saving all the data to the CARE database
   *
   *
   * @access private
   * @return int 0 for success, -1 for failure
   */
  private function savePrivateVariables()
  {
  	if ($this->contactNumber != null) // If updating an existing contact
  	{
	  	$client = Care_Webservice::getInstance($this->istest);
	  	$method = 'UpdateContact';
	  	$params = array('params' => array(
	  	'ContactNumber' => $this->contactNumber,
	  	'Title' => $this->title,
	  	'Forenames' => $this->forenames,
	  	'PreferredForename' => $this->preferredForename,
	  	'Surname' => $this->surname,
	  	'Sex' => $this->sex,
	  	'Initials' => $this->initials,
	  	'Salutation' => $this->salutation
	  	));
  	}
  	else // If adding a new contact
  	{
  		$client = Care_Webservice::getInstance($this->istest);
	  	$method = 'AddContact';
		//$branches = getBranchFromPostcode($this->addresses[0]->get_Postcode(), $this->istest);
		$branchCode = '';
		if (@count($branches) > 0) {
		  $branchCode = $branches[0]->getBranchCode();
		}
		
	  	$params = array('params' => array(
	  	// Personal details
	  	'Title' => $this->title,
	  	'Forenames' => $this->forenames,
	  	'PreferredForename' => $this->preferredForename,
	  	'Surname' => $this->surname,
	  	'Sex' => $this->sex,
	  	'Initials' => $this->initials,
	  	'Salutation' => $this->salutation,
		'Branch' => $branchCode,
		'OwnershipGroup' => $branchCode,
	  	// Address
	  	//'Address' => $this->addresses[0]->get_Address(),
	  	//'Town' => $this->addresses[0]->get_Town(),
	  	//'County' => $this->addresses[0]->get_County(),
	  	//'Country' => 'GB',
	  	//'Postcode' => $this->addresses[0]->get_Postcode(),
	  	// Contacts
	  	//'MobileNumber' => $this->coreCommunications['MB']->get_Number(),
	  	//'EmailAddress' => $this->coreCommunications['E']->get_Number(),
	  	//'DirectNumber' => $this->coreCommunications['T']->get_Number(),
	  	// Additional
	  	'Source' => 'SLFSRV'
	  	));
  	}
  	
  	$return = $client->doMethod($method, $params);
  	if ($this->contactNumber != null) // If updating an existing contact
  	{
	  	if (isset($return['UpdateContactResult']['ContactNumber']))
	  	{
	  		if ((string)$return['UpdateContactResult']['ContactNumber'] == (string)$this->contactNumber)
	  		{
	  			return 0;
	  		}
	  		else
	  		{
	  			return -1; // TODO: We did get a return but it doesn't match our user number, write to log file and/or email
	  		}
	  	}
	  	else
	  	{
	  		return -1; // TODO: Return type not as expected from web service, write to log file and/or email
	  	}
  	}
  	else // If adding a new contact
  	{
  		if (isset($return['AddContactResult']['ContactNumber']))
	  	{
	  		$this->contactNumber = $return['AddContactResult']['ContactNumber']; // any point in this?
	  		self::__construct($this->contactNumber,
	  						//$_SESSION['isTest'],
							"TURE",
	  						null,
	  						null,
	  						null,
	  						null,
	  						null,
	  						null,
	  						null,
	  						null,
	  						null
	  						//$this->coreCommunications['T']->get_Number(),
	  						//$this->coreCommunications['MB']->get_Number(),
	  						//$this->coreCommunications['E']->get_Number()
	  						); // reconstruct
	  		return 0;
	  	}
	  	else
	  	{
	  		return -1;
	  	}
  	}
  	
  }
  /* DATABASE SAVES <- */
  
  // Add pampers contact 
  protected function addPampersContact ($contactNumber = null,
  							                     $istest = false,
  							                     $forenames = null,
  							                     $surname = null,
  							                     $sex = null,
							                     $title = null,
							                     $email = null)
  {
  	$this->success = false;
	$this->reason = array(); 
    $this->istest = $istest;
  	
    if ($contactNumber == null) // assume new contact registering - manual 
    { 	
    	if ($forenames != null &&
    		$surname != null &&
    		$sex != null &&
			$title != null &&
			$email != null)
    	{
    		// set personal details
    		$this->set_title($title);
    		$this->set_forenames($forenames);
    		$this->set_surname($surname);
    		$this->set_sex($sex);
			$this->coreCommunications['E']->set_Number($email);
    		$this->savePrivateVariables($this->contactNumber);
		  }
    	else
    	{
    		$this->success = FALSE;
		    $this->reason[] = "Not enough data to make a valid new record";
		    return -1;
    	}
    }
  }
}
?>