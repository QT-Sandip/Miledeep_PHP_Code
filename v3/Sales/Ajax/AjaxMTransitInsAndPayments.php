<?php



include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';



$api = API_M_TRANSIT_INS_AND_PAYMENTS;



$Year = trim($_POST["Year"]);

$ExporterID = trim($_POST["ExporterID"]);

$CountryID = trim($_POST["CountryID"]);

$ConsigneeID = trim($_POST["ConsigneeID"]);

$ClientConfigData = (object)$_SESSION['ClientConfigData'];

$Hide10PercentColumn = !empty($ClientConfigData->Hide10PercentChargeInInsuranceScreen)?$ClientConfigData->Hide10PercentChargeInInsuranceScreen:'';//DFLVE





$bodyContent = array(

                'Year' => $Year,

                'ExporterID' => $ExporterID,

                'CountryID' => $CountryID,

                'ConsigneeID' => $ConsigneeID,

                 'Hide10PercentColumn' => $Hide10PercentColumn

            );



$resList = CallAPI($api, "POST", "", $bodyContent);

//echo '<pre>';print_r($resList);die;

?>



<?php 

if (isset($resList) && !empty($resList)) {

    

    $error = $resList->Error;

    

    if (!isset($error) || empty($error)) {



        $List = $resList->Data;

        $ListTotals = $resList->Totals;

?>

<div class="card">

    <div class="card-body">



        <table id="tblDataTable" class="table table-hover table-bordered">

            <thead class="thead-light">

                <tr>

                    <th style="min-width: 60px;" class="sticky etaStatusCol1">S.No</th>

                    <th style="min-width: 110px;" class="sticky etaStatusCol2">Cover Given</th>

                    <th style="min-width: 120px;" class="sticky etaStatusCol3">Invoice No</th>

                    <th style="min-width: 130px;">Shipped On Vessel</th>

                    <th style="min-width: 120px;">Container Number</th>

                    <th style="min-width: 120px;">Port Of Discharge</th>

                    <th style="min-width: 110px;">Invoice Value</th>

                    <?php 

			            if(empty($Hide10PercentColumn) || $Hide10PercentColumn == '0000-00-00'){?>

                            <th style="min-width: 130px;">Invoice Value+10%</th>

                    <?php }?>

                    <th style="min-width: 80px;">% Age</th>

                    <th style="min-width: 110px;">Premiums</th>

                    <th style="min-width: 110px;">Ex Rate</th>

                    <th style="min-width: 110px;">Total Amount RS</th>

                    <th style="min-width: 140px;">Our Reference No</th>

                    <th style="min-width: 110px;">Payment Date</th>

                </tr>

            </thead>

            <tbody>

                <?php foreach ($List as $item) { ?>

                <tr>

                    <td class="sticky etaStatusCol1"><?php echo $item->SNo ?></td>

                    <td class="sticky etaStatusCol2"><?php echo $item->CoverGiven ?></td>

                    <td class="sticky etaStatusCol3 bodyInvNo" ><?php echo $item->InvoiceNo ?></td>

                    <td><?php echo $item->ShippedonVessel ?></td>

                    <td><?php echo $item->ContainerNo ?></td>

                    <td><?php echo $item->PortofDischarge ?></td>

                    <td class="bodyInvVal"><?php 
                        if(!empty($Hide10PercentColumn) && $Hide10PercentColumn != '0000-00-00'){
                            echo ( $item->CInvDate >= $Hide10PercentColumn ) ? $item->InvoiceValue : $item->InvoiceValuewithPercentage;
                        }else{
                            echo $item->InvoiceValue;
                        }
                    
                     ?></td>

                    <?php 

			            if(empty($Hide10PercentColumn) || $Hide10PercentColumn == '0000-00-00'){?>

                            <td class="bodyInvPerc"><?php echo $item->InvoiceValuewithPercentage ?></td>

                    <?php }?>

                    <td><?php echo $item->Percentage ?></td>

                    <td class="bodyPremiums"><?php echo $item->Premiums ?></td>

                    <td><?php echo $item->ExchangeRate ?></td>

                    <td><?php echo $item->AmountRs?></td>

                    <td><?php echo $item->OurReferenceNo ?></td>

                    <td><?php echo $item->PaymentDate ?></td>

                </tr>

                <?php } ?>

            </tbody>

            <tfoot>

                <tr>

                    <th><b style="font-weight: bold;font-size: 15px;">Total</b></th>

                    <th>&nbsp;</th>

                    <th><b style="font-weight: bold;font-size: 15px;" class="footInvNo"><?php  echo $ListTotals->TotalInvoices ?></b></th>

                    <th>&nbsp;</th>

                    <th>&nbsp;</th>

                    <th>&nbsp;</th>

                    <th><b style="font-weight: bold;font-size: 15px;" class="footInvVal"><?php  echo $ListTotals->TotalInvoiceValue ?></b></th>

                    <?php 

			        if(empty($Hide10PercentColumn) || $Hide10PercentColumn == '0000-00-00'){?>

                        <th><b style="font-weight: bold;font-size: 15px;" class="footInvPerc"><?php  echo $ListTotals->TotalInvoiceValuewithPercentage ?></b></th>

                    <?php }?>

                    <th>&nbsp;</th>

                    <th><b style="font-weight: bold;font-size: 15px;" class="footPremiums"><?php  echo $ListTotals->TotalPremiums ?></b></th>

                    <th>&nbsp;</th>

                    <th>&nbsp;</th>

                    <th>&nbsp;</th>

                    <th>&nbsp;</th>

                    <!-- <th><b style="font-size: 15px;">Total</b></th>	

                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalInvoices ?></b></th>		        

                <th>&nbsp;</th>

                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalNetWeightLbs ?></b></th>

                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalNetWeightKgs ?></b></th>

                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalFOBValueUSD ?></b></th>

                <th><b style="font-size: 15px;"><?php // echo $ListTotal->TotalFobValueINR ?></b></th>

                <th>&nbsp;</th>

                <th>&nbsp;</th>

                <th>&nbsp;</th> -->

                </tr>

            </tfoot>

        </table>



    </div>

</div>



<?php 

    } else {

        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";

    }

} 

?>