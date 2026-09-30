<?php 
$PageTitle = "EQUIPMENT USED Master";
$PageTitleFolder = "Quality Control";
$Currentyear = date('Y');
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
//Paramaters list
$SqlParameterList = "SELECT TestID as ParameterID,TestName as ParameterName FROM `QcTestList`";
$StmtParameterList = $iconn->query($SqlParameterList);

//end here
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php'?>
    <style>
        /* .select2-container--open {
                z-index: 9999999
            }             */
    </style>

</head>

<?php include_once ROOT . '/includes/body-preloader.php'?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php 
    include_once ROOT . '/includes/menu.php';
    
    ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include($IncludesPath . "page-title.php"); ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <div class="text-center">                                    
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal" onclick="AddnewItem()">Add New Equipment</button>
                                </div>
                            </div>
                        </div>
                        <div id="divDataList">

                        </div>
                        <input type="hidden" id="hdnActionType" />



                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php'?>

    </div>
    <!-- end main content-->
    <!-- Modal -->    
        <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              
              <div class="modal-header">
                <h5 class="modal-title" id="addItemModalLabel">Add New Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              
              <form id="addItemForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ParameterID">PARAMETER </label>
                        <select class="form-control select2" name="ParameterID" id="ParameterID" required
                            >
                            <option Value="0"> Select</option>
                            <?php while ($RowParameterList = $StmtParameterList->fetch_array()) {
                                ?>
                                <option Value="<?php echo $RowParameterList['ParameterID']; ?>">
                                    <?php echo $RowParameterList['ParameterName']; ?>
                                </option>
                            <?php } ?>       
                        </select>
                    </div> 
                    <div class="mb-3">
                        <label for="NameMediaUsed">NAME OF THE MEDIA USED</label>
                        <select class="form-control select2" name="MediaId" id="MediaId" required
                            >
                            <option Value=""> Select</option>                            
                        </select>
                    </div>                 
                  <div class="mb-3">
                    <label for="InstrumentName" class="form-label">Equipment Name</label>
                    <input type="text" class="form-control" id="InstrumentName" name="InstrumentName" required>
                    <input type="hidden" name="InstrumentId" id="InstrumentId" value="">
                  </div>

                                   <div class="mb-3">
                                            <label for="txtcalibrationDate" class="form-label">Calibration Date</label>
                                            <input type="date" class="form-control" id="txtcalibrationDate"
                                                name="txtcalibrationDate" placeholder=""
                                                max="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                             <div class="mb-3">
                                            <label for="txtduedate" class="form-label">Due Date</label>
                                            <input type="date" class="form-control" id="txtduedate"
                                                name="txtduedate " placeholder="" />
                                        </div>

                                        

                </div>
                
                <div class="modal-footer">
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-primary">Add Equipment</button>
                </div>
              </form>
              
            </div>
          </div>
        </div>

</div>
<!-- END layout-wrapper -->




<?php include_once ROOT . '/includes/common-js.php'?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php'?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


<script>
    function AddnewItem(){
        $("#ParameterID").val("");
        $("#MediaId").html("");
        $("#MediaId").val("");
        $("#InstrumentName").val("");
        $("#InstrumentId").val("");
    }

    $('#ParameterID').change(function() { 
        $("#NameMediaUsed").html("");
        $("#InstrumentName").val("");
        var parameterVal = $("#ParameterID").val();                
        if (parameterVal == 0) {
            return false;
        }
        $.ajax({
            url: 'Ajax/Ajax-inhouse-getmedia.php',
            type: 'POST',
            data: { parameterId: parameterVal, action: "getMediaList" },
            success: function(data) {
                console.log(data); // for debugging
                $("#MediaId").html(data);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });
    });
    function EditInstrument(InstrumentId){
        $.ajax({
            url: 'Ajax/ajax-instrument-master',
            type: 'POST',
            data: {
                InstrumentId: InstrumentId,
                action: "GetInstrumentData",                
            },
            success: function(response) {                
                if (response.Data && response.Data.length > 0) {
                    const InstrumentName = response.Data[0].InstrumentName;                    
                    const InstrumentId = response.Data[0].InstrumentId;                    
                    const ParameterId = response.Data[0].ParameterID;                    
                    const MediaId = response.Data[0].MediaId;                    
                    $("#ParameterID").val(ParameterId).trigger('change') && setTimeout(() => $("#MediaId").val(MediaId), 500);;
                    $("#InstrumentName").val(InstrumentName);
                    $("#InstrumentId").val(InstrumentId);

                    //$("#MediaId").val(MediaId);
                } else {
                    
                }
                const modalElement = document.getElementById("addItemModal");
                if (modalElement) {
                    const modalInstance = new bootstrap.Modal(modalElement);
                    modalInstance.show();
                } else {
                    console.error("Modal element not found");
                }
            }
        });
        
    }
    $("#addItemForm").submit(function(){        
        event.preventDefault();        
        var ParameterId = "";
        var MediaId = "";
        var MediaName = "";
        ParameterId = $("#ParameterID").val();        
        MediaId = $("#MediaId").val();
        InstrumentId = $("#InstrumentId").val();
        InstrumentName = $("#InstrumentName").val();
        var CalibrationDate = $("#txtcalibrationDate").val(); 
        var DueDate = $("#txtduedate").val();
        
        if( ParameterId == "" || ParameterId == 0){
            alert("Please select the Parameter Name");
            return false;
        }else if( MediaId == "" || MediaId == 0){
            alert("Please select the Media Name");
            return false;
        }else if( InstrumentName == "" ){
            alert("Please fill the equipment Name");
            return false;
        }

        $.ajax({
            url: 'Ajax/ajax-instrument-master',
            type: 'POST',
            data: {
                ParameterId: ParameterId,
                MediaId: MediaId,
                InstrumentId: InstrumentId,
                InstrumentName: InstrumentName,
                CalibrationDate: CalibrationDate, 
                DueDate: DueDate,
                action: "InsertMasterEquipment",                
            },
            success: function(data) {
                if(data.Error){
                    alert(data.Error);
                    return false;
                }                
                $("#ParameterID").val(0);
                $("#InstrumentId").val(0);
                $("#InstrumentName").val("");
                $("#MediaId").val(0);
                $("#txtcalibrationDate").val(""); 
                $("#txtduedate").val("");
                ShowList("1", "", "0");
                const modalElement = document.getElementById("addItemModal");
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                modalInstance.hide();

          // Reset form
          
                return false;
            }
        });          
    
    });

    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();        

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-instrument-master',
            type: 'POST',
            data: {
                PageNo: pageNo,
                SearchKey: searchkey,
                LoadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }


    $(document).ready(function() {
        ShowList("1", "", "0");
    });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function() {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function() {
        ShowList();
    })
</script>

</body>

</html>
