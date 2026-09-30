<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$PageTitle = "Freight Rates List";
$PageTitleFolder = "Masters";
// $BranchID=$_SESSION['BranchID']; 
// $AccessType=$_SESSION['AccessType']; 


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$RecordsPerPage = 100;
$where = '';
$queryParm = '';
if(!empty($_GET['liner_id'])){
    $where .= " AND SFR.Liner=".$_GET['liner_id'];
    $queryParm = '&liner_id='.$_GET['liner_id'];
}
if(empty($_POST['submit'])){
    $SqlLinerMaster = "SELECT * FROM `ScrmLinerMaster`";
    $StmtLinerMaster = $iconn->query($SqlLinerMaster);

    $cntSql = "SELECT COUNT(ID) CNT FROM `ScrmFreightRates` SFR WHERE 1  $where";
    $stmtCount = $iconn->query($cntSql);
    $rowCount = $stmtCount->fetch_assoc();
    $TotalCount = $rowCount['CNT'];
    $PageCount = ceil($TotalCount / $RecordsPerPage);
    $PageNo = !empty($_GET['PageNo'])?$_GET['PageNo']:1;

    $SqlFreightRates = "SELECT SFR.ID,`LinerName`,
                        (SELECT GROUP_CONCAT(`Name`) FROM ScrmCountriesList WHERE FIND_IN_SET(id,Country ) > 0)
                         as CountryName,
                        `FromDate`,`ToDate`,RateType,Combined,
                        (SELECT GROUP_CONCAT(`Name`) FROM ScrmPortCities WHERE FIND_IN_SET(PortId,DestinationPorT ) > 0) AS portName FROM `ScrmFreightRates` as SFR 
                        left join ScrmLinerMaster as SLM on SLM.LinerID=SFR.Liner
                        WHERE 1 $where ";

    $SqlFreightRates .= " ORDER BY ID DESC";
    $LimitMax = ($PageNo - 1) * $RecordsPerPage;
    $SqlFreightRates .= ' LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;
    $StmtFreightRates = $iconn->query($SqlFreightRates);
}
/*duplicate freight charges insert start*/
if(!empty($_POST['submit']) && $_POST['submit']=='freightDuplicateSubmit' && !empty($_POST['freightRateId'])){
    $freightRatesQuery = "SELECT * FROM ScrmFreightRates WHERE ID=".$_POST['freightRateId'];
    $StmtfreightRatesQuery= $iconn->query($freightRatesQuery);
    $freightRatesRes = $StmtfreightRatesQuery->fetch_assoc();
    if(!empty($freightRatesRes)){
        $linerId = $freightRatesRes['Liner'];
        $portIds = $freightRatesRes['DestinationPort'];
        $pluginFD = $freightRatesRes['PlugInFD'];
        $thcAndDC = $freightRatesRes['THCAndDC'];
        $genset = $freightRatesRes['Genset'];
        $fromDate = $_POST['from_date'];
        $toDate = $_POST['to_date'];
        $RateType = $freightRatesRes["RateType"];
        $Dfd1 = $freightRatesRes["DFD1"];
        $Dfd2 = $freightRatesRes["DFD2"];
        $Combined = $freightRatesRes["Combined"];
        $Country = $freightRatesRes["Country"];
        $LinerContractId = $freightRatesRes["LinerContractId"];
        $OFD = $freightRatesRes["OFD"];
        $sqlInsertFreightQuery = "INSERT INTO `ScrmFreightRates`
                    (`Liner`, `DestinationPort`, `FromDate`, `ToDate`, `PlugInFD`,`THCAndDC`, `Genset`,`RateType`,`DFD1`,`DFD2`,`Combined`,Country,LinerContractId,OFD) 
                    VALUES ('$linerId','$portIds','$fromDate','$toDate','$pluginFD','$thcAndDC','$genset','$RateType','$Dfd1','$Dfd2','$Combined','$Country','$LinerContractId','$OFD')";
        $stmtInsert = $iconn->query($sqlInsertFreightQuery);
        if(!empty($iconn->insert_id)){
            $parentId = $iconn->insert_id;
            legacyAuditLogInsertById('ScrmFreightRates', 'ID', $parentId, $_POST, [
                'Remarks' => 'Freight rate copied from list',
            ], 'Sales');
            $freightRatesBrkupQuery = "SELECT * FROM `ScrmFreightRatesBreakUp` WHERE `ParentID`=".$_POST['freightRateId'];
            $StmtfreightRatesBrkup = $iconn->query($freightRatesBrkupQuery);
            $RowfreightRatesRes = [];
            while ($freightRatesRes = $StmtfreightRatesBrkup->fetch_assoc()) {
                $port_id = $freightRatesRes["DestinationPortID"];
                //$RateType = $freightRatesRes["RateType"];
                $Rate = $freightRatesRes["Rate"];
                 $Dfd1 = $freightRatesRes["DFD1"];
                $Dfd2 = $freightRatesRes["DFD2"];
                //$Combined = $freightRatesRes["Combined"]; 
                $ContryCode = $freightRatesRes["ContryCode"];
                $Remarks = $freightRatesRes["Remarks"];
                $Via = $freightRatesRes["Via"];
                $date = date('Y-m-d h:i:s');
                $sqlFreightBkpInsertQuery = "INSERT INTO `ScrmFreightRatesBreakUp`
                            (`ParentID`, `DestinationPortID`,  `Rate`,CreatedOn,DFD1,DFD2,ContryCode,Remarks,Via) 
                        VALUES 
                            ('$parentId','$port_id','$Rate','$date','$Dfd1','$Dfd2','$ContryCode','$Remarks','$Via')";
                $statmentBkup = $iconn->query($sqlFreightBkpInsertQuery);
            }
            //sleep(30);
            if(!empty($statmentBkup)){
                $redirectionLink = 'AddFreightRatesNew.php?id='.$parentId;
                echo '<script language="javascript" type="text/javascript"> 
                            /* alert("'.$redirectionLink.'"); */
                            alert("Fright Rates added Successfully");
                           /*  window.location = "FreightRatesList.php"; */
                          window.location.href = "'.$redirectionLink.'";
                    </script>';
                    /*  header( 'location:'.$redirectionLink);
                    die; */
            }                    
        }else{
            echo '<script language="javascript" type="text/javascript"> 
            alert("Unable to add Fright Rates, Please try again.");
            window.location = "FreightRatesList.php";
            </script>';
        }
    }
    /*duplicate freight charges insert end*/
}
?>
<!DOCTYPE html>

