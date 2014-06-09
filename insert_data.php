<?php

/**
 * @name insert_data.php
 *
 * @usage Uploads txt file and inserts data into database and then call web services
 * 
 * @author S S Qureshi
 *
 * @copyright NCT 2014
 * 
 * 
 */
 
	// include the library files for web services
	include_once '../lib/address.class.php';
	include_once '../lib/booking.class.php';
	include_once '../lib/branch.class.php';
	include_once '../lib/care_webservice.class.php';
	include_once '../lib/communication.class.php';
	include_once '../lib/contact.class.php';
	include_once '../lib/pampers_contact.class.php';
	include_once '../lib/event.class.php';
	include_once '../lib/member.class.php';
	include_once '../lib/nct_webservice.class.php';
	include_once '../lib/nct_webservice_magento.class.php';
	include_once '../lib/organisation.class.php';
	include_once '../lib/phppostcode.php';
	include_once '../lib/role.class.php';
	include_once '../lib/user.class.php';
	include_once '../lib/venue.class.php';
	
	// include connection file
	include_once 'db.php';
	
	$path = "/var/www/database_dept/pampers";
	$dir = "$path/uploads";
	$files = scandir($dir);
	$last_id;
	$last_batch;
	$data;
	$found;
	

	$uploaded_files = array();
	foreach($files as $file) {
		if($file === "." || $file== "..")
		continue;
		else
		$uploaded_files[] = $file;
	}
	
	// filename , loaded_on  , loaded_by  , source_date  , notes
	// get the data and notes fields
	
	$_SESSION["user"] = "shahid";
	//$header_data = array();
	$source_date =  $_POST["date"];
	$notes = $_POST["notes"];
	$loaded_by = $_SESSION["user"];
	$loaded_on = date("d/m/Y");
	$filename = $_POST["file_name"];
	

	if(!isset($_POST["date"]) || empty($_POST["date"])) {
		// delete the uploaded file
		unlink("uploads/$uploaded_files[0]");
		echo "Please fill in date field and try again!";
		exit;
	}
	
	if(preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", $_POST["date"]) === 0) { // wrong date format
			unlink("uploads/$uploaded_files[0]");
			echo "Wrong format! Please try again!";
			exit;
	}
	/**
		$client = Care_Webservice::getInstance(1);
	  	$method = 'AddContact';
		//$branches = getBranchFromPostcode($this->addresses[0]->get_Postcode(), $this->istest);
		$branchCode = '';
		if (@count($branches) > 0) {
		  $branchCode = $branches[0]->getBranchCode();
		}
		
	  	$params = array('params' => array(
	  	// Personal details
	  	'Title' => 'Mr',
	  	'Forenames' => 'shahid',
	  	'PreferredForename' => 'shahid',
	  	'Surname' => 'Qureshi',
	  	'Sex' => 'M',
	  	'Initials' => 'S',
	  	'Salutation' => 'Dear shahid',
		'Branch' => $branchCode,
		'OwnershipGroup' => $branchCode,
	  	// Address
	  	'Address' => '47 William St',
	  	'Town' => 'Bristol',
	  	'County' => 'Avon',
	  	'Country' => 'GB',
	  	'Postcode' => 'BS3 4TY',
	  	// Contacts
	  	//'MobileNumber' => '122345678',
	  	'EmailAddress' => 'test@yahoo.com',
	  	'DirectNumber' => '7894612',
	  	// Additional
	  	'Source' => 'SLFSRV'
	  	));
  	
  	
  	$return = $client->doMethod($method, $params);
	print_r($return);
	echo "<br />";
	die("ends");
	
	$client  = new Contact("","true","mr", "Shahid14", "Shahid14", "Qureshi14", "M", "test", "bristol", "avon", "BS3 4TY", "07886243919", "01314894561", "shahidmzd@yahoo.com" );
	echo "<br />";
	print_r($client->get_reason());
	die("test");
	
	**/
	$row = 1;
	$email = "";
	// read the file
	$handle = fopen("uploads/$uploaded_files[0]", "r");
	if ($handle) {
		while (($line = fgets($handle, 4096)) !== false) {
			if($row == "1") {
				$query = "insert into pampers_import_header(filename , loaded_on  , loaded_by  , source_date  , notes ) values (\"$filename\", \"$loaded_on\", \"$loaded_by\", \"$source_date\", \"$notes\")";
				$query = $dbh->prepare($query);
				$query->execute();
				$last_id = $dbh->lastInsertId();
				$row = 2;
				continue;
			}
			$data = explode("\t", $line);
			$email .= $data[9] . ":";
			if ($data[4] == "Mister") {
				$data[4] = "Mr";
			}
			$data[10] = preg_replace( "/\r|\n/", "", $data[10]); // get rid of new line character
			$query = "insert into pampers_import_detail (ID, Site_WDM_ID, firstName , lastName , gender , title , salutation , keycode , locale , WDM_campaign_ID, email , child_date_of_birth ) values";
			$query .= "(\"$last_id\",\"$data[0]\",\"$data[1]\",\"$data[2]\",\"$data[3]\",\"$data[4]\",\"$data[5]\",\"$data[6]\",\"$data[7]\",\"$data[8]\",\"$data[9]\",\"$data[10]\")";
			$query = $dbh->prepare($query);
			$query->execute();
			$row ++;
			
		}
		// echo "$email";
		// call the web service to find out if this email is already in database?
		$email = rtrim($email, ':'); // remove the last separator ":"
		$client  = Nct_Webservice::getInstance(true);
		$found = $client->doMethod("savePampersHeaders",array($email));
		echo "Success : $row rows has been inserted into databases<br /><br /> ";
		echo "emails not found in database: $found<br /><br />";
		
		//echo "last insert id was : $last_id<br /> ";
		$last_batch = $last_id - 1;
		
		if (!feof($handle)) {
			echo "Error: unexpected fgets() fail\n";
		}
	}
    fclose($handle);
	
		
	// Delete the file after the data analysis
	unlink("$path/uploads/$uploaded_files[0]");
	
	// empty last batch table
	$del_last_batch_query = "delete from pampers_import_last_batch ";
	$del_last_batch_query = $dbh->prepare($del_last_batch_query); 
	$del_last_batch_query->execute();
	
	// update last batch table 
	$last_batch_query = "insert pampers_import_last_batch select * from  pampers_import_detail where id =\"$last_batch\" ";
	$last_batch_query = $dbh->prepare($last_batch_query);
	$last_batch_query->execute();
	
	// empty the table except last two batches
	$del_query = "delete from pampers_import_detail where id < $last_batch";
	$del_query = $dbh->prepare($del_query);
	$del_query->execute();
	
	$email = str_replace(":", "\",\"", $email); // replace : with comma and quotes
	$email = rtrim($email, ', '); // remove last comma
	$email = "\"" . $email . "\""; // enclose whole string in double quotes 
	
	/**
	$client  = new PampersContact("",TRUE, "Shahid10", "Qureshi10", "M", "Mr", "shahidmzd@yahoo.com" );
	//echo $client->addPampersContact("",TRUE, "Shahid10", "Qureshi10", "M", "Mr", "shahidmzd@yahoo.com" );
	echo "<br />";
	print_r($client->get_reason());
	die("test");
	**/
	
	if(!empty($found)) {
		// create a contact in CARE for emails not found in the CARE
		$email_array = explode(":", $found);
		//print_r($email_array);
		foreach ($email_array as $email) {
			$local_query = "select * from pampers_import_detail where email = '$email' and id= '$last_id' ";
			$local_query = $dbh->prepare($local_query);
			$local_query->execute();
			$local_result = $local_query->fetchAll();
			// Call Web service to create contact in database
			$client = Care_Webservice::getInstance(1);
			$method = 'AddContact';
			//$branches = getBranchFromPostcode($this->addresses[0]->get_Postcode(), $this->istest);
			$branchCode = '';
			if (@count($branches) > 0) {
			$branchCode = $branches[0]->getBranchCode();
			}
		
			$params = array('params' => array(
			// Personal details
			'Title' => $local_result['title'],
			'Forenames' => $local_result['firstname'],
			'PreferredForename' => $local_result['title'],
			'Surname' => $local_result['surname'],
			'Sex' => $local_result['sex'],
			'Initials' => substr($local_query['title'],0,1),
			'Salutation' => "Dear" . "$local_result['firstname']",
			'Branch' => $branchCode,
			'OwnershipGroup' => $branchCode,
			// Address
			'AddressNumber' =>  0,
			// Contacts
			'EmailAddress' => $local_query['email'],
			// Additional
			'Source' => 'Pampers Script'
			));
		// Call the method with prams
		$return = $client->doMethod($method, $params);
			
		}
	} else {
		echo "All email addresses were already in the CARE Database<br />";
	}
	
	
	//die("die");
	$compare_query = "select * from pampers_import_last_batch where email not in($email) ";
	$compare_query = $dbh->prepare($compare_query);
	$compare_query->execute();

	$result = $compare_query->fetchAll();
	
	if ($result) {
		echo "Email addresses which were not included in last batch<br /><br />";
		foreach($result as $row) {
			echo $row['email'] . "<br />";
		}
	} else {
		echo "No new email in this batch which was not uploaded in last batch!<br />";
	}
	
?>



