<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

?>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
            <button id="btnMobileMenu"> <i class="ri-menu-2-line align-middle"></i></button>
            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">

                    <?php
                    if (isset($_SESSION["MenuList"]) && !empty($_SESSION["MenuList"])) {
                        foreach ($_SESSION["MenuList"] as $item) {
                            ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle arrow-none" href="#"
                                    id="topnav-<?php echo $item->MenuName ?>" role="button">
                                    <i class="<?php echo $item->MenuIcon ?> me-2"></i><?php echo $item->MenuName ?>
                                    <div class="arrow-down"></div>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="topnav-<?php echo $item->MenuName ?>">
                                    <?php foreach ($item->SubMenu as $submenu) { ?>
                                        <a href="<?php echo WORKING_URL . $submenu->PathUrl ?>"
                                            data-loc="<?php echo $submenu->Location ?>" data-menu="<?php echo $item->MenuName ?>"
                                            class="dropdown-item"><?php echo $submenu->Name ?></a>
                                    <?php } ?>
                                </ul>
                            </li>
                        <?php }
                    } ?>

                    <?php if ($_SESSION["AccessType"] == "1" && $_SESSION["ClientID"] != "SLS2300011") { // SLS230001 > Camaro ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-Users" role="button">
                                <i class="ri-shield-user-line me-2"></i>Users <div class="arrow-down"></div>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="topnav-Users">
                                <a href="<?php echo $host ?>v3/Users/GetUsersList" data-loc="Exports" data-menu="Users"
                                    class="dropdown-item">User List</a>
                                <a href="<?php echo $host ?>v3/Users/get-access-roles" data-loc="Exports" data-menu="Users"
                                    class="dropdown-item">Access Roles</a>
                            </ul>
                        </li>
                    <?php } ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-Account" role="button">
                            <i class="ri-user-line me-2"></i><?php echo $_SESSION["UserName"] ?>
                            <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-Account">

                            <a target="_blank" href="<?php echo $host ?>ChangePassword" class="dropdown-item">Change
                                Password</a>
                            <a href="/auth-login" class="dropdown-item">Logout</a>
                        </div>
                    </li>
                </ul>


                <div style="position: absolute;right: 0px;display: inline-block;">
                    <?php
                    if ($_SESSION["DeptID"] == 9 && $_SESSION["AccessType"] != '3') {
                        ?>
                        <button type="button" class="btn header-item noti-icon waves-effect fullScreen" id="btnNotify"
                            style="position: relative;">
                            <!-- <i class="ri-notification-4-line" style="color: #FFFFFF; font-size: 18px;"></i>                     -->
                            <i class="ri-notification-3-line"></i>
                            <span class="noti-dot"></span>
                        </button>
                        <?php
                    }
                    ?>
                    <!-- <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item noti-icon" id="page-header-notifications-dropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-notification-3-line"></i>
                            <span class="noti-dot"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                            aria-labelledby="page-header-notifications-dropdown" style="left:initial;right: 0px;">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0"> Notifications </h6>
                                    </div>
                                    <div class="col-auto">
                                        <a href="#!" class="small"> View All</a>
                                    </div>
                                </div>
                            </div>
                            <div data-simplebar style="max-height: 230px;padding:15px;">
                                <a href="" class="text-reset notification-item">
                                    <div class="d-flex" style="padding:5px 4px">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-primary rounded-circle font-size-16">
                                                <i class="ri-shopping-cart-line"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">Your order is placed</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">If several languages coalesce the grammar</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 3 min ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="" class="text-reset notification-item">
                                    <div class="d-flex" style="padding:5px 4px">
                                        <img src="assets/images/users/avatar-3.jpg"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-1">
                                            <h6 class="mb-1">James Lemire</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">It will seem like simplified English.</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 1 hours ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <a href="" class="text-reset notification-item">
                                    <div class="d-flex" style="padding:5px 4px">
                                        <div class="avatar-xs me-3">
                                            <span class="avatar-title bg-success rounded-circle font-size-16">
                                                <i class="ri-checkbox-circle-line"></i>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-1">Your item is shipped</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">If several languages coalesce the grammar</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 3 min ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                <a href="" class="text-reset notification-item">
                                    <div class="d-flex" style="padding:5px 4px">
                                        <img src="assets/images/users/avatar-4.jpg"
                                            class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                        <div class="flex-1">
                                            <h6 class="mb-1">Salena Layfield</h6>
                                            <div class="font-size-12 text-muted">
                                                <p class="mb-1">As a skeptical Cambridge friend of mine occidental.</p>
                                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> 1 hours ago</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2 border-top">
                                <div class="d-grid">
                                    <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                                        <i class="mdi mdi-arrow-right-circle me-1"></i> View More..
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <button type="button" class="btn header-item noti-icon waves-effect fullScreen client-name"
                        data-toggle="fullscreen" style="position: relative;">
                        <?php echo $_SESSION["ClientName"] ?>
                    </button>
                    <button type="button" class="btn header-item noti-icon waves-effect fullScreen"
                        style="position: relative;" data-toggle="fullscreen">
                        <i class="ri-fullscreen-line" style="color: #FFFFFF"></i>
                    </button>
                </div>

            </div>
        </nav>
    </div>
</div>