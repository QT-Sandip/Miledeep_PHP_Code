<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

// $api = API_RM_BOOKING_LIST;

// $PsId = trim($_POST["PsId"]);
// $BookingDate = trim($_POST["BookingDate"]);
// $ArrivalPlant = trim($_POST["ArrivalPlant"]);

// $bodyContent = array(
//     'BookingDate' => $BookingDate,
//     'PsId' => $PsId,
//     'ArrivalPlant' => $ArrivalPlant,
//     'Type' => "",
// );

// $resList = CallAPI($api, "GET", "", $bodyContent);

// $error = $resList->Error;
// $List = $resList->Data;
?>


<?php // if (!isset($error) || empty($error)) { ?>
                                            
    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>	
                <th>Arrival Date</th>	
                <th>Lot Number </th>
                <th>Arrival</th>	
                <th>Deheading</th>	
                <th>Grading</th>
                <th>VA</th>
                <th>Soaking</th>
                <th>Packed Qty</th>
                <th>In Store</th>	
            </tr>
        </thead>
        <tbody>
            <?php // foreach ($List as $item) { ?>
                <tr>
                    <td>1</td>	
                    <td>15-11-2023</td>	
                    <td>123456 </td>
                    <td>Arrival</td>	
                    <td>Deheading</td>	
                    <td>Grading</td>
                    <td>VA</td>
                    <td>Soaking</td>
                    <td>125</td>
                    <td>In Store</td>
                </tr>
            <?php // } ?>
        </tbody>
    </table>

<?php // } ?>