<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

    $api = API_MATERAIL_DISPATCH_REPORT;
        $fromStation = trim($_POST["fromStation"]);
        $toStation = trim($_POST["toStation"]);        
        $fromdate = trim($_POST["fromdate"]);
        $todate = trim($_POST["todate"]);

        $bodyContentPost = array(
            "FromStation" => $fromStation,
            "ToStation" => $toStation,
            "ToDate" => $todate,
            "FromDate" => $fromdate            
        );

        $result = CallAPI($api, "GET", "", $bodyContentPost);  
            $List = $result->Data->DispatchMaterialList;      
    
?>
        <div class="">
            <div class="">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>S.No</th>
                            <th>Batch No</th>
                            <th>From station</th>
                            <th>To station</th>
                            <th>Transferred Date</th>
                            <th>Grade</th>
                            <th>Variety</th>
                            <th>Quantity</th>
                            <th>Dispatched Crates</th>                            
                          
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($List as $item) { ?>
                            <tr>
                            <td><?php echo $item ->SNo?></td>
                                <td><?php echo $item ->BatchNumber ?></td>
                                <td><?php echo $item ->FromStationName ?></td>
                                <td><?php echo $item ->ToStationName ?></td>
                                <td><?php echo $item ->FormattedTransferDate ?></td>
                                <td><?php echo $item ->Grade?></td>
                                <td><?php echo $item ->VarietyName ?></td>
                                <td><?php echo $item ->Quantity ?></td>
                                <td><?php echo $item ->DispatchedCrates ?></td>
                                                             
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
