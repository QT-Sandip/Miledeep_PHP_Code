<?php

include_once '../config/global.php';
include_once '../config/api-call.php';

$api = API_MENU;


$resList = CallAPI($api, "GET", "", "");

// var_dump($resList);

$List = $resList->Data;

// $EditLink = $resList->EditLink;

// $error = $resList->Error;

// $list = $resList->Data;

?>

<div class="topnav">
    <div class="container-fluid">
        <nav class="navbar navbar-light navbar-expand-lg topnav-menu">
            <button id="btnMobileMenu"> <i class="ri-menu-2-line align-middle"></i></button>
            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav">

                    <!-- <li class="nav-item">
                        <a class="nav-link" href="index.html">
                            <i class="ri-dashboard-line me-2"></i> Dashboard
                        </a>
                    </li> -->
                    <?php foreach($List as $item) { ?>

                        <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-apps" role="button">
                            <i class="<?php echo $item->MenuIcon ?> me-2"></i><?php echo $item->MenuName ?> <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-apps">
                            <?php foreach($item->SubMenu as $submenu) { ?>
                                <a href="<?php echo $submenu->PathUrl ?>" data-loc="<?php echo $submenu->Location ?>" class="dropdown-item"><?php echo $submenu->Name ?></a>
                            <?php } ?>
                        </div>
                    </li>

                    <?php } ?>
                    
                    <!--
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-apps" role="button">
                            <i class="ri-apps-2-line me-2"></i>Apps <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-apps">

                            <a href="calendar.html" class="dropdown-item">Calendar</a>
                            <a href="apps-chat.html" class="dropdown-item">Chat</a>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-email"
                                    role="button">
                                    Email <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-email">
                                    <a href="email-inbox.html" class="dropdown-item">Inbox</a>
                                    <a href="email-read.html" class="dropdown-item">Read Email</a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a class="dropdown-item dropdown-toggle arrow-none" href="#" id="topnav-ecommerce"
                                    role="button">
                                    Ecommerce <div class="arrow-down"></div>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="topnav-ecommerce">
                                    <a href="ecommerce-products.html" class="dropdown-item">Products</a>
                                    <a href="ecommerce-product-detail.html" class="dropdown-item">Product Detail</a>
                                    <a href="ecommerce-orders.html" class="dropdown-item">Orders</a>
                                    <a href="ecommerce-customers.html" class="dropdown-item">Customers</a>
                                    <a href="ecommerce-cart.html" class="dropdown-item">Cart</a>
                                    <a href="ecommerce-checkout.html" class="dropdown-item">Checkout</a>
                                    <a href="ecommerce-shops.html" class="dropdown-item">Shops</a>
                                    <a href="ecommerce-add-product.html" class="dropdown-item">Add Product</a>
                                </div>
                            </div>

                            <a href="apps-kanban-board.html" class="dropdown-item">Kanban Board</a>
                        </div>
                    </li> -->

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="topnav-apps" role="button">
                            <i class="ri-user-line me-2"></i>My Account <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="topnav-apps">

                            <a href="calendar.html" class="dropdown-item">Change Password</a>
                            <a href="../logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </li>

                </ul>


                <button type="button" class="btn header-item noti-icon waves-effect fullScreen mr-5 client-name" data-toggle="fullscreen">
                    MY CLIENT NAME
                </button>
                <button type="button" class="btn header-item noti-icon waves-effect fullScreen" data-toggle="fullscreen">
                    <i class="ri-fullscreen-line"  style="color: #FFFFFF"></i>
                </button>

            </div>
        </nav>
    </div>
</div>