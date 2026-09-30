<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

//include "../includes/config.php";
$PageTitle="Purchase Code";
$PageTitleFolder="Admin";
$PageTitleLink="home";



?>

<!doctype html>
<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body>
 <?php  include ($IncludesPath."PreLoader.php"); ?>
        <div class="my-5 pt-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center my-5">
                           <h3 class="text-uppercase">Please Enter Purchase Code</h3>
                            
							<div class="form-group auth-form-group-custom mb-4">
                                                        <i class="ri-user-2-line auti-custom-input-icon"></i>
                                                        <label for="username">Purchase Code</label>
                                                        <input max="10" required type="text" 
														class="form-control" id="PurchaseCode" name="PurchaseCode" placeholder="Enter Purchase Code">
                                                    </div>
							
							<div class="mt-5 text-center">
                                <button class="btn btn-primary waves-effect waves-light" 
								id="submit">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include ($IncludesPath."vendor-scripts.php");?>

        <script src="<?php echo $BaseDist?>assets/js/app.js"></script>

    </body>
</html>

<script>

	$(document).on('click', '#submit', function(e){
	e.preventDefault();

	 
	 var PurchaseCode=$('#PurchaseCode').val();
	 //alert(PurchaseCode);
	 //return;
	 
	$.ajax({	
	type: 'POST',
	dataType: "json",
    url: 'https://serp.silverlights.in/api/api.php?f=ValidatePurchase',
    data: JSON.stringify({'PurchaseCode':PurchaseCode}),  
    success: function(data){
		
		var JsonData=JSON.parse(JSON.stringify(data));

		if(JsonData['Error'] == null){
		submitPurchase(data);	 
		}else{
			alert(JsonData['Error']);
		} 
			
     }
  });
  });


function submitPurchase(data){
	
	var $BaseUrl='<?php echo $BaseUrl ?>';
	
	$.ajax({	
	type: 'POST',
    url: 'AjaxValidate',
    data: {'ClientData':data},  
    success: function(data){
		//alert('hi');
		window.location.replace($BaseUrl);
     }
  });
}

</script>
