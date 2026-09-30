    <div id="mainHeader" class="bg-white border-b border-gray-200 transition-all duration-300 header-visible">
      <div class="px-4 py-2">
        <div class="flex items-center justify-between gap-4">
          <!-- Logo Section - Compact -->
          <a href="<?php echo $_SESSION["ClientPath"]; ?>" style="color:unset;">
          <div class="flex items-center gap-2">
            <?php echo $ClientLogo; ?>
            <div class="tw-hidden lg:block">
              <div class="font-bold text-sm"><?php echo isset($_SESSION["ClientName"]) ? htmlspecialchars($_SESSION["ClientName"]) : "Client"; ?></div>
              <div class="text-gray-500 leading-tight" style="font-size: 10px;">Powered By Camaroo</div>
            </div>
          </div>
          </a>

          <!-- Search Bar -->
          <div class="flex-1 max-w-2xl" style="display:none;">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.3-4.3"></path>
              </svg>
              <input type="text" placeholder="Search..." class="w-full pl-10 pr-14 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:bg-white">
              <kbd class="absolute right-3 top-1/2 -translate-y-1/2 px-2 py-0.5 bg-white border border-gray-300 rounded text-xs text-gray-500 font-sans">
                ⌘K
              </kbd>
            </div>
          </div>
          <!-- User Section -->
          <div class="flex items-center gap-3">
            <?php if($_SESSION['ClientID']!='serp' && $isMastersExist){
            ?>
               <a href="/v3/includes/masters-list.php">
                <div class="settings-menu items-center text-xs text-gray-400 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200 tw-hidden md:block">
                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600">
                       <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                       <circle cx="12" cy="12" r="3"></circle>
                     </svg>
                     <span class="text-sm text-gray-700 tw-hidden lg:inline pl-1">Settings</span>
                </div>
                </a>
            <?php   
            }
            ?>
            <?php
            if($_SESSION['ClientConfigData']['notification_panel']){
                if( isset($_SESSION['ClientConfigData']['notification_panel_accessuserlist']) && $_SESSION['ClientConfigData']['notification_panel_accessuserlist'] !=''){
                    $notification_panel_accessuserlist = $_SESSION['ClientConfigData']['notification_panel_accessuserlist'];
                    // 1. Strip out pesky backslashes if magic quotes or escaping happened
                    $clean_json = stripslashes($notification_panel_accessuserlist);

                    // 2. Remove UTF-8 non-breaking spaces (C2 A0) and other hidden whitespace
                    $clean_json = preg_replace('/^\xEF\xBB\xBF/', '', $clean_json); // Remove BOM if present
                    $clean_json = str_replace(array("\xc2\xa0", "\xa0"), ' ', $clean_json); // Convert non-breaking spaces to regular spaces
                    $clean_json = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $clean_json); // Clean control characters

                    // 3. Decode to an associative array
                    $required_users = json_decode($clean_json, true);
                    if (is_array($required_users) && in_array($_SESSION['UserID'], $required_users)) {
                ?>
                <div class="settings-menu items-center text-xs text-gray-400 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200 tw-hidden md:block" id="btnNotifyWeb" aria-label="Notifications">
                   <button class="text-gray-900 hover:text-white transition-colors relative">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell">
                         <path d="M10.268 21a2 2 0 0 0 3.464 0"></path>
                         <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"></path>
                      </svg>
                      <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full notify-ring"></span>
                   </button>
                </div>
            <?php } } } ?>
               <div class="relative" id="profile-dropdown-container">
                  <div class="flex items-center gap-3 pl-6 border-l border-gray-100 cursor-pointer" onclick="toggleProfileMenu()">
                     <div class="text-right tw-hidden sm:block">
                        <p class="text-sm font-medium text-gray-900"><?php echo isset($_SESSION["UserName"]) ? htmlspecialchars($_SESSION["UserName"]) : 'User' ?></p>
                        <p class="text-xs text-gray-500"><?php echo $_SESSION["Designation"];?></p>
                     </div>
                    <div style="width: 40px;height: 40px;">
                        <div class="circle1">
                            <div class="circle2"> </div>
                            <div class="circle3"> </div>
                            <div class="circle5"> </div>
                            <div class="circle4"> </div>
                        </div>
                    </div>
                     <!--<button class="relative focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-full transition-transform active:scale-95">
                        <img class="h-10 w-10 rounded-full object-cover border border-gray-200 shadow-sm" src="https://developer-tools.miledeepworks.in/assets/user-profile.png?w=100&amp;h=100&amp;fit=crop" width="80" alt="Profile">
                        <div class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full bg-green-500 border-2 border-white"></div>
                     </button> -->
                  </div>
                  <!-- Dropdown Menu -->
                  <div id="profile-menu" class="tw-hidden absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transform origin-top-right transition-all">
                     <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        <div class="px-4 py-3 border-b border-gray-100 sm:tw-hidden">
                           <p class="text-sm font-medium text-gray-900"><?php echo isset($_SESSION["UserName"]) ? htmlspecialchars($_SESSION["UserName"]) : 'User' ?></p>
                           <p class="text-xs text-gray-500">Super Admin</p>
                        </div>
                       <?php if (isset($_SESSION["AccessType"]) && $_SESSION["AccessType"] == "1" && $_SESSION["ClientID"] != "SLS2300011") { ?>
                           <a href="<?php echo $host ?>/v3/Users/GetUsersList"
                              class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                              role="menuitem">
                              <!-- Users Icon -->
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                   viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                   class="lucide lucide-users mr-3 h-4 w-4 text-gray-400">
                                 <path d="M16 21v-2a4 4 0 0 0-3-3.87"></path>
                                 <path d="M7 21v-2a4 4 0 0 1 3-3.87"></path>
                                 <circle cx="12" cy="7" r="4"></circle>
                                 <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                 <path d="M2 21v-2a4 4 0 0 1 3-3.87"></path>
                              </svg>
                              User List
                           </a>

                           <a href="<?php echo $host ?>/v3/Users/get-access-roles"
                              class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                              role="menuitem">
                              <!-- Shield Icon -->
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                   viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                   class="lucide lucide-shield mr-3 h-4 w-4 text-gray-400">
                                 <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                              </svg>
                              Access Roles
                           </a>

                           <a href="<?php echo $host ?>/v3/Users/get-access-urls"
                              class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors"
                              role="menuitem">
                              <!-- Link Icon -->
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                   viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                   stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                   class="lucide lucide-link mr-3 h-4 w-4 text-gray-400">
                                 <path d="M10 13a5 5 0 0 0 7.54.54l1.92-1.92a5 5 0 0 0-7.07-7.07L10.47 6.47"></path>
                                 <path d="M14 11a5 5 0 0 0-7.54-.54L4.54 12.38a5 5 0 1 0 7.07 7.07l1.92-1.92"></path>
                              </svg>
                              Access Urls
                           </a>
                       <?php } ?>
                        <a href="/ChangePassword" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors" role="menuitem">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="key" aria-hidden="true" class="lucide lucide-key mr-3 h-4 w-4 text-gray-400">
                              <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"></path>
                              <path d="m21 2-9.6 9.6"></path>
                              <circle cx="7.5" cy="15.5" r="5.5"></circle>
                           </svg>
                           Change Password
                        </a>
                        <a href="/auth-login" class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors" role="menuitem">
                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="log-out" aria-hidden="true" class="lucide lucide-log-out mr-3 h-4 w-4 text-red-400">
                              <path d="m16 17 5-5-5-5"></path>
                              <path d="M21 12H9"></path>
                              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                           </svg>
                           Logout
                        </a>
                     </div>
                  </div>
               </div>
          </div>
        </div>
      </div>
  </div>
