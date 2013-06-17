<?php

class AIOWPSecurity_Configure_Settings
{    
    function __construct(){
        
    }
    
    static function set_default_settings()
    {
        global $aio_wp_security;
        $blog_email_address = get_bloginfo('admin_email'); //Get the blog admin email address - we will use as the default value

        //WP Generator Meta Tag feature
        $aio_wp_security->configs->set_value('aiowps_remove_wp_generator_meta_info','');//Checkbox
        
        //User password feature
        
        //Lockdown feature
        $aio_wp_security->configs->set_value('aiowps_enable_login_lockdown','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_max_login_attempts','3');
        $aio_wp_security->configs->set_value('aiowps_retry_time_period','5');
        $aio_wp_security->configs->set_value('aiowps_lockout_time_length','60');
        $aio_wp_security->configs->set_value('aiowps_set_generic_login_msg','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_enable_email_notify','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_email_address',$blog_email_address);//text field
        $aio_wp_security->configs->set_value('aiowps_enable_forced_logout','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_logout_time_period','60');
        
        //DB Security feature
        //$aio_wp_security->configs->set_value('aiowps_new_manual_db_pefix',''); //text field
        $aio_wp_security->configs->set_value('aiowps_enable_random_prefix','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_enable_automated_backups','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_db_backup_frequency','4');
        $aio_wp_security->configs->set_value('aiowps_db_backup_interval','2'); //Dropdown box where (0,1,2) => (hours,days,weeks)
        $aio_wp_security->configs->set_value('aiowps_backup_files_stored','2');
        $aio_wp_security->configs->set_value('aiowps_send_backup_email_address','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_backup_email_address',$blog_email_address);
        
        //Filesystem Security feature
        $aio_wp_security->configs->set_value('aiowps_disable_file_editing','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_prevent_default_wp_file_access','');//Checkbox

        //Blacklist feature
        $aio_wp_security->configs->set_value('aiowps_enable_blacklisting','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_banned_ip_addresses','');

        //Firewall features
        $aio_wp_security->configs->set_value('aiowps_enable_basic_firewall','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_disable_index_views','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_disable_trace_and_track','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_forbid_proxy_comments','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_deny_bad_query_strings','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_advanced_char_string_filter','');//Checkbox
        $aio_wp_security->configs->set_value('aiowps_enable_5g_firewall','');//Checkbox
        
        //TODO - keep adding default options for any fields that require it
        
        //Save it
        $aio_wp_security->configs->save_config();
    }
    
    static function turn_off_all_security_features()
    {
        AIOWPSecurity_Configure_Settings::set_default_settings();
    }
    
    static function restore_to_factory_default()
    {
        //TOOD - complete the implementation
        //restore wp_config_file();//TODO - //TODO - write implementation in the utility class
        //restore site_htaccess_file();//TODO - write implementation in the utility class
        //AIOWPSecurity_Configure_Settings::set_default_settings();
        //Maybe allow them to revert the DB Prefix too?
        //File permissions
    }
}