<html lang="en">


<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
    input.error {
            border: 1px solid red;
        }
    .closePop:hover{
        cursor: pointer;
    }
    </style>
</head>

<body data-sidebar="dark">
    <style>
        .pagination-rounded .page-link {
            border-radius: 30px !important;
            margin: 0 3px;
            border: none;
            text-align: center;
            height: 35px;
        }
        .page-link.active {
        z-index: 3;
        color: #fff;
        background-color: #5664d2;
        }
    </style>
    <?php  include ($IncludesPath."PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php include($IncludesPath . "page-title.php"); ?>
                    <!-- search row start -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <form role="form" action="FreightRatesList.php" method="get" id="formAddFreightRates">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-sm-3">
                                                <label for="liner_id">Liner<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="liner_id"
                                                    id="liner_id">
                                                    <option value=""> All </Option>
                                                    <?php while ($RowLinerMaster = $StmtLinerMaster->fetch_array()) { ?>
                                                        <option 
                                                        <?php echo !empty($_GET['liner_id']) && $_GET['liner_id']==$RowLinerMaster['LinerID']?'selected="selected"':'';?>
                                                            value="<?php echo $RowLinerMaster['LinerID']; ?>">
                                                            <?php echo $RowLinerMaster['LinerName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-9 mt-4">
                                                <button type="submit" class="btn btn-success"><i class="fa fa-search"></i> Search</button>
                                                <a href="FreightRatesList.php" class="btn btn-danger"> Clear</a>
                                            
                                            <a href="AddFreightRatesNew.php<?php echo !empty($_GET['liner_id'])?'?L_id='.$_GET['liner_id']:'';?>" class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i> Add New Freight Rates</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- search row ends -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ShowDataTable">
                                   <!--  <div class="col-lg-12 box-footer" align="center">
                                        <a href="AddFreightRates.php"
                                            class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i>
                                            Add New Freight Rates</a>
                                    </div> -->


                                    <table id="key-datatable" data-paging="false"
                                        class="table dt-responsive nowrap w-100">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Liner</th>
                                                <th width="20%" style="min-width: 20%;max-width: 20%;">Country</th>
                                                <th width="20%"  style="min-width: 20%;max-width: 20%;">Port Names</th>
                                                <th>Rate Type</th>
                                                <th>Combined</th>
                                                <th>From Date</th>
                                                <th>To Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $SNo = 1;
                                            $i = $LimitMax+1;
                                            // printf ($StmtAdminExporter->fetch_array());
                                            while ($RowFreightRates = $StmtFreightRates->fetch_array()) {
                                                $ID = $RowFreightRates['ID'];
                                                $LinerName = $RowFreightRates['LinerName'];
                                                $CountryName = $RowFreightRates['CountryName'];
                                                $portName = $RowFreightRates['portName'];
                                                $FromDate =  date("d-m-Y", strtotime($RowFreightRates['FromDate']));
                                                $ToDate =  date("d-m-Y", strtotime($RowFreightRates['ToDate']));
                                                $RateType = !empty($RowFreightRates['RateType'])?'CONTRACT':'SPOT';
                                                $Combined = !empty($RowFreightRates['Combined'])?'YES':'NO';
                                                ?>
                                                <tr> 
                                                    <td width="5%"> <?php echo $i++ ?></td>
                                                    <td width="10%"> <?php echo $LinerName ?></td>
                                                    <td width="20%" style="min-width: 20%;max-width: 20%;"> 
                                                        <?php echo strlen($CountryName)>20?'<p data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="'.str_replace(',',"<br>",$CountryName).'">'.substr($CountryName, 0, 20).'...</p>':$CountryName; ?>
                                                    </td>
                                                    <td width="20%" style="min-width: 20%;max-width: 20%;"> <?php echo strlen($portName)>30?'<p data-toggle="tooltip" data-placement="right" data-html="true" data-original-title="'.str_replace(',',"<br>",$portName).'">'.substr($portName, 0, 30).'...</p>':$portName; ?></td>
                                                    <td width="10%"> <?php echo $RateType ?></td> 
                                                    <td width="10%"> <?php echo $Combined ?></td> 
                                                    <td width="10%"> <?php echo $FromDate ?></td>                                                                                                      
                                                    <td width="10%"> <?php echo $ToDate ?></td>
                                                    <td width="10%">
                                                        <a target="" href="AddFreightRatesNew.php?id=<?php echo $ID; ?>"
                                                            class="mr-3 text-primary Edit" data-toggle="tooltip"
                                                            data-placement="top"  data-original-title="Edit"><i
                                                                class="mdi mdi-pencil font-size-18"></i></a>
                                                        <a target="" href="javascript:void(0);" data-fid="<?php echo $ID; ?>"
                                                            class="mr-3 text-default Copy" data-toggle="tooltip"
                                                            data-placement="top" data-original-title="Copy"><i
                                                                class="ri-file-copy-line font-size-18"></i></a>        
                                                    </td>
                                                </tr>
                                                <?php //$SNo++;
                                            } ?>
                                        </tbody>
                                    </table>
                                    <?php 
                                    if(!empty($PageCount)){?>
                                        <div class="mt-3 mb-3">Showing <?php echo !empty($LimitMax)?$LimitMax+1:1;?> to <?php echo --$i ?> out of <?php echo $TotalCount ?> entries </div>
                                        <div>
                                            <ul class="pagination pagination-rounded <?php echo $PageCount<30?'justify-content-center':'';?>" style="overflow-x: auto;margin: 0px auto;">
                                                <?php
                                                for ($p = 1; $p <= $PageCount; $p++) {
                                                    $pageActive = ($PageNo == $p? "active" : "");
                                                    $pageLink = ($PageNo == $p? "javascript:void(0);" : "?PageNo=$p$queryParm");
                                                    echo '<li class="page-item"><a class="page-link ' . $pageActive . ' btnPageno"  href="' . $pageLink. '" data-pageno="' . $p . '">' . $p . '</a></li>';
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    <?php }?>

                                </div>
                                <!-- end col -->
                            </div> <!-- end row -->





                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->

        </div> <!-- container-fluid -->
    </div>
    <!-- copy popup start -->
    <!-- Modal -->
    <div class="modal fade" id="copyPopup" tabindex="-1" aria-labelledby="copyPopup" aria-hidden="true">
        <form id="duplicateFreightRates" role="form" method="post">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Create Duplicate Freight Rates</h5>
                        <a  class="btn-close closePop" data-bs-dismiss="modal" aria-label="Close">&times;</a>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="form-group col-sm-6">
                                <label for="from_date">From Date <span
                                        style="color:red">*</span></label>
                                <input required type="date" class="form-control" name="from_date"
                                    id="from_date"
                                    value="" />
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="to_date">To Date <span style="color:red">*</span></label>
                                <input required type="date" class="form-control" name="to_date"
                                    id="to_date"
                                    value="" />
                            </div>
                            <input type="hidden" id="freightRateId" name="freightRateId">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger closePop" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name='submit' value="freightDuplicateSubmit" id="copySubmitBtn" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- copy popup ends -->
    <!-- End Page-content -->

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php ///include($IncludesPath . "ShowDataTable.php"); ?>

 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
</body>

</html>

<script>
    $(document).ready(function () {
        $('#key-datatable').DataTable({
            scrollY: "600px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "150%",
            // scrollCollapse:true,
            paging: false,
            // pageLength: 100,
            // lengthMenu: [10, 25, 50, 100],
            destroy: true,
            fixedColumns: false,
            bSort: false,
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
            info: false,//for Showing 1 to 10 of 100 entries
        })
        /* $('#key-datatable').DataTable({
            // other options...
            drawCallback: function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
            }
        }); */
      //  $('[data-toggle="tooltip"]').tooltip();
          $("body").tooltip({ selector: '[data-toggle=tooltip]' });


        //showData();
        $('.Copy').click(function(){
            $("#freightRateId").val('');
            if(confirm('Are you sure you want to copy the item ?')){ 
                $("#copyPopup").modal('show');
                var fid = $(this).attr("data-fid");
                $("#freightRateId").val(fid);
            }
        })
        $('.closePop').click(function(){
             $("#freightRateId,#from_date,#to_date").val('');
              $("#copyPopup").modal('hide');
        })
        $("#duplicateFreightRates").validate({ignore: "",
                rules: {
                    from_date: {
                        required: true,
                        date: true
                    },
                    to_date: {
                        required: true,
                        date: true
                    },
                },
                messages: {                             
                    from_date: {
                        required: "Please select the from date.",
                        date: "Please enter a valid date."
                    },
                    to_date: {
                        required: "Please select the to date.",
                        date: "Please enter a valid date."
                    },
                },
                errorPlacement: function() {
                    return false; // suppresses error message text
                },
                submitHandler: function() {
                    $('#preloader,#status').show().css('display','block')
                    return true;
                }
            });

    });



</script>
