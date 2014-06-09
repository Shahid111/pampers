<?php
/**
 * @name insert_data.php
 *
 * @usage Uploads txt file and inserts data into database
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
	include_once '../lib/event.class.php';
	include_once '../lib/member.class.php';
	include_once '../lib/nct_webservice.class.php';
	include_once '../lib/nct_webservice_magento.class.php';
	include_once '../lib/organisation.class.php';
	include_once '../lib/phppostcode.php';
	include_once '../lib/role.class.php';
	include_once '../lib/user.class.php';
	include_once '../lib/venue.class.php';
	
	$path = "/var/www/database_dept/pampers";
	$dir = "$path/uploads";
	$files = scandir($dir);

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
	$loaded_on = date("d/m/Y H:i:s");
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
	
	// empty the table by passing special variable $delete_data, so that
	// the data in the table get deleted
	$delete_data = 1;
	$client  = Nct_Webservice::getInstance(true);
	$delete_header_message = $client->doMethod("savePampersHeaders",array($delete_data));
	if ($delete_header_message) {
		echo "Success : data deleted from ext_pampers_import_header table.<br />";
	} else {
		echo "Failure : Could not delete data from ext_pampers_import_header.<br />";
	}
	// delete data from ext_pampers_import_holding
	$delete_holding_message = $client->doMethod("savePampersData",array($delete_data));
	if ($delete_holding_message) {
		echo "Success : data deleted from ext_pampers_import_holding.<br />";
	} else {
		echo "Failure : Could not delete data from ext_pampers_import_holding.<br />";
	}
	$delet_data = 0;
	
	$row = 1;
	// read the file
	$handle = fopen("uploads/$uploaded_files[0]", "r");
	if ($handle) {
		while (($line = fgets($handle, 4096)) !== false) {
			if ($row == 1) {
				$client  = Nct_Webservice::getInstance(true);
				$header_message = $client->doMethod("savePampersHeaders",array($source_date,$notes,$loaded_by,$filename,$loaded_on));
				if($header_message) {
					echo "Success : Header fields are inserted<br />";
				}
				$row = 2;
				continue; 
			} 
			
			$data = explode("\t", $line);
			$client  = Nct_Webservice::getInstance(true);
			$message = $client->doMethod("savePampersData",array($data[0],$data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8],$data[9],preg_replace( "/\r|\n/", "", $data[10])));
			if(!$message) {
				echo "Error : $row has not been inserted into database<br />";
			}
			//echo "Row : $row<br />";
			//echo print_r($data,true);
			//echo "<br />=========================<br />";
			$row++;
		}
		echo "Success : $row rows has been inserted into databases ";
		if (!feof($handle)) {
			echo "Error: unexpected fgets() fail\n";
		}
	}
    fclose($handle);
	
	// Delete the file after the data analysis
	unlink("uploads/$uploaded_files[0]");
?>



