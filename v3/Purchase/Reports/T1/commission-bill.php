<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

 $ArrivalBillID = isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "";

 if (isset($_GET['ID']) && !empty($_GET['ID'])) {

   $ArrivalBillID = trim($_GET['ID']);

     $api = API_RM_BILLS;

   $bodyContent = array("ArrivalID" => $ArrivalBillID);
    $resPostPurchaseBillPrint = CallAPI($api, "GET", "", $bodyContent);

   //   var_dump($resPostPurchaseBillPrint);

     $result = $resPostPurchaseBillPrint->Data; 
     $resPurchaseData = $resPostPurchaseBillPrint->Data->ArrivalData;
     $resPurchaseBreakUpData = $resPostPurchaseBillPrint->Data->BreakUpData;
     $resGrandTotal = $resPostPurchaseBillPrint->Data->GrandTotal;
	 
	// var_dump($resPurchaseData);

 } else {

 }


?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>
        <link href="<?php echo WORKING_URL ?>assets/css/InvoiceHtmlPdf.css?i=<?php echo rand() ?>"  rel="stylesheet" type="text/css">

        <style>
            h1{
                font-size: 27px;
                color: #000000;
            }
            .w495{
                width: 49.5%;
            }
            .w1{
                width: 1%;
            }
            .text-right{
                text-align: right;
            }
            td {
                padding: 2px 3px;
            }
             @media print {
                .page {
                    page-break-before: always;
                }
            }
            .page {
                page-break-before: always; /* Force a page break before this content */
                }
        </style>
        
    </head>

    <?php include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . '/includes/menu.php' ?>                   

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
                                        <div class="card-body" id="ShowDataTable">
                                                                                
                                                <div  class="pdf-content" id="printArea">
                                                    <div style="text-align: center; margin-bottom: 25px;">
                                                        <button class="generate" id="generate" onclick="downloadpdf('commission-bill.pdf')">Download PDF</button>
                                                    </div>
                                                    
                                                    <div id="printTable">

                                                        <div class="page">
                                                            <table class="tblMain w100 bt bl bb br">
                                                                <tr>
                                                                    <td class="text-center w495" style="padding-right: 0px;padding-top: 3px;padding-left:5px">
                                                                        <div class="bt bl bb br">Original</div>
                                                                    </td>
                                                                    <td class="w1">&nbsp;</td>
                                                                    <td class="text-center w495" style="padding-left: 0px;padding-right:5px;padding-top: 3px;">
                                                                        <div class="bt bl bb br">Duplicate</div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3" class="bl bb br" style="padding: 2px 5px 5px 5px;">
                                                                        <table class=" bl bb w100">                                                                        
                                                                            <tr>
                                                                                <td class="w495 bt p-0">
                                                                                    <table class="w100">
                                                                                        <tr>
                                                                                            <td class="p-0 br ">
                                                                                                <table class="tblHeader w100">
                                                                                                    <tr>
                                                                                                        <td colspan="2">GST. 37AAACD7852Q1ZZ</td>
                                                                                                    </tr>
                                                                                                    <tr class="trimg">
                                                                                                        <td colspan="2" class="text-left " style="padding: 0px 5px" >
                                                                                                            <img src="<?php echo ROOT_URL ?>assets/images/logo-Devi.png" style="height:45px;" /> 
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td colspan="2" class="text-center">
                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  Devi Fisheries Limited </h1>
                                                                                                            <p class="f-16" style="margin-bottom: 2px;">BOUGHT NOTE</p>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td>Bill No : <?php echo $resPurchaseData-> ArrivalNumber ?></td>
                                                                                                        <td class="text-right">Bill Date : <?php echo $resPurchaseData-> ArrivalDate ?></td>
                                                                                                    </tr>
                                                                                                    <tr><td colspan="2">Supplier : <?php echo $resPurchaseData-> SupplierName ?></td></tr>
                                                                                                    <tr><td colspan="2">Address : <?php echo $resPurchaseData-> SupplierAddress ?></td></tr>
                                                                                                    <tr>
                                                                                                        <td style="text-align: left">PAN/GST : <?php echo $resPurchaseData-> PanNumber ?></td>
                                                                                                        <td class="text-right">Phone : <?php echo $resPurchaseData-> PhoneNumber ?></td>
                                                                                                    </tr>                        
                                                                                                </table>   
                                                                                                <table class="bt  w100">
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" style="width:12%">Sl No</td>
                                                                                                        <td class="br text-center" style="width:12%">Variety</td>
                                                                                                        <td class="br text-center" style="width:12%">Count</td>
                                                                                                        <td class="br text-center" style="width:24%">Quantity - In kgs</td>
                                                                                                        <td class="br text-center" style="width:16%">Rate In Rs </td>
                                                                                                        <td class="text-center" style="width:24%">Amount In Rs</td>
                                                                                                    </tr> 
																									
																								<?php  	  $i = 1; 
                                                                                            foreach($resPurchaseBreakUpData as $item) { 
                                                                                                       ?>
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" ><?php  echo  $i ?></td>
                                                                                                        <td class="br text-center" ><?php  echo  $item->Variety ?></td>
                                                                                                        <td class="br text-center" ><?php  echo  $item->Count ?></td>
                                                                                                        <td class="br text-right"><?php  echo  $item->Weight ?></td>
                                                                                                        <td class="br text-right"><?php  echo  $item->Rate ?></td>
                                                                                                        <td class=" text-right"><?php  echo  $item->BreakupTotal ?></td>
                                                                                                    </tr> 
                                                                                            <?php   $i++;      
																							} ?>
 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Total</td>
                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->ValueTotal?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Less TDS @ 0.1 %</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->ValueTotalExTds ?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Amount Payable After TDS</td>
                                                                                                        <td class="br text-right"></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->TdsValue ?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Amount in Words </td>
                                                                                                        <td class=" text-center" colspan="3"><?php  echo  $resGrandTotal->AmountInWords ?></td>
                                                                                                    </tr> 
                                                                                                    <tr class="">
                                                                                                        <td class="text-center p-0" colspan="6">
                                                                                                            <table class="w100">
                                                                                                                <tr>
                                                                                                                    <td class="br bb" style="width:24%">&nbsp;<br><br></td> 
                                                                                                                    <td class="br bb" style="width:36%">&nbsp;</td>
                                                                                                                    <td class="bb" style="width:40%">&nbsp;</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <td class="br" style="width:24%">Supplier <br>Signature</td>
                                                                                                                    <td class="br" style="width:36%"><br>Checked By</td>
                                                                                                                    <td style="width:40%"><br>Authorized Sign</td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </td>
                                                                                                    </tr> 
                                                                                                </table>               
                                                                                            </td>
                                                                                        </tr>       
                                                                                    </table>
                                                                                </td>
                                                                                <td class="w1">&nbsp;</td>
                                                                                <td class="w495 bl bt p-0">
                                                                                    <table class="w100">
                                                                                        <tr>
                                                                                            <td class="p-0 br ">
                                                                                                <table class="tblHeader w100">
                                                                                                    <tr>
                                                                                                        <td colspan="2">GST. 37AAACD7852Q1ZZ</td>
                                                                                                    </tr>
                                                                                                    <tr class="trimg">
                                                                                                        <td colspan="2" class="text-left " style="padding: 0px 5px" >
                                                                                                            <img src="<?php echo ROOT_URL ?>/assets/images/logo-Devi.png" style="height:45px;" /> 
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td colspan="2" class="text-center">
                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  Devi Fisheries Limited </h1>
                                                                                                            <p class="f-16" style="margin-bottom: 2px;">BOUGHT NOTE</p>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td>Bill No :  <?php echo $resPurchaseData-> ArrivalNumber ?></td>
                                                                                                        <td class="text-right">Bill Date :  <?php echo $resPurchaseData-> ArrivalDate ?></td>
                                                                                                    </tr>
                                                                                                    <tr><td colspan="2">Supplier :  <?php echo $resPurchaseData-> SupplierName ?></td></tr>
                                                                                                    <tr><td colspan="2">Address :  <?php echo $resPurchaseData-> SupplierAddress ?></td></tr>
                                                                                                    <tr>
                                                                                                        <td style="text-align: left">PAN/GST :  <?php echo $resPurchaseData-> PanNumber ?></td>
                                                                                                        <td class="text-right">Phone :  <?php echo $resPurchaseData-> PhoneNumber ?></td>
                                                                                                    </tr>                        
                                                                                                </table>   
                                                                                                <table class="bt  w100">
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" style="width:12%">Sl No</td>
                                                                                                        <td class="br text-center" style="width:12%">Variety</td>
                                                                                                        <td class="br text-center" style="width:12%">Count</td>
                                                                                                        <td class="br text-center" style="width:24%">Quantity - In kgs</td>
                                                                                                        <td class="br text-center" style="width:16%">Rate In Rs </td>
                                                                                                        <td class="text-center" style="width:24%">Amount In Rs</td>
                                                                                                    </tr> 
																									
																						<?php  	  $i = 1; 
                                                                                            foreach($resPurchaseBreakUpData as $item) { 
                                                                                                       ?>
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" ><?php  echo  $i ?></td>
                                                                                                        <td class="br text-center" ><?php  echo  $item->Variety ?></td>
                                                                                                        <td class="br text-center" ><?php  echo  $item->Count ?></td>
                                                                                                        <td class="br text-right"><?php  echo  $item->Weight ?></td>
                                                                                                        <td class="br text-right"><?php  echo  $item->Rate ?></td>
                                                                                                        <td class=" text-right"><?php  echo  $item->BreakupTotal ?></td>
                                                                                                    </tr>
																					<?php   $i++;      
																							} ?>
                                                                                                   
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Total</td>
                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->ValueTotal?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Less TDS @ 0.1 %</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->ValueTotalExTds ?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Amount Payable After TDS</td>
                                                                                                        <td class="br text-right"></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->TdsValue ?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="3">Amount in Words </td>
                                                                                                        <td class=" text-center" colspan="3"><?php  echo  $resGrandTotal->AmountInWords ?></td>
                                                                                                    </tr> 
                                                                                                    <tr class="">
                                                                                                        <td class="text-center p-0" colspan="6">
                                                                                                            <table class="w100">
                                                                                                                <tr>
                                                                                                                    <td class="br bb" style="width:24%">&nbsp;<br><br></td> 
                                                                                                                    <td class="br bb" style="width:36%">&nbsp;</td>
                                                                                                                    <td class="bb" style="width:40%">&nbsp;</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <td class="br" style="width:24%">Supplier <br>Signature</td>
                                                                                                                    <td class="br" style="width:36%"><br>Checked By</td>
                                                                                                                    <td style="width:40%"><br>Authorized Sign</td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </td>
                                                                                                    </tr> 
                                                                                                </table>               
                                                                                            </td>
                                                                                        </tr>       
                                                                                    </table>
                                                                                </td>
                                                                            </tr>                                                                      
                                                                        </table>   
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <br><br>
                                                        <div class="page">
                                                            <table class="tblMain w100 bt bl bb br">
                                                                <tr>
                                                                    <td class="text-center w495" style="padding-right: 0px;padding-top: 3px;padding-left:5px">
                                                                        <div class="bt bl bb br">Original</div>
                                                                    </td>
                                                                    <td class="w1">&nbsp;</td>
                                                                    <td class="text-center w495" style="padding-left: 0px;padding-right:5px;padding-top: 3px;">
                                                                        <div class="bt bl bb br">Duplicate</div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="3" class="bl bb br" style="padding: 2px 5px 5px 5px;">
                                                                        <table class=" bl bb w100">                                                                        
                                                                            <tr>
                                                                                <td class="w495 bt p-0">
                                                                                    <table class="w100">
                                                                                        <tr>
                                                                                            <td class="p-0 br ">
                                                                                                <table class="tblHeader w100">
                                                                                                    <tr>
                                                                                                        <td colspan="2">GST. 37AAACD7852Q1ZZ</td>
                                                                                                    </tr>
                                                                                                    <tr class="trimg">
                                                                                                        <td colspan="2" class="text-left " style="padding: 0px 5px" >
                                                                                                            <img src="<?php echo ROOT_URL ?>/assets/images/logo-Devi.png" style="height:45px;" /> 
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td colspan="2" class="text-center">
                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  Devi Fisheries Limited </h1>
                                                                                                            <p class="f-16" style="margin-bottom: 2px;">BOUGHT NOTE</p>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td>Bill No : <?php echo $resPurchaseData-> ArrivalNumber ?></td>
                                                                                                        <td class="text-right">Bill Date : <?php echo $resPurchaseData-> ArrivalDate ?></td>
                                                                                                    </tr>
                                                                                                    <tr><td colspan="2">Supplier : <?php echo $resPurchaseData-> SupplierName ?></td></tr>
                                                                                                    <tr><td colspan="2">Address : <?php echo $resPurchaseData-> SupplierAddress ?></td></tr>
                                                                                                    <tr>
                                                                                                        <td style="text-align: left">PAN/GST : <?php echo $resPurchaseData-> PanNumber ?></td>
                                                                                                        <td class="text-right">Phone : <?php echo $resPurchaseData-> PhoneNumber ?></td>
                                                                                                    </tr>                        
                                                                                                </table>   
                                                                                                <table class="bt  w100">
                                                                                                    <tr class=" bb">
                                                                                                       
                                                                                                        <td class="br text-center" style="width:36%">Variety</td>
                                                                                                        <td class="br text-center" style="width:24%">Quantity - In kgs</td>
                                                                                                        <td class="br text-center" style="width:16%">Harvest</td>
                                                                                                        <td class="text-center" style="width:24%">Amount In Rs</td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" >Harvest Expenses</td>                   
                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>
                                                                                                        <td class="br text-right"><?php echo $resPurchaseData-> Extra ?></td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>
                                                                                                    </tr> 
                                                                                                 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Total</td>
                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Less TDS @ 0.1 %</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpansesTds?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Amount Payable After TDS</td>
                                                                                                        <td class="br text-right"></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpAfterTds?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Amount in Words </td>
                                                                                                        <td class=" text-center" colspan="3"><?php  echo  $resGrandTotal->HarvestExpAmountInWords?></td>
                                                                                                    </tr> 
                                                                                                    <tr class="">
                                                                                                        <td class="text-center p-0" colspan="6">
                                                                                                            <table class="w100">
                                                                                                                <tr>
                                                                                                                    <td class="br bb" style="width:24%">&nbsp;<br><br></td> 
                                                                                                                    <td class="br bb" style="width:36%">&nbsp;</td>
                                                                                                                    <td class="bb" style="width:40%">&nbsp;</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <td class="br" style="width:24%">Supplier <br>Signature</td>
                                                                                                                    <td class="br" style="width:36%"><br>Checked By</td>
                                                                                                                    <td style="width:40%"><br>Authorized Sign</td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </td>
                                                                                                    </tr> 
                                                                                                </table>               
                                                                                            </td>
                                                                                        </tr>       
                                                                                    </table>
                                                                                </td>
                                                                                <td class="w1">&nbsp;</td>
                                                                                <td class="w495 bl bt p-0">
                                                                                    <table class="w100">
                                                                                        <tr>
                                                                                            <td class="p-0 br ">
                                                                                                <table class="tblHeader w100">
                                                                                                    <tr>
                                                                                                        <td colspan="2">GST. 37AAACD7852Q1ZZ</td>
                                                                                                    </tr>
                                                                                                    <tr class="trimg">
                                                                                                        <td colspan="2" class="text-left " style="padding: 0px 5px" >
                                                                                                            <img src="<?php echo ROOT_URL ?>/assets/images/logo-Devi.png" style="height:45px;" /> 
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td colspan="2" class="text-center">
                                                                                                            <h1 style="margin-bottom: 2px;" class="bold">  Devi Fisheries Limited </h1>
                                                                                                            <p class="f-16" style="margin-bottom: 2px;">BOUGHT NOTE</p>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                    <tr>
                                                                                                        <td>Bill No : <?php echo $resPurchaseData-> ArrivalNumber ?></td>
                                                                                                        <td class="text-right">Bill Date : <?php echo $resPurchaseData-> ArrivalDate ?></td>
                                                                                                    </tr>
                                                                                                    <tr><td colspan="2">Supplier : <?php echo $resPurchaseData-> SupplierName ?></td></tr>
                                                                                                    <tr><td colspan="2">Address : <?php echo $resPurchaseData-> SupplierAddress ?></td></tr>
                                                                                                    <tr>
                                                                                                        <td style="text-align: left">PAN/GST : <?php echo $resPurchaseData-> PanNumber ?></td>
                                                                                                        <td class="text-right">Phone : <?php echo $resPurchaseData-> PhoneNumber ?></td>
                                                                                                    </tr>                        
                                                                                                </table>   
                                                                                                <table class="bt  w100">
                                                                                                     <tr class=" bb">
                                                                                                       
                                                                                                        <td class="br text-center" style="width:36%">Variety</td>
                                                                                                        <td class="br text-center" style="width:24%">Quantity - In kgs</td>
                                                                                                        <td class="br text-center" style="width:16%">Harvest</td>
                                                                                                        <td class="text-center" style="width:24%">Amount In Rs</td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" >Harvest Expenses</td>                   
                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>
                                                                                                        <td class="br text-right"><?php echo $resPurchaseData-> Extra ?></td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>
                                                                                                    </tr> 
                                                                                                 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Total</td>
                                                                                                        <td class="br text-right"><?php  echo  $resGrandTotal->WeightTotal?></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpanses?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Less TDS @ 0.1 %</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpansesTds?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Amount Payable After TDS</td>
                                                                                                        <td class="br text-right"></td>
                                                                                                        <td class="br text-right">&nbsp;</td>
                                                                                                        <td class=" text-right"><?php  echo  $resGrandTotal->HarvestExpAfterTds?></td>
                                                                                                    </tr> 
                                                                                                    <tr class=" bb">
                                                                                                        <td class="br text-center" colspan="1">Amount in Words </td>
                                                                                                        <td class=" text-center" colspan="3"><?php  echo  $resGrandTotal->HarvestExpAmountInWords?></td>
                                                                                                    </tr> 
                                                                                                    <tr class="">
                                                                                                        <td class="text-center p-0" colspan="6">
                                                                                                            <table class="w100">
                                                                                                                <tr>
                                                                                                                    <td class="br bb" style="width:24%">&nbsp;<br><br></td> 
                                                                                                                    <td class="br bb" style="width:36%">&nbsp;</td>
                                                                                                                    <td class="bb" style="width:40%">&nbsp;</td>
                                                                                                                </tr>
                                                                                                                <tr>
                                                                                                                    <td class="br" style="width:24%">Supplier <br>Signature</td>
                                                                                                                    <td class="br" style="width:36%"><br>Checked By</td>
                                                                                                                    <td style="width:40%"><br>Authorized Sign</td>
                                                                                                                </tr>
                                                                                                            </table>
                                                                                                        </td>
                                                                                                    </tr> 
                                                                                                </table>               
                                                                                            </td>
                                                                                        </tr>       
                                                                                    </table>
                                                                                </td>
                                                                            </tr>                                                                      
                                                                        </table>   
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        
                                                        
                                                    </div>  <!-- Actual Page Content -->
                                                    

                                                    
                                                    

                                                </div> <!-- END pdf-content -->



                                        </div> <!-- end card body-->
                                    </div> <!-- end card -->
                                </div><!-- end col-->
                            </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                <?php include_once ROOT . '/includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . '/includes/common-js.php' ?>
        <?php include_once ROOT . '/includes/app-js.php' ?>

        <script>
            var invNo = "<?php echo "commission-bill" ?>";
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js "></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script> 


