<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include_once __DIR__.'/../config/Dotenv.php';
$DB_HOST = (getenv('DB_HOST')) ? getenv('DB_HOST') : $_ENV['DB_HOST'];
$DB_NAME = (getenv('DB_NAME')) ? getenv('DB_NAME') : $_ENV['DB_NAME'];
$DB_USER = (getenv('DB_USER')) ? getenv('DB_USER') : $_ENV['DB_USER'];
$DB_PASSWORD = (getenv('DB_PASSWORD')) ? getenv('DB_PASSWORD') : $_ENV['DB_PASSWORD'];
$admin_db_conn = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);
$PageTitleFolder = "Master";
$PageTitle = "Masters List";

$HeaderIDs = $_SESSION["MenuList"]->HeaderIDs;
$OptionIDs = $_SESSION["MenuList"]->OptionIDs;

$_SESSION['SelectedMenu'] = [
    "Sid" => 0,
    "Header" => 'Masters List',
    "Url" => '/v3/includes/masters-list'
    ];
$_SESSION['SelectedSubMenu'] = [
    "ID" => 0,
    "Name" => 'Masters List',
    "Url" => '/v3/includes/masters-list'
];

$SqlMenu = "SELECT Headers.Sid,Headers.Header,Options.ID,Options.Directory,Options.Name,Options.FileName,Options.Module,Module.MenuIcon FROM `ClientSideBarOptions` Options LEFT JOIN ClientSideBarHeaders Headers ON Options.HeaderId=Headers.Sid LEFT JOIN ClientSideBarModules Module ON Options.Module=Module.Name WHERE Headers.Sid IN ( $HeaderIDs ) AND ID IN ( $OptionIDs ) AND Headers.Header IN ('Master','Cs Master') ORDER BY Headers.SortOrder ASC, Options.SortOrder ASC";
$StmtMenu = $admin_db_conn->query($SqlMenu);
$masterListAry = [];

if ($StmtMenu->num_rows > 0) {
    while ($row = $StmtMenu->fetch_assoc()) {

        $module = $row['Module'];
        $logo   = $row['MenuIcon'];
        $header = $row['Header'];

        // Create module if not exists
        if (!isset($masterListAry[$module])) {
            $masterListAry[$module] = [
                'MenuIcon' => $logo
            ];
        }

        // Create header if not exists
        if (!isset($masterListAry[$module]['headers'][$header])) {
            $masterListAry[$module]['headers'][$header] = [];
        }

        // Add menu items
        $masterListAry[$module]['headers'][$header][] = [
            'ID'        => $row['ID'],
            'Name'      => $row['Name'],
            'Directory' => $row['Directory'],
            'FileName'  => $row['FileName']
        ];
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>

	<?php include($IncludesPath . "title-meta.php"); ?>
	<?php include($IncludesPath . "DbLinks.php"); ?>
	<?php include($IncludesPath . "head-css.php"); ?>
	<style>
		select.error+.select2-container,
		input.error,
		textarea.error {
			border: 1px solid red;
		}
	.navbar-menu-container{display: none;}
	.master-list-container .list-view{padding: 10px 0px;}
	.master-list-container .list-view p{display: flex;font-weight: 600;line-height: 2;}	
	a{color: inherit;}
    /* Breadcrumb Styles */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .breadcrumb-separator {
        width: 16px;
        height: 16px;
        color: #d1d5db;
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
        background-color: #eff6ff;
        color: #2563eb;
        font-weight: 500;
    }
	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
	  font-size:inherit;
	  font-weight:inherit
	}
	</style>

</head>

<body data-sidebar="dark">


	<?php
	include($IncludesPath . "PreLoader.php"); ?>

	<!-- Begin page -->
	<div id="layout-wrapper">


		<?php
		include($IncludesPath . "topbar.php");
		include($IncludesPath . "sidebar.php");
		?>



		<!-- ============================================================== -->
		<!-- Start right Content here -->
		<!-- ============================================================== -->
		<div class="main-content">

<div class="page-content">
    <div class="container-fluid">
        <?php if ($Status != '') { //Show Alert  
        ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-all mr-2"></i>
                <?php
                $_SESSION['Status'] = '';
                ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php } ?>
        <?php //include($IncludesPath . "page-title.php"); ?>

        <div>
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-gray-900 mb-2">Masters List</h1>
                <p class="text-gray-600">Manage all master data and configurations</p>
            </div>

		<!-- Cards Grid -->
		<div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

		<?php foreach ($masterListAry as $module => $moduleData) { ?>

		    <?php 
		        $icon = $moduleData['MenuIcon'];
		        $headers = $moduleData['headers'];
		    ?>

		    <!-- Card -->
		    <div class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">

		        <!-- Card Header -->
		        <div class="flex items-start gap-3 mb-4">
		            <div class="bg-blue-50 rounded-lg p-2.5">
		                <?php echo $icon; ?>
		            </div>

		            <div class="flex-1">
		                <h3 class="font-semibold text-gray-900 mb-1">
		                    <?php echo $module; ?>
		                </h3>
		                <p class="text-sm text-gray-500">
		                    Manage <?php echo strtolower($module); ?> related masters
		                </p>
		            </div>
		        </div>

		        <?php foreach ($headers as $header => $items) { ?>

		            <!-- Items -->
		            <div class="space-y-2.5 mb-4">
		                <?php foreach ($items as $item) { ?>
		                    <div class="text-sm text-gray-700 hover:text-blue-600 cursor-pointer transition-colors">
		                        <a href="<?php echo WORKING_URL.$item['Directory'].$item['FileName']; ?>">
		                            <?php echo $item['Name']; ?>
		                        </a>
		                    </div>
		                <?php } ?>
		            </div>

		        <?php } ?>

		    </div>

		<?php } ?>

		</div>

        </div>
    </div><!-- end col-->
</div>
			<!-- end row-->

		</div> <!-- container-fluid -->
	</div>
	<br/>
	<br/>
	<br/>
	<!-- End Page-content -->

	<?php include($IncludesPath . "footer.php"); ?>

	</div>
	<!-- end main content-->

	</div>
	<!-- END layout-wrapper -->



	<?php include($IncludesPath . "vendor-scripts.php"); ?>
	<?php include($IncludesPath . "NewScripts.php"); ?>
</body>

</html>