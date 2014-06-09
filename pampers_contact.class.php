<?php

/**
 * Pampers_contact Class
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
  							                     $istest = True,
  							                     $forenames = null,
  							                     $surname = null,
  							                     $sex = null,
							                     $title = null,
							                     $email = null)
  {
	$this->success = false;
	$this->reason = array(); 
    $this->istest = TRUE;
  	
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
			
    		
    		// set communication and method exits
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
		$this->coreCommunications['E']->set_Number($email);
		$this->saveChanges();
		echo "test: $contactNumber";
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