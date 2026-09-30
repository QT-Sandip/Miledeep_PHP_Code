<?php
session_start();

$FOLDER_NAME=$_GET['ID'];



include "../includes/config.php";

  if(isset($_POST['upload']))
  {
  	if($FOLDER_NAME != "")
  	{
  		$foldername=$FOLDER_NAME;
  		if(!is_dir($foldername))
  			mkdir($foldername);
  		foreach($_FILES['files']['name'] as $i => $name)
		{
  			if(!is_dir($name))
  			{
  				//mkdir($foldername."/".$name);
  				if(strlen($_FILES['files']['name'][$i]) > 1)
  				{
  					move_uploaded_file($_FILES['files']['tmp_name'][$i],$foldername."/".$name);
  				}
  			}
  			else
  			{
  				if(strlen($_FILES['files']['name'][$i]) > 1)
  				{
					move_uploaded_file($_FILES['files']['tmp_name'][$i],$foldername."/".$name);
  				}
  			}
  		}
  		echo "Folder is successfully uploaded";
  	}else{		
  		echo "Upload folder name is empty";
      }

  }
  
  






?>

  <html>
  <head>
  <title>Upload Folder using PHP </title>
  </head>
  <body>
  <form action="#" method="post" enctype="multipart/form-data"> 
 
  Select Folder to Upload: <input type="file" name="files[]" id="files" multiple directory="" webkitdirectory="" moxdirectory="" /><br/><br/> 
  <input type="Submit" value="Upload" name="upload" />
  </form>
  </body>
  </html>