<?php 
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once __DIR__.'/../config/Dotenv.php';
$DB_HOST = (getenv('DB_HOST')) ? getenv('DB_HOST') : $_ENV['DB_HOST'];
$DB_NAME = (getenv('DB_NAME')) ? getenv('DB_NAME') : $_ENV['DB_NAME'];
$DB_USER = (getenv('DB_USER')) ? getenv('DB_USER') : $_ENV['DB_USER'];
$DB_PASSWORD = (getenv('DB_PASSWORD')) ? getenv('DB_PASSWORD') : $_ENV['DB_PASSWORD'];

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

$currentUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//$currentUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$BaseDist = $protocol . '://' . $_SERVER['HTTP_HOST']."/assetsv2/";

$admin_db_conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
$currentUrlFile = basename(parse_url($currentUrl, PHP_URL_PATH));
$path = parse_url($currentUrl, PHP_URL_PATH); // /v3/QualityControl/LotsList
$parts = explode('/', trim($path, '/'));
$folderName = $parts[1]; 
$urlParts = parse_url($currentUrl);
$currentUrlFileWithParams = basename($urlParts['path']) . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');

$activeMenuIndex = null;
$selected_tab_menu = [];
$headerIDs = $_SESSION["MenuList"]->HeaderIDs;
$OptionIDs = $_SESSION["MenuList"]->OptionIDs;
if($_SESSION['Module'] == ""){
   $_SESSION['Module'] = $_SESSION["MenuList"]->Modules[0]->Name;
}

if($folderName == $currentUrlFile){
    $currentUrlFile = 'index';
}

if (strpos($currentUrlFile, '.php') !== false) {
    $urlExtCheck = "OR Options.FileName="."'".str_replace('.php', '', $currentUrlFile)."'";
} else {
    $urlExtCheck = "";
}

$condtion = "";
if($currentUrlFile == 'index'){
    if($folderName=='Purchase')
        $condtion = " AND Options.Module='Purchase'";
    if($folderName=='QualityControl')
        $condtion = " AND Options.Module='Quality Control'";
}    
if($folderName=='Purchase' && ($currentUrlFileWithParams=='Users?Type=2' OR $currentUrlFileWithParams=='Users?Type=1')){
    $currentUrlFile = $currentUrlFileWithParams;
}
if($folderName=='PreProcessing' && $currentUrlFileWithParams=='Users.php?Type=5'){
    $currentUrlFile = $currentUrlFileWithParams;    
}
if($folderName=='Hrm' && $currentUrlFile == 'MonthlyScript'){
    $currentUrlFile = 'ManualScripts/MonthlyScript';
}
if($folderName=='Hrm' && $currentUrlFile == 'MonthlyScript'){
    $currentUrlFile = 'ManualScripts/MonthlyScript';
}
if($folderName=='Hrm' && $currentUrlFile == 'DailyScript'){
    $currentUrlFile = 'ManualScripts/DailyScript';
}
if($_SESSION['ClientID']=='serp')
    $sqlCurrentPath  = "SELECT Headers.Sid,Headers.Header,Options.ID,Options.Directory,Options.Name,Options.PathUrl as FileName,Options.Module FROM `AdminSideBarOptions` Options LEFT JOIN AdminSideBarHeaders Headers ON Options.HeaderId=Headers.Sid WHERE Options.PathUrl ='{$currentUrlFile}' AND Headers.Sid IN( $headerIDs ) AND Options.ID IN($OptionIDs ) $condtion";
else{
    $dir = rtrim($folderName, '/') . '/';
    $sqlCurrentPath  = "SELECT Headers.Sid,Headers.Header,Options.ID,Options.Directory,Options.Name,Options.FileName,Options.Module FROM `ClientSideBarOptions` Options LEFT JOIN ClientSideBarHeaders Headers ON Options.HeaderId=Headers.Sid WHERE Options.FileName ='{$currentUrlFile}' $urlExtCheck AND Headers.Sid IN( $headerIDs ) AND Options.ID IN($OptionIDs ) $condtion AND Options.Directory='{$dir}'";    
}
//echo $sqlCurrentPath;

$StmtCurrentFile = $admin_db_conn->query($sqlCurrentPath);
$currentUrlData = [];
if($StmtCurrentFile->num_rows > 0){
    $rowCurrentUrl = $StmtCurrentFile->fetch_assoc();
    $currentUrlData = $rowCurrentUrl;
}

if(isset($currentUrlData) && sizeof($currentUrlData) > 0 ){
    $activeModule = $currentUrlData['Module'];
}else{
    $activeModule = $_SESSION['Module'];
}

if(isset($currentUrlData) && sizeof($currentUrlData) > 0 ){
    $activeMenu = $currentUrlData['Sid'];
}else{
    $activeMenu = $_SESSION['MenuID'];
}
if(sizeof($currentUrlData)>0){
    $_SESSION['NavState'] = $currentUrlData;
    if(isset($_SESSION['default_dashboard'])){
        $_SESSION['default_dashboard'] = '
            <a href="'.$_SESSION["ClientPath"].'"
               class="module-tab flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-sm bg-white text-gray-600 hover:bg-gray-50 border border-gray-200"
               data-module=""
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <!-- Dashboard with widgets -->
                      <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                      <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                      <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                      <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                    </svg>
                </i>
                <span>Dashboard</span>
            </a>';
        $isDefaultDashboardAct = false;
    }    
}
else{
    if($currentUrlFile == 'index'){
    $_SESSION['default_dashboard'] = '
        <a href="'.$_SESSION["ClientPath"].'"
           class="active module-tab flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all bg-gradient-to-r from-blue-500 to-cyan-400 text-white shadow-lg shadow-blue-500/25"
           data-module=""
            <i>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <!-- Dashboard with widgets -->
                  <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                  <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                  <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                  <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                </svg>
            </i>
            <span>Dashboard</span>
        </a>';
        $isDefaultDashboardAct = true;    
    }    
}

 //print_r($_SESSION['MenuList']->RootUrl);
