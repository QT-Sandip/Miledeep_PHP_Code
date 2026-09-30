<style>
.select2 {
    width: 200px;
}
</style>

<style>
table {
    width: 250px;
}

th,
td {
    font-size: 14px;
    width: 200px;
    color: black;
    text-align: center;
}

input[type="text"]:disabled,
input[type="number"]:disabled,
textarea.form-control:disabled {
    background: #eeeeee;
}
</style>
<?php


$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include($cp . "Includes/IncludePage.php");

extract($_POST);
extract($_GET);

$PoID = $_GET['PoID'];


$SqlPo = "SELECT * FROM `ScrmPoData` where PoID='$PoID'";
$StmtPo = $iconn->query($SqlPo);
$RowPo = $StmtPo->fetch_array();


$SqlBreakup = "SELECT DISTINCT VarietyID FROM `ScrmPoGradeData` WHERE InvID='$PoID'";
$SqlBreakup = "SELECT  * FROM `ScrmPoGradeData` SPGD
LEFT JOIN ScrmPIVarietyList SPVL ON SPVL.VarietyID=SPGD.VarietyID WHERE InvID='$PoID'";
$StmtBreakup = $iconn->query($SqlBreakup);
$StmtBreakup1 = $iconn->query($SqlBreakup);
$STMT_PO_GRADE_DATA = $iconn->query($SqlBreakup);

$SqlQcVarityList = "SELECT * FROM `$TblQcPoVarityList` where InvID='$PoID'";
$StmtQcVarityList = $iconn->query($SqlQcVarityList);



while ($RowBreakup1 = $StmtBreakup1->fetch_array()) {
    $VarietyID = $RowBreakup1['VarietyID'];
    $SqlPoVarietyList = "SELECT *  FROM ScrmPIVarietyList WHERE VarietyID='$VarietyID'";
    $StmtPoVarietyList = $iconn->query($SqlPoVarietyList);
    $RowPoVarityList = $StmtPoVarietyList->fetch_array();

    $ArrayPIVarieties[] = $RowPoVarityList;
}


$PageTitle = "Variety List - " . $RowPo['PoNo'];

$PageTitleFolder = "Quality Control";


extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {

    $oldPoGradeRow = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $PoGradeId, 'QualityControl');
    
   echo   $SqlInsert ="UPDATE ScrmPoGradeData SET
	 QcDescription='$Description' WHERE ID='$PoGradeId'";

   // $StmtInsert = $iconn->query($SqlInsert);

    if ($StmtInsert) {
        legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $PoGradeId, $oldPoGradeRow, $_POST, ['Remarks' => 'Variety Description Updated'], 'QualityControl');
        header('Location: VarityAdd.php?Q=' . $QueryParm);
        //header('Location: VarityAddTest.php?Q='.$QueryParm);
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
    <!--class="vertical-collpsed"-->


    <?php include($IncludesPath . "PreLoader.php"); ?>

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





                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-primary AddNewItem" disabled style="
                                width: 70px;
                                position: relative;
                                right: 0px;
                                padding: 4px;
                                margin-bottom: 5px;">Add Item</button>
                        </div>
                        <div class="col-12">
                            <div class="card">
                                <!-- <input type="text" class="form-control" hidden id="displayinformation" readonly > -->
                                <div class="card-body" id="ShowDataTable">


                                    <table id="key-datatable" data-paging="false" style="font-size: 12px;"
                                        class="table   wrap w-100  ">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="max-width: 60px;">ID</th>
                                                <th style="width : 15%">Variety</th>
                                                <th style="max-width: 80px;">Short Name</th>
                                                <th style="width : 25%">Description</th>
                                                <th>Code</th>
                                                <th> Mcs</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            //echo $sql;
                                            $TotalMcs = 0;
                                            $TotalAmount = 0;
                                            $aryPoGradeId = array();
                                            while ($row = $STMT_PO_GRADE_DATA->fetch_array()) {
                                                $aryPoGradeId[] = $PoGradeId = $row['ID'];
                                                $PackingID = $row['PackingStyleID'];

                                                $_SESSION["rowList"] = $aryPoGradeId;

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

                                                //$row['Variety'] = $row['Description'];
                                            
                                                ?>
                                                <tr data-rowid="<?php echo $PoGradeId; ?>"
                                                    id="rowid<?php echo $PoGradeId; ?>">
                                                    <td style="max-width: 60px;" id="PoGradeId<?php echo $PoGradeId; ?>">
                                                        <?php echo $PoGradeId; ?></td>

                                                    <td>
                                                        <p class="fullname Variety" id="Variety<?php echo $PoGradeId; ?>">
                                                            <?php echo $row['Variety']; ?></p>
                                                        <input type="hidden" id="VarietyId<?php echo $PoGradeId; ?>"
                                                            name="VarietyId<?php echo $PoGradeId; ?>"
                                                            class="form-control hidden"
                                                            value="<?php echo $row['VarietyID']; ?>" />

                                                    </td>
                                                    <td id="shortName<?php echo $PoGradeId; ?>" style="max-width: 80px;"><?php echo $row['ShortName']; ?> </td>
                                                    <!-- <td id="tdGrade<?php // echo $PoGradeId; ?>"><?php // echo $row['Grade']; ?></td>	 -->
                                                    <!-- <td id="tdPackingStyle<?php // echo $PoGradeId; ?>"><?php // echo $Packing; ?></td>	 -->
                                                    <!-- <td><?  // echo $row['Treatment']; ?> </td>	
                                                    <td><?php //echo $row['FreezingType']; ?> </td>	 -->
                                                    <td>

                                                        <textarea name="Description<?php echo $PoGradeId; ?>"
                                                            class="form-control" rows="2" cols="50"
                                                            placeholder="Enter Description(Optional)"
                                                            id="Description<?php echo $PoGradeId; ?>"
                                                            ><?php echo $row['QcDescription'] ?></textarea>
                                                        <p>
                                                            <?php
                                                            $infoData = $row['Grade'] . " - " . $Packing . " - " . $row['Treatment'] . " - " . $row['Brand']; ?>
                                                            <!-- <span id="tdGrade<?php echo $PoGradeId ?>"><?php echo $row['Grade'] ?></span> - 
                                                            <span id="tdPackingStyle<?php echo $PoGradeId ?>"><?php echo $Packing ?></span> - 
                                                            <span id="tdBrand<?php echo $PoGradeId ?>"><?php echo $row['Brand'] ?></span>	-												 -->

                                                            <?php // $VarietyDescription=$DeviSUPC.'-'.$Grade.$PackingStyle.$ProductDescription .$FreezingTypee.$GlazePercc. '%' .$Glazee.$Treatmentt.$BrandName; ?>
                                                            <span
                                                                id="tdInfo<?php echo $PoGradeId ?>"><?php echo $infoData ?></span>
                                                        </p>
                                                    </td>
                                                    <td>
                                                        <input name="code" type="text" class="form-control"
                                                            id="code<?php echo $PoGradeId; ?>" disabled
                                                            value="<?php echo $row['BuyerItemCode'] ?>" />
                                                    </td>
                                                    <!-- <td  id="tdBrand<?php // echo $PoGradeId; ?>"><?php // echo $row['Brand']; ?></td>	 -->
                                                    <td>
                                                        <?php // echo $row['Mcs']; ?>
                                                        <input required type="number" max="999999" min="1"
                                                            class="form-control mcs" value="<?php echo $row['Mcs'] ?>"
                                                            name="MCS<?php echo $PoGradeId; ?>"
                                                            id="MCS<?php echo $PoGradeId; ?>"
                                                            placeholder="Enter MASTER CARTONS" disabled>
                                                    </td>



                                                </tr>
                                                <?php

                                            }
                                            ?>
                                            <tr>
                                                <td colspan="4"
                                                    style="text-align: right;font-weight:600; font-size: 16px;">
                                                    Total</td>
                                                <td style="text-align: right;font-weight:600; font-size: 16px;">
                                                    <?php echo $TotalMcs ?></td>
                                                <td style="text-align: right;font-weight:600; font-size: 16px;">
                                                    <?php echo number_format($TotalAmount, 2) ?></td>

                                            </tr>
                                        </tbody>

                                    </table>
                                    <input id="inputSelectedVariety" type="hidden" />

                                    <div class="col-xs-12 box-footer" align="right">
                                        <input type="hidden" id="editmode" value="T" />
                                        <!-- <button type="button" id="close"  name="close"  class="btn btn-danger btnClose">Close </button> -->
                                        <?php if (isset($_GET["backurl"]) && !empty($_GET["backurl"])) { ?>
                                            <a href="/Clients/DFL/Sales/<?php echo $_GET["backurl"] . ".php?PIID=" . $_GET["id"]; ?>"
                                                class="btn btn-danger btnClose">Close</a>
                                        <?php } else { ?>
                                            <button type="button" id="btn_close" name="btn_close"
                                                class="btn btn-danger btn_close">Close </button>
                                        <?php } ?>

                                      <!--  <button type="button" id="edit" name="edit"
                                            class="btn btn-primary aEditPoBreakUp">Edit </button> 
                                        <button type="submit" name='submit' id="btnSubmit<?php echo $PoGradeId; ?>" 
										class="btn btn-success"
                                            >Save</button>-->
                                         <button class="btn  btn-success" id="btnSubmit" data-id="<?php echo $PoGradeId; ?>"
											data-toggle="tooltip" data-placement="top" title="" value=""
											data-original-title="Edit">Save</button>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>



                    <!-- end row -->

                </div> <!-- end card body-->




                </section>

            </div>

        </div> <!-- end card -->
    </div><!-- end col-->
    </div>
    <!-- end row-->

    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>



