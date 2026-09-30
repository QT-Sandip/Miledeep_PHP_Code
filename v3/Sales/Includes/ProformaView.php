<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");


$PageTitleFolder="Sales";
$PageTitle="Proforma Invoice Edit";

$PreparedBy = $_SESSION["UserID"];


 $POID=$PIId;
 $INVOICE_ID=$POID;
 
 
// $SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES' order by ABS(OPTIONS) asc ";
// $STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
// $ARY_OPTIONS=array();
// WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
// 	$ARY_OPTIONS[]=$ROW;
// }


$SqlUom = "SELECT * FROM ScrmUnitsMaster WHERE Status=1";
$StmtUom = $iconn->query($SqlUom);

$SqlCommercial = "SELECT count(*) as Total FROM ScrmCommercialData where PoID='$PIId'";
$StmtCommercial = $iconn->query($SqlCommercial);
$RowCommercial = $StmtCommercial->fetch_array();
$CommTotal = $RowCommercial['Total'];
if($CommTotal>0){
$buttonState='disabled';
}else{
    $buttonState='';
}

    $sqlPIData="SELECT * FROM ScrmPoData  WHERE PoID='$POID'";
	$stmtPIData=$iconn->query($sqlPIData);
	$RowPIData=$stmtPIData->fetch_array();
    // echo json_encode($RowPIData, JSON_PRETTY_PRINT);

	  $Country=$RowPIData['DestinationCountry']; 
	 $InvoiceNo=$RowPIData['InvNo'];
	 $ExpID=$RowPIData['ExporterID'];

      $SqlImporter="SELECT * FROM `ScrmImporterOfRecord` WHERE Status=1";
	$StmtImporter=$iconn->query($SqlImporter);
	
	$SqlNotifyparty="SELECT * FROM `ScrmNotifyparty` WHERE Status=1";
	$StmtNotifyparty=$iconn->query($SqlNotifyparty);
	
$SqlExporter="SELECT * FROM AdminExporterDetails WHERE ExpType in(1,3)";
	$StmtExporter=$iconn->query($SqlExporter);
  
    $SqlProcessor="SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
	$StmtProcessor=$iconn->query($SqlProcessor);

	$SqlCertification="SELECT * FROM `ScrmOrderCertification` WHERE Status=1";
	$StmtCertification=$iconn->query($SqlCertification);
	
	$SqlConsignee="SELECT * FROM ScrmConsigneeDetails WHERE Status=1";
	$StmtConsignee=$iconn->query($SqlConsignee);
	
	$SqlBuyer="SELECT * FROM ScrmBuyerDetails WHERE Status=1";
	$StmtBuyer=$iconn->query($SqlBuyer);
	
	$SqlAgent="SELECT * FROM `ScrmPiAgentMaster` WHERE Status=1";
	$StmtAgent=$iconn->query($SqlAgent);
	
	$SqlBank="SELECT * FROM ScrmBankDetailsMaster WHERE `BankStatus` = '1'";
	$StmtBank=$iconn->query($SqlBank);
	
	$SqlCurrency="SELECT * FROM `ScrmCurrencyMaster` WHERE `Status` = '1'";
	$StmtCurrency=$iconn->query($SqlCurrency);
	
    $SqlCountryList="SELECT * FROM ScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList); 

	$SqlPaymentTerms="SELECT * FROM ScrmPaymentTerms";
	$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);

    $SqlAdminUsers="SELECT * FROM AdminUserDetails WHERE DepartmentID=9 and PoUserStatus=1";
	$StmtAdminUsers=$iconn->query($SqlAdminUsers);
	
	$SqlPorts1="SELECT * FROM ScrmPortCities WHERE IsPortofLoading=1";
    $StmtPorts1=$iconn->query($SqlPorts1);

	$SqlUltimateConsignee="SELECT * FROM ScrmUltimateConsigneeDetails WHERE Status=1";
	$StmtUltimateConsignee=$iconn->query($SqlUltimateConsignee);
	
	
	 $SqlPorts="SELECT * FROM ScrmPortCities where Country='$Country'"; 
	$StmtPorts=$iconn->query($SqlPorts);
	
		$SqlShipmentTerms="SELECT * FROM `ScrmShipmentTermsMaster` ";
	$StmtShipmentTerms=$iconn->query($SqlShipmentTerms);
	
	
	while($RowPo=$StmtPorts->fetch_array()){
		$AryPorts[]=$RowPo;
	}

		$AccAval=true;
			$CYear=date("Y");
			$AYear=$CYear+1;
			while($AccAval){
			$PoRefId=generateAccountId();
			//$PINo="SME/".$CYear.'-'.$AYear.'/'.$PoRefId;
			$sql="SELECT count(*) as Total FROM `ScrmPoData` where PoRefId='$PoRefId'";
			$sqlca=$iconn->query($sql);
			$rlt = mysqli_fetch_array($sqlca,MYSQLI_ASSOC);
			if($rlt['Total'] == 0 ){
				$AccAval=false;
			}			
			}
	
	 function generateAccountId(){
				$generator = "1357902468"; 
                $result = ""; 
                for ($i = 1; $i <= 5; $i++) { 
                    $result .= substr($generator, (rand()%(strlen($generator))), 1); 
                }  
				
				return $result;
		}
	
