<style>
        /* Breadcrumb Styles */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.2rem;
            margin-bottom: 0px;
            padding-left: 0px;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb-separator {
            width: 16px;
            height: 16px;
            color: #6b7280a1;
        }

        .breadcrumb-link {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .breadcrumb-link.inactive {
            color: #6b7280;
        }

        .breadcrumb-link.inactive:hover {
            color: #374151;
        }

        .breadcrumb-link.active {
            color: #2563eb;
            font-weight: 500;
        }    
        .breadcrumb .breadcrumb-item:first-child a{
            padding-left: 0px;
        }
        .breadcrumb-page-title{margin: 15px 0px 10px 0px;font-size: 15px;}
        #PageHeader {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        #PageHeader.show {
            opacity: 1;
        }
</style>
<?php 
    if($_SESSION['SelectedMenu']['Header'] == 'Master' OR $_SESSION['SelectedMenu']['Header'] == 'Cs Master' ){
        $MenuUrl = WORKING_URL.'includes/masters-list.php';
    }else{
        $MenuUrl = $_SESSION['SelectedMenu']['Url'];
    }

    $OptionCurrentPath  = "SELECT * FROM `AdminProfileSideBarOptions` WHERE FileName = '$currentUrlFile'"; 
    $StmtCurrentOption = $admin_db_conn->query($OptionCurrentPath);
    if($StmtCurrentOption->num_rows > 0){
        $rowCurrentOption = $StmtCurrentOption->fetch_assoc();
        $currentOptionData = $rowCurrentOption;
        //var_dump($currentOptionData);
        if($folderName == 'Users'){
            $_SESSION['SelectedMenu']['Header'] = 'Dashboard';
            //$MenuUrl = WORKING_URL.$folderName.'/'.$currentOptionData['FileName'];
            $MenuUrl = $_SESSION["ClientPath"];
            unset($_SESSION['SelectedSubMenu']);
            //echo $MenuUrl;
        }
    }
    if (!function_exists('getPageTitle')) {
        function getPageTitle()
        {
            // Current path: AntiDumping/anti-dumping-form
            $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

            // Remove project folder (v3)
            if (strpos($currentPath, 'v3/') === 0) {
                $currentPath = substr($currentPath, 3);
            }

            if (empty($_SESSION['MenuList'])) {
                return '';
            }

            foreach ($_SESSION['MenuList'] as $menu) {
                if (empty($menu->SubMenu)) {
                    continue;
                }

                foreach ($menu->SubMenu as $subMenu) {
                    if (strcasecmp(trim($subMenu->PathUrl, '/'), $currentPath) === 0) {
                        return $subMenu->Name;
                    }
                }
            }

            return '';
        }
    }
    $PageTitle =getPageTitle();
    if($PageTitle==""){
        $PageTitle = fileNameToTitle($currentUrlFile);
    }
    if(sizeof($currentUrlData) == 0){
        $sqlInnerpage  = $admin_db_conn->query("
                            SELECT
                                Pages.ID AS PageID,
                                Pages.Directory,
                                Pages.PageTitle,
                                Pages.FileName,
                                OPTIONS.Name AS SubMenuName,
                                OPTIONS.Directory AS SubMenuDir,
                                OPTIONS.FileName AS SubMenuFile
                            FROM
                                `ClientSideBarInnerPages` Pages
                            LEFT JOIN ClientSideBarOptions OPTIONS ON OPTIONS
                                .ID = Pages.SubMenuID
                            WHERE
                                Pages.FileName = '$currentUrlFile' AND Pages.Directory = '$folderName'
        ");
        if($sqlInnerpage->num_rows > 0){
            $sqlInnerpageData = $sqlInnerpage->fetch_assoc();
        }         
    }

?>

<!-- start page title -->
<div id="breadcrumb-mobile" class="d-block d-lg-none">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0" id="PageHeader"><?php echo $PageTitle?></h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo $PageTitleLink?>"><?php echo $PageTitleFolder?></a></li>
                        <li class="breadcrumb-item active"><?php echo $PageTitle?></li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
</div>    

<!-- Desktop Breadcrumb -->
<?php if(!$isDefaultDashboardAct){?>
<div id="breadcrumb-desktop" class="d-none d-lg-block">
    <div class="row"  style="display:none!important;">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="text-2xl font-semibold text-gray-900 mb-2" id="PageHeader"><?php echo $PageTitle?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <?php
                        if(isset($_SESSION['SelectedModule']) && sizeof($_SESSION['SelectedModule'])> 0){
                            echo "<li class='breadcrumb-item'><a href=".$_SESSION['SelectedModule']['Url'].">".$_SESSION['SelectedModule']['Name']."</a></li>";
                        }  
                        ?>
                        <?php
                        if(isset($_SESSION['SelectedMenu']) && sizeof($_SESSION['SelectedMenu'])> 0){
                            echo "<li class='breadcrumb-item'><a href=".$MenuUrl.">".$_SESSION['SelectedMenu']['Header']."</a></li>";
                        }  
                        ?>
                        <?php
                        if(isset($_SESSION['SelectedSubMenu']) && sizeof($_SESSION['SelectedSubMenu'])> 0  && sizeof($currentUrlData)==0 ){
                            echo "<li class='breadcrumb-item'><a href=".$_SESSION['SelectedSubMenu']['Url'].">".$_SESSION['SelectedSubMenu']['Name']."</a></li>";
                        }  
                        ?>
                        <!-- <li class="breadcrumb-item"><a href="<?php echo $PageTitleLink?>"><?php echo $PageTitleFolder?></a></li> -->
                        <li class="breadcrumb-item active"><?php echo $PageTitle?></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
        <!-- Breadcrumb -->
        <?php if((sizeof($currentUrlData) > 0 && $currentUrlData['Header'] == 'Master') OR (sizeof($currentUrlData) > 0 && $currentUrlData['Header'] == 'Cs Master') OR (sizeof($currentUrlData) == 0)  ){?>
        <nav class="breadcrumb">
            <?php
            if(sizeof($sqlInnerpageData) > 0 && $folderName!='Users' ){
                $isHasMenu = true;
                echo '
                <div class="breadcrumb-item">
                    <a href='.$MenuUrl.' class="breadcrumb-link inactive">'.$_SESSION['SelectedMenu']['Header'].'</a>
                </div>
                <svg class="breadcrumb-separator" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <div class="breadcrumb-item">
                    <a href='.WORKING_URL.$sqlInnerpageData['SubMenuDir'].$sqlInnerpageData['SubMenuFile'].' class="breadcrumb-link inactive">
                    '.$sqlInnerpageData['SubMenuName'].'
                    </a>
                </div>
                <svg class="breadcrumb-separator" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                ';   

            }else{
            if(isset($_SESSION['SelectedModule']) && sizeof($_SESSION['SelectedModule'])> 0){
                // echo "<div class='breadcrumb-item'>
                //         <a href='#' class='breadcrumb-link inactive'>Sales</a>
                //     </div>
                //     <svg class='breadcrumb-separator' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                //         <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7' />
                //     </svg>
                //     ";
                }     
            ?>
            <?php
            if(isset($_SESSION['SelectedMenu']) && sizeof($_SESSION['SelectedMenu'])> 0){
                    $isHasMenu = true;
                    echo '
                    <div class="breadcrumb-item">
                        <a href='.$MenuUrl.' class="breadcrumb-link inactive">'.$_SESSION['SelectedMenu']['Header'].'</a>
                    </div>
                    <svg class="breadcrumb-separator" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    ';                
            }  
            ?>
            <?php
            if(isset($_SESSION['SelectedSubMenu']) && sizeof($_SESSION['SelectedSubMenu'])> 0  && sizeof($currentUrlData)==0 ){
                    $isHasMenu = true;                
                    echo '
                    <div class="breadcrumb-item">
                        <a href='.$_SESSION['SelectedSubMenu']['Url'].' class="breadcrumb-link inactive">
                        '.$_SESSION['SelectedSubMenu']['Name'].'
                        </a>
                    </div>
                    <svg class="breadcrumb-separator" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    ';
                    if($PageTitle){
                        $sqlCheckPage  = $admin_db_conn->query("SELECT * FROM `ClientSideBarInnerPages` WHERE FileName = '$currentUrlFile'")->num_rows; 
                        if(!$sqlCheckPage){
                                $admin_db_conn->query("INSERT INTO `ClientSideBarInnerPages` 
                                (SubMenuID, PageTitle, Directory, FileName) 
                                VALUES (
                                '{$_SESSION['SelectedSubMenu']['ID']}',
                                '{$_SESSION['SelectedSubMenu']['Name']}',
                                '$folderName',
                                '$currentUrlFile'
                                )");
                        }
                    }
                }
            }  
            ?>
            <?php if($isHasMenu) {
              //unset($_SESSION['default_dashboard']);
            ?>
            <div class="breadcrumb-item">
                <a href="#" class="breadcrumb-link active"><?php echo $PageTitle?></a>
            </div>
            <?php } ?>
        </nav>
        <?php
    // $sqlCheckPage  = "SELECT * FROM `AdminProfileSideBarOptions` WHERE FileName = '$currentUrlFile'"; 
    // $StmtCurrentOption = $admin_db_conn->query($OptionCurrentPath);
    // if($StmtCurrentOption->num_rows > 0){
    //       $sqlCheckPage = ""
        } ?>
        <div class="page-title-box">
            <nav class="breadcrumb-page-title">
                <h4 class="mb-0" id="PageHeader"><?php echo $PageTitle?></h4>            
            </nav>            
        </div>
</div>
<?php } ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<script>
$('document').ready(function(){
    var mainMenu = $(".nav-item.dropdown.active a span").text();
    var subMenu = $("#level3-container button.active").text().trim();
//     console.log(mainMenu, subMenu);  

if(subMenu) {
    $("#PageHeader").text(fileNameToTitleJs(subMenu));
    // $(".breadcrumb-item a").text(mainMenu);
    // $(".breadcrumb-item.active").text(subMenu);
}
$('#breadcrumb-desktop #PageHeader').addClass('show');
//     var SideBarHeader=$('li.mm-active span').text();
//     var SideBarHeaderr=$('li.mm-active span').text();
//     var SideBarOption=$('li.mm-active a.active').text();
//     var SideBarOptionT=$('li.dropdown-item a.active').text();

//     if(SideBarHeader !='' && SideBarOption!=''){
//         var SideBarHeader=$('li.breadcrumb-item a').text(SideBarHeader);
//         var SideBarHeader=$('li.breadcrumb-item.active').text(SideBarOption);
//         var SideBarOption=$('#PageHeader').text(SideBarOptionT);
//     }    

$('#breadcrumb-desktop .breadcrumb').each(function () {
    if ($(this).children().length === 0) {
        $(this).remove();
    }
});
    
});
function fileNameToTitleJs(filename) {
    return filename
        .replace(/\.[^/.]+$/, '')   // remove extension (.php etc.)
        .replace(/[-_]+/g, ' ')     // replace hyphens AND underscores
        .trim()
        .toLowerCase()
        .replace(/\b\w/g, c => c.toUpperCase()); // Title Case
}
</script>
<?php 

if($folderName == 'Users'){
    $_SESSION['SelectedMenu']['Header'] = 'Dashboard';
    $MenuUrl = WORKING_URL.$folderName.'/'.$currentOptionData['FileName'];
    $_SESSION['SelectedSubMenu']['Name'] = $currentOptionData['Header'];
    $_SESSION['SelectedSubMenu']['Url'] = $MenuUrl;
    //echo $MenuUrl;
}
function fileNameToTitle($filename) {
    // // Remove extension if exists
    // $filename = pathinfo($filename, PATHINFO_FILENAME);

    // // Replace hyphens/underscores with space
    // $filename = str_replace(['-', '_'], ' ', $filename);

    // // Convert to Title Case
    // return ucwords($filename);
    
    // Remove extension if exists
    $filename = pathinfo($filename, PATHINFO_FILENAME);

    // Replace hyphens/underscores with space
    $filename = str_replace(['-', '_'], ' ', $filename);

    // Add space before capital letters (except first)
    $filename = preg_replace('/(?<!^)([A-Z])/', ' $1', $filename);

    // Convert to Title Case
    return ucwords($filename);
}
?>
<!-- end page title -->