</body>

</html>

<script>
$('document').ready(function() {
    //showData(data,false);
});
$('#YieldID').change(function() {
    var VarietyID = $('#YieldID').val();
    var PoId = '<?php echo $PoID ?>';
    if (VarietyID != '') {
        document.getElementById("TypeProduct").disabled = true;
    } else {
        document.getElementById("TypeProduct").disabled = false;
    }
    $.ajax({
        type: 'POST',
        url: 'Ajax/AjaxDependantDropDowns.php',
        dataType: 'json',
        data: {
            'Type': 'Variety',
            'VarietyID': VarietyID,
            'PoId': PoId
        },
        success: function(response) {
            var myJSON = JSON.parse(JSON.stringify(response));

            $("#TypeProduct").val(myJSON['TypeProduct']).change();
            $("#ProductType").val(myJSON['TypeProduct']);
            $("#ShortName").val(myJSON['ShortName']);
            $("#FullName").val(myJSON['FullName']);

        }
    });


});


$(document).on('click', '.Delete', function(e) {
    e.preventDefault();
    var PoID = '<?php echo $PoID ?>';

    var ClickedId = $(this).data('id');
    var Type = 'DeleteVariety';
    var r = confirm("Are You Sure Do You Want To Delete?");
    if (r == true) {


        $.ajax({

            type: 'POST',
            url: 'Ajax/AjaxBDdelete.php',
            data: {
                'Type': Type,
                'ClickedId': ClickedId,
                'PoID': PoID
            },

            success: function(data) {

                var obj = JSON.parse(JSON.stringify(data));



                if (obj.error == null) {
                    alert("Deleted Successfully");
                    // showData(data);

                    location.reload();
                } else {
                    alert(obj.error);
                }




            }
        });
    } else {}
});