extract($_POST);
extract($_GET);












$SqlPI="SELECT * FROM `ScrmPoData`   WHERE PoID='$INVOICE_ID'";

$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();
$PoRefId=$RowPI['PoRefId'];
$QtyUnit=$RowPI['QtyUnit'];
$UnitCurrency=$RowPI['UnitCurrency'];


$SqlPIRef="SELECT * FROM `ScrmPoData`   WHERE PoRefId='$PoRefId'";

$StmtPIRef=$iconn->query($SqlPIRef);
$RowCurrency=$StmtPIRef->fetch_array();
// echo json_encode($RowCurrency, JSON_PRETTY_PRINT);

$UnitCurrency=$RowCurrency['UnitCurrency'];

$StmtPIRef=$iconn->query($SqlPIRef);


$sql_packing="SELECT * FROM `AdminPackingStyles` Where Status='1' 
order by abs(PackingStyle) Asc  limit 100";

$stmt_packing=$iconn->query($sql_packing);



  $SQL_PO_GRADE_DATA="SELECT * FROM `ScrmPoGradeData` 
  as PGD
left join ScrmPIVarietyList as PVL on PGD.VarietyID=PVL.VarietyID
 where InvId='$INVOICE_ID'  ORDER BY ID ASC"; 
$STMT_PO_GRADE_DATA=$iconn->query($SQL_PO_GRADE_DATA); 

$GRADE_ROW=$STMT_PO_GRADE_DATA->FETCH_ARRAY();

$STMT_PO_GRADE_DATA=$iconn->query($SQL_PO_GRADE_DATA); 
/*
$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

*/

  $SqlPIVarietyList="SELECT * FROM `ScrmPIVarietyList` as  SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	left join ScrmPreparationMaster as  SPM on SPM.ShortCode =SPV.PreparationType
		where SPV.Status='1'";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);

function checkEmpty($Value){
		if($Value==''){
		$Value='';	
		}
		else{
		$Value=' '.$Value;	
		}
		return $Value;
	}
	
	$AryVarietyList=array();

while($RowPIVarietyList=$StmtPIVarietyList->fetch_array()){
	$AryItem=array();
	$PreparationName=checkEmpty($RowPIVarietyList['PreparationName']);
	$TypeProduct=checkEmpty($RowPIVarietyList['TypeProduct']);
	$ProductFormm=checkEmpty($RowPIVarietyList['FullName']);
	$FreezingTypee=checkEmpty($RowPIVarietyList['FreezingType']);
	$PreparationTypee=checkEmpty($RowPIVarietyList['PreparationType']);
	$GlazePercc=checkEmpty($RowPIVarietyList['GlazePerc']);
	$Glazee=checkEmpty($RowPIVarietyList['Glaze']);
	$Treatmentt=checkEmpty($RowPIVarietyList['Treatment']);
	$Grade=checkEmpty($RowPIVarietyList['Grade']);
	$PackingStyle=checkEmpty($RowPIVarietyList['PackingStyle']);
	$BrandName=checkEmpty($RowPIVarietyList['BrandName']);
	$PreparationDesc=checkEmpty($RowPIVarietyList['PreparationDesc']);
	$ProductDescription=checkEmpty($RowPIVarietyList['ProductDescription']);
	$DeviSUPC=checkEmpty($RowPIVarietyList['DeviSUPC']);
	$BuyerSUPC=checkEmpty($RowPIVarietyList['BuyerSUPC']);
	
	
	// $Data=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt.$Grade.$PackingStyle.$BrandName; 
	 $Description=$DeviSUPC.'-'.$Grade.$PackingStyle.$ProductDescription .$FreezingTypee.$GlazePercc. '%' .$Glazee.$Treatmentt.$BrandName; 
	$AryItem['Name']=$Description;
	$AryItem['ID']=$RowPIVarietyList['VarietyID'];
	$AryVarietyList[]=$AryItem;
	
}


