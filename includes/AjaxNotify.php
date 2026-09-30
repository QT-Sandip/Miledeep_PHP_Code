<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

include_once ROOT . 'config/api-call.php';



$api = API_NOTIFICATIONS;



$res = CallAPI($api, "GET", "", "");



// echo json_encode($res, JSON_PRETTY_PRINT);

// exit;

$error = $res->Error;



if (!isset($error) || empty($error)) {

    echo '

    <style>.notifyCount{

        position: absolute;

        top: -5px;

        display: block;

        right: -25px;

        color: #03207c;

        border: 1px solid #03207c;

        font-weight: bold;

        padding: 0px 5px;

        border-radius: 15px;

        font-size: 11px;

        line-height: 17px;

    }</style>';



    echo '

    <div class="acc-item">

    <button class="acc-header"><i class="fas fa-plus"></i><span style="position:relative">Shipments <span class="notifyCount">' . $res->ShipmentsData->ShipmentCount . '</span></span></button>';

    if ($res->ShipmentsData->ShipmentCount > 0) {

        echo '

            <div class="acc-content">

                <div class="table-responsive">

                    <table class="table mb-0">

                        <thead>

                            <tr>

                                <th>S No</th>

                                <th>InvoiceNo</th>

                                <th>Consignee Name</th>

                                <th>PoNo</th>

                                <th>Product</th>

                                <th>StockOut Date</th>

                            </tr>

                        </thead>

                        <tbody>';

        foreach ($res->ShipmentsData->ShipmentDetails as $item) {

            echo '

                            <tr>

                                <th scope="row">' . $item->SNo . '</th>

                                <td>' . $item->InvoiceNo . '</td>

                                <td>' . $item->ConsigneeName . '</td>

                                <td>' . $item->PoNo . '</td>

                                <td>' . $item->Product . '</td>

                                <td>' . $item->StockOutDate . '</td>

                            </tr>';

        }

        echo '

                        </tbody>

                    </table>

                </div>

            </div>';

    }

    echo '

        </div>';





    echo '

        <div class="acc-item">

        <button class="acc-header"><i class="fas fa-plus"></i> <span style="position:relative">Pending Contracts<span class="notifyCount">' . $res->PendingContractsData->PendingContractsCount . '</span></span></button>';

    if ($res->PendingContractsData->PendingContractsCount > 0) {

        echo '

            <div class="acc-content">

                <div class="table-responsive">

                    <table class="table mb-0">

                        <thead>

                            <tr>

                                <th>S No</th>

                                <th>InvoiceNo</th>

                                <th>Consignee Name</th>

                                <th>PoNo</th>

                                <th>Product</th>

                                <th>ShipBy Date</th>

                            </tr>

                        </thead>

                        <tbody>';

        foreach ($res->PendingContractsData->PendingContractsDetails as $item) {

            echo '

                            <tr>

                                <th scope="row">' . $item->SNo . '</th>

                                <td>' . $item->InvoiceNo . '</td>

                                <td>' . $item->ConsigneeName . '</td>

                                <td>' . $item->PoNo . '</td>

                                <td>' . $item->Product . '</td>

                                <td>' . $item->ShipByDate . '</td>

                            </tr>';

        }

        echo '

                        </tbody>

                    </table>

                </div>

            </div>';

    }

    echo '

        </div>';



    echo '

        <div class="acc-item">

        <button class="acc-header"><i class="fas fa-plus"></i> <span style="position:relative">Revised POs<span class="notifyCount">' . $res->revisedOrdersData->revisedOrdersCount . '</span></span></button>';

    if ($res->revisedOrdersData->revisedOrdersCount > 0) {

        echo '

            <div class="acc-content">

                <div class="table-responsive">

                    <table class="table mb-0">

                        <thead>

                            <tr>
                                <th style="width: 50px;vertical-align: middle;text-align: center;">S No</th>
                                <th style="width: 150px;vertical-align: middle;text-align: center;">PoNo</th>
                                <th style="width: 150px;vertical-align: middle;text-align: center;">Consignee Name</th>
                                <th style="width: 200px;text-align: center;">Revised</th>
                                <th style="width: 150px;text-align: center;">Modified Date</th>

                            </tr>

                        </thead>

                        <tbody>';

        foreach ($res->revisedOrdersData->revisedOrdersdetails as $item) {

            echo '

                             <tr>
                                <th scope="row" style="width: 50px; vertical-align: middle;text-align: center;">' . $item->SNo . '</th>                                
                                <td style="width: 150px; vertical-align: middle;text-align: center;"  >' . $item->PoNo . '</td>
                                <td style="width: 150px; vertical-align: middle;text-align: center;"  >' . $item->ConsigneeName . '</td>                                
                                <!--td style="width: 200px; vertical-align: middle;text-align: center;"  >' . $item->product . '</td-->
                                <td style="padding: 0px !important;" colspan="2">
                                    <table class="subTable" style="width: 100%;border-collapse: collapse;">

                                        <tbody>';

            foreach ($item->BreakUp as $itemBreak) {
                $cus_current_value = htmlspecialchars($itemBreak->currentValue);
                if(htmlspecialchars($itemBreak->fieldName) == "VarietyID"){
                    $pos = strpos($itemBreak->Product, '-');
                    $cus_product = ($pos !== false) ? substr($itemBreak->Product, $pos + 1) : $itemBreak->Product;                    
                    $cus_current_value = $cus_product;
                    $cus_current_value = $itemBreak->Product;
                }
                if(htmlspecialchars($itemBreak->fieldName) == "PortOfDischarge" || htmlspecialchars($itemBreak->fieldName) == "FinalDestination"){
                    $cus_current_value = $itemBreak->Countryname;
                }
                echo '<tr>                                                                      
                                                    
                        <td style="width:200px;padding: 5px; border-top: 1px solid #dee2e6;text-align: left;">'.$itemBreak->fieldName.' : ' .$cus_current_value. '&nbsp;</td>       
                        <td style="width: 150px; padding: 5px; border-top: 1px solid #dee2e6;text-align: center;">' . htmlspecialchars($itemBreak->modifyDate) . '&nbsp;</td>
                    </tr>';
}
            echo '

                                        </tbody>

                                    </table>

                                </td>           

                            </tr>';

        }

        echo '

                        </tbody>

                    </table>

                </div>

            </div>';

    }

    echo '

        </div>';



    echo '

        <div class="acc-item">

        <button class="acc-header"><i class="fas fa-plus"></i><span style="position:relative"> Collections/Negotiations<span class="notifyCount">' . $res->NegotiationData->NegotiationCount . '</span></span></button>';



    if ($res->NegotiationData->NegotiationCount > 0) {

        echo '

            <div class="acc-content">

                <div class="table-responsive">

                    <table class="table mb-0">

                        <thead>

                            <tr>

                                <th>S No</th>

                                <th>InvoiceNo</th>

                                <th>Payment Type</th>

                                <th>Consignee Name</th>

                                <th>PoNo</th>

                                <th>Nego/Coll Date</th>

                            </tr>

                        </thead>

                        <tbody>';

        foreach ($res->NegotiationData->NegotiationDetails as $item) {

            echo '

                            <tr>

                                <th scope="row">' . $item->SNo . '</th>

                                <td>' . $item->InvoiceNo . '</td>

                                <td>' . $item->PaymentType . '</td>

                                <td>' . $item->ConsigneeName . '</td>

                                <td>' . $item->PoNo . '</td>

                                <td>' . $item->CollectionorNegotiationDate . '</td>

                            </tr>';

        }

        echo '

                        </tbody>

                    </table>

                </div>

            </div>';

    }

    echo '

        </div>';





    echo '

        <div class="acc-item">

        <button class="acc-header"><i class="fas fa-plus"></i><span style="position:relative"> Payments Received<span class="notifyCount">' . $res->PaymentsReceivedData->PaymentReceivedCount . '</span></span></button>';

    if ($res->PaymentsReceivedData->PaymentReceivedCount > 0) {

        echo '

        <div class="acc-content">

            <div class="table-responsive">

                <table class="table mb-0">

                    <thead>

                        <tr>

                            <th>S No</th>

                            <th>InvoiceNo</th>

                            <th>Payment Type</th>

                            <th>Consignee Name</th>

                            <th>PoNo</th>

                            <th>Nego/Coll Date</th>

                        </tr>

                    </thead>

                    <tbody>';

        foreach ($res->PaymentsReceivedData->PaymentReceivedDetails as $item) {

            echo '

                        <tr>

                            <th scope="row">' . $item->SNo . '</th>

                            <td>' . $item->InvoiceNo . '</td>

                            <td>' . $item->PaymentType . '</td>

                            <td>' . $item->ConsigneeName . '</td>

                            <td>' . $item->PoNo . '</td>

                            <td>' . $item->CollectionorNegotiationDate . '</td>

                        </tr>';

        }

        echo '

                    </tbody>

                </table>

            </div>

        </div>';

    }

    echo '

    </div>';

}



?>