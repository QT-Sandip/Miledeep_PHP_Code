<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);


if (isset($methodType) && $methodType === "get") {
    $Status = isset($_POST["txtStatus"]) ? trim($_POST["txtStatus"]) : "0";

   
    $api = GET_USER_LIST;

    $bodyContent = array(
    'ClientID' => $_SESSION["ClientID"],
    'ReqUserID' => '', 
    'UserID' => '',
    'AccessRoleID' => '',
    'Status' => $Status
);
   // print_r($bodyContent);die;

    $resUsersList = CallAPI($api, "GET", "", $bodyContent);
     //echo print_r($resUsersList);
      //      exit;

    if (!empty($resUsersList->Data)) {
        $SNo = 1;
        foreach ($resUsersList->Data as $items) {

            // echo print_r($resUsersList);
            // exit;
            ?>
            <tr>
                <td><?php echo $SNo; ?></td>
                <td><?php echo $items->UserID; ?></td>
                <td><?php echo $items->DepartmentName; ?></td>
                <td><?php echo $items->UserLoginID; ?></td>
                <td><?php echo $items->UserName; ?></td>
                <td><?php echo $items->DesignationID; ?></td>
                <td><?php echo $items->AccessIPs; ?></td>
                <td><?php
                    if ($items->AccessType == "1") {
                        echo "Super Admin";
                    } else if ($items->AccessType == "2") {
                        echo "Admin";
                    } else {
                        echo "User";
                    }
                    ?></td>
                <td><?php echo $items->RoleName; ?></td>
                <td><?php echo $items->LastLogin; ?></td>
                <td>
                    <span style="color:
                        <?php
                        if ($items->Status == "0") {
                            echo '#dc3545';
                        } else if ($items->Status == "1") {
                            echo '#28a745';
                        } else {
                            echo '#6c757d';
                        }
                        ?>;">
                        <?php
                        if ($items->Status == "0") {
                            echo "Inactive";
                        } else if ($items->Status == "1") {
                            echo "Active";
                        } else {
                            echo "--";
                        }
                        ?>
                    </span>
                </td>
                  <td>
                                <a target="" href="edit-user.php?id=<?php echo $items->UserID; ?>"
                                    class="mr-3 text-primary Edit" data-toggle="tooltip"
                                    data-placement="top" title="" data-original-title="Edit"><i
                                        class="mdi mdi-pencil font-size-18"></i>
                                </a>
                                                        </td>
            </tr>
            <?php
            $SNo++;
        }
    } 
} 
?>
