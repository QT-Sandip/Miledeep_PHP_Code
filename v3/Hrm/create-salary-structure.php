<?php 

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT .'config/api-call.php';


if(isset($_GET["id"]) && !empty($_GET["id"])) {
    $sID = $_GET["id"];
} else {
    $sID = "";
}

$bodyContent = array(
    "StructureName" => "",
    "Deduction" => "",
    "Addition" => "",
    "StructureID" => $sID
);
 
$api = API_SALARY_STRUCTURE_MASTER;

$resList = CallAPI($api, "GET", "", $bodyContent);

$error =  $resList->Error;
$resData =  $resList->Data;
$AllValues =  $resList->Data->AllValues;
$SelectedList =  $resList->Data->SelectedValues;

if(isset($_POST['submit'])) {	

	$structureName = $_POST["txtStructureName"];
	$hdnminus = $_POST["hdnminus"];
	$hdnplus = $_POST["hdnplus"];
	
    $bodyContent = array(
        "StructureName" => $structureName,
        "Deduction" => $hdnminus,
        "Addition" => $hdnplus,
        "StructureID" => !empty($sID) ? $sID : ""
    );
     
    $api = API_SALARY_STRUCTURE_MASTER;
    
    $result = CallAPI($api, "POST", "", $bodyContent);

    if(!isset($result->Error) || empty($result->Error)) {
        header('Location:salary-stractures-list');
        exit;
    }

}

?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . 'includes/head.php' ?>

    </head>

    <?php include_once ROOT . 'includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . 'includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . 'includes/page-header.php' ?>   

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">

                                        <form role="form" method="post" id="frmEditRoles">

                                            <div class="row">
                                                <div class="form-group col-sm-3" >
                                                    <label for="txtStructureName">Structure Name<span style="color:red">*</span></label>
                                                    <input type="text" required class="form-control" name="txtStructureName" id="txtStructureName"  value="<?php echo $resData->StructureName ?>" />
                                                    <input type="hidden" id="hdnStructureID" name="hdnStructureID" value="<?php echo $resData->StructureID ?>" />
                                                </div>
                                            </div>           

                                            <div class="row">                                                                    
                                            <?php 
                                                $i = 1;
                                                foreach($AllValues as $item) {
                                                    echo '
                                                    <div class="form-group col-sm-12 checkdiv chkdiv'.$item->Header.' mt-4 mb-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input headerCheck" type="checkbox" id="headerCheck'.$i.'" name="headerId[]" value="" />
                                                            <label class="form-check-label text-danger font-weight-bold" for="headerCheck'.$i.'" style="text-transform: uppercase"> '.$item->Header.' </label>
                                                            <input type="hidden" id="hdn'.$item->Header.'" name="hdn'.$item->Header.'" />
                                                        </div>';
                                                        $j = 1;         
                                                        foreach($item->Options as $subItem) {
                                                            echo '
                                                            <div class="form-check form-check-inline">';
                                                                if(!empty($sID)) {                                                            
                                                                    $checked =  in_array($subItem->ElementID, $SelectedList) ? "checked" : "" ;
                                                                }
                                                                echo '
                                                                <input type="checkbox" id="optionId'.$i.$j.'" name="optionId[]" '.$checked.'  value="'.$subItem->ElementID.'" class="form-check-input optionsCheck" />
                                                                <label for="optionId'.$i.$j.'" class="form-check-label">'.$subItem->ElementName.'</label>
                                                            </div>';
                                                            $j++;
                                                        }                       
                                                    echo '                                                        
                                                    </div> <hr />';
                                                    $i++;
                                                }
                                            ?>
                                            </div>

                                            <div class="col-xs-12 box-footer text-end">
                                                <!-- <input type="hidden" id="hdnHeaderOptions" name="hdnHeaderOptions" value="<?php // echo implode(", ",$headerValues); ?>" /> 
                                                    <input type="hidden" id="hdnOptions" name="hdnOptions" value="<?php // echo implode(", ", $optionsValue); ?>" />  -->
                                                <!--<a target="" href="LinersList.php" class="btn btn-danger">Close</a>-->
                                                <button type="submit"  name='submit' class="btn btn-success">
                                                <?php 
                                                    if(!empty($sID)) { 
                                                        echo "Update";
                                                    } else { 
                                                        echo "ADD";
                                                    } 
                                                ?>
                                                </button>
                                            </div>	
                                        </form>

                                    </div>
                                </div>


                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . 'includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . 'includes/common-js.php' ?>

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . 'includes/app-js.php' ?>

        <script>
            var m = '<?php echo $AllValues[0]->Header; ?>';
            var p = '<?php echo $AllValues[1]->Header; ?>';

            // function checkHeader() {
			// 	var selected = new Array();
			// 	$("input.headerCheck:checked").each(function () {
			// 		selected.push(this.value);
			// 	});
			// 	$('#hdnHeaderOptions').val(selected.join(","));				
			// }

			// function checkOptions() {
			// 	var selectedOptions = new Array();
			// 	$("input.optionsCheck:checked").each(function () {
			// 		selectedOptions.push(this.value);
			// 	});
			// 	$('#hdnOptions').val(selectedOptions.join(","));
			// }

            function checkOptionsMinus() {
				var minus = new Array();
                $(".chkdiv"+m+" .optionsCheck").each(function () {
                    if ($(this).is(':checked')){
					    minus.push(this.value);
                    }
				});
                $("#hdn"+m).val(minus.join(","));
			}
            function checkOptionsPlus() {
                var plus = new Array();
                $(".chkdiv"+p+" .optionsCheck").each(function () {
                    if ($(this).is(':checked')){
					    plus.push(this.value);
                    }
				});                
                $("#hdn"+p).val(plus.join(","));
			}

            $('document').ready(function(){
                $('.checkdiv input').change(function() {                
                    var OptionIds = $("input[name='OptionId']:checked").map(function() {
                                        return this.value;
                                    }).get().join(', ');
                                    $('#OptionId').val(OptionIds + '');
                });

                <?php if(!isset($_GET["id"])){ ?>			
                    $('input:checkbox').prop('checked', true);
                <?php } ?>

                checkOptionsMinus();
                checkOptionsPlus();

            }); 

            $(document).on("change", ".headerCheck", function() {

				if ($(this).is(':checked')){
					$(this).closest(".checkdiv").find(".optionsCheck").each(function (){
						$(this).prop("checked", true);
					});
				}else{
					$(this).closest(".checkdiv").find(".optionsCheck").each(function (){
						$(this).prop("checked", false);
					});
				}

                checkOptionsMinus();
                checkOptionsPlus();

			});

			$(document).on("change", ".optionsCheck", function() {

				var rowOptionsCheckedList = $(this).closest(".checkdiv").find(".optionsCheck:checked").length;
				if(rowOptionsCheckedList == 0) {
					$(this).closest(".checkdiv").find(".headerCheck").prop("checked", false);
				}else{
					$(this).closest(".checkdiv").find(".headerCheck").prop("checked", true);
				}

                checkOptionsMinus();
                checkOptionsPlus();
			});

		</script>

    </body>
</html>
