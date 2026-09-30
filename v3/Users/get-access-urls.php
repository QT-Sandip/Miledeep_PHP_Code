<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/config/api-call.php");
$PageTitle="Give access";
$PageTitleFolder="Users";

if( isset($_POST['action']) && $_POST['action'] == 'getUsers' ){
    $SqlgetUserroles = " SELECT * FROM `AdminLoginDetails` where Status=1 and  ClientID = '".$_POST['ClientID']."' ";
    $StmtUserRolesList = $iconn->query($SqlgetUserroles);
    $output = "";
    while($RowUserRolesList=$StmtUserRolesList->fetch_array()){
        $output .= '<option value="'.$RowUserRolesList['UserID'].'">'.$RowUserRolesList['UserLoginID'].'</optoin>';
    } 
    echo $output;
    exit;
}

$SqlClientsList="SELECT * FROM `AdminRegisteredClients` WHERE ExpiryDate > Now() ";  
$StmtClientsList=$iconn->query($SqlClientsList);

$accessgiven = false;
$getclientname ="";
$getdetpid = "";
$getaccetype ="";
//$getsubdeptid ="";
if( isset($_GET['ClientID']) && $_GET['ClientID'] && isset($_GET['UserID']) && $_GET['UserID'] !="" ){

    $selcteduserdetails = " SELECT * FROM `AdminLoginDetails` where Status=1 and  UserID = '".$_GET['UserID']."' ";
    $selectsmt =  $iconn->query($selcteduserdetails);
    $selectrow = $selectsmt->fetch_array();
    $selectedUserName = $selectrow['UserLoginID'];

    $accessgiven = true;    
    $getclientname =$_GET['ClientID'];
    $getuserid = $_GET['UserID'];

    $sqlGetClient = "SELECT * FROM `AdminClientDbDetails` WHERE ClientID = ? and DbStatus=1 LIMIT 1";
    $stmt = $iconn->prepare($sqlGetClient);
    $stmt->bind_param("s", $_GET['ClientID']);
    $stmt->execute();
    $DbDetails = $stmt->get_result()->fetch_array();
    //echo "<pre>"; print_r($DbDetails); echo "</pre>"; 
    $host = $DbDetails['DbHost'];
    $database_name = $DbDetails['DbName'];
    $username = $DbDetails['DbUserName'];
    $password = $DbDetails['DbPassword'];

    $options = [
                //PDO::ATTR_PERSISTENT => true, // This enables persistent connection
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ];
    //echo "mysql:host=" . $host . ";dbname=" . $database_name."unme-". $username." pwd-". $password ."opm;". $options;        
            try {
    $clientApi = new PDO("mysql:host=" . $host . ";dbname=" . $database_name, $username, $password, $options);
    } catch (\PDOException $e) {
        error_log($e->getMessage());
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
    $clientApi->exec("set names utf8");

    $getAdminSideBarOptionsQry = "Select * from AdminSideBarOptions where UserID = '".$_GET['UserID']."'
    ";
  //echo $getAdminSideBarOptionsQry; exit;
    $smtAdminSideBarOptionsQry = $clientApi->query($getAdminSideBarOptionsQry);  
    $acceurls = array();
    
    while( $rowAdminSideBarOptions = $smtAdminSideBarOptionsQry->fetch(PDO::FETCH_ASSOC) ){
        $acceurls[] = $rowAdminSideBarOptions['PathUrl'];
    }   

    //  echo "<pre>"; print_r($acceurls); echo "</pre>"; exit;

    //$baseFolder = realpath(__DIR__ . '\..\..\v3');
    $baseFolder = $_SERVER['DOCUMENT_ROOT'].'/v3'; 
    $excludeFolders = [
        'Ajax', 
        'ajax',
        'Api',
        'ApiV1',
        'assets',
        'config',
        'styles', 
        'images', 
        'SalesInculdes', 
        'Maneeja',
        'DevApi', 
        'Arun', 
        'Bkp', 
        'TempFolder', 
        'Uploads',    
        'RestAPI',
        'Images',
        'Scripts',
        'BaseDb',
        'StoreCss',
        'uiwrapper',
    ];

    function renderFileTree($folder, $relativePath = '', $excludeFolders = [])
    {
        global $acceurls;
        $fullPath = rtrim($folder, '/') . '/' . ltrim($relativePath, '/');

        if (!is_dir($fullPath)) return '';

        $entries = scandir($fullPath);
        $folders = [];
        $files = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;

            $relativeItemPath = ltrim($relativePath . '/' . $entry, '/');
            $absoluteItemPath = $folder . '/' . $relativeItemPath;

            // Skip excluded folders
            $parts = explode('/', $relativeItemPath);
            foreach ($parts as $part) {
                if (in_array($part, $excludeFolders)) continue 2;
            }

            if (is_dir($absoluteItemPath)) {
                $folders[] = [
                    'name' => $entry,
                    'relative' => $relativeItemPath
                ];
            } else {
                if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === 'php') {
                    $files[] = [
                        'name' => $entry,
                        'relative' => $relativeItemPath,
                        'absolute' => $absoluteItemPath
                    ];
                }
            }
        }

        $html = "<ul class='file-tree'>";

        // Render folders first (no flex)
        foreach ($folders as $folderEntry) {
            $html .= "<li>
                <details>
                    <summary><strong>" . htmlspecialchars($folderEntry['name']) . "</strong></summary>";
            // Recursive call here — note: NOT inside file-list
            $html .= renderFileTree($folder, $folderEntry['relative'], $excludeFolders);
            $html .= "</details>
            </li>";
        }

        // Then render only files inside flex container
        if (!empty($files)) {            
                $folderId = md5($relativePath); 
                $html .= "<li>
        <div class='file-list-wrapper' data-folder-id='$folderId'>
            <label><input type='checkbox' class='select-all' data-folder-id='$folderId'> <strong>Select All</strong></label>
            <div class='file-list'>";
            $html .= "<li><div class='file-list'>";
            foreach ($files as $fileEntry) {
                $isChecked = false;

                foreach ($acceurls as $url) {
                    // Changed to strpos to prevent false positives like matching "confirm.php" with "Hrm"
                    if (strpos($fileEntry['absolute'], $url) !== false) {
                        $isChecked = true;
                        break; // Stop looping through URLs for this file once matched
                    }
                }

                // Cleaned up the HTML concatenation for readability
                $checkedAttribute = $isChecked ? 'checked' : '';
                $relativeStr      = htmlspecialchars($fileEntry['relative'], ENT_QUOTES, 'UTF-8');
                $folderIdStr      = htmlspecialchars($folderId, ENT_QUOTES, 'UTF-8');
                $nameStr          = htmlspecialchars($fileEntry['name'], ENT_QUOTES, 'UTF-8');

                $html .= "
                    <label>
                        <input type='checkbox'
                               name='files[]'
                               value='{$relativeStr}'
                               {$checkedAttribute}
                               data-folder-id='{$folderIdStr}'
                               class='file-checkbox'>
                        {$nameStr}
                    </label>";
            }
            $html .= "</div></li>";
        }

        $html .= "</ul>";
        return $html;
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $selectedFiles = isset($_POST['files']) ? $_POST['files'] : [];

            $cleanedFiles = [];

            foreach ($selectedFiles as $file) {
                $cur_path = parse_url($file, PHP_URL_PATH);
                $cleanedFiles[] = basename($cur_path);  // extract only filename
            }

            $selectedFiles = $cleanedFiles; // final result

        $toDelete = array_diff($acceurls, $selectedFiles);
         foreach ($toDelete as $file) {            
            $stmt = $clientApi->prepare("
                DELETE FROM AdminSideBarOptions 
                WHERE PathUrl=? AND UserID=?
            ");

            $stmt->execute([
                $file, 
                $_GET["UserID"]
            ]);
        }
        if (!empty($selectedFiles)) {
            //echo "<h3>Selected Files:</h3><ul>";
            foreach ($selectedFiles as $file) {
                //echo "<li>" . htmlspecialchars($file) . "</li>";
                $cur_path = parse_url($file, PHP_URL_PATH); 
                $file = basename($cur_path);
                if( !in_array(htmlspecialchars($file),$acceurls) ){

                    $sqlInsert = "INSERT INTO AdminSideBarOptions (PathUrl, UserID)
                                  VALUES (:pathUrl, :userId)";
                    $stmt = $clientApi->prepare($sqlInsert);
                    $stmt->execute([
                        ':pathUrl' => htmlspecialchars($file),
                        ':userId' => $_GET["UserID"]
                    ]);

                }
            }
            //echo "</ul>";
            $getAdminSideBarOptionsQry = "Select * from AdminSideBarOptions where 
                UserID = '".$_GET["UserID"]."'
            ";
          
            $smtAdminSideBarOptionsQry = $clientApi->query($getAdminSideBarOptionsQry);  
            $acceurls = array();
            
            while( $rowAdminSideBarOptions = $smtAdminSideBarOptionsQry->fetch(PDO::FETCH_ASSOC) ){
                $acceurls[] = $rowAdminSideBarOptions['PathUrl'];
            }   

            // DB insertion logic here if needed

        } else {
            echo "<p>No files selected.</p>";
        }
    }
        // After all DB work is complete
    $clientApi = null;    
    $iconn->close();   // MySQLi
}


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>
    <style>
        .file-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 20px;
            padding-left: 10px;
            margin-top: 5px;
        }

        .file-list label {
            display: flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .file-tree {
            list-style: none;
            padding-left: 15px;
            margin: 0;
        }

        .file-tree > li {
            margin-bottom: 8px;
        }
    </style>
            <?php include ($IncludesPath."title-meta.php"); ?>
            <?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>
</head>

<body data-sidebar="dark">

    
     <?php          include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
            include ($IncludesPath."topbar.php");
            include ($IncludesPath."sidebar.php"); 
            ?>
    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include ($IncludesPath."page-title.php");?>
                
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">                        

                        <div class="card">
                            <div class="card-body">
                                
                                <form method="post" ><div class="row">           

                                    <div class="form-group col-sm-3" >
                                        <label for="exampleInputEmail1">Client Name</label>
                                        <select class="form-control select2" name="ClientID" id="ClientID" <?php if($getclientname != ""){ echo "disabled"; } ?> onChange="getUserRoles()">
                                            <option value="">Select Client</option>
                                            <?php while($RowClientsList=$StmtClientsList->fetch_array()){?> 
                                            <option value="<?php echo $RowClientsList['ClientAccountID'] ;?>" <?php if($getclientname == $RowClientsList['ClientAccountID']){ echo "selected"; } ?> >
                                            <?php  echo $RowClientsList['ClientName']; ?></option>
                                            <?php    } ?>
                                        </select>
                                    </div>

                                    <div class="form-group col-sm-3" >
                                        <label for="exampleInputEmail1">Users</label>
                                        <select class="form-control select2" name="UserRoleID" id="UserID" <?php if($getclientname != ""){ echo "disabled"; } ?>>
                                            <?php if($selectedUserName){ ?>
                                                <option value="<?php echo $_GET['UserID']; ?>">
                                                    <?php echo $selectedUserName; ?>
                                                </option>
                                            <?php }else{ ?>
                                                <option value="">Select User</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php if(!$accessgiven){ ?>
                                    <div class="form-group col-sm-3 mt-4 pt-1">
                                        <button type="button" name='button' class="btn btn-success" onclick="callGet()">Get Details</button>
                                  </div>                                  
                                  <?php }else{ ?>
                                    <div class="form-group col-sm-3 mt-4 pt-1">
                                        <button type="button" name='button' class="btn btn-success" onclick="clearGet()">Back</button>
                                    </div>
                                    <div> 
                                    <?php echo renderFileTree($baseFolder, '', $excludeFolders); ?>
                                    <br>
                                    <input type="submit" class="btn btn-primary" value="Submit Selected Files">
                                    </div>
                                <?php } ?>
                                </div>
                                </form>
                                
                            </div>
                        </div>


                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />



<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
    $(document).ready(function() {
        // Initialize Select2 on page load
        initSelect2();
    });
    function initSelect2() {
        $('#UserID').select2({
            placeholder: "Select User",
            allowClear: true,
            width: '100%'
        });
    }
    function getUserRoles(){
        var ClientID = $("#ClientID").val();
        if( ClientID != ''  && ClientID != undefined){
            $.ajax({
                url:'',
                type:'POST',
                data:{'ClientID':ClientID,'action':'getUsers'},
                success:function(data){
                    if ($('#UserID').hasClass("select2-hidden-accessible")) {
                        $('#UserID').select2('destroy');
                    }

                    // 2. Insert the new options from AJAX
                    $("#UserID").html(data);

                    // 3. Re-initialize Select2 so the search box returns!
                    initSelect2();

                    // 4. Force Select2 to recognize any changes
                    $('#UserID').trigger('change');
                }
            })
        }
    }

    function callGet(){
        var ClientID = $("#ClientID").val();
        var UserID = $("#UserID").val();
        window.location.href = "/v3/Users/get-access-urls.php?ClientID="+ClientID+"&UserID="+UserID;
        return false;

    }
    function clearGet(){
        window.location.href = "/v3/Users/get-access-urls.php";
    }

    document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.select-all').forEach(selectAllCheckbox => {
        selectAllCheckbox.addEventListener('change', function () {
            const folderId = this.dataset.folderId;
            const checkboxes = document.querySelectorAll('.file-checkbox[data-folder-id="' + folderId + '"]');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    });

    // Optional: update "Select All" checkbox state when individual boxes are changed
    document.querySelectorAll('.file-checkbox').forEach(fileCheckbox => {
        fileCheckbox.addEventListener('change', function () {
            const folderId = this.dataset.folderId;
            const all = document.querySelectorAll('.file-checkbox[data-folder-id="' + folderId + '"]');
            const allChecked = [...all].every(cb => cb.checked);
            const selectAll = document.querySelector('.select-all[data-folder-id="' + folderId + '"]');
            if (selectAll) selectAll.checked = allChecked;
        });
    });
});
</script>
</body>

</html>