window.jsPDF = window.jspdf.jsPDF;
var pdf = new jsPDF({
    orientation: 'portrait', // 'portrait' for vertical, 'landscape' for horizontal
    unit: 'px', // Measurement unit
    format: 'a4', // 'a3', // Page size format, 'a4' for A4 size
    
});

function downloadpdf(fileName) {
    var pWidth = pdf.internal.pageSize.getWidth(); // Page width
    var pHeight = pdf.internal.pageSize.getHeight(); // Page height

    // Define the HTML element you want to capture (e.g., a table with id "printTable")
    var element = document.getElementById("printTable");

    // Adjust the width of the HTML content to match the PDF page width
    // element.style.width = pWidth + "px"; // Set the width in points

    // Calculate the adjusted height to maintain the original aspect ratio
    var aspectRatio = element.offsetWidth / element.offsetHeight;
    var adjustedHeight = pWidth / aspectRatio;

    // Calculate the margins (adjust as needed)
    var marginLeft = 50;
    var marginTop = 30;
    var marginRight = 50;
    var marginBottom = 30;

    var contentWidth = element.offsetWidth - marginLeft - marginRight;
    console.log(contentWidth);
    var contentHeight = element.offsetHeight - marginTop - marginBottom;

    // Calculate the x and y position to center the content within the PDF page
    var x = 0; // Left-aligned content

    pdf.html(element, {
        html2canvas: {
            // scale: 0.61, // a3
             scale: 0.43, // a3
            width: contentWidth,
            height: contentHeight,
            removeContainer: false // for analyzing iframe
        },
        x: x,
        margin: [15, 0, 30, 0], // Adds the margin to the PDF view 
        autoPaging: 'text',
        callback: function (pdf) {
            
            var footerHeight = 20; // Height of the footer
            var gap = 10; // Gap between content and footer

            for (var i = 1; i <= pdf.internal.getNumberOfPages(); i++) {
                pdf.setPage(i);
                pdf.setFontSize(12);

                // Calculate the Y position for the footer
                var footerY = pHeight - marginBottom + gap;

                // Calculate the Y position for the page number text
                var pageNumberY = footerY + (footerHeight / 2);

                // pdf.setDrawColor(0); // Set border color to black
                // // pdf.line(margin, footerY, pWidth - margin, footerY); // Draw border at the top of the footer
                // pdf.line(1, 1, 1, 1); // Draw border at the top of the footer


                pdf.text(
                    pWidth / 2,
                    pageNumberY,
                   'Page ' + i + ' of ' + pdf.internal.getNumberOfPages() + " - " + invNo,
                    null,
                    null,
                    'center'
                );
            }

            var timestamp = Date.now();
            var pdfName = fileName;
            pdf.save(pdfName);
        }
    });
}

</script> 

<script>
    $(document).ready(function() {
        // Check if the user agent string contains "Android" or "iOS"
        if (navigator.userAgent.match(/Android/i)) {
            // For Android devices, set a specific viewport meta tag
            $('meta[name=viewport]').attr('content', 'width=1150');
        } else if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
            // For iOS devices, set a different viewport meta tag
            $('meta[name=viewport]').attr('content', 'width=1150');
        }
    });
</script>

    </body>
</html>