<?php
/**
 *
 * @file Index.php
 *
 * @usage reads excel file and insert data into database
 * 
 * @author S S Qureshi
 *
 * @copyright NCT 2014
 *
 */
 
 // enable sessions
session_start();
 // check if the user is logged in
 if($_SESSION["authenticated"] != true) {
	echo "not logged in <br />";
	header("Location: ../login.php");
	exit();
 }

// include the header file
include('templates/header.php');
?>

<div id="content-container" style="width: 600px; margin: 0 auto;min-height: 260px;">
	<div style=" width: 456px; margin: 0 auto;">
	<strong><i>Note : Fields maked with <span style="color:red">*</span> are mandatory:</i></strong><br /><br />
	<form>
		<form name="form1" id="form1" action="upload.php" method="post">
		<strong>Source date(dd/mm/yyyy): <span style="color:red">*</span></strong><br />
		<input type="text" name="souce_date" id="source_date"><br /><br />
		<strong>Loaded by:</strong><br />
		<input type="text" name="loaded_by" id="loaded_by"><br /><br />
		<strong>Notes:</strong> <br />
		<textarea name="notes"cols="52" rows="10" id="notes"></textarea>
	</form> 
	<br /><br />
	<strong>Please upload a file:</strong>
	<br />
	<div id="mulitplefileuploader">Upload</div>

	<div id="status"></div>
	<script>
	$(document).ready(function()
	{
	var source_date;
	var notes;
	var loaded_by;
	var settings = {
		url: "upload_txt.php",
		dragDrop:true,
		fileName: "myfile",
		allowedTypes:"txt",	
		returnType:"html",
		dynamicFormData: function() {
			var data ={ };
		},
		onSuccess:function(files,data,xhr)
		{
			//alert(files);
			source_date  = document.getElementById('source_date').value;
			notes  = document.getElementById('notes').value;
			loaded_by  = document.getElementById('loaded_by').value;
			//alert(notes);
			var xmlhttp;
			if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp=new XMLHttpRequest();
			}else {// code for IE6, IE5
				xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange=function() {
				if (xmlhttp.readyState==4 && xmlhttp.status==200){
					if (xmlhttp.responseText == "") {
						document.getElementById("response").innerHTML = "No result found";
					} else {
						document.getElementById("response").innerHTML = xmlhttp.responseText;
					}
				}
			}
			xmlhttp.open("POST","insert_data.php",true);
			document.getElementById("response").innerHTML = "<img class=\"\" height=\"120\" width=\"280\" src=\"img/wait.gif\">";
			xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
			xmlhttp.send("date=" +source_date + "&" + "notes=" + notes + "&" + "file_name=" + files);
			
		},
		showDelete:false,
		deleteCallback: function(data,pd)
		{
		for(var i=0;i<data.length;i++)
		{
			$.post("delete.php",{op:"delete",name:data[i]},
			function(resp, textStatus, jqXHR)
			{
				//Show Message  
				$("#status").append("<div>File Deleted</div>");      
			});
		 }      
		pd.statusbar.hide(); //You choice to hide/not.

	}
	}
	var uploadObj = $("#mulitplefileuploader").uploadFile(settings);

	$('.ajax-file-upload-green').click(function() {
		alert("test");
	});
	});
	</script>
	<br /><br />
	<strong>Status Message:</strong><br />
	<div id="response" style="color:black;padding-bottom:10px;display: block;">No file uploaded yet</div>
	</div>
</div>
<?php 
	// include the footer file
	include('templates/footer.php');
?>

