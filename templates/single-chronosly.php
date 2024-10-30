<?php
if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"])   get_header();
$timezone = date_default_timezone_get();
ini_set('date.timezone', 'UTC');
date_default_timezone_set("UTC");
// ini_set('date.timezone', $timezone);
// date_default_timezone_set($timezone);
$repeat = get_query_var("repeat");
if(!$repeat and isset($_REQUEST["repeat"])) $repeat = $_REQUEST["repeat"];
$season = get_query_var("season");
if(!$season and isset($_REQUEST["season"])) $season = $_REQUEST["season"];
if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"])  {
    ?>
    <section id="primary" class="content-area">
        <div id="content" class="site-content" role="main">

    <?php

 }
echo '<div class="chronosly-closure">';


$Post_Type_Chronosly->template->templates_tabs("dad2", 1);
$stilo = "margin:auto;padding:30px;";
if($Post_Type_Chronosly->settings["chronosly_template_max"]) $stilo .= "max-width:".$Post_Type_Chronosly->settings["chronosly_template_max"]."px;";
if($Post_Type_Chronosly->settings["chronosly_template_min"]) $stilo .= "min-width:".$Post_Type_Chronosly->settings["chronosly_template_min"]."px;";
if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"] or (isset($_REQUEST["navigation"]) and $_REQUEST["navigation"])){


    ?>


        <div class="ch-header ch-<?php echo $Post_Type_Chronosly->settings["chronosly_titles_template_default"];?>" style="<?php echo $stilo;?>">
        <?php 

        $back = __($Post_Type_Chronosly->settings["chronosly-event-list-title"],"chronosly");
       if(!$Post_Type_Chronosly->settings["chronosly_hide_navigation_title"]) { 

        if($Post_Type_Chronosly->settings["chronosly-event-list-url"]){ ?>
             <a href="<?php  echo $Post_Type_Chronosly->settings["chronosly-event-list-url"];?>" class="back"><i class="fa fa-chevron-left"></i> <?php echo $back; ?></a>

        <?php } else { ?>
            <a href="<?php  echo (get_option('permalink_structure')?get_post_type_archive_link( 'chronosly' ):get_site_url()."/index.php?post_type=chronosly") ;?>" class="back"><i class="fa fa-chevron-left"></i> <?php echo $back; ?></a>
        <?php }
         }  
        if(!$Post_Type_Chronosly->settings["chronosly_hide_navigation_calendar"]) {?>
          <?php   if($Post_Type_Chronosly->settings["chronosly-calendar-url"]){ ?>
                 <a href="<?php  echo $Post_Type_Chronosly->settings["chronosly-calendar-url"]; ?>" class="icon-calendar"></a>

            <?php } else { ?>
                    <a href="<?php  echo (get_option('permalink_structure')?Post_Type_Chronosly_Calendar::get_permalink()."year_".date("Y")."/month_".date("n")."/":get_site_url()."/?post_type=chronosly_calendar&y=".date("Y")."&mo=".date("n")); ?>" class="icon-calendar"></a>
        <?php    }
         }  ?>
       </div>
       
    <?php
              
}

if(!$_REQUEST["shortcode"] or ($_REQUEST["shortcode"] and $_REQUEST["before_events"])) do_action("chronosly-before-events", $stilo);
echo "<div class='chronosly-content-block' style='".$stilo.";clear:both;'>";
// $tiempo_inicio = microtime(true);

            if (have_posts() ) {
                    // Start the Loop.
                     the_post();
                        $extra = array();
                        // echo "<pre>"
                        // print_r($_REQUEST);
                        if($repeat){
                            $exp = explode("_", $repeat);
                            $extra = array("start" => $exp[0], "end" => $exp[1]);

                        }

                        if($season) {
                            $exp = explode("_", $season);
                            $extra["h"] = $exp[0];
                            $extra["m"] = $exp[1];
                        }

                        $Post_Type_Chronosly->template->print_template(get_the_ID(), "dad2", "", "", "front", $extra);
                        // ob_start();
                        // $Post_Type_Chronosly->template->print_template(get_the_ID(), "dad2", "", "", "front", $extra);
                        // ob_clean();
                        // $dads = array("dad1","dad2","dad3","dad4","dad5","dad6","dad7","dad8","dad9","dad10","dad11","dad12");
                        // foreach($dads as $d) $Post_Type_Chronosly->template->vars->metas[$d] ="";
                        // echo "<pre>";
                        // print_r($Post_Type_Chronosly->template->vars);
                        // echo "<pre>";



            } else {
                    echo '<div class="ch-error" style="'.$stilo.'">';
                    _e("No events found", "chronosly");
                    echo '</div>';
            }
// $tiempo_fin = microtime(true);
// echo "Tiempo empleado: " . ($tiempo_fin - $tiempo_inicio);
if(!$_REQUEST["shortcode"] or ($_REQUEST["shortcode"] and $_REQUEST["after_events"])) do_action("chronosly-after-events");
echo "</div>"; //close chronosly block
echo "</div>"; //close chronosly closure

if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"])  {
            ?>
        </div><!-- #content -->
    </section><!-- #primary -->

    <?php
}
ini_set('date.timezone', $timezone);
date_default_timezone_set($timezone);
wp_reset_postdata();
if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"])  {

    get_footer();
}