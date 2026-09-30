<?php

header("Content-Type: application/xls");
header("Content-Disposition: attachment; filename=PendingOrderPlantWise.xls");
header("Pragma: no-cache");
header("Expires: 0");

// include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
// include($cp . "Includes/IncludePage.php");
include("../RestAPI.php");

$Type = trim($_GET["Type"]);
$Year = trim($_GET["Year"]);
$Exporter = trim($_GET["Exporter"]);
$Processor = trim($_GET["Processor"]);
$Consignee = trim($_GET["Consignee"]);
$Country = trim($_GET["Country"]);
$ShowPrice = trim($_GET["ShowPrice"]);

$api = GetPendingContracts_API;
$bodyContent = array(
    'Type' => $Type,
    'Year' => $Year,
    'Exporter' => $Exporter,
    'Processor' => $Processor,
    'Consignee' => $Consignee,
    'Country' => $Country
);

$resGetPendingContractsAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetPendingContractsAPI->Data->Data;

echo count($resList);

?>

<table border="1" >
    <thead>
        <tr>
            <th style="min-width:40px;">S No.</th>
            <th style="min-width:250px;">Buyer</th>
            <th style="min-width:120px;">Destination</th>
            <th style="min-width:120px;">Shipment</th>
            <th style="min-width:180px;">P.O.NO.</th>
            <th style="min-width:120px;">Proc./Exp</th>

            <th style="min-width: 160px;">Brand</th>           
            <th style="min-width: 110px;">Variety</th>
            <th style="min-width: 110px;">Form</th>
            <th style="min-width: 110px;">Packing</th>
            <th style="min-width: 80px;">Glaze</th>
            <th style="min-width: 160px;">Certification</th>
            <th style="min-width: 80px;">'6/8'</th>
            <th style="min-width: 80px;">'8/12'</th>
            <th style="min-width: 80px;">13/15</th>
            <th style="min-width: 80px;">16/20</th>
            <th style="min-width: 80px;">21/25</th>
            <th style="min-width: 80px;">25/30</th>
            <th style="min-width: 80px;">31/35</th>
            <th style="min-width: 80px;">31/40</th>
            <th style="min-width: 80px;">41/50</th>
            <th style="min-width: 80px;">51/60</th>
            <th style="min-width: 80px;">61/70</th>
            <th style="min-width: 80px;">71/90</th>
            <th style="min-width: 80px;">91/110</th>
            <th style="min-width: 80px;">100/150</th>
            <th style="min-width: 80px;">150/200</th>
            <th style="min-width: 80px;">200/250</th>

            <th style="min-width:80px;">Total</th>
            <th style="min-width:80px;">Payment</th>
            <!-- <th style="min-width:90px;">Lab Sample</th>
            <th style="min-width:150px;">Buy Sample PSIP</th> -->
        </tr>
    </thead>

    <tbody>

    
    <?php
    // $i = 1; 
    foreach ($resList as $item) {
        ?>        
            <tr>
                <td style="vertical-align: middle"><?php echo $item->Sno ?></td>
                <td style="vertical-align: middle"><?php echo $item->ConsigneeName ?></td>
                <td style="vertical-align: middle"><?php echo $item->PortOfDischarge ?></td>
                <td style="vertical-align: middle"><?php echo $item->ShipByDate ?></td>
                <td style="vertical-align: middle"><?php echo $item->PoNO ?></td>
                <td style="vertical-align: middle"><?php echo $item->ExpProc ?></td>

                <td colspan="23">
                    <table border="1" >
                        <?php foreach ($item->BreakUpData as $itemBreak) { ?>
                            <tr>
                                <td style="min-width: 160px;"><?php echo $itemBreak->Brand ?>&nbsp;</td>                                
                                <td style="min-width: 110px;"><?php echo $itemBreak->Variety ?>&nbsp;</td>
                                <td style="min-width: 110px;"><?php echo $itemBreak->Form ?>&nbsp;</td>
                                <td style="min-width: 110px;"><?php echo $itemBreak->Packing ?>&nbsp;</td>
                                <td style="min-width: 80px;"><?php echo $itemBreak->Glaze ?>&nbsp;</td>
                                <td style="min-width: 160px;"><?php echo $item->Cerification ?>&nbsp;</td>
                                <td style="min-width: 80px;"><?php
                                $G1 = explode("&", $itemBreak->G1);
                                echo $G1[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G1[1]) ? '(' . $G1[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G2 = explode("&", $itemBreak->G2);
                                echo $G2[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G2[1]) ? '(' . $G2[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G3 = explode("&", $itemBreak->G3);
                                echo $G3[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G3[1]) ? '(' . $G3[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G4 = explode("&", $itemBreak->G4);
                                echo $G4[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G4[1]) ? '(' . $G4[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G5 = explode("&", $itemBreak->G5);
                                echo $G5[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G5[1]) ? '(' . $G5[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G6 = explode("&", $itemBreak->G6);
                                echo $G6[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G6[1]) ? '(' . $G6[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G7 = explode("&", $itemBreak->G7);
                                echo $G7[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G7[1]) ? '(' . $G7[1] . ')' : "";  
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G8 = explode("&", $itemBreak->G8);
                                echo $G8[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G8[1]) ? '(' . $G8[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G9 = explode("&", $itemBreak->G9);
                                echo $G9[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G9[1]) ? '(' . $G9[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G10 = explode("&", $itemBreak->G10);
                                echo $G10[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G10[1]) ? '(' . $G10[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G11 = explode("&", $itemBreak->G11);
                                echo $G11[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G11[1]) ? '(' . $G11[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G12 = explode("&", $itemBreak->G12);
                                echo $G12[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G12[1]) ? '(' . $G12[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G13 = explode("&", $itemBreak->G13);
                                echo $G13[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G13[1]) ? '(' . $G13[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G14 = explode("&", $itemBreak->G14);
                                echo $G14[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G14[1]) ? '(' . $G14[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G15 = explode("&", $itemBreak->G15);
                                echo $G15[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G15[1]) ? '(' . $G15[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php
                                $G16 = explode("&", $itemBreak->G16);
                                echo $G16[0];
                                if($ShowPrice == "T") {
                                    echo !empty($G16[1]) ? '(' . $G16[1] . ')' : "";
                                }
                                ?></td>
                                <td style="min-width: 80px;"><?php echo $itemBreak->Total ?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
                <td style="vertical-align: middle"><?php echo $item->Payment ?></td>        
                <!-- <td><?php // echo $item->LabSample  ?></td>
            <td><?php // echo $item->BuyerSample  ?></td> -->
            </tr>
        <?php
        // 	$i++ ;
    }
    ?>

    </tbody>
</table>