// var_dump($currentUrlData);
// echo "<br><br>";
   $modulesArray = [];
   $menuArray = [];      // Headers
   $subMenuArray = [];   // Submenus
   if($_SESSION['ClientID']=='serp'){
      $SqlMenu = "SELECT Headers.Sid, Headers.Header, OPTIONS.ID, OPTIONS.Directory, OPTIONS.Name,OPTIONS.PathUrl as FileName,OPTIONS.Module FROM `AdminSideBarOptions` OPTIONS LEFT JOIN AdminSideBarHeaders Headers ON OPTIONS .HeaderId = Headers.Sid WHERE Headers.Sid IN( $headerIDs ) ORDER BY Headers.SortOrder ASC";
   }else{
      $SqlMenu = "SELECT
                    Headers.Sid,
                    Headers.Header,
                    OPTIONS.ID,
                    OPTIONS.Directory,
                    OPTIONS.Name,
                    OPTIONS.FileName,
                    OPTIONS.Module,
                    Modules.MenuIcon,
                    Headers.SortOrder,
                    OPTIONS.SortOrder,
                    Modules.SortOrder
                FROM
                    `ClientSideBarOptions` OPTIONS
                LEFT JOIN ClientSideBarHeaders Headers ON OPTIONS
                    .HeaderId = Headers.Sid
                LEFT JOIN ClientSideBarModules Modules ON OPTIONS
                    .Module = Modules.Name
                WHERE
                    Headers.Sid IN($headerIDs) AND ID IN($OptionIDs ) AND Modules.SortOrder!=''
                ORDER BY
                    Modules.SortOrder ASC,
                    Headers.SortOrder ASC,
                    OPTIONS.SortOrder ASC";
   }
   //echo $SqlMenu;
   $StmtMenu = $admin_db_conn->query($SqlMenu);

   if($StmtMenu->num_rows > 0){
    while ($row = $StmtMenu->fetch_assoc()) {

        $module = $row['Module'];
        $sid = $row['Sid'];
        if($row['Header'] == 'Master' OR $row['Header'] == 'Cs Master')
         $isMastersExist = true;

        // 1️⃣ Create Module root
        if(!isset($modulesArray[$module])){
            if($row['Header'] != "Master"){
                $modulesArray[$module] = [
                    'Module' => [
                        "Name" => $row['Module'],
                        "Sid" => $row['Sid'],
                        "Icon" => $row['MenuIcon'],
                        'Directory' => $row['Directory'],
                        'FileName' => $row['FileName']
                    ]
                ];
            }
        }

        // 2️⃣ Create Header inside Module
        if(!isset($menuArray[$module][$sid])){
            if($row['Header'] != "Master")
            $menuArray[$module][$sid] = [
                'Sid' => $row['Sid'],
                'Header' => $row['Header'],
                'Directory' => $row['Directory'],
                'FileName' => $row['FileName'],
                'Module' => $module,
                'SubMenus' => []
            ];
        }

        // 3️⃣ Add submenu under Header
        if((isset($currentUrlData['Sid']) && $currentUrlData['Sid']!="") && $currentUrlData['Sid'] == $row['Sid'] ){
            $subMenuArray[$module][$sid][] = [
                'ID' => $row['ID'],
                'Name' => $row['Name'],
                'Directory' => $row['Directory'],
                'FileName' => $row['FileName']
            ];
        }
    }
}
    //print_r($menuArray);
    if(($currentUrlData['Header'] == 'Master' && sizeof($currentUrlData) > 0) OR ($currentUrlData['Header'] == 'Cs Master' && sizeof($currentUrlData) > 0) OR (sizeof($currentUrlData) == 0 && sizeof($menuArray) > 0 && $currentUrlFile != 'index') ){
        $NavStatus = "tw-hidden";
    }else{
        $NavStatus = "";
    }

    if(sizeof($currentUrlData)>0)
        $_SESSION['NavState'] = $currentUrlData;


   if($_SESSION['MenuID'] == ""){
      $_SESSION['MenuID'] = array_keys($menuArray)[0]; 
   }

   if($_SESSION['SubMenuID'] == ""){
      $_SESSION['SubMenuID'] = $subMenuArray[$_SESSION['MenuID']][0]['ID']; 
   }

   //Brand Logo
   if( (isset($_SESSION['MenuList']->BrandLogo)) && $_SESSION['MenuList']->BrandLogo!="" ){
        $ClientLogo = "<div class='w-16 h-16 flex items-center justify-center'>
              <span class='text-white font-bold text-base'><img src=".$BaseDist.'assets/images/companies/'.$_SESSION['MenuList']->BrandLogo." /></span>
            </div>";
        $ClientLogoV2 = "<div class='w-8 h-8 flex items-center justify-center'>
              <span class='text-white font-bold text-base'><img src=".$BaseDist.'assets/images/companies/'.$_SESSION['MenuList']->BrandLogo." /></span>
            </div>";
   }else{
        $ClientLogo = "<div class='w-16 h-16 bg-orange-500 rounded flex items-center justify-center'>
              <span class='text-white font-bold text-base'>".(isset($_SESSION["ClientName"]) ? strtoupper(htmlspecialchars($_SESSION["ClientName"])[0]) : strtoupper("C"))."</span>
            </div>";
        $ClientLogoV2 = "<div class='w-8 h-8 bg-orange-500 rounded flex items-center justify-center'>
              <span class='text-white font-bold text-base'>".(isset($_SESSION["ClientName"]) ? strtoupper(htmlspecialchars($_SESSION["ClientName"])[0]) : strtoupper("C"))."</span>
            </div>";
   }