$(document).on('click', '.edit', function(e) {
    e.preventDefault();


    var ClickedId = $(this).data('id');
	var aryPoGradeId = <?php echo json_encode($aryPoGradeId); ?>;
    var ref_id = '<?php echo $PoID ?>';

 

    var TypeProduct = $(TypeProductId + "").val();
    var ShortName = $(ShortNameId + "").val();
    var FullName = $(FullNameId + "").val();
    var YieldID = $(Yield + "").val();
    var Description = $(Description + "").val();

			var AryBreakUp = [];

			for (i = 0; i < aryPoGradeId.length; i++) {
				ClickedId = aryPoGradeId[i];
				
    var TypeProductId = '#TypeProduct' + ClickedId;
    var ShortNameId = '#ShortName' + ClickedId;
    var FullNameId = '#FullName' + ClickedId;
    var Yield = '#YieldID' + ClickedId;
    var Description = '#Description' + ClickedId;
	
	
	var TypeProduct = $(TypeProductId + "").val();
    var ShortName = $(ShortNameId + "").val();
    var FullName = $(FullNameId + "").val();
    var YieldID = $(Yield + "").val();
    var Description = $(Description + "").val();
	
	
	var jsonData = {};

				jsonData['PoGradeID'] = ClickedId;
				jsonData['TypeProduct'] = TypeProduct;
				jsonData['ShortName'] = ShortName;
				jsonData['FullName'] = FullName;
				jsonData['YieldID'] = YieldID;
				jsonData['Description'] = Description;
				
				AryBreakUp.push(jsonData);
	
			}


    var r = confirm("Are You Sure Do You Want To Update?");
    if (r == true) {

        $.ajax({

            type: 'POST',
            url: 'Ajax/AjaxBDdelete.php',
            dataType: 'json',
            data: {

                'Type': 'VarietyUpdate',
                'D_ID': ClickedId,
                'ref_id': ref_id,

                'ShortName': ShortName,
                'FullName': FullName,
                'TypeProduct': TypeProduct,
                'YieldID': YieldID,
                'Description': Description,
				'AryBreakUp': JSON.stringify(AryBreakUp)
            },


            success: function(data) {

                var obj = JSON.parse(JSON.stringify(data));
                if (obj.error == null) {
                    alert(obj.Msg);
                    //showData(data);

                    location.reload();
                } else {
                    alert(obj.error);
                }


            }
        });
    } else {}
});




$('#btn_close').on("click", function() {

   // window.open('', '_parent', '');
   // window.close();

    history.back()
});


$(document).on("click", "#btnSubmit",function (e) {
    e.preventDefault();

	// var ClickedId = $('#btnSubmit').val();

    <?php // echo json_encode($_SESSION["rowList"]); ?>
    
    var AryPoGradeID =   <?php  echo json_encode($aryPoGradeId); ?>;
    var AryBreakUp = [];
    var rowID = $(this).data('id');
    var rowID;

	// alert(AryPoGradeID);

    for (i = 0; i < AryPoGradeID.length; i++) {
        
        rowID = AryPoGradeID[i];
        
        var PoGradeId = $.trim($('#PoGradeId' + rowID).text());
        var Variety = $.trim($('#Variety' + rowID).text());
        var ShortName = $.trim($('#shortName' + rowID).text());
        var Description = $.trim($('#Description' + rowID).val());
        var Code = $.trim($('#code' + rowID).val());
        var MCS = $.trim($('#MCS' + rowID).val());
        
        var jsonData = {};

        jsonData['rowID'] = rowID;
        jsonData['PoGradeId'] = PoGradeId;
        jsonData['Variety'] = Variety;
        jsonData['ShortName'] = ShortName;
        jsonData['Description'] = Description;
        jsonData['Code'] = Code;
        jsonData['MCS'] = MCS;

        AryBreakUp.push(jsonData);
    }
    
    console.log(AryBreakUp);

    var r = confirm("Are You Sure Do You Want To Update?");
    if (r == true) {
        $.ajax({
            url: 'Ajax/AjaxVarityAdd.php',
            type: 'POST',
            data: {
                'AryBreakUp': JSON.stringify(AryBreakUp)
            },
            success: function (data) {

                //	  console.log(data);
                
            }
        });
    }

    return false;

})
</script>
