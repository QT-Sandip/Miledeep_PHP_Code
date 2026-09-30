<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$PageTitleFolder = "Sales";
$PageTitle = "Grade Wise Data";


$INVOICE_ID = $_GET['id'];

$SqlPI = "SELECT * FROM `ScrmPIData`   WHERE PoID='$INVOICE_ID'";

$StmtPI = $iconn->query($SqlPI);
$RowPI = $StmtPI->fetch_array();
$PoRefId = $RowPI['PoRefId'];
$QtyUnit = $RowPI['QtyUnit'];
$UnitCurrency = $RowPI['UnitCurrency'];


$SqlPIRef = "SELECT * FROM `ScrmPIData`   WHERE PoRefId='$PoRefId'";

$StmtPIRef = $iconn->query($SqlPIRef);
$RowCurrency = $StmtPIRef->fetch_array();
$UnitCurrency = $RowCurrency['UnitCurrency'];

$StmtPIRef = $iconn->query($SqlPIRef);


$sql_packing = "SELECT * FROM `AdminPackingStyles` Where Status='1' 
order by abs(PackingStyle) Asc  limit 100";
$stmt_packing = $iconn->query($sql_packing);

// $sqlVarietyPacking



$SQL_PO_GRADE_DATA = "SELECT * FROM `ScrmPoGradeData` 
 where InvId='$INVOICE_ID'  ORDER BY ID ASC";
$STMT_PO_GRADE_DATA = $iconn->query($SQL_PO_GRADE_DATA);

$GRADE_ROW = $STMT_PO_GRADE_DATA->FETCH_ARRAY();

$STMT_PO_GRADE_DATA = $iconn->query($SQL_PO_GRADE_DATA);

// $SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc ";
// $STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
// $ARY_OPTIONS=array();
// WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
// 	$ARY_OPTIONS[]=$ROW;
// }

$SqlPIVarietyList = "SELECT * FROM `ScrmPIVarietyList` as  SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	left join ScrmPreparationMaster as  SPM on SPM.ShortCode =SPV.PreparationType
		where SPV.Status='1'";
$StmtPIVarietyList = $iconn->query($SqlPIVarietyList);

function checkEmpty($Value)
{
    if ($Value == '') {
        $Value = '';
    } else {
        $Value = ' ' . $Value;
    }
    return $Value;
}

$AryVarietyList = array();

while ($RowPIVarietyList = $StmtPIVarietyList->fetch_array()) {
    $AryItem = array();
    $PreparationName = checkEmpty($RowPIVarietyList['PreparationName']);
    $TypeProduct = checkEmpty($RowPIVarietyList['TypeProduct']);
    $ProductFormm = checkEmpty($RowPIVarietyList['FullName']);
    $FreezingTypee = checkEmpty($RowPIVarietyList['FreezingType']);
    $PreparationTypee = checkEmpty($RowPIVarietyList['PreparationType']);
    $GlazePercc = checkEmpty($RowPIVarietyList['GlazePerc']);
    $Glazee = checkEmpty($RowPIVarietyList['Glaze']);
    $Treatmentt = checkEmpty($RowPIVarietyList['Treatment']);
    $Grade = checkEmpty($RowPIVarietyList['Grade']);
    $PackingStyle = checkEmpty($RowPIVarietyList['PackingStyle']);
    $BrandName = checkEmpty($RowPIVarietyList['BrandName']);
    $PreparationDesc = checkEmpty($RowPIVarietyList['PreparationDesc']);
    $ProductDescription = checkEmpty($RowPIVarietyList['ProductDescription']);
    $DeviSUPC = checkEmpty($RowPIVarietyList['DeviSUPC']);
    $BuyerSUPC = checkEmpty($RowPIVarietyList['BuyerSUPC']);


    // $Data=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt.$Grade.$PackingStyle.$BrandName; 
    $Description = $DeviSUPC . '-' . $Grade . $PackingStyle . $ProductDescription . $FreezingTypee . $GlazePercc . '%' . $Glazee . $Treatmentt . $BrandName;
    $AryItem['Name'] = $Description;
    $AryItem['ID'] = $RowPIVarietyList['VarietyID'];
    $AryVarietyList[] = $AryItem;
}