?>
<div id="app" class="flex flex-col navbar-container">


  <div class="bg-gray-50">
    <!-- Main Header - Hidden on scroll -->
    <?php if( !$_SESSION['ClientConfigData']['HeaderMenuV2']) {
        include 'menu-components/main-header-bar.php';
    }else if($_SESSION['ClientConfigData']['HeaderMenuV2'] && $NavStatus == "tw-hidden"){
        include 'menu-components/menu-container-layout-2.php';
        echo "<script>document.getElementById('level1-tabs').style.display = 'none';</script>";
    }    
    ?>

   <!-- Navigation Header -->
   <div class="bg-white border-b border-gray-200">
     <div class="navbar-menu-container <?php echo $NavStatus; ?>"> 
            <?php if(sizeof($modulesArray) > 0){ ?>
                <?php if($_SESSION['ClientConfigData']['HeaderMenuV2']){
                    include 'menu-components/menu-container-layout-2.php';    
                }else{
                    include 'menu-components/menu-container-layout-1.php';
                } 
                ?> 
            <?php } ?>    
    
 
              <!-- Level 2: Sub-tabs -->
              <?php if(sizeof($menuArray) > 0){ ?>
              <div id="level2-container" class="px-6 py-2 bg-gray-50 border-t border-gray-100 scroll-container relative">
                 <button id="scroll-left-btn-level2" class="arrow-button arrow-button-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-left" aria-hidden="true" class="lucide lucide-chevron-left w-4 h-4 text-gray-600">
                       <path d="m15 18-6-6 6-6"></path>
                    </svg>
                 </button>
                <?php

                //print_r($menuArray[array_keys($menuArray)[0]]);
                if(!empty($menuArray[$currentUrlData['Module']]))
                        $menuArrayData = $menuArray[$currentUrlData['Module']];
                    else{
                        $parentModule = array_keys($menuArray)[0];
                        $menuArrayData = $menuArray[$parentModule];                        
                    }


                if (!empty($menuArray[$currentUrlData['Module']])) { ?>
                    <div class="scroll-gradient-left" id="scroll-gradient-left-level2"></div>
                    <div id="level2-tabs" class="flex items-center gap-2 scroll-wrapper">
                        <?php
                        foreach ($menuArrayData as $MenuIndex => $item) {
                            
                            if($item['Sid'] == $activeMenu){
                                $isActive = 'active menu-tab px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all bg-gradient-to-r from-pink-400 via-purple-400 to-indigo-400 text-white shadow-md shadow-purple-400/30';
                                $_SESSION['SelectedMenu'] = [
                                    "Sid" => $item['Sid'],
                                    "Header" => $item['Header'],
                                    "Url" => WORKING_URL . $item['Directory'] . $item['FileName']
                                    ];
                            }else{
                                $isActive = 'menu-tab px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-all bg-white text-gray-600 hover:bg-gray-50 border border-gray-200';
                            }
                            if($item['Header'] != 'Master' && $item['Header'] != 'Cs Master' ){
                        ?>
                                <a href="<?php echo WORKING_URL . $item['Directory'] . $item['FileName']; ?>">
                                <button class="<?php echo $isActive; ?>" data-menu="<?php echo $item['Sid'];?>" data-url="<?php echo WORKING_URL . $item['Directory'] . $item['FileName']; ?>">
                                    <?php echo htmlspecialchars($item['Header']); ?>
                                </button>
                                </a>
                        <?php
                            }
                        }
                        ?>

                    </div>
                <?php } ?>

                 <div class="scroll-gradient-right" id="scroll-gradient-right-level2"></div>
                 <button id="scroll-right-btn-level2" class="arrow-button arrow-button-right">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" aria-hidden="true" class="lucide lucide-chevron-right w-4 h-4 text-gray-600">
                       <path d="m9 18 6-6-6-6"></path>
                    </svg>
                 </button>
              </div>  
              <?php } ?>          
              <!-- Level 3: Masters tabs -->
              <?php
                 if(isset($currentUrlData) && sizeof($currentUrlData) > 0 ){
                    $activeSubMenu = $currentUrlData['ID'];
                 }else{
                    $activeSubMenu = $_SESSION['SubMenuID'];
                 }
              if (!empty($subMenuArray[$currentUrlData['Module']])) { ?>
              <div id="level3-container" class="px-6 py-2 bg-white border-t border-gray-100 scroll-container relative">
                 <button id="scroll-left-btn-level3" class="arrow-button arrow-button-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-left" aria-hidden="true" class="lucide lucide-chevron-left w-4 h-4 text-gray-600">
                       <path d="m15 18-6-6 6-6"></path>
                    </svg>
                 </button>
                 <div class="scroll-gradient-left scroll-gradient-left-level3" id="scroll-gradient-left-level3"></div>
                 <div id="level3-wrapper" class="scroll-wrapper">
                  <div id="level3-tabs" class="inline-flex bg-gray-200 rounded-lg p-1 shadow-inner">
                    <?php
                        foreach ($subMenuArray[$currentUrlData['Module']][$currentUrlData['Sid']] as $submenu_items) {
                            if($currentUrl == WORKING_URL . $submenu_items['Directory'] . $submenu_items['FileName'])
                            $_SESSION['SubMenuID'] = $submenu_items['ID']; 
                            if(sizeof($subMenuArray[$activeMenu]) == 1 OR ($currentUrl == WORKING_URL . $submenu_items['Directory'] . $submenu_items['FileName'] )  OR $activeSubMenu == $submenu_items['ID']){
                                $active_link = 'active submenu-tab px-6 py-2 text-sm font-medium whitespace-nowrap rounded-md transition-all bg-white text-gray-900 shadow-sm';
                                $_SESSION['SelectedSubMenu'] = [
                                    "ID" => $submenu_items['ID'],
                                    "Name" => htmlspecialchars($submenu_items['Name']),
                                    "Url" => WORKING_URL . $submenu_items['Directory'] . $submenu_items['FileName']
                                ];
                            }else{
                                $active_link = 'submenu-tab px-6 py-2 text-sm font-medium whitespace-nowrap rounded-md transition-all text-gray-600 hover:text-gray-900';                                
                            }
                            ?>
                            <a href="<?php echo WORKING_URL . $submenu_items['Directory'] . $submenu_items['FileName']; ?>">
                                 <button class="<?php echo $active_link; ?>" data-submenu="<?php echo $submenu_items['ID'];?>" data-url="<?php echo WORKING_URL . $submenu_items['Directory'] . $submenu_items['FileName']; ?>">
                               <?php echo htmlspecialchars($submenu_items['Name']); ?>
                               </button>
                            </a>
                    <?php
                        }
                    ?>
                  </div>
                 </div>
                 <div class="scroll-gradient-right scroll-gradient-right-level3" id="scroll-gradient-right-level3"></div>
                 <button id="scroll-right-btn-level3" class="arrow-button arrow-button-right">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" aria-hidden="true" class="lucide lucide-chevron-right w-4 h-4 text-gray-600">
                       <path d="m9 18 6-6-6-6"></path>
                    </svg>
                 </button>
              </div>
              <?php } ?>
        </div>
    </div>   

      </div>
   </div>

