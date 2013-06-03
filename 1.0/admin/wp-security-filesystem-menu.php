<?php

class AIOWPSecurity_Filescan_Menu extends AIOWPSecurity_Admin_Menu
{
    var $menu_page_slug = AIOWPSEC_FILESYSTEM_MENU_SLUG;
    
    /* Specify all the tabs of this menu in the following array */
    var $menu_tabs = array(
        'tab1' => 'File Permissions', 
        'tab2' => 'PHP File Editing',
        'tab3' => 'Host System Logs',
        );

    var $menu_tabs_handler = array(
        'tab1' => 'render_tab1', 
        'tab2' => 'render_tab2',
        'tab3' => 'render_tab3',
        );
    
    function __construct() 
    {
        $this->render_menu_page();
        add_action( 'admin_footer', array( &$this, 'filesystem_menu_footer_code' ) );
    }
    
    function get_current_tab() 
    {
        $tab_keys = array_keys($this->menu_tabs);
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $tab_keys[0];
        return $tab;
    }

    /*
     * Renders our tabs of this menu as nav items
     */
    function render_menu_tabs() 
    {
        $current_tab = $this->get_current_tab();

        echo '<h2 class="nav-tab-wrapper">';
        foreach ( $this->menu_tabs as $tab_key => $tab_caption ) 
        {
            $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
            echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->menu_page_slug . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
        }
        echo '</h2>';
    }
    
    /*
     * The menu rendering goes here
     */
    function render_menu_page() 
    {
        $tab = $this->get_current_tab();
        ?>
        <div class="wrap">
        <div id="poststuff"><div id="post-body">
        <?php 
        $this->render_menu_tabs();
        //$tab_keys = array_keys($this->menu_tabs);
        call_user_func(array(&$this, $this->menu_tabs_handler[$tab]));
        ?>
        </div></div>
        </div><!-- end of wrap -->
        <?php
    }
    