$sql_invoice="SELECT * FROM `ScrmPoData` where PoID='$INVOICE_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();

$UnitCurrency=$invoice_details['UnitCurrency'];

	

extract($_POST);
extract($_GET);


 ?>


<!DOCTYPE html>
<html lang="en">

<div class="row">
    <div class="col-12">
        <div class="card">

            <form role="form" method="post" enctype="multipart/form-data" id="frmPoEdit">
                <div class="card-body">

                    <div class="row">


                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Select Exporter <span style="color:red">*</span></label>
                            <select required class="form-control select2" name="ExporterID" id="ExporterID" disabled>
                                <option value="">Select Exporter</option>
                                <?php while($RowExporter=$StmtExporter->fetch_array()) { ?>

                                <option <?php if($RowPIData['ExporterID']==$RowExporter['ExpID']){ ?>
                                    selected="selected" <?php } ?> Value="<?php echo $RowExporter['ExpID'];?>">
                                    <?php echo $RowExporter['ExpName'];?></OPTION>
                                <?php } ?>

                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Select Processor <span style="color:red">*</span></label>
                            <select required class="form-control select2" name="ProcessorID" id="ProcessorID" disabled>
                                <option value="">Select Processor</option>
                                <?php while($RowProcessor=$StmtProcessor->fetch_array()) { ?>

                                <option <?php if($RowPIData['ProcessorID']==$RowProcessor['ExpID']){ ?>
                                    selected="selected" <?php } ?> Value="<?php echo $RowProcessor['ExpID'];?>">
                                    <?php echo $RowProcessor['ExpName'].'('.$RowProcessor['ExpApprovalNo'].')';?>
                                </OPTION>

                                <?php } ?>

                            </select>
                        </div>
                        <div class="form-group col-lg-3">
                            <label for="exampleInputEmail1">Reference Number</label>
                            <input type="text" class="form-control" name='ReferenceNo' id="ReferenceNo"
                                placeholder="Enter Reference Number" value="<?php echo $RowPIData['ReferenceNo'];?>"
                                disabled>
                        </div>

                        <div class="form-group col-lg-3"></div>




                        <div class="form-group col-lg-3">
                            <label for="exampleInputEmail1">Proforma Invoice No<span style="color:red">*</span></label>
                            <input required type="number" class="form-control" name='InvNo' id="InvNo"
                                placeholder="Enter Proforma Invoice No" value="<?php echo $RowPIData['InvNo']; ?>"
                                disabled>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputEmail1">Proforma Invoice Date<span
                                    style="color:red">*</span></label>
                            <input required type="date" value="<?php  echo $RowPIData['InvDate'];?>"
                                class="form-control" name='InvDate' id="InvDate"
                                placeholder="Enter Proforma Invoice No " disabled>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1"> P.O No</label>
                            <input type="text" class="form-control" name='PoNo'
                                value="<?php  echo $RowPIData['PoNo'];?>" id="PoNo" placeholder="Enter P.O Number"
                                disabled>
                        </div>

                        <?php  //echo date("Y-m-d");?>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1"> P.O Date</label>
                            <input type="date" value="<?PHP ECHO $RowPIData['PoDate']; ?>" class="form-control"
                                name='PoDate' id="PoDate" placeholder="Enter P.O Date" disabled>
                        </div>









                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Consignee<span style="color:red">*</span></label>
                            <select required class="form-control select2" name="ConsigneeID" id="ConsigneeID" disabled>
                                <option value="">Select Consignee</option>
                                <?php while ($RowConsignee=$StmtConsignee->fetch_array()){ ?>
                                <option <?php if($RowPIData['ConsigneeID']==$RowConsignee['ConID']){ ?>
                                    selected="selected" <?php } ?> value="<?php echo $RowConsignee['ConID'] ?>">
                                    <?php echo $RowConsignee['ConsigneeName'] ?></option>

                                <?php  }?>
                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Ultimate Consignee</label>
                            <select class="form-control select2" name="UltimateConsigneeID" id="UltimateConsigneeID"
                                disabled>
                                <option value="">Select</option>
                                <?php while ($RowUltimateConsignee=$StmtUltimateConsignee->fetch_array()){ ?>
                                <option <?php if($RowPIData['UltimateConsigneeID']==$RowUltimateConsignee['ConID']){ ?>
                                    selected="selected" <?php } ?> value="<?php echo $RowUltimateConsignee['ConID'] ?>">
                                    <?php echo $RowUltimateConsignee['ConsigneeName'] ?></option>

                                <?php  }?>
                            </select>
                        </div>


                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Buyer Name</label>
                            <select class="form-control select2" name="BuyerID" id="BuyerID" disabled>
                                <option value="">Select Buyer</option>
                                <?php while ($RowBuyer=$StmtBuyer->fetch_array()){ ?>
                                <option <?php if($RowPIData['BuyerID']==$RowBuyer['BuyerID']){ ?> selected="selected"
                                    <?php } ?> value="<?php echo $RowBuyer['BuyerID'] ?>">
                                    <?php echo $RowBuyer['BuyerName']?></option>

                                <?php  }?>
                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Notify Party</label>
                            <select class="form-control select2" name="NotifyPartyID" id="NotifyPartyID" disabled>
                                <option value="">Select</option>
                                <?php while ($RowNotifyparty=$StmtNotifyparty->fetch_array()){ ?>
                                <option <?php if($RowPIData['NotifyPartyID']==$RowNotifyparty['NotifyPartyID']){ ?>
                                    selected="selected" <?php } ?>
                                    value="<?php echo $RowNotifyparty['NotifyPartyID'] ?>">
                                    <?php echo $RowNotifyparty['NotifyPartyName'] ?></option>

                                <?php  }?>
                            </select>
                        </div>



                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Importer of Record <span
                                    style="color:red">*</span></label>
                            <select class="form-control select2" name="ImporterID" id="ImporterID" disabled>
                                <option value="">Select</option>
                                <?php while ($RowImporter=$StmtImporter->fetch_array()){ ?>
                                <option <?php if($RowPIData['ImporterID']==$RowImporter['ImporterID']){ ?>
                                    selected="selected" <?php } ?> value="<?php echo $RowImporter['ImporterID'] ?>">
                                    <?php echo $RowImporter['ImporterName'] ?></option>

                                <?php  }?>
                            </select>
                        </div>



                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Agent</label>
                            <select class="form-control select2" name="Agent" id="Agent" disabled>
                                <option value="">Select</option>
                                <?php while ($RowAgent=$StmtAgent->fetch_array()){ ?>
                                <option <?php if($RowPIData['Agent']==$RowAgent['AgentID']){ ?> selected="selected"
                                    <?php } ?> value="<?php echo $RowAgent['AgentID'] ?>">
                                    <?php echo $RowAgent['AgentName'] ?></option>

                                <?php  }?>
                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Certification</label>
                            <select class="form-control select2" name="Certification" id="Certification" disabled>
                                <option value="">Select</option>
                                <?php while ($RowCertification=$StmtCertification->fetch_array()){ ?>
                                <option <?php if($RowPIData['Certification']==$RowCertification['CerificationID']){ ?>
                                    selected="selected" <?php } ?>
                                    value="<?php echo $RowCertification['CerificationID'] ?>">
                                    <?php echo $RowCertification['CerificationName'] ?></option>

                                <?php  }?>
                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Port of Loading</label>
                            <select required class="form-control select2" name="PortofLoading" id="PortofLoading"
                                disabled>
                                <option value="">Select</option>
                                <?php while ($RowPorts=$StmtPorts1->fetch_array()){ ?>
                                <option <?php  if($RowPIData['PortofLoading']==$RowPorts['PortId']) { ?>
                                    selected=="selected" <?php } ?> value="<?php echo $RowPorts['PortId'] ?>">
                                    <?php echo $RowPorts['Name'] ?></option>

                                <?php  }?>
                            </select>
                        </div>


                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Destination Country<span
                                    style="color:red">*</span></label>


                            <select required class="form-control select2" name="DestinationCountry"
                                id="DestinationCountry" disabled>
                                <option value="">Select Country</option>
                                <?php 
                                $StmtCountryList->data_seek(0);
				   $DestinationCountry=$RowPIData['DestinationCountry'];
				   while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
                                <option <?php if($DestinationCountry==$RowCountry['id']){ ?> selected="selected"
                                    <?php } ?> value="<?php echo $RowCountry['id'] ?>">
                                    <?php echo $RowCountry['name']?></option>

                                <?php  }?>
                            </select>
                        </div>





                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Port of Discharge<span style="color:red">*</span></label>
                            <select required class="form-control select2" name="PortOfDischarge" id="PortOfDischarge"
                                disabled>
                                <option value="">Select Port</option>
                                <?php 
				   $PortOfDischarge=$RowPIData['PortOfDischarge'];
				   foreach ($AryPorts as $RowPort ){ ?>
                                <option <?php if($PortOfDischarge==$RowPort['PortId']){ ?> selected="selected"
                                    <?php } ?> value="<?php echo $RowPort['PortId'] ?>">
                                    <?php echo $RowPort['Name']?></option>

                                <?php  }?>
                            </select>

                        </div>


                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Final Destination Country</label>
                            <select required class="form-control select2" name="FinalDestinationCountry"
                                id="FinalDestinationCountry" disabled>
                                <option value="">Select Country</option>
                                <?php
