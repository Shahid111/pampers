<?php

/**
 * Contact Pampers Class
 *
 * This class extends Contact class and being used  to insert pampers data into
 * CARE database.
 *
 * @author S S Qureshi
 * @version 1.0
 * @package NCT
 *
 */

class PampersContact extends Contact 
{

  // Add pampers contact 
  public function PampersContact ($contactNumber = null,
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