    function render_tab1() 
    {
        //if this is the case there is no need to display a "fix permissions" button
        global $wpdb, $aio_wp_security;
        if (isset($_POST['aiowps_fix_permissions']))
        {
            $nonce=$_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'aiowpsec-fix-permissions-nonce'))
            {
                $aio_wp_security->debug_logger->log_debug("Nonce check failed for manual DB backup operation!",4);
                die(__('Nonce check failed for manual DB backup operation!','aiowpsecurity'));
            }
            if (isset($_POST['aiowps_permission_chg_file']))
            {
                $folder_or_file = $_POST['aiowps_permission_chg_file'];
                $rec_perm_oct_string = $_POST['aiowps_recommended_permissions']; //Convert the octal string to dec so the chmod func will accept it
                $rec_perm_dec = octdec($rec_perm_oct_string); //Convert the octal string to dec so the chmod func will accept it
                $perm_result = @chmod($_POST['aiowps_permission_chg_file'], $rec_perm_dec);
                if ($perm_result === true)
                {
                    $msg = sprintf( __('The permissions for %s were succesfully changed to %s', 'aiowpsecurity'), $folder_or_file, $rec_perm_oct_string);
                    $this->show_msg_updated($msg);
                }else if($perm_result === false)
                {
                    $msg = sprintf( __('Unable to change permissions for %s!', 'aiowpsecurity'), $folder_or_file);
                    $this->show_msg_error($msg);
                }
            }
        }
        ?>
        <h2><?php _e('File Permissions Scan', 'aiowpsecurity')?></h2>
        <div class="aio_blue_box">
            <?php
            echo '<p>'.__('Your WordPress file and folder permission settings govern the accessability and read/write privileges of the files and folders which make up your WP installation.', 'aiowpsecurity').'
            <br />'.__('Your WP installation already comes with reasonably secure file permission settings for the filesystem.', 'aiowpsecurity').'
            <br />'.__('However, sometimes people or other plugins modify the various permission settings of certain core WP folders or files such that they end up making their site less secure because they chose the wrong permission values.', 'aiowpsecurity').'
            <br />'.__('This feature will scan the critical WP core folders and files and will highlight any permission settings which are insecure.', 'aiowpsecurity').'    
            </p>';
            ?>
        </div>

        <div class="postbox">
        <h3><label for="title"><?php _e('WP Directory and File Permissions Scan Results', 'aiowpsecurity'); ?></label></h3>
        <div class="inside">
        <?php
        //Display security info badge
        global $aiowps_feature_mgr;
        $aiowps_feature_mgr->output_feature_details_badge("filesystem-file-permissions");
        ?>
        <form action="" method="POST">
        <?php wp_nonce_field('aiowpsec-fix-permissions-nonce'); ?>            
            <table class="widefat file_permission_table">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'aiowpsecurity') ?></th>
                        <th><?php _e('File/Folder', 'aiowpsecurity') ?></th>
                        <th><?php _e('Current Permissions', 'aiowpsecurity') ?></th>
                        <th><?php _e('Recommended Permissions', 'aiowpsecurity') ?></th>
                        <th><?php _e('Recommended Action', 'aiowpsecurity') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $util = new AIOWPSecurity_Utility_File;
                    $files_dirs_to_check = $util->files_and_dirs_to_check;
                    foreach ($files_dirs_to_check as $file_or_dir)
                    {
                        $this->show_wp_filesystem_permission_status($file_or_dir['name'],$file_or_dir['path'],$file_or_dir['permissions']);
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?php _e('Name', 'aiowpsecurity') ?></th>
                        <th><?php _e('File/Folder', 'aiowpsecurity') ?></th>
                        <th><?php _e('Current Permissions', 'aiowpsecurity') ?></th>
                        <th><?php _e('Recommended Permissions', 'aiowpsecurity') ?></th>
                        <th><?php _e('Recommended Action', 'aiowpsecurity') ?></th>
                </tfoot>
            </table>
        </form>
        </div></div>
        <?php
    }
    
    function render_tab2()
    {
        global $aio_wp_security;

        if(isset($_POST['aiowps_disable_file_edit']))//Do form submission tasks
        {
            $nonce=$_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'aiowpsec-disable-file-edit-nonce'))
            {
                $aio_wp_security->debug_logger->log_debug("Nonce check failed on disable PHP file edit options save!",4);
                die("Nonce check failed on disable PHP file edit options save!");
            }

            if(isset($_POST['aiowps_disable_file_editing']))
            {

                $res = $this->disable_file_edits();
            } else 
            {
                $res = $this->enable_file_edits();
            }
            if ($res)
            {
                //Save settings if no errors
                $aio_wp_security->configs->set_value('aiowps_disable_file_editing',isset($_POST["aiowps_disable_file_editing"])?'1':'');
                $aio_wp_security->configs->save_config();
            }
            //$this->show_msg_settings_updated();

        }
        ?>
        <h2><?php _e('File Editing', 'aiowpsecurity')?></h2>
        <div class="aio_blue_box">
            <?php
            echo '<p>'.__('The Wordpress Dashboard by default allows administrators to edit PHP files, such as plugin and theme files.', 'aiowpsecurity').'
            <br />'.__('This is often the first tool an attacker will use if able to login, since it allows code execution.', 'aiowpsecurity').'
            <br />'.__('This feature will disable the ability for people to edit PHP files via the dashboard.', 'aiowpsecurity').'    
            </p>';
            ?>
        </div>

        <div class="postbox">
        <h3><label for="title"><?php _e('Disable PHP File Editing', 'aiowpsecurity'); ?></label></h3>
        <div class="inside">
        <?php
        //Display security info badge
        global $aiowps_feature_mgr;
        $aiowps_feature_mgr->output_feature_details_badge("filesystem-file-editing");
        ?>

        <form action="" method="POST">
        <?php wp_nonce_field('aiowpsec-disable-file-edit-nonce'); ?>            
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php _e('Disable Ability To Edit PHP Files', 'aiowpsecurity')?>:</th>                
                <td>
                <input name="aiowps_disable_file_editing" type="checkbox"<?php if($aio_wp_security->configs->get_value('aiowps_disable_file_editing')=='1') echo ' checked="checked"'; ?> value="1"/>
                <span class="description"><?php _e('Check this if you want to remove the ability for people to edit PHP files via the WP dashboard', 'aiowpsecurity'); ?></span>
                </td>
            </tr>            
        </table>
        <input type="submit" name="aiowps_disable_file_edit" value="<?php _e('Save Settings', 'aiowpsecurity')?>" class="button-primary" />
        </form>
        </div></div>
    <?php
    }

    function render_tab3()
    {
        global $aio_wp_security;

        ?>
        <h2><?php _e('System Logs', 'aiowpsecurity')?></h2>
        <div class="aio_blue_box">
            <?php
            echo '<p>'.__('Sometimes your hosting platform will produce error or warning logs in a file called "error_log".', 'aiowpsecurity').'
            <br />'.__('Depending on the nature and cause of the error or warning, your hosting server can create multiple instances of this file in numerous directory locations of your WordPress installation.', 'aiowpsecurity').'
            <br />'.__('By occassionally viewing the contents of these logs files you can keep informed of any underlying problems on your system which you might need to address.', 'aiowpsecurity').'
            </p>';
            ?>
        </div>

        <div class="postbox">
        <h3><label for="title"><?php _e('View System Logs', 'aiowpsecurity'); ?></label></h3>
        <div class="inside">
            <p>Please click the button below to view the latest system logs:</p>
            <form action="" method="POST">
                <?php wp_nonce_field('aiowpsec-view-system-logs-nonce'); ?>
                <input type="submit" name="aiowps_search_error_files" value="<?php _e('View Latest System Logs', 'aiowpsecurity'); ?>" class="button-primary search-error-files" />
                <span class="aiowps_loading_1">
                    <img src="<?php echo AIO_WP_SECURITY_URL.'/images/loading.gif'; ?>" alt="<?php __('Loading...', 'aiowpsecurity'); ?>" />
                </span>            
            </form>
        </div></div>
    <?php
        if (isset($_POST['aiowps_search_error_files']))
        {
            $nonce=$_REQUEST['_wpnonce'];
            if (!wp_verify_nonce($nonce, 'aiowpsec-view-system-logs-nonce'))
            {
                $aio_wp_security->debug_logger->log_debug("Nonce check failed on view system log operation!",4);
                die("Nonce check failed on view system log operation!");
            }

            $logResults = AIOWPSecurity_Utility_File::recursive_file_search('error_log', 0, ABSPATH);
            if (empty($logResults) || $logResults == NULL || $logResults == '')
            {
                $this->show_msg_updated(__('No system logs were found!', 'aiowpsecurity'));
            }
            else 
            {
                foreach($logResults as $file)
                {
                    $this->display_system_logs_in_table($file);
                }
            }
        }
    }
    
    /*
     * Scans WP key core files and directory permissions and populates a wp wide_fat table
     * Displays a red background entry with a "Fix" button for permissions which are "777"
     * Displays a yellow background entry with a "Fix" button for permissions which are less secure than the recommended
     * Displays a green entry for permissions which are as secure or better than the recommended
     */
    function show_wp_filesystem_permission_status($name,$path,$recommended)
    {
        $fix = false;
        $configmod = AIOWPSecurity_Utility_File::get_file_permission($path);
        if ($configmod == "0777"){
            $trclass = "aio_table_row_red"; //Display a red background if permissions are set as least secure ("777")
            $fix = true;
        } 
        else if($configmod != $recommended)
        {
            //$res = $this->is_file_permission_secure($recommended, $configmod);
            $res = AIOWPSecurity_Utility_File::is_file_permission_secure($recommended, $configmod);
            if ($res)
            {
                $trclass = "aio_table_row_green"; //If the current permissions are even tighter than recommended then display a green row
                $fix = true;
            }
            else
            {
                $trclass = "aio_table_row_yellow"; //Display a yellow background if permissions are set to something different than recommended
                $fix = true;
            }
        } 
        else
        {
            $trclass = "aio_table_row_green";
        }
        echo "<tr class=".$trclass.">";
            echo '<td>' . $name . "</td>";
            echo '<td>'. $path ."</td>";
            echo '<td>' . $configmod . '</td>';
            echo '<td>' . $recommended . '</td>';
            if ($fix)
            {
                echo '<td>
                    <input type="submit" name="aiowps_fix_permissions" value="'.__('Set Recommended Permissions','aiowpsecurity').'" class="button-secondary" />
                    <input type="hidden" name="aiowps_permission_chg_file" value="'.$path.'"/>
                    <input type="hidden" name="aiowps_recommended_permissions" value="'.$recommended.'"/>                        
                    </td>';
            } else
            {
                echo '<td>'.__('No Action Required', 'aiowpsecurity').'</td>';
            }
        echo "</tr>";
    }
    
    
    /*
     * Modifies the wp-config.php file to disable PHP file editing from the admin panel
     * This func will add the following code:
     * define('DISALLOW_FILE_EDIT', false);
     * 
     * NOTE: This function will firstly check if the above code already exists and it will modify the bool value, otherwise it will insert the code mentioned above
     */
    function disable_file_edits()
    {
        global $aio_wp_security;
        $edit_file_config_entry_exists = false;
        
        //Config file path
        $config_file = ABSPATH.'wp-config.php';

        //Get wp-config.php file contents so we can check if the "DISALLOW_FILE_EDIT" variable already exists
        $config_contents = file($config_file);
	foreach ($config_contents as $line_num => $line) 
        {
            if (strpos($line, "'DISALLOW_FILE_EDIT', false"))
            {
                $config_contents[$line_num] = str_replace('false', 'true', $line);
                $edit_file_config_entry_exists = true;
                //$this->show_msg_updated(__('Settings Saved - The ability to edit PHP files via the admin the panel has been DISABLED.', 'aiowpsecurity'));
            } else if(strpos($line, "'DISALLOW_FILE_EDIT', true"))
            {
                $edit_file_config_entry_exists = true;
                $this->show_msg_updated(__('Your system config file is already configured to disallow PHP file editing.', 'aiowpsecurity'));
                return true;
                
            }
	}
        
        if ($edit_file_config_entry_exists)
        {
            //Now let's modify the wp-config.php file
            if (AIOWPSecurity_Utility_File::write_content_to_file($config_file, $config_contents))
            {
                $this->show_msg_updated(__('Settings Saved - Your system is now configured to not allow PHP file editing.', 'aiowpsecurity'));
                return true;
            }else
            {
                $this->show_msg_error(__('Operation failed! Unable to modify wp-config.php file!', 'aiowpsecurity'));
                $aio_wp_security->debug_logger->log_debug("Disable PHP File Edit - Unable to modify wp-config.php",4);
                return false;
            }
        }else
        {
            //Make a backup of the config file
            if(!AIOWPSecurity_Utility_File::backup_a_file($config_file))
            {
                $this->show_msg_error(__('Failed to make a backup of the wp-config.php file. This operation will not go ahead.', 'aiowpsecurity'));
                $aio_wp_security->debug_logger->log_debug("Disable PHP File Edit - Failed to make a backup of the wp-config.php file.",4);
                return false;
            }
            else{
                $this->show_msg_updated(__('A backup copy of your wp-config.php file was created successfully....', 'aiowpsecurity'));
            }

            //Construct the config code which we will insert into wp-config.php
            $new_snippet = "//Disable File Edits\n";
            $new_snippet .= 'define(\'DISALLOW_FILE_EDIT\', true);';
            $write_result = file_put_contents($config_file, $new_snippet, FILE_APPEND | LOCK_EX);
            if ($write_result)
            {
                $this->show_msg_updated(__('Settings Saved - Your system is now configured to not allow PHP file editing.', 'aiowpsecurity'));
            }else
            {
                $this->show_msg_error(__('Operation failed! Unable to modify wp-config.php file!', 'aiowpsecurity')); 
            }
        }
            return $write_result; //will return true or false depending on whether file write was successful
    }

    /*
     * Modifies the wp-config.php file to allow PHP file editing from the admin panel
     * This func will modify the following code by replacing "true" with "false":
     * define('DISALLOW_FILE_EDIT', true);
     */
    
    function enable_file_edits()
    {
        global $aio_wp_security;
        $edit_file_config_entry_exists = false;
        
        //Config file path
        $config_file = ABSPATH.'wp-config.php';

        //Get wp-config.php file contents
        $config_contents = file($config_file);
	foreach ($config_contents as $line_num => $line) 
        {
            if (strpos($line, "'DISALLOW_FILE_EDIT', true"))
            {
                $config_contents[$line_num] = str_replace('true', 'false', $line);
                $edit_file_config_entry_exists = true;
            } else if(strpos($line, "'DISALLOW_FILE_EDIT', false"))
            {
                $edit_file_config_entry_exists = true;
                $this->show_msg_updated(__('Your system config file is already configured to allow PHP file editing.', 'aiowpsecurity'));
                return true;
            }
        }
        
        if (!$edit_file_config_entry_exists)
        {
            //if the DISALLOW_FILE_EDIT settings don't exist in wp-config.php then we don't need to do anything
            $this->show_msg_updated(__('Your system config file is already configured to allow PHP file editing.', 'aiowpsecurity'));
            return true;
        } else
        {
            //Now let's modify the wp-config.php file
            if (AIOWPSecurity_Utility_File::write_content_to_file($config_file, $config_contents))
            {
                $this->show_msg_updated(__('Settings Saved - Your system is now configured to allow PHP file editing.', 'aiowpsecurity'));
                return true;
            }else
            {
                $this->show_msg_error(__('Operation failed! Unable to modify wp-config.php file!', 'aiowpsecurity'));
                $aio_wp_security->debug_logger->log_debug("Disable PHP File Edit - Unable to modify wp-config.php",4);
                return false;
            }
        }
    }
    
    function filesystem_menu_footer_code()
    {
        ?>
        <script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function($) {
            loading_span = $('.aiowps_loading_1');
            loading_span.hide(); //hide the spinner gif after page has successfully loaded
            $('.search-error-files').on("click",function(){
                loading_span.show();
            });
	});
	/* ]]> */
	</script>
         <?php
    }
    
    function display_system_logs_in_table($filepath)
    {
        global $aio_wp_security;
        //Get contents of the error_log file
        $error_file_contents = file($filepath);
        if (!$error_file_contents)
        {
            //TODO - error could not read file, display notice???
            $aio_wp_security->debug_logger->log_debug("AIOWPSecurity_Filescan_Menu - Unable to read file: ".$filepath,4);
            
        }
        $last_50_entries = array_slice($error_file_contents, -50); //extract the last 50 entries
        ?>
        <table class="widefat file_permission_table">
            <thead>
                <tr>
                    <th><?php echo(sprintf(__('Showing latest entries of error_log file: %s', 'aiowpsecurity'),'<strong>'.$filepath.'</strong>')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($last_50_entries as $entry)
                {
                    echo "<tr>";
                    echo '<td>' . $entry . "</td>";
                    echo "</tr>";                    
                }
                ?>
            </tbody>
        </table>
        <?php

    }
} //end class