<?php
$output_dir = "uploads/";
if(isset($_FILES["myfile"]))
{
	//return $data;
	$ret = array();

	$error =$_FILES["myfile"]["error"];
	//You need to handle  both cases
	//If Any browser does not support serializing of multiple files using FormData() 
	if(!is_array($_FILES["myfile"]["name"])) //single file
	{
 	 	$fileName = $_FILES["myfile"]["name"];
 		if (move_uploaded_file($_FILES["myfile"]["tmp_name"],$output_dir.$fileName)) {
			$ret[]= $fileName;
		} else {
			die("Problem occurred...couldn't move the file from /tmp directory\n ");
		}
	}
	else  //Multiple files, file[]
	{
	  $fileCount = count($_FILES["myfile"]["name"]);
	  for($i=0; $i < $fileCount; $i++)
	  {
	  	$fileName = $_FILES["myfile"]["name"][$i];
		if (move_uploaded_file($_FILES["myfile"]["tmp_name"][$i],$output_dir.$fileName) {
			$ret[]= $fileName;
		} else {
			die("Problem occurred...couldn't move the file from /tmp directory\n ");
		}
	  }
	
	}
	unset($ret);
    echo json_encode($ret);
 }
 ?>