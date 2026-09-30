<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $api = API_RM_ARRIVAL_LIST;

// $PsId = trim($_POST["PsId"]);
// $ArrivalDate = trim($_POST["ArrivalDate"]);

// $bodyContent = array(
//     'ArrivalDate' => $ArrivalDate,
//     'PsId' => $PsId
// );

// $resList = CallAPI($api, "GET", "", $bodyContent);

// $error = $resList->Error;
// $List = $resList->Data;
?>


<?php // if (!isset($error) || empty($error)) { ?>
                                            
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 80px;">S.No</th>	
                    <th>VoucherNo</th>	
                    <th>PaymentLots </th>
                    <th>PaymentAmount</th>	
                    <th>Balance</th>
                    <th style="min-width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php // foreach ($List as $item) { ?>
                    <tr>
                        <td>1</td>	
                        <td>C123</td>	
                        <td>m001 <br> M002 <br> M003 </td>
                        <td>50000 <br> 20000 <br> 15000 </td>	
                        <td>0 <br> 0 <br> 0 </td>	
                        <td>
                            <?php // if ($resList->EditAccess) { ?>
                                <a target="_blank" href="#" class="mr-3 text-primary mx-2" data-placement="top" title="Edit" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                                <a target="_blank" href="#" class="mr-3 text-primary mx-2" data-placement="top" title="Eye" data-original-title="Edit">
                                    <i class="mdi mdi-printer font-size-16"></i>
                                </a>
                            <?php // } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>	
                        <td>C123</td>	
                        <td>m001 <br> M002 <br> M003 </td>
                        <td>50000 <br> 20000 <br> 15000 </td>	
                        <td>0 <br> 0 <br> 0 </td>	
                        <td>
                            <?php // if ($resList->EditAccess) { ?>
                                <a target="_blank" href="#" class="mr-3 text-primary mx-2" data-placement="top" title="Edit" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                                <a target="_blank" href="#" class="mr-3 text-primary mx-2" data-placement="top" title="Eye" data-original-title="Edit">
                                    <i class="mdi mdi-printer font-size-16"></i>
                                </a>
                            <?php // } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>	
                        <td>C123</td>	
                        <td>m001 <br> M002 <br> M003 </td>
                        <td>50000 <br> 20000 <br> 15000 </td>	
                        <td>0 <br> 0 <br> 0 </td>	
                        <td>
                            <?php // if ($resList->EditAccess) { ?>
                                <a target="_blank" href="#" class="mr-3 text-primary mx-2" data-placement="top" title="Edit" data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                                <a target="_blank" href="#" class="mr-3 text-primary mx-2" data-placement="top" title="Eye" data-original-title="Edit">
                                    <i class="mdi mdi-printer font-size-16"></i>
                                </a>
                            <?php // } ?>
                        </td>
                    </tr>
                <?php // } ?>
            </tbody>
        </table>

<?php // } ?>