<!-- Styles -->
<style>
/* ===================== LAYOUT STABILITY FIX ===================== */

/* Prevent scrollbar width shift between pages */
html {
  overflow-y: scroll;
  scrollbar-gutter: stable;
}

/* Prevent font rendering shift */
/*body {
  font-family: ui-sans-serif, system-ui, sans-serif,
               "Apple Color Emoji", "Segoe UI Emoji",
               "Segoe UI Symbol", "Noto Color Emoji";
}*/

/* Prevent active/inactive width difference */
#level2-tabs button,
#level3-tabs button,
#level1-tabs button {
  font-weight: 500 !important;      /* Reserve weight space */
/*  border: 1px solid transparent;    
  box-sizing: border-box;*/
}

/* Remove layout transitions (keep only visual ones) */
#level2-tabs button,
#level3-tabs button,
#level1-tabs button {
  transition: background-color 0.2s ease,
              color 0.2s ease,
              box-shadow 0.2s ease !important;
}

/* Prevent height shift */
#level2-tabs button.active,
#level3-tabs button.active {
  border: 1px solid transparent !important;
}

  /* ================== Reduced Height Navigation Bar ================== */

  /* Basic nav sizing */
  .topnav .navbar {
    padding: 0rem 0 !important;
    /* removed extra vertical padding */
    min-height: 35px !important;
    /* ↓ reduced from 38px */
    height: 35px !important;
    /* line-height: 1 !important; */
  }

  /* Container */
  .menu-scroll-container {
    display: flex;
    align-items: center;
    gap: 6px;
    width: 85%;
    position: relative;
    min-height: 45px !important;
  }

  /* Scroll wrapper - horizontal scroll, focusable */
  .menu-scroll-wrapper {
    flex: 1 1 auto;
    overflow-x: auto;
    -ms-overflow-style: none;
    /* IE/Edge */
    scrollbar-width: none;
    /* Firefox */
    outline: none;
    scroll-behavior: smooth;
    padding: 0 !important;
    /* ↓ removed padding to shrink height */
    min-height: 26px !important;
  }

  .menu-scroll-wrapper::-webkit-scrollbar {
    display: none;
  }

  /* Menu items layout */
  .navbar-nav {}

  .navbar-nav .nav-link {}

  /* Icon scroll buttons */
  .scroll-btn {
    position: relative;
    z-index: 20;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border: none;
    width: 28px !important;
    /* ↓ from 36px */
    height: 30px !important;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    opacity: 0;
    transform: translateY(0);
    transition: opacity 220ms ease, transform 220ms ease;
  }

  /* visible state */
  .scroll-btn.visible {
    opacity: 1;
  }

  /* focus style */
  .scroll-btn:focus {
    outline: 2px solid rgba(255, 255, 255, 0.18);
  }

  /* Dropdown menu base */
  .dropdown-menu {
    position: fixed !important;
    z-index: 9999 !important;
    display: none;
    min-width: 150px;
    opacity: 0;
    transform: translateY(4px);
    transition: opacity 180ms ease, transform 180ms ease;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
    background: #fff;
    max-height: 80%;
    overflow-x: auto;
  }

  /* visible dropdown: fade-in */
  .dropdown-menu.visible {
    display: block;
    opacity: 1;
    transform: translateY(0);
  }

  /* Right items */
  .header-item {
    padding: 2px 6px !important;
    /* ↓ smaller buttons */
    font-size: 12px !important;
    height: 26px !important;
    line-height: 1 !important;
  }

  /* small screens: hide icon buttons (swipe) */
  @media (max-width: 768px) {
    .scroll-btn {
      display: none !important;
    }
  }


  /* Strong mobile-only overrides for submenus */
  @media (max-width: 991.98px) {

    /* make sure the mobile container sits above page */
    #topnav-mobile {
      position: relative !important;
      z-index: 3000 !important;
      background: #0f0f0f !important;
    }

    /* ensure all dropdown menus are collapsed by default but visible in flow */
    #topnav-mobile .dropdown-menu {
      display: block !important;
      /* keep in flow (we use max-height to collapse) */
      max-height: 0 !important;
      /* collapsed */
      overflow: hidden !important;
      transition: max-height 260ms ease, padding 200ms ease, opacity 160ms ease !important;
      padding-top: 0 !important;
      padding-bottom: 0 !important;
      background: #0f0f0f !important;
      /* dark background so white text is visible */
      box-shadow: none !important;
      left: auto !important;
      top: auto !important;
      position: static !important;
      opacity: 1 !important;
      /* override desktop opacity:0 */
      transform: none !important;
      /* override desktop transform */
      z-index: 3001 !important;
      /* ensure submenu above other page elements */
    }

    /* when open, expand fully */
    #topnav-mobile .nav-item.show>.dropdown-menu {
      max-height: 1000px !important;
      padding-top: 8px !important;
      padding-bottom: 8px !important;
    }

    /* submenu items styling — force visible, readable text */
    #topnav-mobile .dropdown-item,
    #topnav-mobile .dropdown-menu a {
      display: block !important;
      padding: 10px 16px !important;
      color: #fff !important;
      font-weight: 500 !important;
      text-decoration: none !important;
      background: transparent !important;
      white-space: normal !important;
    }

    /* nested items indented */
    #topnav-mobile .dropdown-menu .nav-item>.dropdown-item {
      padding-left: 26px !important;
    }

    /* chevron rotate */
    #topnav-mobile .nav-item.dropdown>.nav-link::after {
      content: "▾";
      float: right;
      transform: rotate(0deg);
      transition: transform 160ms ease;
      opacity: 0.7;
      font-size: 12px;
      margin-left: 8px;
      color: rgba(255, 255, 255, 0.7) !important;
    }

    #topnav-mobile .nav-item.show>.nav-link::after {
      transform: rotate(180deg);
    }
  }

  #tabSet{position: relative;margin: 0px 15px;}
  .card-container{margin: 8px 32px;}
  .scroll_tab_inner{border-bottom: 2px solid #f1f5f7;}
  .scroll_tab_inner a{color: #383838 !important;}
  #tabSet .active a{color: #4f46e5 !important;font-weight: 600;}
#tabSet span.active {
  position: relative;
}

#tabSet span::after {
  content: "";
  background: #4f46e5;
  height: 3px;
  position: absolute;
  width: 100%;
  left: 0;
  bottom: -1px;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 250ms ease;
}

