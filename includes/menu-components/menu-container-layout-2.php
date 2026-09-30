<div class="custom-flex-grid">
	<div style="flex:1;min-width:0;background: #fff;">
            <div class="px-6 py-2">
                <!-- Level 1: Main Module Tabs -->
                <div id="level1-container" class="scroll-container">
                    <button id="scroll-left-btn-level1" class="arrow-button arrow-button-left">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-left" aria-hidden="true" class="lucide lucide-chevron-left w-4 h-4 text-gray-600">
                           <path d="m15 18-6-6 6-6"></path>
                        </svg>
                    </button>
                    <div id="level1-tabs" class="flex items-center gap-2 scroll-wrapper">

                        <?php echo (isset($_SESSION['default_dashboard']) ) ? $_SESSION['default_dashboard']: ''; ?>
                        <?php foreach($modulesArray as $module => $moduleData){ 
                            if(!empty($moduleData['Module']['Name'])){

                                if($activeModule === $moduleData['Module']['Name']){
                                    $isModuleActive = 'active module-tab flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all bg-gradient-to-r from-blue-500 to-cyan-400 text-white shadow-lg shadow-blue-500/25';
                                    $_SESSION['SelectedModule'] = [
                                            'Name' => $moduleData['Module']['Name'],
                                            "Url" => WORKING_URL . $moduleData['Module']['Directory'] . $moduleData['Module']['FileName'] 
                                        ];
                                }else{
                                    $isModuleActive = 'module-tab flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all text-sm bg-white text-gray-600 hover:bg-gray-50 border border-gray-200';
                                }

                        ?>

                            <a href="<?php echo WORKING_URL . $moduleData['Module']['Directory'] . $moduleData['Module']['FileName']; ?>" class="<?php echo $isModuleActive; ?>"
                               data-module="<?= $moduleData['Module']['Name'] ?>"
                               data-act-module="<?= ( $folderName."/" == $moduleData['Module']['Directory'] ) ? $folderName : '' ?>"
                               data-url="<?php echo WORKING_URL . $moduleData['Module']['Directory'] . $moduleData['Module']['FileName']; ?>">

                                <i><?php echo isset($moduleData['Module']['Icon']) ? $moduleData['Module']['Icon'] : ""; ?></i>
                                <span><?php echo $moduleData['Module']['Name']; ?></span>

                            </a>

                        <?php 
                            } 
                        } ?>

                    </div>
                    <div class="scroll-gradient-right" id="scroll-gradient-right-level1"></div>
                    <button id="scroll-right-btn-level1" class="arrow-button arrow-button-right">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="chevron-right" aria-hidden="true" class="lucide lucide-chevron-right w-4 h-4 text-gray-600">
                           <path d="m9 18 6-6-6-6"></path>
                        </svg>
                    </button>
                </div>
            </div>		
	</div>
	<div style="display:flex;flex-shrink:0;">
		<?php include 'main-header-bar-layout-2.php' ?>
	</div>
</div>