<?php
global $Post_Type_Chronosly, $wp_query,$wp_the_query;
$timezone = date_default_timezone_get();
$offset = get_option("gmt_offset");
if($offset[0] != "-") $offset = "-".$offset;
else $offset = "+".substr($offset, 1);
date_default_timezone_set("Etc/GMT$offset");
$limit = (isset($_REQUEST["chcount"]) and $_REQUEST["chcount"])?$_REQUEST["chcount"]:$Post_Type_Chronosly->settings["chronosly_events_x_page"];
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

if($_REQUEST["page"]) $paged = $_REQUEST["page"];
if($_REQUEST["js_render"]) $_REQUEST["shortcode"] = 1;

if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"])  {

    get_header();

    ?>
    <section id="primary" class="content-area">
        <div id="content" class="site-content" role="main">

<?php
}
echo '<div class="chronosly-closure">';

    $Post_Type_Chronosly->template->templates_tabs("dad1", 1);



$tax = $wp_query->query["chronosly_category"];
if(!$tax) $tax = $wp_query->query_vars["chronosly_category"];
if(!$tax) $tax = $wp_query->queried_object->chronosly_category;
if(!$tax) $tax = $wp_the_query->queried_object->chronosly_category;
$wp_query->query("chronosly_category=$tax&posts_per_page=-1&numberposts=-1");
remove_action( 'pre_get_posts', array("Post_Type_Chronosly",'add_custom_post_vars')  );
$extra = array("chronosly_category" => $tax);
$repeated = Post_Type_Chronosly::get_events_repeated_by_date($limit, $paged, $extra);
$elementos = Post_Type_Chronosly::get_days_by_date($wp_query, $repeated, $limit, $paged);
$elements = $elementos[0];
$cat_link = "../";

$stilo = "margin:auto;padding:30px;";
if($Post_Type_Chronosly->settings["chronosly_template_max"]) $stilo .= "max-width:".$Post_Type_Chronosly->settings["chronosly_template_max"]."px;";
if($Post_Type_Chronosly->settings["chronosly_template_min"]) $stilo .= "min-width:".$Post_Type_Chronosly->settings["chronosly_template_min"]."px;";

if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"] or (isset($_REQUEST["navigation"]) and $_REQUEST["navigation"])){
    ?>

    <div class="ch-header ch-<?php echo $Post_Type_Chronosly->settings["chronosly_titles_template_default"];?>" style="<?php echo $stilo;?>">
  <?php
      $back = __($Post_Type_Chronosly->settings["chronosly-categories-list-title"],"chronosly");
       if(!$Post_Type_Chronosly->settings["chronosly_hide_navigation_title"]) {

        if($Post_Type_Chronosly->settings["chronosly-categories-list-url"]){ ?>
             <a href="<?php  echo $Post_Type_Chronosly->settings["chronosly-categories-list-url"];?>" class="back"><i class="fa fa-chevron-left"></i> <?php echo $back; ?></a>

        <?php } else { ?>
            <a href="<?php  echo (get_option('permalink_structure')?$cat_link:get_site_url()."/index.php?post_type=chronosly_category") ;?>" class="back"><i class="fa fa-chevron-left"></i> <?php echo $back; ?></a>
        <?php }
         }

    if(!$Post_Type_Chronosly->settings["chronosly_hide_navigation_calendar"]) { ?>
      <?php   if($Post_Type_Chronosly->settings["chronosly-calendar-url"]){ ?>
             <a href="<?php  echo $Post_Type_Chronosly->settings["chronosly-calendar-url"]; ?>" class="icon-calendar"></a>

        <?php } else { ?>
                <a href="<?php  echo (get_option('permalink_structure')?Post_Type_Chronosly_Calendar::get_permalink()."year_".date("Y")."/month_".date("n")."/":get_site_url()."/?post_type=chronosly_calendar&y=".date("Y")."&mo=".date("n")); ?>" class="icon-calendar"></a>
    <?php    }
     }  ?>
   </div>

<?php
}
$tax_ob =get_term_by("slug", $tax, "chronosly_category");