/* active state */
#tabSet span.active::after {
  transform: scaleX(1);
}

#topnav-desktop .topnav{background: #fff;}
#topnav-desktop .topnav a,#topnav-desktop .topnav .client-name,body[data-topbar=dark] .noti-icon i{color:#383838!important; }
#topnav-desktop .topnav .navbar-nav .dropdown.active>a{background: #4f46e5;border-radius: 18px;}
#topnav-desktop .topnav .active{color: #fff!important;}

#topnav-desktop .navbar{height: unset!important;}
#topnav-desktop .navbar-nav .nav-link{padding: unset!important;}
#topnav-desktop .nav-item{
  padding: 10px 16px;
  border-radius: 10px;
  cursor: pointer;
  background: #eef1ff;
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
  flex-shrink: 0;
  margin: 10px 8px;
}
#topnav-desktop .nav-item.active{background: #4f46e5;}
@media (min-width: 992px) {
  .page-content{padding-top: 10px!important;}
}
.border{border-color: #0000001a!important;}
.navbar-container{font-family: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"}
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scroll-container {
            position: relative;
            overflow: hidden;
        }
        .scroll-wrapper {
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
            scroll-behavior: smooth;
        }
        .scroll-wrapper::-webkit-scrollbar {
            display: none;
        }
        .scroll-gradient-left,
        .scroll-gradient-right {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 40px;
            pointer-events: none;
            z-index: 10;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .scroll-gradient-left {
            left: 0;
            background: linear-gradient(to right, rgb(249, 250, 251), transparent);
        }
        .scroll-gradient-right {
            right: 0;
            background: linear-gradient(to left, rgb(249, 250, 251), transparent);
        }
        .scroll-gradient-left.visible,
        .scroll-gradient-right.visible {
            opacity: 1;
        }
        .arrow-button {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 20;
            padding: 6px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 9999px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
            opacity: 0;
            pointer-events: none;
            cursor: pointer;
        }
        .arrow-button.visible {
            opacity: 1;
            pointer-events: auto;
        }
        .arrow-button:hover {
            background: #f9fafb;
        }
        .arrow-button-left {
            left: 8px;
        }
        .arrow-button-right {
            right: 8px;
        }
        
        /* Level 3 specific styles */
        .scroll-gradient-left-level3 {
            background: linear-gradient(to right, rgb(255, 255, 255), transparent);
        }
        .scroll-gradient-right-level3 {
            background: linear-gradient(to left, rgb(255, 255, 255), transparent);
        }

        /* Search bar styles */
        .search-kbd {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.25rem;
        }

        /* Auto-scroll animation */
        @keyframes highlight-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .auto-scroll-highlight {
            animation: highlight-pulse 0.3s ease-in-out;
        }
        .settings-menu{display: flex;}
        #mainHeader img{  max-width:100%;height:auto}
        #profile-dropdown-container .text-right p{margin-bottom: 0px;}

        .circle1 {
          width: 40px;
          height: 40px;
          background: #ccc;
          animation: circle1 1s;
          position: relative;
          overflow: hidden;
          border-radius: 50%;
        }

        /* inner circle */
        .circle2 {
          width: 34px;
          height: 34px;
          background: #fff;
          position: absolute;
          border-radius: 50%;
          left: 3px;
          top: 3px;
          animation: circle2 1s;
        }

        /* bottom shape */
        .circle3 {
          width: 60%;
          height: 20px;
          background: #ccc;
          position: absolute;
          border-radius: 50%;
          bottom: 0;
          left: 20%;
          animation: circle3 1s;
        }

        /* top dot */
        .circle4 {
          width: 10px;
          height: 10px;
          background: #ccc;
          position: absolute;
          border-radius: 50%;
          top: 9px;
          left: 50%;
          margin-left: -5px;
          animation: circle4 1s;
        }

        /* highlight */
        .circle5 {
          width: 13px;
          height: 10px;
          background: #fff;
          position: absolute;
          border-radius: 50%;
          top: 13px;
          left: 50%;
          margin-left: -6.5px;
          animation: circle5 1s;
        }

        /* hover */
        .circle1:hover,
        .circle1:hover .circle3,
        .circle1:hover .circle4 {
          background: #10B1ED;
        }

        /* animations */
        @keyframes circle1 {
          0%,10% {width:0;height:0;}
          30%,100% {width:40px;height:40px;}
        }

        @keyframes circle2 {
          0%,30% {width:0;height:0;}
          45%,100% {width:34px;height:34px;}
        }

        @keyframes circle3 {
          0%,45% {height:0;}
          55%,100% {height:20px;}
        }

        @keyframes circle4 {
          0%,75% {width:0;height:0;}
          100% {width:10px;height:10px;}
        }

        @keyframes circle5 {
          0%,85% {width:0;height:0;}
          100% {width:13px;height:10px;}
        }
        .notify-ring{width: .38rem;height: .35rem;left: -2;right: -2;top:0px;right: 2px;}
        .custom-flex-grid{display: flex; justify-content: space-between;}
</style>

<!-- Scripts -->
<script>
  (function () {
    /* -------------------------------
       Scroll Buttons (level 2/3)
    --------------------------------*/
    function initTabScroll(wrapperId, leftBtnId, rightBtnId, leftGradientId, rightGradientId) {
      const wrapper = document.getElementById(wrapperId);
      const btnLeft = document.getElementById(leftBtnId);
      const btnRight = document.getElementById(rightBtnId);
      const gradientLeft = leftGradientId ? document.getElementById(leftGradientId) : null;
      const gradientRight = rightGradientId ? document.getElementById(rightGradientId) : null;
      if (!wrapper || !btnLeft || !btnRight) return;

      function getScrollStep() {
        return Math.max(120, Math.round(wrapper.clientWidth * 0.5));
      }

      function updateScrollUI() {
        const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
        const canScroll = maxScroll > 5;
        const atLeft = wrapper.scrollLeft <= 6;
        const atRight = wrapper.scrollLeft >= (maxScroll - 6);

        const showLeft = canScroll && !atLeft;
        const showRight = canScroll && !atRight;

        btnLeft.classList.toggle('visible', showLeft);
        btnRight.classList.toggle('visible', showRight);
        btnLeft.style.display = canScroll ? 'flex' : 'none';
        btnRight.style.display = canScroll ? 'flex' : 'none';

        if (gradientLeft) gradientLeft.classList.toggle('visible', showLeft);
        if (gradientRight) gradientRight.classList.toggle('visible', showRight);
      }

      btnLeft.addEventListener('click', function () {
        wrapper.scrollBy({ left: -getScrollStep(), behavior: 'smooth' });
      });
      btnRight.addEventListener('click', function () {
        wrapper.scrollBy({ left: getScrollStep(), behavior: 'smooth' });
      });

      // Mouse wheel -> horizontal scroll
      wrapper.addEventListener('wheel', function (e) {
        if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
          e.preventDefault();
          wrapper.scrollBy({ left: e.deltaY, behavior: 'auto' });
        }
      }, { passive: false });

      // Mouse drag scroll
      let isDragging = false;
      let startX = 0;
      let startScrollLeft = 0;

      wrapper.addEventListener('mousedown', function (e) {
        if (e.button !== 0) return;
        isDragging = true;
        startX = e.pageX;
        startScrollLeft = wrapper.scrollLeft;
        wrapper.classList.add('dragging');
      });

      window.addEventListener('mousemove', function (e) {
        if (!isDragging) return;
        const delta = e.pageX - startX;
        wrapper.scrollLeft = startScrollLeft - delta;
      });

      window.addEventListener('mouseup', function () {
        if (!isDragging) return;
        isDragging = false;
        wrapper.classList.remove('dragging');
      });

      wrapper.addEventListener('scroll', updateScrollUI);
      window.addEventListener('resize', updateScrollUI);
      window.addEventListener('load', updateScrollUI);
      setTimeout(updateScrollUI, 120);
      setTimeout(updateScrollUI, 350);

      // Ensure active tab is visible on first load.
      function scrollActiveIntoView() {
        const active = wrapper.querySelector('.menu-tab.is-active, .active');
        if (active && typeof active.scrollIntoView === 'function') {
          active.scrollIntoView({ behavior: 'auto', inline: 'center', block: 'nearest' });
        }
        updateScrollUI();
      }

      window.addEventListener('load', scrollActiveIntoView);
      setTimeout(scrollActiveIntoView, 60);
      setTimeout(scrollActiveIntoView, 250);
    }

    initTabScroll('level1-tabs', 'scroll-left-btn-level1', 'scroll-right-btn-level1', 'scroll-gradient-left-level1', 'scroll-gradient-right-level1');
    initTabScroll('level2-tabs', 'scroll-left-btn-level2', 'scroll-right-btn-level2', 'scroll-gradient-left-level2', 'scroll-gradient-right-level2');
    initTabScroll('level3-wrapper', 'scroll-left-btn-level3', 'scroll-right-btn-level3', 'scroll-gradient-left-level3', 'scroll-gradient-right-level3');

    /* -------------------------------
       Dropdown Behavior (responsive)
    --------------------------------*/

    // throttle utility (used in dropdown repositioning)
    function throttle(fn, wait) {
      let last = 0, timer = null;
      return function (...args) {
        const now = Date.now();
        const remaining = wait - (now - last);
        if (remaining <= 0) {
          if (timer) { clearTimeout(timer); timer = null; }
          last = now;
          fn.apply(this, args);
        } else if (!timer) {
          timer = setTimeout(() => {
            last = Date.now();
            timer = null;
            fn.apply(this, args);
          }, remaining);
        }
      };
    }

    /* Desktop Dropdowns */
    function initDesktopDropdowns() {
      const dropdowns = document.querySelectorAll('#topnav-desktop .nav-item.dropdown');
      dropdowns.forEach(dropdown => {
        if (dropdown.__desktopActive) return;

        const link = dropdown.querySelector('.nav-link');
        const menu = dropdown.querySelector('.dropdown-menu');
        if (!link || !menu) return;

        function showMenu() {
          const rect = link.getBoundingClientRect();
          const menuWidth = Math.max(menu.offsetWidth, 180);
          let left = rect.left;
          const viewportRight = window.innerWidth - 8;
          if (left + menuWidth > viewportRight) left = Math.max(8, viewportRight - menuWidth);
          left = Math.max(8, left);
          menu.style.left = left + 'px';
          menu.style.top = rect.bottom + 6 + 'px';
          menu.classList.add('visible');
        }

        function hideMenu() {
          setTimeout(() => {
            if (!menu.matches(':hover') && document.activeElement !== link && !menu.contains(document.activeElement)) {
              menu.classList.remove('visible');
            }
          }, 120);
        }

        const reposition = throttle(() => {
          if (menu.classList.contains('visible')) showMenu();
        }, 120);

        link.addEventListener('mouseenter', showMenu);
        link.addEventListener('focus', showMenu);
        dropdown.addEventListener('mouseleave', hideMenu);
        link.addEventListener('blur', hideMenu);
        menu.addEventListener('mouseenter', () => menu.classList.add('visible'));
        menu.addEventListener('mouseleave', hideMenu);
        window.addEventListener('resize', reposition);
        window.addEventListener('scroll', reposition, true);

        dropdown.__desktopActive = true;
        dropdown.__desktopCleanup = () => {
          link.removeEventListener('mouseenter', showMenu);
          link.removeEventListener('focus', showMenu);
          link.removeEventListener('blur', hideMenu);
          dropdown.removeEventListener('mouseleave', hideMenu);
          menu.removeEventListener('mouseenter', () => menu.classList.add('visible'));
          menu.removeEventListener('mouseleave', hideMenu);
          window.removeEventListener('resize', reposition);
          window.removeEventListener('scroll', reposition, true);
          menu.classList.remove('visible');
        };
      });
    }

    function destroyDesktopDropdowns() {
      document.querySelectorAll('#topnav-desktop .nav-item.dropdown').forEach(dropdown => {
        if (dropdown.__desktopActive && dropdown.__desktopCleanup) {
          dropdown.__desktopCleanup();
          dropdown.__desktopActive = false;
        }
      });
    }

    /* Mobile Dropdowns */

    function initMobileDropdowns() {
      const dropdowns = document.querySelectorAll('#topnav-mobile .nav-item.dropdown');
      dropdowns.forEach(dropdown => {
        if (dropdown.__mobileActive) return;
        const link = dropdown.querySelector('.nav-link');
        const submenu = dropdown.querySelector('.dropdown-menu');
        if (!link || !submenu) return;

        function toggleDropdown(e) {
          const href = link.getAttribute('href') || '';
          // prevent navigation if the item has a submenu
          if (submenu) e.preventDefault();

          // toggle this submenu
          const isOpen = dropdown.classList.toggle('show');
          submenu.style.display = isOpen ? 'block' : 'none';

          // close other open dropdowns
          dropdown.parentElement.querySelectorAll('.nav-item.dropdown').forEach(sib => {
            if (sib !== dropdown) {
              sib.classList.remove('show');
              const sibMenu = sib.querySelector('.dropdown-menu');
              if (sibMenu) sibMenu.style.display = 'none';
            }
          });
        }

        link.addEventListener('click', toggleDropdown);
        dropdown.__mobileActive = true;
        dropdown.__mobileCleanup = () => {
          link.removeEventListener('click', toggleDropdown);
          dropdown.classList.remove('show');
          submenu.style.display = 'none';
        };
      });
    }

    function destroyMobileDropdowns() {
      document.querySelectorAll('#topnav-mobile .nav-item.dropdown').forEach(dropdown => {
        if (dropdown.__mobileActive && dropdown.__mobileCleanup) {
          dropdown.__mobileCleanup();
          dropdown.__mobileActive = false;
        }
      });
    }

    /* Responsive switcher */
    const mq = window.matchMedia('(min-width: 992px)');
    function handleViewChange(e) {
      if (e.matches) {
        destroyMobileDropdowns();
        initDesktopDropdowns();
      } else {
        destroyDesktopDropdowns();
        initMobileDropdowns();
      }
    }
    handleViewChange(mq);
    mq.addEventListener('change', handleViewChange);

    /* -------------------------------
       Fullscreen Toggle (existing)
    --------------------------------*/
    const fullscreenBtn = document.getElementById('btnFullscreen');
    if (fullscreenBtn) {
      fullscreenBtn.addEventListener('click', function () {
        if (!document.fullscreenElement) {
          document.documentElement.requestFullscreen?.().catch(err => console.error(err));
        } else {
          document.exitFullscreen?.();
        }
      });
    }
  })();

// Toggle Profile Menu
function toggleProfileMenu() {
    const menu = document.getElementById('profile-menu');
    menu.classList.toggle('tw-hidden');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const container = document.getElementById('profile-dropdown-container');
    const menu = document.getElementById('profile-menu');
    if (container && !container.contains(event.target)) {
        menu.classList.add('tw-hidden');
    }
});



// document.querySelectorAll('.module-tab').forEach(button => {
//     button.addEventListener('click', function () {
//         let selectedModule = this.dataset.module;
//         let rootUrl = this.dataset.url;
//         let baseUrl = "<?php echo WORKING_URL; ?>";
//         fetch('http://168.231.102.34/v3/SAdmin/Ajax/AjaxProcessMenu', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: 'field=' + encodeURIComponent('Module') + 
//                   '&value=' + encodeURIComponent(selectedModule)
//         })
//         .then(response => response.json()) 
//         .then(data => {
//             location.href = baseUrl+data.url;
//         });
//     });
// });

// document.querySelectorAll('.menu-tab').forEach(button => {
//     button.addEventListener('click', function () {
//         let selectedModule = this.dataset.menu;
//         let MenuUrl = this.dataset.url;
//         fetch('http://168.231.102.34/v3/SAdmin/Ajax/AjaxProcessMenu', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: 'field=' + encodeURIComponent('MenuID') + 
//                   '&value=' + encodeURIComponent(selectedModule) +
//                   '&url=' + encodeURIComponent(MenuUrl)
//         })
//         .then(response => response.json()) 
//         .then(data => {
//             location.href = data.url;
//         });
//     });
// });


// document.querySelectorAll('.submenu-tab').forEach(button => {
//     button.addEventListener('click', function () {
//         let selectedSubMenu = this.dataset.submenu;
//         let SubMenuUrl = this.dataset.url;
//         let baseUrl = "<?php echo WORKING_URL; ?>";
//         fetch('http://168.231.102.34/v3/SAdmin/Ajax/AjaxProcessMenu', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: 'field=' + encodeURIComponent('SubMenuID') + 
//                   '&value=' + encodeURIComponent(selectedSubMenu) +
//                   '&url=' + encodeURIComponent(SubMenuUrl)
//         })
//         .then(response => response.json()) 
//         .then(data => {
//             location.href = data.url;
//         });
//     });
// });
//     var static_menu = <?php echo json_encode($_SESSION['static_menu']); ?>;
//     var static_menu_act = <?php echo json_encode($_SESSION['static_menu_act']); ?>;
//     var static_sub_menu = <?php echo json_encode($_SESSION['static_sub_menu']); ?>;
//     var static_menu_dir = <?php echo json_encode($_SESSION['static_menu_directory']); ?>;
//     var currentDir = "<?php echo $folderName; ?>";
//     var currentUrlFile = "<?php echo $currentUrlFile; ?>";
//     if(static_menu!=null){
//         var ModuleTab = document.querySelector('[data-act-module="' + currentDir + '"]');

//         if (ModuleTab) {
//             ModuleTab.href = "/v3/" + currentDir + "/index";
//             ModuleTab.classList.remove(
//                 'bg-white',
//                 'text-gray-600',
//                 'border',
//                 'border-gray-200'
//             );

//             ModuleTab.classList.add(
//                 'active',
//                 'module-tab',
//                 'flex',
//                 'items-center',
//                 'gap-2',
//                 'px-4',
//                 'py-2',
//                 'rounded-lg',
//                 'font-medium',
//                 'transition-all',
//                 'bg-gradient-to-r',
//                 'from-blue-500',
//                 'to-cyan-400',
//                 'text-white',
//                 'shadow-lg',
//                 'shadow-blue-500/25'
//             );
//         }
//         if(currentDir == static_menu_dir){
//             if(currentUrlFile == 'index'){
//                 document.getElementById("level2-tabs").insertAdjacentHTML("afterbegin", static_menu_act);
//                 document.getElementById("level3-tabs").insertAdjacentHTML("afterbegin", static_sub_menu);
//             }else{
//                 document.getElementById("level2-tabs").insertAdjacentHTML("afterbegin", static_menu);
//             }                        
//         }
//     }

</script>
