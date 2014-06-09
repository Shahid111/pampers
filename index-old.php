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
 *
 */
 
?>

<?php 
	// include the header file
	include('templates/header.php');
?>
<div id="content-container" style="width: 600px; margin: 0 auto;min-height: 260px;">
<h1>Upload file</h1>
Fields maked with * are mandatory:<br /><br />
<form>
	<form name="input" action="upload.php" method="post">
	Souce date*:<br />
	<input type="text" name="souce-date"><br /><br />
	Notes: <br />
	<textarea name="notes"cols="52" rows="10"></textarea>
</form> 
</form>
<br />
Please upload a file:
<br /><br />
<div id="mulitplefileuploader">Upload</div>

<div id="status"></div>
<script>
$(document).ready(function()
{
var settings = {
    url: "upload.php",
    dragDrop:true,
    fileName: "myfile",
    allowedTypes:"txt",	
    returnType:"html",
	 onSuccess:function(files,data,xhr)
    {
		//alert("test");
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
		xmlhttp.open("POST","get_data.php",true);
		document.getElementById("response").innerHTML = "<img class=\"\" height=\"120\" width=\"280\" src=\"img/wait.gif\">";
		xmlhttp.send();
		
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
<div id="response" style="margin-top: 20px;color:black;padding-bottom:10px;display: none;"><b>Status Message:...</b></div>
</div>
<?php 
	// include the footer file
	include('templates/footer.php');
?>
