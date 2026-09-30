 <?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	
$PageTitle="ADD SALES PRICES";	
$PageTitleFolder="ColdStore";

extract($_POST);
extract($_GET);
$UNIT=$_SESSION["UNIT"];
$PostedDate=date('Y-m-d');


//ORIGINAL

$SqlCsOptions="SELECT * FROM `CsGeneralOptions`";
$StmtCsOptions=$iconn->query($SqlCsOptions);
 

$ARY_OPTIONS=array();
WHILE($RowCsOPtions=$StmtCsOptions->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$RowCsOPtions;
}

$SqlRmUsersList="SELECT * FROM `$RmUsersList`";
$StmtRmUsersList=$iconn->query($SqlRmUsersList);

$AryUserList=array();
while($RowRmUsersList=$StmtRmUsersList->fetch_array()) {
$AryUserList[]=$RowRmUsersList;
}





 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
				

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
              <div class="row">
                    <div class="col-12">
                        <div class="card">
								
							<form role="form" method="post">
                                <div class="card-body">
									
									<div class="row">

                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Variety Name</label>
				  
				<select class="form-control select2" name="Variety" id="Variety">
			    <?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='PRODUCT_VARIETY'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['CsOptID'];
				?>
			     <option <?php if($UNIT==$VALUE){ ?> selected="selected" <?php }?>
			    value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}?>
				<select>
				</div>
				
				
                <div class="form-group col-sm-12"  align="right">
		
				<button type="submit" style="background-color: #008000" name='submit' 
				     id="list"class="btn btn-primary">Search</button>			
			
                </div>
				

				</div>
              <!-- /.box-body -->


            </form>



             </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>				

<script>


document.addEventListener('blur', (e) => {
  // your custom code here
});
// user enters the tab (again)
document.addEventListener('focus', (e) => {
 alert("focus");
});
var ClickedId="usd1";
$(document).on("click", ":focus", function() {
	var ClickedId=this.id;
	if (ClickedId.includes("usd")) {
	var ClickedIndex = ClickedId.replace('usd','');
	$("#"+"Svalue"+ClickedIndex).text("NA");
	//alert(ClickedIndex);
	 $(document).on('change',"#"+ClickedId,function(e){			
			e.preventDefault();
			var Kgs=$("#"+"kgs"+ClickedIndex).text();
			var Usd=$("#"+"usd"+ClickedIndex).val();
			var Svalue=Math.round(Kgs*Usd);
			
			$("#"+"Svalue"+ClickedIndex).text(Svalue);
			
			
			
			});	
  
  

	
	}
  });
  
  
$(document).on('onChange','#Variety',function(e){

getList();	
});	

			


$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			
			getList();
			

			

    	});	

		$(document).on('click','#update',function(e){			
			e.preventDefault();
			var TotalItemsCount=$('#update').val();
			var Variety=$('#Variety').val();
		//	alert(TotalItemsCount);
			
			
		 for (i = 1; i < TotalItemsCount; i++) {
			 
			   var UsdId='#usd'+i;			  
			   var BaseRate=$(UsdId+"").val();
			   
			  // alert(BaseRate);
				
				if(!Number(BaseRate)) {
				 alert('Only Number Allowed for Price ' +BaseRate );				 
				  document.getElementById("usd"+i).focus();					
					return;					
				}
			} 
			
		var AryValues = [];
			
		for (i = 1; i < TotalItemsCount; i++) {
			   var GradeId='#grade'+i;
			   var UsdId='#usd'+i;
			  
			   var GradeValue=$(GradeId+"").text();
			   var Usd=$(UsdId+"").val();
			  // alert(GradeValue);
			   
			   var Value=GradeValue +'&'+Usd
			   AryValues.push(Value);
			}
			 
			
			$.ajax({
		  url:'/ColdStoreDev/Ajax/AjaxSalesPriceList.php',
		  type:'POST',
		  data:{'Type':'Update','Variety':Variety, 'Grades' : AryValues},
		  success:function(data){

			$("#ATT_DATA").html(data);
			$('#example1').DataTable();
					}
			});


		
    	});		
		
		
function getList(){
	
		var Variety=$('#Variety').val();
			
			if(Variety=='BOTH'){
				alert("Select Variety");
				return;
			}
	
				$.ajax({
		  url:'/ColdStoreDev/Ajax/AjaxSalesPriceList.php',
		  type:'POST',
		  data:{'Type':'List','Variety':Variety},
		  success:function(data){

			$("#ATT_DATA").html(data);
			$('#example1').DataTable();
					}
			})
}		





</script>
