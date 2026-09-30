<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
//$_GET['ID']=974;
include_once ROOT.'config/api-call.php';
if(isset($_GET['ID']) && !empty($_GET['ID'])){

    $ProformaID = trim($_GET['ID']);

    $api = PostProfromaPrint;

    $bodyContent = array("ProformaID" => $ProformaID);
    $resPostProfromaPrint = CallAPI($api, "POST", "", $bodyContent);

    $result = $resPostProfromaPrint->Data;
    $resLabels = $resPostProfromaPrint->Data->Labels;
    $resItemsGroup = $resPostProfromaPrint->Data->ItemsGroup;
    $resLableKeys = $resPostProfromaPrint->Data->LableKeys;
    $resTableLabels = $resPostProfromaPrint->Data->TableLabels;
    $resGrandTotal = $resPostProfromaPrint->Data->GrandTotal;
     
}else{

}

?>

<h1 class="page-title">PROFORMA INVOICE</h1>

<table class="bt2 bl2 br2 bb2 tblMain" >
    <tr>
        <td class="p-0 tblHeader">
            <table class="w100" >
                <tr>
                    <td class="br bb w50">
                        <span class="f-14 mb-1 d-block"><?php echo $resLabels->Exporter ?>:</span>
                        <span class="bold"><?php echo $result->ExporterName ?> <br>
                        <?php echo nl2br($result->ExporterAddress) ?></span>
                    </td>
                
                    <td class="bb p-0">
                        <table class="w100">
                            <tr>
                                <td class="br bb" style="width: 70%">
                                    <?php echo $resLabels->InvoiceNoDt ?> <br>
                                    <b><?php echo $result->InvoiceNumber . ", " . $result->InvoiceDate ?></b>
                                </td>
                                <td class="bb f-12">Exporter's Ref: <br>
                                    <?php
                                    if (isset($result->IECode) && !empty($result->IECode)) {
                                        echo "I.E.CODE: " . $result->IECode;
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" class="f-14">
                                    <?php echo $resLabels->PurchseOrderNo ?>:<?php echo $result->PoNumber . ", " . $result->PoDate ?><br>
                                    <?php echo $resLabels->ReferenceNo ?> : <?php echo $result->ReferenceNumber ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td class="br bb">
                        <span class="mb-1 d-block f-12"><?php echo $resLabels->Consignee ?>:</span>
                        <span class="bold">
                            <?php echo $result->ConsigneeName ?> <br>
                            <?php echo nl2br($result->ConsigneeAddress) ?>
                        </span>
                    </td>
                    <td class="p-0 bb">
                        <table class="w100 p-0">
                            
                            <tr>
                                <td class="f-12 bb">
                                    Buyer (if other than consignee): 
                                        <?php if (isset($result->BuyerName) && !empty($result->BuyerName)) {
                                            echo $result->BuyerName . "<br>" . nl2br($result->BuyerAddress);
                                        } ?>
                                    <br><br>
                                </td>
                            </tr>
                            
                            <tr>
                                <td class="bolder bb f-13">
                                    <?php echo $resLabels->Manufacturer ?> <br> <?php echo $result->ProcessorName ?> <br>
                                    <?php echo nl2br($result->ProcessorAddress) ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="bolder f-13">
                                    EU APPROVAL No.1866
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="p-0 br"> 
                        <table class="w100">
                            <tr>
                                <td class="bb br">Pre-carriage by </td>
                                <td class="bb">Place of Receipt by pre-carrier</td>
                            </tr>
                            <tr>
                                <td class="bb br">REFRIGERATED CONTAINER</td>    
                                <td class=""><?php echo $resLabels->PortOfLoading ?></td>
                            </tr>
                            <tr>
                                <td class="bb br"><?php echo $resLabels->Vessel ?> / </td>
                                <td class="bb">ANY INDIAN PORT</td>
                            </tr>
                            <tr>
                                <td class="bb br"><?php echo $resLabels->PortOfDischrg ?>: <?php echo $result->PortOfLoading ?></td>
                                <td class="bb"><?php echo $resLabels->FinlDestintn ?>: <?php echo $result->PortOfDischarge ?></td>
                            </tr>
                            <tr>
                                <td class="br">
                                    Marks & Nos./ <br>
                                    <?php echo $resLabels->ContainerNo ?>
                                </td>
                                <td>
                                    No. & Kind of Packages<br>
                                    2016 <br>
                                    MASTER CARTONS
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="p-0 ">
                        <table class="w100">
                            <tr>
                                <td class="br bb">
                                    <?php echo $resLabels->CountryOfOrigin ?> <br> INDIA
                                </td>
                                <td class="bb">
                                    <?php echo $resLabels->FinalDestintnCountry ?> <br> <?php echo $result->CountryOfDestination ?>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <p class="bold mb-2"><?php echo $resLabels->PaymentTerms ?> </p> 
                                    <span><?php echo nl2br($result->PaymentTerms) . "<br>";
                                    echo $result->ShippingTerms . ", " . $result->FinalDestination ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <span class="f-18 bold"><?php echo $resLabels->ShipmetDt ?> : <?php echo $result->ShipByDate ?></span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="p-0 tblBody">
            <table class="w100  text-center" >
                <tr class="text-center  f-12 trBreakUpHeader">

                    <?php
                    $i = 1;
                    foreach ($resLableKeys as $key) {
                        if($i == 1) {
                            echo '<th class="br bt bb" style="width: 50%">' . $resTableLabels->$key . '</th>';
                        }else{
                            echo '<th class="br bt bb" style="">' . $resTableLabels->$key . '</th>';
                        }
                        $i++;
                    } 
                    ?>
                </tr>                
                <?php 

                    foreach($resItemsGroup as $ItemsGroup) {
                        if($ItemsGroup->ShowSubTotal == true) {
                            $rowspan = count($ItemsGroup->ItemsList) + 1; 
                        }else{
                            $rowspan = count($ItemsGroup->ItemsList) ; 
                        }
                        echo '
                        <tr>
                            <td rowspan="'.$rowspan.'" class="text-left br">
                                '.$ItemsGroup->ItemDescription.' <br>';
                                echo isset($ItemsGroup->Species) && !empty($ItemsGroup->Species) ? "SPECIES :" . $ItemsGroup->Species . "<br>" : "";
                                echo isset($ItemsGroup->PackingStyle) && !empty($ItemsGroup->PackingStyle) ? "PACKING :" . $ItemsGroup->PackingStyle . "<br>" : "";
                                echo isset($ItemsGroup->Brand) && !empty($ItemsGroup->Brand) ? "<span class='bold'>BRAND :" . $ItemsGroup->Brand . "</span>" : "";
                                echo '<br><br>
                            </td>
                        </tr>';
                        
                        $j = 1;
                        foreach($ItemsGroup->ItemsList as $RowItem) {
                            $count = count($ItemsGroup->ItemsList);
                            if($count != $j) {
                                echo '<tr>';
                                $s = 1;
                                foreach ($resLableKeys as $keyVal) {
                                    if ($s > 1) {
                                        echo '<td class="br">' . $RowItem->$keyVal . '</td>';
                                    }
                                    $s++;
                                }
                                echo '</tr>';
                            } else { // Last Record for Subtotal
                                if ($ItemsGroup->ShowSubTotal == true) {
                                    echo '<tr>';
                                    $a = 1;
                                    foreach ($resLableKeys as $keyVal) {
                                        if ($a > 1) {
                                            echo '<td class="br bt bb">' . $RowItem->$keyVal . '</td>';
                                        }
                                        $a++;
                                    }
                                    echo '</tr>';
                                }

                            }                                                        
                            $j++;
                        }
                    }
                    ?>
                </tr>
                <tr>
                    <?php
                        echo '<td class="bt br bb">'. $resGrandTotal->Lable.'</td>';
                        $x = 1;
                        foreach ($resLableKeys as $keyVal) {
                            if($x > 1) {
                                echo '<td class="br bt bb">' . $resGrandTotal->$keyVal . '</td>';
                            }
                            $x++;
                        }
                    ?>
                </tr>

            </table>    
        </td>
    </tr>
    <tr>
        <td class="p-0">
            <table class="w100 tblFooter" >
                <tr>
                    <td colspan="7" class="text-left">Amount charagble (in words ) <br> (<?php echo $result->CurrencyCode ?> : <?php echo $result->AmountInWords ?> ) </td>
                </tr>

                <?php if (isset($result->BankDetails) && !empty($result->BankDetails)) { ?>
                        <tr>
                            <td colspan="7" class="bold text-left">
                                <?php echo $resLabels->BankDetails . " " . nl2br($result->BankDetails) ?>
                            </td>
                        </tr>                        
                        <tr>
                            <td colspan="7" class="bold text-left">
                                <?php echo $resLabels->BankRoutionDetails . " " . nl2br($result->BankRoutionDetails) ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" class="bold text-left">
                                <?php echo $resLabels->BankNegotiationDetails . " " . nl2br($result->BankNegotiationDetails) ?>
                            </td>
                        </tr>
                <?php  }  ?>                

                <tr>
                    <td colspan="7" class="w100 bt p-0 text-left">
                        <table style="width: 100%">
                            <tr>
                                <td class="br" style="width: 70%">

                                    <?php echo $resLabels->Declrtn ?> : <br>
                                    <?php echo $result->Declearation ?> <br><br>
                                    <?php echo $result->Remarks ?>
                                </td>
                                <td style="width: 30%; text-align: center">
                                    <?php echo $result->SignFor ?>
                                    <br>
                                    <br>
                                    <br>
                                    <?php echo $result->SignDescription ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

        