if(!$_REQUEST["shortcode"] or ($_REQUEST["shortcode"] and $_REQUEST["before_events"])) do_action("chronosly-before-events", $stilo);
echo "<div class='chronosly-content-block' style='".$stilo.";clear:both;'>";




    ob_start();
    $Post_Type_Chronosly->template->print_template($tax_ob->term_id, "dad12", "", "", "front");

    $content = ob_get_clean();
    $res = "";

    if(stripos($content, "#event_list#")){
        //get the events for this organizer

        $repeats = array();

        if(count($elements)){
            foreach($elements as $el){
                $xid = $ide = 0;
                if(is_array($el)){
                    $xid = $ide =  $el["id"];
                    ob_start();
                     if(isset($repeats[$xid])) $ide .= "_".$repeats[$xid];
                    $evs = array("id" => $ide,"start" => $el["start"], "end" => $el["end"]);
                    if($el["h"] && $el["eh"]) $evs = array("id" => $ide, "start" => $el["start"], "end" => $el["end"], "h" => $el["h"], "m" => $el["m"], "eh" => $el["eh"], "em" => $el["em"]);
                    $Post_Type_Chronosly->template->print_template($el["id"], "dad4", "", "", "front", $evs);
                    $events_list[$ide]= ob_get_clean();
                }
                else {
                    $xid = $ide = $el;
                    ob_start();
                    $Post_Type_Chronosly->template->print_template($el, "dad4", "", "", "front");
                    $events_list[$ide]= ob_get_clean();
                }
                if(isset($repeats[$xid])) ++$repeats[$xid];
                else $repeats[$xid] = 1;




                $is_feat = stripos($events_list[$ide], " ch-featured ");
                if($feats and !$is_feat){
                    $res .= "<span class='feat-sep'></span>";
                    $feats = 0;
                }
                else if($is_feat) $feats = 1;
                $res .= $events_list[$ide];
            }
        }
    } else {
        $res = '<div class="ch-error" style="'.$stilo.'">';
        $res .= __("No events found", "chronosly");
        $res .= '</div>';
    }
    echo str_replace("#event_list#", $res, $content);


 $_REQUEST["ch_code"] = json_encode(array("type"=>'event', "category"=> $tax_ob->term_id, "pagination"=>1, "view"=> "dad4"));

if(!isset($_REQUEST["shortcode"])) {

    echo "<div class='pagination'  style='$stilo'>";
    if($elementos[1]) echo '<a onclick="javascript:ch_prev_page('.$limit.','.$paged.', \''. urlencode($_REQUEST["ch_code"]).'\', this, \'.ev-data.events_list\')"><< '.__("Previous page").'</a> &nbsp;&nbsp;&nbsp;';
    if($elementos[2]) echo '<a onclick="javascript:ch_next_page('.$limit.','.$paged.', \''. urlencode($_REQUEST["ch_code"]).'\', this, \'.ev-data.events_list\')">'.__("Next page").' >></a>';
    echo "</div>";
}
else if( $_REQUEST["pagination"]){
    echo "<div class='pagination'  style='$stilo'>";

    if($elementos[1]) echo '<a onclick="javascript:ch_prev_page('.$limit.','.$paged.', \''. urlencode($_REQUEST["ch_code"]) .'\', this, \'.ev-data.events_list\')"><< '.__("Previous page").'</a> &nbsp;&nbsp;&nbsp;';
    if($elementos[2]) echo '<a onclick="javascript:ch_next_page('.$limit.','.$paged.', \''. urlencode($_REQUEST["ch_code"]) .'\', this, \'.ev-data.events_list\')">'.__("Next page").' >></a>';
    echo "</div>";
}





if(!$_REQUEST["shortcode"] or ($_REQUEST["shortcode"] and $_REQUEST["after_events"])) do_action("chronosly-after-events");
echo "</div>"; //close chronosly block
echo "</div>"; //close chronosly closure
//ini_set('date.timezone', $timezone);
date_default_timezone_set($timezone);
if(!isset($_REQUEST["shortcode"]) or !$_REQUEST["shortcode"])  {

                ?>
        </div><!-- #content -->
    </section><!-- #primary -->

    <?php
    wp_reset_postdata();


    get_footer();
} else{

        global $post, $wp_query;
        $post = "";
        $wp_query->post = $post;
        $wp_query->posts = $post;
        $wp_query->post_count = 0;
}