$sql_invoice = "SELECT * FROM `ScrmPIData` where PoID='$INVOICE_ID'";
$stmt_invoice = $iconn->query($sql_invoice);
$invoice_details = $stmt_invoice->fetch_array();

$UnitCurrency = $invoice_details['UnitCurrency'];



extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {
    $count = count($SelectedInvId);
    $InvId = implode(',', $SelectedInvId);

    $SqlPIRef = "SELECT * FROM `ScrmPIData`   WHERE PoID in ($InvId)";

    $StmtPIRef = $iconn->query($SqlPIRef);

    //echo $VarietyId ;exit;

    $SqlPIVarietyList = "SELECT * FROM `ScrmPIVarietyList` as SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	WHERE VarietyID='$VarietyId'";
    $StmtPIVarietyList = $iconn->query($SqlPIVarietyList);
    $RowPIVarietyList = $StmtPIVarietyList->fetch_array();

    //$VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
    $VarietyName = $FullName;
    $Glaze = $RowPIVarietyList['Glaze'];
    $GlazePerc = $RowPIVarietyList['GlazePerc'];
    $Treatment = $RowPIVarietyList['Treatment'];
    $FreezingType = $RowPIVarietyList['FreezingType'];
    $GRADE = $RowPIVarietyList['Grade'];
    $PackingStyleID = $RowPIVarietyList['PCKID'];
    $BRAND = $RowPIVarietyList['BrandName'];
    $HasSauce = $RowPIVarietyList['HasSauce'];


    $D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
    $D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));


    $ARY_GRADE = explode(',', $GRADE);
    $ARY_MCS = explode(',', $MCS);
    $ARY_AMOUNT_PER_LB = explode(',', $Price);

    $ARY_CUPS = explode(',', $CUPS);
    $ARY_CUPSPrice = explode(',', $CUPSPrice);

    if (count($ARY_GRADE) == count($ARY_MCS) && count($ARY_GRADE) == count($ARY_AMOUNT_PER_LB)) {

        for ($I = 0; $I < count($ARY_GRADE); $I++) {
            $Cups = '';
            $CupsPrice = '';
            $GRADE = $ARY_GRADE[$I];
            $MCS = $ARY_MCS[$I];
            $Price = $ARY_AMOUNT_PER_LB[$I];
            if ($HasSauce == 'Yes') {
                $Cups = $ARY_CUPS[$I];
                $CupsPrice = $ARY_CUPSPrice[$I];
            }

            if ($InvId != '') {
                for ($i = 0; $i < $count; $i++) {
                    while ($RowPIRef = $StmtPIRef->fetch_array()) {
                        $Inv_Id = $RowPIRef['PoID'];

                        $sql_org_insert = "INSERT INTO `ScrmPoGradeData`(`Grade`, `Mcs`,`PIMcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`BuyerItemCode`,`Description`,`CodeType`,
     `CUPS`,`CUPSPrice`, `TraysQuantity`,`DdpPrice`)
     VALUES ('$GRADE','$MCS','$MCS','$FreezingType','$Inv_Id','$PackingStyleID','$Glaze','$VarietyName','$GlazePerc',
	 '$Treatment','$BRAND','$Price','$BAP','$VarietyId','$BuyerItemCode','$Description','$CodeType','$Cups','$CupsPrice','$TraysQuantity','$DdpPrice')";
                        $stmt1 = $iconn->query($sql_org_insert);
                        $LastInsertedPoBreakupID = $iconn->insert_id;
                        if($stmt1){
                            legacyAuditLogInsertById('ScrmPoGradeData', 'ID', $LastInsertedPoBreakupID, $_POST, array('InvId' => $Inv_Id), 'Sales');
                        }
                        //  echo   $LastInsertedPoBreakupID=$stmt1->insert_id;  exit;

                        postUpdateGrossweight($iconn, $Inv_Id, $LastInsertedPoBreakupID, '', $MCS);
                    }
                }
            }
        }
    } else {
        echo '<script>alert("Grades And Break Up Mismatch") </script>';
    }
    //echo $iid;
    if ($stmt1) {

        /* $SQL_INV_UPDATE="UPDATE `INVOICE_DATA` SET 
            `DEC_STATUS`='1'
            WHERE ID='$INVOICE_ID'";


$STMT_INV_UPDATE=$iconn->query($SQL_INV_UPDATE);

    */
        header('Location: PoBreakUp.php?id=' . $INVOICE_ID);
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href; ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


    <?php
    include($IncludesPath . "PreLoader.php"); ?>

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
                    <?php include "PopUpTable.php"; ?>
                    <?php

                    if ($Status != '') {
                        //Show Alert 
                    ?>

                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>




                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="form-group col-lg-12">


                                                <input type="text" class="form-control" hidden id="displayinformation"
                                                    readonly>

                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Select PI<span
                                                        style="color:red">*<span></label>

                                                <select multiple class="form-control" name="SelectedInvId[]"
                                                    id="SelectedInvId" required>


                                                    <?php while ($RowPIRef = $StmtPIRef->fetch_array()) {
                                                        $InvNo = $RowPIRef['InvNo'];
                                                        $RefPoID = $RowPIRef['PoID'];
                                                    ?>
                                                        <option value="<?php echo $RefPoID ?>" <?php if ($RefPoID == $GRADE_ROW['InvId'])
                                                                                                    echo 'selected="selected"'; ?>><?php echo $InvNo; ?></option>
                                                    <?php } ?>
                                                </select>

                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Variety Name<span
                                                        style="color:red">*<span></label>
                                                <input hidden type="text" id="VarietyId" name="VarietyId"
                                                    class="form-control hidden" value="">
                                                <input autocomplete="off" required type="text" class="form-control"
                                                    name="Variety" id="Variety" data-id="<?php echo $INVOICE_ID ?>"
                                                    placeholder="Click to select"></input>
                                            </div>

                                            <div class="form-group col-sm-3" style="display:none;">
                                                <label for="exampleInputPassword1">Packing Style</label>
                                                <input type="text" class="form-control" name="PackingStyle" id="PackingStyle">
                                            </div>

                                            <div class="form-group col-sm-3" style="display:none;">
                                                <label for="exampleInputPassword1">Total Slabs</label>
                                                <input type="text" class="form-control" name="TotalSlabs" id="TotalSlabs">
                                            </div>

                                            <div class="form-group col-sm-3" style="display:none;">
                                                <label for="exampleInputPassword1">Slab Weight</label>
                                                <input type="text" class="form-control" name="SlabWeight" id="SlabWeight">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Mcs<span
                                                        style="color:red">*<span></label>
                                                <input required type="number" class="form-control" name="MCS" id="MCS"
                                                    placeholder="Enter MASTER CARTONS ">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Trays Quantity</label>
                                                <input type="number" class="form-control" name="TraysQuantity" id="TraysQuantity">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Rate/<?php echo $QtyUnit ?><span
                                                        style="color:red">*<span></label>
                                                <input required type="number" step="0.001" class="form-control"
                                                    name="Price" id="Price"
                                                    placeholder="Enter Rate/Unit(<?php echo $UnitCurrency ?>) ">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">DDP Price </label>
                                                <input type="number" step="0.01" class="form-control" name='DdpPrice'
                                                    id="DdpPrice" placeholder="Enter Ddp Price " value="<?php 
                                                    ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">CUPS</label>
                                                <input type="number" class="form-control" name="CUPS" id="CUPS"
                                                    placeholder="Enter CUPS ">
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">CUPS
                                                    Price/<?php echo $QtyUnit ?></label>
                                                <input type="number" step="0.001" class="form-control" name="CUPSPrice"
                                                    id="CUPSPrice"
                                                    placeholder="Enter CUPSPrice(<?php echo $UnitCurrency ?>) ">
                                            </div>




                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1"> Code</label>
                                                <input type="text" class="form-control" name="BuyerItemCode"
                                                    id="BuyerItemCode" placeholder="Enter Buyer Item Code" value="">
                                            </div>

                                            <div class="form-group col-sm-12 col-lg-12">
                                                <label for="exampleInputEmail1">Full Name</label>
                                                <input readonly type="text" class="form-control"
                                                    style=" background: grey;color: white;" name="FullName"
                                                    id="FullName" placeholder="" value="">
                                            </div>

                                            <div class="form-group col-lg-12">
                                                <label for="exampleInputEmail1">Description</label>
                                                <input readonly style=" background: grey;color: white;"
                                                    name="BdDescription" class="form-control" id="BdDescription"
                                                    placeholder="Enter Description(Optional)"></textarea>
                                            </div>


                                            <div class="form-group col-lg-12">
                                                <label for="exampleInputEmail1">Buyer Description</label>

                                                <textarea name="Description" class="form-control" rows="2" cols="50"
                                                    id="Description"
                                                    placeholder="Enter Description(Optional)"></textarea>


                                            </div>
                                        </div>










                                        <div class="form-group col-sm-12" align="right">


                                            <!--<a target="" href="PIList.php"
                    class="btn btn-danger  "> Close</a>-->

                                            <button type="button" id="close" name="close" class="btn btn-danger">Close
                                            </button>

                                            <button type="submit" name='submit' class="btn btn-success">ADD TO PO
                                            </button>
                                            <!--<a href="PIList.php"  id="<?php echo $INVOICE_ID; ?>"  class="btn btn-primary eia_report">SUBMIT  </a>-->

                                        </div>

                                </form>




                                <!-- end col -->
                            </div> <!-- end row -->
                        </div> <!-- end row -->
                    </div> <!-- end row -->

                    <div class="col-12">


                        <div class="card">

                            <div class="card-body" id="ShowDataTable">


                                <table id="key-datatable" data-paging="false" class="table   wrap w-100  ">
                                    <thead class="thead-light">


                                        <tr>
                                            <th>ID</th>
                                            <th style="width : 20%">Variety</th>
                                            <th>Grade</th>
                                            <th>Packing Style</th>
                                            <th>Treatment</th>
                                            <th>Freezing Type</th>
                                            <th>Brand</th>
                                            <th>Mcs</th>
                                            <th>Trays Qty</th>
                                            <th>Unit Price<br>(<?php echo $QtyUnit ?>)</th>
                                            <th>Ddp Price</th>
                                            <th>Amount in <br>(<?php echo $UnitCurrency ?>)</th>
                                            <th>Actions </th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        //echo $sql;
                                        $TotalMcs = 0;
                                        $TotalAmount = 0;

                                        while ($row = $STMT_PO_GRADE_DATA->fetch_array()) {
                                            $PoGradeId = $row['ID'];
                                            $PackingID = $row['PackingStyleID'];

                                            // $SqlPckStyl="SELECT * FROM `AdminPackingStyles` WHERE PCKID ='$PackingID'";
                                            $SqlTotal = "SELECT PackingStyle,
                                                sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
                                                sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
                                                sum(Mcs*TotalSlabs*Price ) as PriceBags
                                                FROM `ScrmPoGradeData` 
                                                as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$INVOICE_ID' 
                                                and ID='$PoGradeId'";
                                            $StmtTotal = $iconn->query($SqlTotal);
                                            $RowTotal = $StmtTotal->fetch_array();

                                            if ($QtyUnit == 'BAGS') {
                                                //$Qty=$RowTotal['MCS']*$RowTotal['TotalSlabs'];
                                                $Price = $RowTotal['PriceBags'];
                                            } else if ($invoice_details['QtyUnit'] == 'KGS') {
                                                $Weight = $RowTotal['NetWgtKgs'];
                                                $Price = $RowTotal['PriceKgs'];
                                            } else {
                                                $Weight = $RowTotal['NetWgtLbs'];
                                                $Price = $RowTotal['PriceLbs'];
                                            }


                                            /*$StmtPckStyl=$iconn->query($SqlPckStyl); 
                                            $RowPckStyl=$StmtPckStyl->fetch_array();
                                            $Packing=$RowPckStyl['PackingStyle'];*/
                                            $TotalMcs += $row['Mcs'];
                                            $Packing = $RowTotal['PackingStyle'];
                                            $TotalAmount += $Price;

                                            if ($row['Description'] != '') {
                                                $row['Variety'] = $row['Description'];
                                            }

                                        ?>
                                            <tr>

                                                <td><?php echo $PoGradeId; ?> </td>
                                                <td><?php echo $row['Variety']; ?> </td>
                                                <td><?php echo $row['Grade']; ?> </td>
                                                <td><?php echo $Packing; ?> </td>
                                                <td><?php echo $row['Treatment']; ?> </td>
                                                <td><?php echo $row['FreezingType']; ?> </td>
                                                <td><?php echo $row['Brand']; ?> </td>
                                                <td><?php echo $row['Mcs']; ?> </td>
                                                <td><?php echo $row['TraysQuantity']; ?> </td>
                                                <td><?php echo $row['Price']; ?> </td>
                                                <td><?php echo $row['DdpPrice']; ?></td>
                                                <td><?php echo number_format($Price, 2); ?> </td>



                                                <td>
                                                    <a href="CsPoGradesEdit.php?id=<?php echo $row['ID']; ?>&PoID=<?php echo $INVOICE_ID; ?>"
                                                        class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
                                                        title="" data-original-title="Edit"><i
                                                            class="mdi mdi-pencil font-size-18"></i></a>


                                                    <button class="btn btn-link text-danger Delete"
                                                        data-id="<?php echo $row['ID']; ?>" data-toggle="tooltip"
                                                        data-placement="top" title="" value=""
                                                        data-original-title="Delete"><i
                                                            class="mdi mdi-trash-can font-size-18"></i></button>

                                                </td>


                                            </tr>

                                        <?php

                                        }
                                        ?>
                                        <tr>
                                            <td> Total</td>
                                            <td> </td>
                                            <td> </td>
                                            <td> </td>
                                            <td> </td>
                                            <td> </td>
                                            <td> </td>
                                            <td><?php echo $TotalMcs ?></td>
                                            <td></td>
                                            <td><?php echo number_format($TotalAmount, 2) ?></td>

                                            <td> </td>

                                        </tr>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div> <!-- end card -->
            </div> <!-- end card -->
        </div> <!-- end card -->




        <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>



</body>







<Script>
    $('document').ready(function() {

        $("#SelectedInvId").select2();
        var arr = [];

        $("#CUPS").prop("readonly", true);
        $("#CUPSPrice").prop("readonly", true);
        $("#TraysQuantity").prop("readonly", true);

        $(document).on('click', '#Variety', function(e) {
            e.preventDefault();


            var PoID = $(this).data('id');

            //$('#Update').val(PoID);


            showPoPopUp(PoID);

        });

        $(document).on('change keyup', '#MCS', function() {
            var mcs = parseFloat($('#MCS').val()) || 0;
            var totalSlabs = parseFloat($('#TotalSlabs').val()) || 0;
            var trayQty = mcs * totalSlabs;
            $('#TraysQuantity').val(trayQty);
        });

        function showPoPopUp(PoID) {

            $.ajax({
                url: 'Ajax/AjaxPopUpVarietyDetails.php',
                type: 'POST',
                data: {
                    'PoID': PoID
                },
                success: function(data) {
                    $("#Update").hide();
                    $('.modal-title').text('Variety List');
                    $("#PopUpTable").html(data);
                    $('#myModal').modal('show');
                }
            });

        }

        $(document).on('click', '.SelectVariety', function(e) {
            e.preventDefault();

            var VarietyID = $(this).data('id');
            $('#myModal').modal('hide');


            getVarietyData(VarietyID);



        });


        $(document).on('click', '.Delete', function(e) {
            var D_ID = $(this).data('id');


            var r = confirm("Are you sure to delete this Grade?");
            if (r == true) {



                $.ajax({
                    url: 'Ajax/AjaxDelete.php',
                    type: 'POST',
                    data: {
                        'TYPE': 'BREAKUP_DELETE',
                        'D_ID': D_ID
                    },
                    success: function(data) {
                        console.log(data);

                        alert("Deleted");
                        showData(data, false);
                        location.reload();
                    }
                });

            } else {}
        });

        $('#close').click(function(e) {
            //  window.close();
            window.location.href = 'proforma-list';

        });

        function getVarietyData(VarietyID) {
            var PoId = '<?php echo $INVOICE_ID; ?>';
            var Variety = VarietyID;
            document.getElementById('displayinformation').hidden = false;
            $.ajax({
                type: 'POST',
                url: 'Ajax/AjaxAutoFillOptions.php',
                dataType: 'json',
                data: {
                    'Type': 'VARIETY',
                    'PoId': PoId,
                    'Variety': Variety
                },
                success: function(response) {
                    var myJSON = JSON.parse(JSON.stringify(response));
                    var Species = myJSON['Species'];
                    var ProductFormm = myJSON['FullName'];
                    var FreezingTypee = myJSON['FreezingType'];
                    var PreparationTypee = myJSON['PreparationType'];
                    var GlazePercc = myJSON['GlazePerc'];
                    var Glazee = myJSON['Glaze'];
                    var Treatmentt = myJSON['Treatment'];
                    var Grade = myJSON['Grade'];
                    var PackingStyle = myJSON['PackingStyle'];
                    var BrandName = myJSON['BrandName'];
                    var FullName = myJSON['FullName'];
                    var Description = myJSON['Description'];
                    var BuyerDescription = myJSON['BuyerDescription'];
                    var VarietyDescription = myJSON['VarietyDescription'];
                    var VarietyId = myJSON['Variety'];
                    var HasSauce = myJSON['HasSauce'];
                    var PackingStyleID = myJSON['PackingStyleID'];
                    var TotalSlabs = myJSON['TotalSlabs'];
                    var SlabWeight = myJSON['SlabWeight'];



                    if (HasSauce === 'Yes') {
                        $("#CUPS").val('').prop("readonly", false);
                        $("#CUPSPrice").val('').prop("readonly", false);
                    } else {
                        $("#CUPS").val('').prop("readonly", true);
                        $("#CUPSPrice").val('').prop("readonly", true);
                    }

                    //alert(VarietyDescription);

                    $("#BdDescription").val(BuyerDescription);
                    //$("#Description").val(BuyerDescription);
                    $("#VarietyId").val(VarietyId);
                    $("#Variety").val(VarietyDescription);
                    $("#displayinformation").val(Description);
                    $("#FullName").val(FullName);
                    //$("input:text").val(Data);

                    $("#PackingStyle").val(PackingStyleID);
                    $("#TotalSlabs").val(TotalSlabs);
                    $("#SlabWeight").val(SlabWeight);

                }
            });

        }

        function checkEmpty(Value) {
            if (Value == '') {
                Value = '';
            } else {
                Value = '(' + Value + ')';
            }
            return Value;
        }
    });
</script>


</html>
