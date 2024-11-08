<?php
/**
 * Plugin Name: Chronosly Events Calendar Lite
 * Plugin URI: https://www.chronosly.com
 * Description: Chronosly is one of the plugins you have always dreamt about. Designed to suit all users (basic, designers, and software developers). Choose your template and you are ready to publish your events. Install addons to enhance the preset plugin features. You can get a wide selection of templates and addons in our marketplace
 * Version: 2.7.2
 * Author: Chronosly
 * Author URI: https://www.chronosly.com
 * Requires at least: 3.1
 * Tested up to: 4.9.1
 *
 * Text Domain: chronosly
 * Domain Path: /languages/
 *
 * @package Chronosly
 * @category Core
 * @author Chronosly
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('CHRONOSLY_PATH', dirname(__FILE__));//path para los includes
define('CHRONOSLY_URL',  plugin_dir_url(__FILE__) ); //path para incluir scripts o css
define('CHRONOSLY_ADDONS_URL', plugins_url()."/chronosly-addons"); //path para incluir scripts o css
define('CHRONOSLY_TEMPLATES_URL',  plugins_url()."/chronosly-templates"); //path para incluir scripts o css
define('CHRONOSLY_DEBUG', false); //debug mode
define('CHRONOSLY_VERSION', "2.7.2"); //debug mode
define('CHRONOSLY_ADMIN_INTERFACE', 1);  //Todo: hacer diferentes interficies de admin simple o varios event
define('CHRONOSLY_ADMIN_MODALITY', 1);  //Todo: hacer diferentes modalidades segun la tematica
define("CHRONOSLY_CAPABILITY_TYPE", 'chronosly'); //capability
define("CHRONOSLY_TEMPLATES_PATH", WP_PLUGIN_DIR."/chronosly-templates"); //path de los templates
define("CHRONOSLY_ADDONS_PATH", WP_PLUGIN_DIR."/chronosly-addons"); //path de los addons

if(!CHRONOSLY_DEBUG) error_reporting(0);
else  error_reporting(E_ERROR | E_WARNING | E_PARSE);

if (!class_exists('Chronosly')) {
    global $Post_Type_Chronosly, $Post_Type_Organizer, $Post_Type_Places, $Post_Type_Category,$Post_Type_Tag, $Chronosly_Marketplace,$Chronosly_Settings, $Chronosly_Extend, $chshortcode,$chronosly_running;

    class Chronosly
    {
        /**
         * Construimos el plugin
         */
        public function __construct()
        {
            global $Post_Type_Chronosly, $Post_Type_Organizer, $Post_Type_Places, $Post_Type_Category,$Post_Type_Tag, $Chronosly_Marketplace,$Chronosly_Settings,
                   $Chronosly_Extend, $pastformat;

            if(!is_dir(CHRONOSLY_TEMPLATES_PATH)) mkdir ( CHRONOSLY_TEMPLATES_PATH );
            if(!is_dir(CHRONOSLY_ADDONS_PATH)) mkdir ( CHRONOSLY_ADDONS_PATH );

            add_action('init', array( $this, 'load_translate'));
            // add_action('init', array( $this, 'load_cron'));
            add_action('chronosly_autoimport', array( $this, 'load_autoimport'));



            // Initialize Settings
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_cache.php");
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_utils.php");
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_widgets.php");
            require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_settings.php");
            $Chronosly_Settings = new Chronosly_Settings();
             require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_paint.php");
            $Chronosly_Paint = new Chronosly_Paint();
            require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_extend.php");
            $Chronosly_Extend = new Chronosly_Extend();

            if($Chronosly_Extend->plugin_updated()) {
                $Chronosly_Extend->copy_default_template();
                $Chronosly_Extend->rebuild_addons_files();
            }

           if(!is_admin() || stripos($_REQUEST["action"], "chronosly_") !== false || $_REQUEST["ch_code"]){
                require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_shortcode.php");
                $Chronosly_Shortcode = new Chronosly_Shortcode();
            }
            require_once(CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_dad_elements.php");
            $dad_elements = new Chronosly_Dad_Elements();
            require_once(CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_templates.php");
            $templates = new Chronosly_Templates();
            // Register custom post types

            require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."post-types".DIRECTORY_SEPARATOR."post_type_chronosly.php");
            $Post_Type_Chronosly = new Post_Type_Chronosly($templates);
            if($Post_Type_Chronosly->template->settings["chronosly_organizers"] and $Post_Type_Chronosly->template->settings["chronosly_organizers_addon"]){
                require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."post-types".DIRECTORY_SEPARATOR."post_type_chronosly_organizer.php");
                $Post_Type_Organizer = new Post_Type_Chronosly_Organizer();
            }
            if($Post_Type_Chronosly->template->settings["chronosly_places"] and $Post_Type_Chronosly->template->settings["chronosly_places_addon"]){

                require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."post-types".DIRECTORY_SEPARATOR."post_type_chronosly_places.php");
                $Post_Type_Places = new Post_Type_Chronosly_Places();
            }
            require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."post-types".DIRECTORY_SEPARATOR."post_type_chronosly_calendar.php");
            $Post_Type_Calendar = new Post_Type_Chronosly_Calendar();
            require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."post-types".DIRECTORY_SEPARATOR."post_type_chronosly_category.php");
            $Post_Type_Category = new Post_Type_Chronosly_Category();
            require_once( CHRONOSLY_PATH.DIRECTORY_SEPARATOR."post-types".DIRECTORY_SEPARATOR."post_type_chronosly_tag.php");
            $Post_Type_Tag = new Post_Type_Chronosly_Tag();

            require_once(CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_mk_addons.php");
            require_once(CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_mk_templates.php");
            require_once(CHRONOSLY_PATH.DIRECTORY_SEPARATOR."classes".DIRECTORY_SEPARATOR."chronosly_marketplace.php");

            if(is_admin()){

                $mk_addons =  new Chronosly_MK_Addons();
                $mk_templates =  new Chronosly_MK_Templates();
                $Chronosly_Marketplace = new Chronosly_MarketPlace($mk_addons, $mk_templates);
            }



            $plugin = plugin_basename(__FILE__);
            add_filter("plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ));
            add_filter("plugin_action_links_$plugin", array( $this, 'plugin_settings_link' ));
            register_activation_hook(__FILE__, array('Chronosly', 'activate'));
            register_deactivation_hook(__FILE__, array('Chronosly', 'deactivate'));

            if(isset($_REQUEST["force_addon_update"])) {
                echo "Forcing addons update";
                do_action("chronosly_update_addons");
            }
            if(isset($_REQUEST["force_template_update"])) {
                echo "Forcing templates update";

                do_action("chronosly_update_templates");
            }

        } // END public function __construct

        public static function load_translate()
        {
           // echo  CHRONOSLY_PATH.DIRECTORY_SEPARATOR.'languages';
            load_plugin_textdomain('chronosly', FALSE, dirname( plugin_basename( __FILE__ ) ).DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR.'custom');
            load_plugin_textdomain('chronosly', FALSE, dirname( plugin_basename( __FILE__ ) ).DIRECTORY_SEPARATOR.'languages');
        }

         public static function load_cron()
        {
           //get all files to include for cron process
            $settings = unserialize(get_option('chronosly-settings'));
             if(isset($settings["cron_includes"])) {
                foreach ($settings["cron_includes"] as $key => $file) {
                    require_once($file);
                    // print_r($file);
                }
             }
        }

         public function load_autoimport()
        {
           global $Chronosly_import_and_export;
            if(!isset($Chronosly_import_and_export) ||  !isset($Chronosly_import_and_export->id)) {
                require_once(CHRONOSLY_ADDONS_PATH.DIRECTORY_SEPARATOR."import_and_export".DIRECTORY_SEPARATOR."init.php");
                $Chronosly_import_and_export = new Chronosly_import_and_export();
            }
            $Chronosly_import_and_export->schedule_job();
            // update_option("chronosly_settings_test", time());
        }


        /**
         * Activate the plugin
         */
        public static function activate()
        {
            global  $Chronosly_Extend;

            //TODO MIRAR LA FUNCION add_role PARA AÑADIR ROLES DE USUARIOS NUEVOS

            // Capabilities for the chronosly custom post type
            $WP_Roles = new WP_Roles();
            foreach(array(
                        'chronosly_author',
                        'edit_'.CHRONOSLY_CAPABILITY_TYPE,
                        'read_'.CHRONOSLY_CAPABILITY_TYPE,
                        'delete_'.CHRONOSLY_CAPABILITY_TYPE,
                        'delete_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'edit_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'edit_others_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'edit_published_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'delete_published_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'delete_private_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'delete_others_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'publish_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'read_private_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'chronosly_options',
                        'chronosly_license'
                    ) as $cap){
                $WP_Roles->add_cap( "administrator", $cap );
                $WP_Roles->add_cap( "editor", $cap );
            }


            $ext = new Chronosly_Extend();

            //the new code will set CHRONOSLY_VERSION code to the latest
            //update templates and addons

            $ext->update_addons();
            $ext->update_templates();
            $ext->rebuild_template_addons("default");

            Chronosly_Cache::clear_cache();

            wp_schedule_event( time(), "daily", 'chronosly_update_addons' );
            wp_schedule_event( time(), "daily", 'chronosly_update_templates');



        } // END public static function activate

        /**
         * Deactivate the plugin
         */
        public static function deactivate()
        {
            $WP_Roles = new WP_Roles();
            foreach(array(
                        'chronosly_author',
                        'edit_'.CHRONOSLY_CAPABILITY_TYPE,
                        'read_'.CHRONOSLY_CAPABILITY_TYPE,
                        'delete_'.CHRONOSLY_CAPABILITY_TYPE,
                        'delete_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'edit_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'edit_others_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'edit_published_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'delete_published_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'delete_private_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'delete_others_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'publish_'.CHRONOSLY_CAPABILITY_TYPE.'s',
                        'read_private_'.CHRONOSLY_CAPABILITY_TYPE.'s',

                    ) as $cap){
                $WP_Roles->remove_cap( CHRONOSLY_ADMIN_ROLE, $cap );
            }

            delete_option("chronosly-settings");
            wp_clear_scheduled_hook( 'chronosly_update_addons' );
            wp_clear_scheduled_hook( 'chronosly_update_templates' );


        } // END public static function deactivate








        // Add the settings link to the plugins page
        function plugin_settings_link($links)
        {
            $settings_link = '<a href="admin.php?page=chronosly">Settings</a>';
            array_unshift($links, $settings_link);

            return $links;
        }

    } // END class Chronosly
} // END

if (class_exists('Chronosly')) {
    // Installation and uninstallation hooks
    // instantiate the plugin class
    $Chronosly = new Chronosly();

}