$StmtCountryList->data_seek(0);
				   $FinalDestinationCountry=$RowPIData['FinalDestinationCountry'];
				   while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
                                <option <?php if($FinalDestinationCountry==$RowCountry['id']){ ?> selected="selected"
                                    <?php } ?> value="<?php echo $RowCountry['id'] ?>">
                                    <?php echo $RowCountry['name']?></option>

                                <?php  }?>
                            </select>
                        </div>

                        <!--   <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Final Destination<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination'];?>">
                </div> -->

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1"> Final Destination<span
                                    style="color:red">*</span></label>

                            <!--<input required type="text" class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination'];?>">
               -->

                            <select required class="form-control select2" name="FinalDestination" id="FinalDestination"
                                disabled>
                                <option value="">Select Final Destination</option>
                                <?php 
				   $FinalDestination=$RowPIData['FinalDestination'];
				   foreach ($AryPorts as $RowPort ){ ?>
                                <option <?php if($FinalDestination==$RowPort['PortId']){ ?> selected="selected"
                                    <?php } ?> value="<?php echo $RowPort['PortId'] ?>">
                                    <?php echo $RowPort['Name']?></option>

                                <?php  }?>
                            </select>

                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">ShipBy Date</label>
                            <input type="date" class="form-control" name="ShipByDate" id="ShipByDate"
                                placeholder="Enter ShipBy Date" Value="<?php echo $RowPIData['ShipByDate'] ;?>"
                                disabled></input>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Bank Details<span style="color:red">*</span></label>
                            <select required class="form-control select2" name="BankID" id="BankID" disabled>
                                <option value="">Select Bank</option>
                                <?php 
				   $BankID=$RowPIData['BankID'];
				   while ($RowBank=$StmtBank->fetch_array()){ ?>
                                <option <?php if($BankID==$RowBank['ID']){ ?> selected="selected" <?php } ?>
                                    value="<?php echo $RowBank['ID'] ?>">
                                    <?php echo $RowBank['AccntName'].' - 
					 '.$RowBank['BankName'].'-'.$RowBank['AccountNumber'] ?></option>

                                <?php  }?>
                            </select>
                        </div>


                        <div class="form-group col-lg-3">
                            <label for="exampleInputEmail1">Show Bank<span style="color:red">*</span></label>
                            <select required class="form-control select2" name="ShowBank" id="ShowBank" disabled>
                                <option <?php if($RowPIData['ShowBank']==0){ ?> selected="selected" <?php } ?>
                                    value="0">No</option>
                                <option <?php if($RowPIData['ShowBank']==1){ ?> selected="selected" <?php } ?>
                                    value="1">Yes</option>

                            </select>
                        </div>

                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Payment Terms<span style="color:red">*</span></label>

                            <select required class="form-control select2" name="ByPayment" id="ByPayment" disabled>
                                <option value="">Select Payment</option>
                                <?php 
				   $PaymentTerms=$RowPIData['PaymentTerms'];
				   
				   while ($RowPayment=$StmtPaymentTerms->fetch_array()){ ?>
                                <option <?php if($PaymentTerms==$RowPayment['ID']){ ?> selected="selected" <?php } ?>
                                    value="<?php echo $RowPayment['ID'] ?>">
                                    <?php echo $RowPayment['PaymentName']?></option>

                                <?php  }?>
                            </select>
                        </div>




                        <div class="form-group col-lg-3">
                            <label for="exampleInputEmail1">Shipment Terms<span style="color:red">*</span></label>

                            <select required class="form-control select2" name="ShippingTerms" id="ShippingTerms"
                                disabled>
                                <?php 
					 $ShippingTerms=$RowPIData['ShippingTerms'];
					 while($RowShipterms=$StmtShipmentTerms->fetch_array()){?>

                                <option <?php if($ShippingTerms==$RowShipterms['TermName']){ ?> selected="selected"
                                    <?php } ?> value="<?php echo $RowShipterms['TermName'] ?>">
                                    <?php echo $RowShipterms['TermName'] ?></option>
                                <?php  }?>
                                <select>

                        </div>





                        <div class="form-group col-lg-3">
                            <label for="exampleInputEmail1">Delivery Terms<span style="color:red">*</span></label>
                            <select required class="form-control select2" name="DeliveryTerms" id="DeliveryTerms"
                                disabled>
                                <option value="">Select</option>
                                <option <?php if($RowPIData['DeliveryTerms']=='CY/CY'){ ?> selected=="selected"
                                    <?php } ?> value="CY/CY">CY/CY</option>
                                <option <?php if($RowPIData['DeliveryTerms']=='CY/SD'){ ?> selected=="selected"
                                    <?php } ?> value="CY/SD">CY/SD</option>

                            </select>
                        </div>


                        <div class="form-group col-lg-3">
                            <label for="exampleInputEmail1">Quantity Units<span style="color:red">*</span></label>

                            <select required class="form-control select2" name="QtyUnit" id="QtyUnit" disabled>
                            <?php 
					 $QtyUnit=$RowPIData['QtyUnit'];
                    //  var_dump($ARY_OPTIONS);
                  while ($RowUnit = $StmtUom->fetch_array()) { ?>
                                                    <option <?php if($QtyUnit==$RowUnit['UnitCode']){ ?> selected="selected"
                                                        <?php } ?> value="<?php echo $RowUnit['UnitCode'] ?>"><?php echo $RowUnit['UnitName'] ?>
                                                    </option>
                                                    <?php  }?>
                                <select>

                        </div>

                       



                        <!-- Hello  -->
                        <div class="form-group col-lg-3">
                            <label for="exampleInputPassword1">Currency<span style="color:red">*</span></label>
                            <select required class="form-control select2" name="UnitCurrency" id="UnitCurrency"
                                disabled>
                                <option value="">Select Currency</option>

                                <?php while ($RowCurrency=$StmtCurrency->fetch_array()){ ?>
                                    <?php // var_dump($RowCurrency)  ?>
                                <option <?php if($RowPIData['UnitCurrency']==$RowCurrency['CurrencyCode']){ ?>
                                    selected="selected" <?php } ?> value="<?php echo $RowCurrency['CurrencyCode'] ?>">
                                    <?php echo $RowCurrency['CurrencyName'].' - 
					 '.$RowCurrency['CurrencyCode'] .'('.$RowCurrency['CurrenySymbol'].')'?></option>

                                <?php  }?>
                            </select>
                        </div>





                        <!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Prepared By<span style="color:red">*</span></label>
                 
                <select required class="form-control select2" name="PreparedBy" id="PreparedBy">
					  <option   value="">Select Prepared By</option>
					<?php while($RowAdminUsers=$StmtAdminUsers->fetch_array()){
					$UserName=$RowAdminUsers['UserName']; 
					$PreparedBy=$RowPIData['PreparedBy'];
					?>
			 
					<option <?php if($PreparedBy==$UserName){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $UserName ?>"><?php echo $UserName ?></option>
					<?php  }?>
				<select>
				</div> -->


                        <div class="form-group col-sm-3">
                            <label for="exampleInputEmail1">Freight</label>
                            <input type="text" class="form-control" name='Freight' id="Freight"
                                placeholder="Enter Freight" value="<?php echo $RowPIData['Freight']?>" disabled>
                        </div>

                        <div class="form-group col-lg-12">
                            <label for="exampleInputEmail1">Remarks</label>

                            <textarea name="Remarks" class="form-control" rows="2" cols="50" id="Remarks"
                                placeholder="Enter Remarks" disabled><?php echo $RowPIData['Remarks']; ?></textarea>


                        </div>

                    </div>




                </div>

                <!-- end col -->
        </div> <!-- end row -->

    </div> <!-- end card body-->
</div> <!-- end card -->














<div class="row">
    <div class="col-12">
        <div class="card">
            <!-- <input type="text" class="form-control" hidden id="displayinformation" readonly > -->
            <div class="card-body" id="ShowDataTable">


                <table id="key-datatables" data-paging="false" style="font-size: 12px;" class="table   wrap w-100  ">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th style="width : 15%">Variety</th>
                            <!-- <th>Grade</th>
                            <th>Packing Style</th> -->
                            <!-- <th>Treatment</th>
                                                    <th>Freezing Type</th> -->
                            <th style="width : 25%">Description</th>
                            <th>Code</th>
                            <th>Gross Weight<br>(<?php echo $QtyUnit ?>)</th> 
                            <th> Mcs</th>
                            <th> Unit Price<br>(<?php echo $QtyUnit ?>)</th>
                            <th> Amount in <br>(<?php echo $UnitCurrency ?>)</th>
                            <th> Actions </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                                                //echo $sql;
                                                    $TotalMcs=0;
                                                    $TotalAmount=0;
                                                    $aryPoGradeId=array();
                                                    $i =0 ;
                                                while($row=$STMT_PO_GRADE_DATA->fetch_array()){
                                                    $aryPoGradeId[]=$PoGradeId=$row['ID'];  
                                                    $PackingID=$row['PackingStyleID'];  
                                                    
                                                    // $SqlPckStyl="SELECT * FROM `AdminPackingStyles` WHERE PCKID ='$PackingID'";
                                                $SqlTotal="SELECT PackingStyle,
                                                            sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
                                                            sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs,
                                                            sum(Mcs*TotalSlabs*Price ) as PriceBags
                                                            FROM `ScrmPoGradeData` 
                                                            as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$INVOICE_ID' 
                                                            and ID='$PoGradeId'";
                                                $StmtTotal=$iconn->query($SqlTotal);
                                                $RowTotal=$StmtTotal->fetch_array();

                                                            if($QtyUnit=='BAGS'){
                                                            //$Qty=$RowTotal['MCS']*$RowTotal['TotalSlabs'];
                                                            $Price=$RowTotal['PriceBags'];
                                                            }else if( $invoice_details['QtyUnit']=='KGS'){				
                                                                $Weight = $RowTotal['NetWgtKgs'];	
                                                                $Price = $RowTotal['PriceKgs'];	
                                                            }else{
                                                                $Weight = $RowTotal['NetWgtLbs'];
                                                                $Price = $RowTotal['PriceLbs'];
                                                            }

                                                            
                                                    /*$StmtPckStyl=$iconn->query($SqlPckStyl); 
                                                    $RowPckStyl=$StmtPckStyl->fetch_array();
                                                    $Packing=$RowPckStyl['PackingStyle'];*/
                                                    $TotalMcs +=$row['Mcs'];
                                                    $Packing=$RowTotal['PackingStyle'];
                                                    $TotalAmount += $Price;
                                                    $TotalGrossweight += $row['GrossWt'];

                                                    
                                                    if($row['Description']!=''){
                                                        $row['Variety']=$row['Description'];
                                                    }

                                                    if($row['VarietyID']==0){
                                                        $masterDescription="Click To Change ";
                                                    }else{
                                                        // echo   $row['ProductDescription'];
                                                        // $masterDescription=$row['BuyerProdDescription'] ? $row['BuyerProdDescription'] :  $row['ProductDescription'];
                                                        $masterDescription= '<span class="spanBuyerProdDescription">' .$row['BuyerProdDescription']."</span>" .'<br> <span style="color: #30b400">(' .$row['DeviSUPC']." - ". $row['ProductDescription'].")</span>";
                                                    }
                                                    
                                                ?>
                        <tr data-rowid="<?php echo $PoGradeId; ?>" id="rowid<?php echo $PoGradeId; ?>">
                            <td id="PoGradeId<?php echo $PoGradeId; ?>"><?php echo $PoGradeId; ?></td>
                            <td>
                                <p class="fullname Variety" id="Variety<?php echo $PoGradeId; ?>">
                                    <?php  echo $masterDescription; ?>
                                
                                </p>
                                <input type="hidden" id="VarietyId<?php echo $PoGradeId; ?>"
                                    name="VarietyId<?php echo $PoGradeId; ?>" class="form-control hidden"
                                    value="<?php echo $row['VarietyID']; ?>" />

                            </td>
                            <!-- <td id="tdGrade<?php //echo $PoGradeId; ?>"><?php //echo $row['Grade']; ?></td>
                            <td id="tdPackingStyle<?php //echo $PoGradeId; ?>"><?php //echo $Packing; ?></td> -->
                            <!-- <td><?php// echo $row['Treatment']; ?> </td>	
                                                    <td><?php //echo $row['FreezingType']; ?> </td>	 -->
                            <td>
                                <textarea name="Description<?php echo $PoGradeId; ?>" class="form-control" rows="2"
                                    cols="50" placeholder="Enter Description(Optional)"
                                    id="Description<?php echo $PoGradeId; ?>"
                                    disabled><?php echo $row['Description'] ?></textarea><p>
                                                        <?php 
																$infoData =  $row['DeviSUPC']." - ".$row['Grade'] . " - ".  $Packing . " - " . $row['Treatment'] ." - " . $row['Brand'] ;  ?>
                                                        <!-- <span id="tdGrade<?php //echo $PoGradeId ?>"><?php //echo $row['Grade'] ?></span> - 
															<span id="tdPackingStyle<?php //echo $PoGradeId ?>"><?php //echo $Packing ?></span> - 
															<span id="tdBrand<?php //echo  $PoGradeId ?>"><?php //echo  $row['Brand'] ?></span>	-												 -->

                                                        <?php // $VarietyDescription=$DeviSUPC.'-'.$Grade.$PackingStyle.$ProductDescription .$FreezingTypee.$GlazePercc. '%' .$Glazee.$Treatmentt.$BrandName; ?>
                                                        <span
                                                            id="tdInfo<?php echo  $PoGradeId ?>"><?php echo  $infoData ?></span>
                                                    </p>
                            </td>
                            <td>
                                <input name="code" type="text" class="form-control" id="code<?php echo $PoGradeId; ?>"
                                    disabled value="<?php echo $row['BuyerItemCode'] ?>" />
                            </td>
                            <td  id="tdGrossWeight<?php  echo $PoGradeId; ?>">
                                                 <input type="text" readonly class="form-control" value="<?php  echo $row['GrossWt']; ?>" class="txtGrossWt" />
                                                </td>	
                            <td>
                                <?php // echo $row['Mcs']; ?>
                                <input required type="number" class="form-control" value="<?php echo $row['Mcs'] ?>"
                                    name="MCS" id="MCS<?php echo $PoGradeId; ?>" placeholder="Enter MASTER CARTONS"
                                    disabled>
                            </td>
                            <td>
                                <?php // echo $row['Price']; ?>
                                <input required type="number" step="0.001" class="form-control" name="Price"
                                    id="Price<?php echo $PoGradeId; ?>" value="<?php echo $row['Price'] ?>"
                                    placeholder="Enter Rate/Unit(<?php echo $UnitCurrency ?>) " disabled>
                            </td>
                            <td><?php echo number_format($Price,2); ?></td>
                            <td>
                                <!-- <a href="javascript:void(0)" class="mr-3 text-primary aEditPoBreakUp" data-toggle="tooltip" data-placement="top"
                                                            title="" data-original-title="Edit" ><i class="mdi mdi-pencil font-size-18"></i>
                                                        </a> -->
                                <!-- <a  href="CsPoGradesEdit.php?id=<?php echo $row['ID'];?>&PoID=<?php echo $INVOICE_ID;?>" 
                                                                        class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
                                                                        title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a> -->
                                <button class="btn btn-link text-danger Delete" data-id="<?php echo $row['ID'];?>"
                                    data-toggle="tooltip" data-placement="top" title="" value="" disabled
                                    data-original-title="Delete" style="padding:2px;"><i
                                        class="mdi mdi-trash-can font-size-18"></i></button>
                            </td>
                        </tr>
                        <?php  $i++;

                                            }
                                                ?>
                        <tr>
                            <td colspan="4" style="text-align: right;font-weight:600; font-size: 16px;">Total</td>
                            <td style="font-weight:600; font-size: 16px;"><?php echo $TotalGrossweight ?></td>
                            <td style="font-weight:600; font-size: 16px;"><?php echo $TotalMcs ?></td>
                            <td ></td>
                                
                                <td colspan="3" style="font-weight:600; font-size: 16px;">
                                <?php echo number_format($TotalAmount,2) ?></td>

                        </tr>
                    </tbody>

                </table>
                <input id="inputSelectedVariety" type="hidden" />

                <div class="col-xs-12 box-footer" align="right">
                    <input type="hidden" id="editmode" value="F" />
                    <?php $backurl =  substr(baseName($_SERVER['REQUEST_URI']), 0, strpos(baseName($_SERVER['REQUEST_URI']), ".php")) ?>
                    <!--  <button type="button" id="close"  name="close"  class="btn btn-danger btnClose" style="display: none;">Close </button>-->
                   <?php  if($CommTotal>0 ){ ?>
                    <a href="#" id="edit" name="edit"  class="btn btn-danger text aEditPoBreakUp">Edit </a>
                    <?php  }else{ ?>
                        <a href="PoEdit?id=<?php echo $POID . "&backurl=".$backurl; ?>" id="edit" name="edit"  class="btn btn-primary text aEditPoBreakUp">Edit </a>
                        <?php   } ?>
                   
                    <!-- <button type="submit"  name='submit' id="btnSubmit" class="btn btn-success" disabled>Save</button>-->

                </div>

            </div>

        </div>
    </div>
</div>

</form>


</div><!-- end col-->















<style>
input:disabled,
textarea:disabled {
    background: #e7e7e7 !important;
}

.fullname {
    color: #2c74e8;
}

.fullname:hover {
    text-decoration: underline;
    cursor: pointer;
}

select.error+.select2-container,
input.error {
    border: 1px solid red;
}
</style>

</html>