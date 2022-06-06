<?php
/**
  Plugin Name: Custom functionality for RX
  Description: This plugin contains all the custom functionality for RX module including save prescription.
  Author: MJ
  Version: 1.0
 */
defined('ABSPATH') || exit;
// for encrypting prescription images
define('ENCRYPTSALT', 'vcspres');
define('CIPHERING', 'AES-128-CTR');
global $rx_db_version;
$rx_db_version = '1.0';
if (!defined('LEFT_EYE_ID')) {
    define('LEFT_EYE_ID', '1412');
    define('RIGHT_EYE_ID', '1413');
}

global $wpdb;

if (!define('ROOTDIR', plugin_dir_path(__FILE__))) {
    define('ROOTDIR', plugin_dir_path(__FILE__));
}
// Get current URL of product page
$current_url = $_SERVER['REQUEST_URI'];
$explode_array = explode('/product/', $current_url);
// Get last segment from URL
$final_url = str_replace("/", '', $explode_array[1]);
// Get product Object by product slug
$get_product_obj = get_page_by_path($final_url, OBJECT, 'product');
// Get product ID from product OBJ
$product_id_cl = $get_product_obj->ID;
// Get category by prodcut ID
$terms = $wpdb->prefix . "terms";
$term_taxonomy = $wpdb->prefix . "term_taxonomy";
$term_relationships = $wpdb->prefix . "term_relationships";

if ($get_query_param == 'rx') {



    function rx_install() {
        global $wpdb;
        global $rx_db_version;
        $installed_ver = get_option("rx_db_version");
        if ($installed_ver != $rx_db_version) {
            $table_name_prescription = $wpdb->prefix . 'user_prescriptions';
            $table_name_prescription_order_ref = $wpdb->prefix . 'user_prescriptions_order_ref';
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table_name_prescription (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `first_name` varchar(200) DEFAULT NULL,
            `last_name` varchar(200) DEFAULT NULL,
            `rx_group` varchar(400) DEFAULT NULL,
            `rx_prescription_location` varchar(255) DEFAULT NULL,
            `prescription_name` varchar(200) DEFAULT NULL,
            `prescription_date` date DEFAULT NULL,
            `prescription_renewal` varchar(22) DEFAULT NULL,
            `prescription_comment` varchar(255) DEFAULT NULL,
            `prescription_img_url` varchar(255) DEFAULT NULL,
            `pd_img_url` varchar(255) DEFAULT NULL,
            `sphere_left` varchar(22) DEFAULT NULL,
            `cylinder_left` varchar(22) DEFAULT NULL,
            `axis_left` varchar(22) DEFAULT NULL,
            `add_left` varchar(22) DEFAULT NULL,
            `pd_left` varchar(22) DEFAULT NULL,
            `sphere_right` varchar(22) DEFAULT NULL,
            `cylinder_right` varchar(22) DEFAULT NULL,
            `axis_right` varchar(22) DEFAULT NULL,
            `add_right` varchar(22) DEFAULT NULL,
            `pd_right` varchar(22) DEFAULT NULL,
            `verification_type` varchar(50) DEFAULT NULL,
            `is_validated` tinyint(1) DEFAULT 0,
            PRIMARY KEY (`id`)
        ) $charset_collate;
        CREATE TABLE $table_name_prescription_order_ref (
            id int(11) NOT NULL AUTO_INCREMENT,
            `rx_id` int(11) NOT NULL,
            `order_id` int(11) NOT NULL,
            `order_line_item_id` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `rx_id` (`rx_id`),
            KEY `order_id` (`order_id`),
            KEY `order_line_item_id` (`order_line_item_id`)
        ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta($sql);
            update_option('rx_db_version', $rx_db_version);
        }
    }

    register_activation_hook(__FILE__, 'rx_install');

    function myplugin_update_db_check() {
        global $rx_db_version;
        if (get_option('rx_db_version') != $rx_db_version) {
            rx_install();
        }
    }

    add_action('plugins_loaded', 'myplugin_update_db_check');
    /* RX Module functionality start point */
    add_action('wp_enqueue_scripts', 'composite_products_scripts', 2000);

    function composite_products_scripts() {
        //wp_enqueue_style( 'bootstrap', get_stylesheet_directory_uri() . '/assets/css/bootstrap.min.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/bootstrap.min.css') );
        if (is_account_page()) {
            wp_enqueue_style('main-styles', plugin_dir_path(__FILE__) . '/assets/front-assets/css/rx_style.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/rx_style.css'), false);
            wp_enqueue_style('jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
            wp_enqueue_script('jquery-ui-datepicker');
        } else if (is_product()) {
            wp_enqueue_style('pd-style', plugin_dir_path(__FILE__) . '/assets/front-assets/css/cam-style.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/cam-style.css'), false);
            wp_enqueue_style('main-styles', plugin_dir_path(__FILE__) . '/assets/front-assets/css/rx_style.css', array(), filemtime(get_stylesheet_directory() . '/assets/css/rx_style.css'), false);
            wp_enqueue_style('jquery-ui-datepicker-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_register_script('script-prescription', plugin_dir_path(__FILE__) . '/assets/front-assets/js/prescription-validation-script.js', array('jquery'), filemtime(get_stylesheet_directory() . '/assets/js/prescription-validation-script.js'), true);
            wp_enqueue_script('script-prescription');
            $is_logged_in = true;
            if (!is_user_logged_in()) {
                $is_logged_in = false;
            }
            wp_localize_script('script-prescription', 'login_check', array(
                'is_logged_in' => $is_logged_in
            ));
            wp_enqueue_script('scripts', plugin_dir_path(__FILE__) . '/assets/front-assets/js/composite-products-scripts.js', array('jquery'), filemtime(get_stylesheet_directory() . '/assets/js/composite-products-scripts.js'), true);
            wp_enqueue_script('pd-script', plugin_dir_path(__FILE__) . '/assets/front-assets/js/scripts.js', array('jquery'), filemtime(get_stylesheet_directory() . '/assets/js/scripts.js'), true);
        }
        //wp_enqueue_script('pd-webcam', get_stylesheet_directory_uri() . '/assets/js/webcam.js', array('jquery'), filemtime(get_stylesheet_directory() . '/assets/js/webcam.js'), true);
        //wp_enqueue_script( 'bootstrap', get_stylesheet_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery'), filemtime(get_stylesheet_directory() . '/assets/js/bootstrap.min.js'), true );
        //if(is_account_page()) { 
        //}
    }

    add_filter('woocommerce_admin_meta_boxes_variations_per_page', 'woo_increase_variations_per_page');

    function woo_increase_variations_per_page() {
        return 50;
    }

// login, register and prescription saving and its relevant functionality
    /**
     * Register new endpoint to use inside My Account page.
     *
     * @see https://developer.wordpress.org/reference/functions/add_rewrite_endpoint/
     */
    function prescriptions_endpoints() {
        add_rewrite_endpoint('prescriptions', EP_ROOT | EP_PAGES);
    }

    add_action('init', 'prescriptions_endpoints');

    /**
     * Add new query var.
     *
     * @param array $vars
     * @return array
     */
    function prescriptions_query_vars($vars) {
        $vars[] = 'prescriptions';
        return $vars;
    }

    add_filter('query_vars', 'prescriptions_query_vars', 0);

    /**
     * Insert the new endpoint into the My Account menu.
     *
     * @param array $items
     * @return array
     */
    function prescriptions_my_account_menu_items($items) {
        $new_items = array();
        $new_items['prescriptions'] = __('Glasses Prescriptions', 'woocommerce');
        // Add the new item after `orders`.
        return prescriptions_insert_after_helper($items, $new_items, 'orders');
    }

    add_filter('woocommerce_account_menu_items', 'prescriptions_my_account_menu_items');

    /**
     * Custom help to add new items into an array after a selected item.
     *
     * @param array $items
     * @param array $new_items
     * @param string $after
     * @return array
     */
    function prescriptions_insert_after_helper($items, $new_items, $after) {
        // Search for the item position and +1 since is after the selected item key.
        $position = array_search($after, array_keys($items)) + 2;
        // Insert the new item.
        //$array = $items;
        //print_r($items);
        $array = array_slice($items, 0, $position, true);
        $array += $new_items;
        $array += array_slice($items, $position, count($items) - $position, true);
        return $array;
    }

    /**
     * Endpoint HTML content.
     */
    function prescriptions_endpoint_content() {
        //echo do_shortcode('[contact-form-7 id="1742" title="Order Setting Form"]');
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_prescriptions';
        $user_id = get_current_user_id();
        $prescriptions_filter = $wpdb->get_results('SELECT first_name, last_name, rx_group FROM ' . $table_name . ' WHERE user_id =' . $user_id . '  GROUP BY rx_group ORDER BY rx_group', OBJECT);
        // add prescription
        if ((isset($_GET['add_prescription']) && $_GET['add_prescription'] == '1') || (isset($_GET['prescription_id']) && $_GET['prescription_id'] != '')) {
            if (isset($_GET['prescription_id']) && $_GET['prescription_id'] != '') {
                $is_deleted = $wpdb->query("DELETE FROM $table_name WHERE id =" . $_GET['prescription_id'] . " AND user_id=$user_id");
                if ($is_deleted) {
                    echo '<span style="color:green;">Prescription deleted</span>';
                } else {
                    echo '<span style="color:red;">There is some issue while deleting prescription. Please try again later.</span>';
                }
                ?>
                <script type="text/javascript">
                    setTimeout(function () {
                        location.href = '<?= get_permalink() ?>/prescriptions/';
                    }, 1000);
                </script>  <?php
            } else if (isset($_POST['sph_right']) && $_POST['sph_left'] != '') {
                $_POST['user_id'] = $user_id;
                $base = dirname(__FILE__);
                if (!is_dir($base . "/../../uploads/prescription_imgs")) {
                    mkdir($base . "/../../uploads/prescription_imgs", 0755, true);
                }
                $target_dir = $base . "/../../uploads/prescription_imgs/";
                $target_file = $target_dir . basename($_FILES["prescription_upload"]["name"]);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
                /* if (isset($_POST["add_pres_btn"])) {
                  $check = getimagesize($_FILES["prescription_upload"]["tmp_name"]);
                  if ($check !== false) {
                  //echo "File is an image - " . $check["mime"] . ".";
                  $uploadOk = 1;
                  } else {
                  echo "File is not an image.";
                  $uploadOk = 0;
                  }
                  } */
// Check if file already exists
                if (file_exists($target_file)) {
                    //echo "Sorry, file already exists.";
                    $uploadOk = 1;
                }
// Check file size
                //if ($_FILES["prescription_upload"]["size"] > 4000000) { // 4 mb
                if ($_FILES["prescription_upload"]["size"] > 10000000) { // 10 mb
                    echo "Sorry, your file is too large.";
                    $uploadOk = 0;
                }
// Allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "pdf") {
                    echo "Sorry, only JPG, JPEG, PNG GIF & PDF files are allowed.";
                    $uploadOk = 0;
                }
// Check if $uploadOk is set to 0 by an error
                if ($uploadOk == 0) {
                    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
                } else {
                    if (move_uploaded_file($_FILES["prescription_upload"]["tmp_name"], $target_file)) {
                        //echo "The file " . basename($_FILES["prescription_upload"]["name"]) . " has been uploaded.";
                        $uploadOk = 1;
                    } else {
                        echo "Sorry, there was an error uploading your file.";
                        $uploadOk = 0;
                    }
                    //exit();
                }
                if (!$uploadOk) {
                    addEditForm($prescriptions_filter);
                } else {
                    //$_POST['prescription_img_url'] = get_site_url() . '/wp-content/uploads/prescription_imgs/' . basename($_FILES["prescription_upload"]["name"]);
                    $_POST['prescription_img_url'] = generateimagetoencoded(get_site_url() . '/wp-content/uploads/prescription_imgs/' . basename($_FILES["prescription_upload"]["name"]));
                    $inserted_id = add_prescription($_POST);
                    if ($inserted_id) {
                        echo '<span style="color:green;">Prescription added!</span>';
                    } else {
                        echo '<span style="color:red;">There is some issue while processing prescription. You can add maximum 20 prescription.</span>';
                    }
                    ?>
                    <script type="text/javascript">
                        setTimeout(function () {
                            location.href = '<?= get_permalink() ?>/prescriptions/';
                        }, 1000);
                    </script>
                    <?php
                }
            } else {
                ?>
                <h3>Add Prescription</h3>
                <?php
                //$prescriptions_filter = array();
                addEditForm($prescriptions_filter);
            }
        } else {
            echo get_list_prescription($user_id);
        }
    }

    add_action('woocommerce_account_prescriptions_endpoint', 'prescriptions_endpoint_content');

    function update_prescription($arr) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_prescriptions';
        extract($arr);
        if (isset($prescription_date) && $prescription_date != '') {
            $prescription_date = explode('/', $prescription_date);
            $prescription_date = $prescription_date[1] . "/" . $prescription_date[0] . "/" . $prescription_date[2];
            $prescription_date = date('Y-m-d', strtotime($prescription_date));
        }
        return $wpdb->update(
                        $table_name, array(
                    'first_name' => trim($first_name),
                    'last_name' => trim($last_name),
                    'rx_group' => trim($first_name) . ' ' . trim($last_name),
                    'rx_prescription_location' => ((!empty($rx_prescription_location)) ? $rx_prescription_location : ''),
                    'prescription_name' => $prescription_name,
                    'prescription_date' => date('Y-m-d', strtotime($prescription_date)),
                    'prescription_renewal' => $renewal,
                    'prescription_comment' => $comment,
                    'sphere_left' => $sph_left,
                    'cylinder_left' => $cyl_left,
                    'axis_left' => $axis_left,
                    'add_left' => $add_left,
                    'pd_left' => $pd_left,
                    'sphere_right' => $sph_right,
                    'cylinder_right' => $cyl_right,
                    'axis_right' => $axis_right,
                    'add_right' => $add_right,
                    'pd_right' => $pd_right,
                    'is_validated' => $is_validated
                        ), array(
                    'id' => $prescription_id,
                    'user_id' => $user_id
                        )
        );
    }

    function prescription_exist($arr) {
        extract($arr);
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) {
            return 0;
        }
        global $wpdb;
        //$formated_date = str_replace('/', '-', $prescription_date);
        $uid = $wpdb->get_var(
                $wpdb->prepare(
                        "SELECT id FROM " . $wpdb->prefix . "user_prescriptions
                    WHERE user_id = %d AND first_name = %s AND last_name = %s AND prescription_name = %s AND prescription_date = %s AND sphere_left = %s
                    AND cylinder_left = %s AND axis_left = %s AND add_left = %s AND pd_left = %s AND sphere_right = %s
                    AND cylinder_right = %s AND axis_right = %s AND add_right = %s AND pd_right = %s
                    LIMIT 1", $user_id, $first_name, $last_name, $prescription_name, date('Y-m-d', strtotime($prescription_date)), $sph_left, $cyl_left, $axis_left, $add_left, $pd_left, $sph_right, $cyl_right, $axis_right, $add_right, $pd_right
                )
        );
        if ($uid > 0) {
            return $uid;
        }
        return 0;
    }

    function add_prescription($arr) {
        extract($arr);
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        if (!$user_id) {
            return 0;
        }
        if (!isset($is_validated)) {
            $is_validated = 0;
        }
        $arr['is_validated'] = $is_validated;
        if (prescription_exist($arr)) {
            return 0; //$prescription_id;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_prescriptions';
        //$formated_date = str_replace('/', '-', $prescription_date);
        if (isset($prescription_date) && $prescription_date != '') {
            $prescription_date = date('Y-m-d', strtotime($prescription_date));
        }
        $num_of_inserted_rows = $wpdb->insert(
                $table_name, array(
            'user_id' => $user_id,
            'first_name' => trim($first_name),
            'last_name' => trim($last_name),
            'rx_group' => trim($first_name) . ' ' . trim($last_name),
            'rx_prescription_location' => ((!empty($rx_prescription_location)) ? $rx_prescription_location : ''),
            'prescription_name' => $prescription_name,
            'prescription_date' => $prescription_date,
            'prescription_img_url' => $prescription_img_url,
            'pd_img_url' => $pd_img_url,
            'sphere_left' => $sph_left,
            'cylinder_left' => $cyl_left,
            'axis_left' => $axis_left,
            'add_left' => $add_left,
            'pd_left' => $pd_left,
            'sphere_right' => $sph_right,
            'cylinder_right' => $cyl_right,
            'axis_right' => $axis_right,
            'add_right' => $add_right,
            'pd_right' => $pd_right,
            'verification_type' => ((!empty($verification_type)) ? $verification_type : ''),
            'is_validated' => $is_validated
                )
        );

        if ($num_of_inserted_rows) {
            return $wpdb->insert_id;
        }
        return 0;
    }

    function is_prescription_ref_already_exist($prescription_id, $order_id, $item_id) {
        global $wpdb;
        $uid = $wpdb->get_var(
                $wpdb->prepare(
                        "SELECT id FROM " . $wpdb->prefix . "user_prescriptions_order_ref
                    WHERE rx_id = %d AND order_id = %s AND order_line_item_id = %s
                    LIMIT 1", $prescription_id, $order_id, $item_id
                )
        );
        if ($uid > 0) {
            return $uid;
        }
        return 0;
    }

    function save_prescription_order_ref($prescription_id, $order_id, $item_id) {
        if ($prescription_id && $order_id && $item_id) {
            $num_of_inserted_rows = is_prescription_ref_already_exist($prescription_id, $order_id, $item_id);
            if (!$num_of_inserted_rows) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'user_prescriptions_order_ref';
                $num_of_inserted_rows = $wpdb->insert(
                        $table_name, array(
                    'rx_id' => $prescription_id,
                    'order_id' => $order_id,
                    'order_line_item_id' => $item_id
                        )
                );
            }
            if ($num_of_inserted_rows) {
                return $wpdb->insert_id;
            }
        }
        return 0;
    }

    function delete_rx_existing_order_ref($order_id) {
        global $wpdb;
        $table_order_items = $wpdb->prefix . "woocommerce_order_items";
        $ref_table_name = $wpdb->prefix . 'user_prescriptions_order_ref';
        $table_name = $wpdb->prefix . 'user_prescriptions';
        // first get any prescripiton added remove it first if its item id not exit        
        
        $prescriptions = $wpdb->get_results('SELECT rx_id, order_line_item_id FROM ' . $ref_table_name . ' WHERE order_id=' . $order_id, OBJECT);
        foreach ($prescriptions as $prescription) {
            $oid = $wpdb->get_var(
            $wpdb->prepare(
                    "SELECT order_id FROM " . $table_order_items . " 
                    WHERE order_item_id = %d 
                    LIMIT 1", $prescription->order_line_item_id
                    )
            );
            if(!$oid) {
                $wpdb->query("DELETE FROM $table_name WHERE id=" . $prescription->rx_id);
                $wpdb->query("DELETE FROM $ref_table_name WHERE order_id=" . $order_id . " AND order_line_item_id=" . $prescription->order_line_item_id);
            }
        }
    }

    function get_list_prescription($user_id, $product_detail_page = false) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_prescriptions';

        if (isset($_GET['pageno'])) {
            $pageno = $_GET['pageno'];
        } else {
            $pageno = 1;
        }
        $no_of_records_per_page = 10;
        $offset = ($pageno - 1) * $no_of_records_per_page;

        $allprescriptionqry = 'SELECT COUNT(*) FROM ' . $table_name . ' WHERE user_id =' . $user_id . ' AND is_validated=0';
        if (isset($_GET['filter_by_name']) && $_GET['filter_by_name'] != '') {
            $allprescriptionqry .= ' AND rx_group="' . $_GET['filter_by_name'] . '"';
        }
        //$total_rows = $wpdb->get_var('SELECT COUNT(*) FROM ' . $table_name . ' WHERE user_id =' . $user_id);
        $total_rows = $wpdb->get_var($allprescriptionqry);
        $total_pages = ceil($total_rows / $no_of_records_per_page);

        $prescription_query = 'SELECT * FROM ' . $table_name . ' WHERE user_id =' . $user_id . ' AND is_validated=0';
        if (isset($_GET['filter_by_name']) && $_GET['filter_by_name'] != '') {
            $prescription_query .= ' AND rx_group="' . $_GET['filter_by_name'] . '"';
        }
        if (!$product_detail_page) {
            $prescription_query .= " ORDER BY rx_group, id";
        } else {
            $prescription_query .= " ORDER BY rx_group, id LIMIT $offset, $no_of_records_per_page";
        }
        $prescriptions = $wpdb->get_results($prescription_query, OBJECT);
        $prescriptions_filter = $wpdb->get_results('SELECT rx_group FROM ' . $table_name . ' WHERE user_id =' . $user_id . ' AND is_validated=0 GROUP BY rx_group ORDER BY rx_group', OBJECT);
        ob_start();

        //echo "Site Image path = " . $shopvcsimagepath . " <br> --------------- <br>";
        ?>
        <h3><?php
            _e('YOUR SAVED PRESCRIPTIONS', "prescriptions");
            //if (!$product_detail_page && count($prescriptions) <= 20) {
            if (!$product_detail_page) {
                ?><a href="?add_prescription=1" class="button">Add Prescription</a><?php } ?></h3>	
        <div class="panel panel-default">
            <div class="js-table-responsive">
                <?php if (!$product_detail_page) { ?>
                    <div class="filter-area">
                        <form action="" method="get">
                            <label for="filter_by_name">Filter by name</label>
                            <select name="filter_by_name" id="filter_by_name">
                                <option value="">Select</option>
                                <?php foreach ($prescriptions_filter as $pf): ?>
                                    <option value="<?= $pf->rx_group ?>"
                                    <?php if (isset($_GET['filter_by_name']) && $_GET['filter_by_name'] == $pf->rx_group) { ?>
                                                selected="selected"
                                            <?php } ?>
                                            ><?= $pf->rx_group ?></option>
                                        <?php endforeach; ?>
                            </select>
                            <input type="submit" name="sub_ffm" value="Filter">
                        </form>
                    </div>
                <?php } ?>
                <table class="shop_table_responsive">
                    <!-- <thead>
                    <th><?php _e('Prescription Name', "prescriptions") ?></th>
                    <th><?php _e('Date', "prescriptions") ?></th>
                    <th cols="4">Your Prescription</th>
                    <th><?php
                    if (!$product_detail_page) {
                        _e('Action', "prescriptions");
                    }
                    ?></th>
                    </thead> -->
                    <tbody>
                        <?php if (!empty($prescriptions)): ?>
                            <?php foreach ($prescriptions as $p): ?>
                                <tr>
                                    <td>
                                        <div class="presc-header">
                                            <div class="rx-uname"><?= $p->first_name ?> <?= $p->last_name ?></div>
                                            <div class="pres-date"><span><?php if ($p->prescription_date != '') { ?>Date:<?php } ?>&nbsp;</span><?php echo date('d/m/Y', strtotime($p->prescription_date)); ?></div>
                                        </div> <?php
                                        /*
                                          <div class="pres-img">
                                          <?php
                                          if ($p->prescription_img_url != '') { ?>
                                          <?php if (filter_var($p->prescription_img_url, FILTER_VALIDATE_URL)) { ?>
                                          <img src="<?= $p->prescription_img_url ?>" />
                                          <?php } else {
                                          $filename = decryptstring($p->prescription_img_url, CIPHERING, ENCRYPTSALT);
                                          $filename = str_replace('public_html/', '', $filename);
                                          if (strpos($filename, '.pdf') !== false) {
                                          echo getencryptedimgbypathwithicon($filename);
                                          } else {
                                          $img_url = getencryptionofimg($filename);
                                          echo '<img src="' . $img_url . '" >';
                                          }

                                          ?>
                                          <?php } ?>
                                          <?php } ?>
                                          </div>
                                          <?php */
                                        $sph_right = explode('_', $p->sphere_right);
                                        $sph_left = explode('_', $p->sphere_left);
                                        $cyl_right = explode('_', $p->cylinder_right);
                                        $cyl_left = explode('_', $p->cylinder_left);
                                        $axis_right = explode('_', $p->axis_right);
                                        $axis_left = explode('_', $p->axis_left);
                                        $add_right = explode('_', $p->add_right);
                                        $add_left = explode('_', $p->add_left);
                                        $pd_right = explode('_', $p->pd_right);
                                        $pd_left = explode('_', $p->pd_left);
                                        if ($sph_right[0] != '' || $cyl_right[0] != '' || $axis_right[0] != '' || $add_right[0] != '' ||
                                                $sph_left[0] != '' || $cyl_left[0] != '' || $axis_left[0] != '' || $add_left[0] != '') {
                                            ?>
                                            <div class="pres-values">
                                                <div class="js-prescription-list">
                                                    <ul class="nonlist">
                                                        <li class="js-heading">
                                                            <span class="js-box1"></span>
                                                            <span class="js-box2"><?php _e('Sph', "prescriptions") ?></span>
                                                            <span class="js-box3"><?php _e('Cyl', "prescriptions") ?></span>
                                                            <span class="js-box4"><?php _e('Axis', "prescriptions") ?></span>
                                                            <span class="js-box5"><?php _e('Add', "prescriptions") ?></span>
                                                            <span class="js-box5"><?php _e('Pd', "prescriptions") ?></span>
                                                        </li>
                                                        <li>
                                                            <span class="js-box1">Right Eye</span>
                                                            <span class="js-box2"><?= $sph_right[0] ?></span>
                                                            <span class="js-box3"><?= $cyl_right[0] ?></span>
                                                            <span class="js-box4"><?= $axis_right[0] ?></span>
                                                            <span class="js-box5"><?= $add_right[0] ?></span>
                                                            <span class="js-box5"><?= $pd_right[0] ?></span>
                                                        </li>
                                                        <li>
                                                            <span class="js-box1">Left Eye</span>
                                                            <span class="js-box2"><?= $sph_left[0] ?></span>
                                                            <span class="js-box3"><?= $cyl_left[0] ?></span>
                                                            <span class="js-box4"><?= $axis_left[0] ?></span>
                                                            <span class="js-box5"><?= $add_left[0] ?></span>
                                                            <span class="js-box5"><?= $pd_left[0] ?></span>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="pres-edit">
                                            <?php
                                            if (!$product_detail_page) {
                                                if (!$p->is_validated) {
                                                    ?>
                                                    <a href="?prescription_id=<?= $p->id ?>"
                                                       class="button prescription-add-to-cart"><?php _e('Delete', "prescriptions") ?></a>
                                                       <?php
                                                   }
                                               } else {
                                                   $arr_pres = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
                                                   echo "<input type='radio' name='prescription_id' id='prescription_id_" . $p->id . "' onclick='populate_prescription($arr_pres)' class='prescription-radio'> <label for='prescription_id_" . $p->id . "'>Select</label>";
                                               }
                                               ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center !important;">
            <!--                                <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/ex-mark.png" class="no-pres-img">-->
                                    <p class="no-pres-text">You have no saved prescriptions. </p>
            <!--                                <span class="add-pres-span" onclick="enterNewPrescription();"><?php _e('Add New Prescription', "prescriptions") ?></span>-->
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody> 
                </table>
                <?php if ($total_pages > 1 && !$product_detail_page) { ?>
                    <ul class="pagination">
                        <!-- <li><a href="?pageno=1">First</a></li> -->

                        <li class="<?php
                        if ($pageno <= 1) {
                            echo 'disabled';
                        }
                        ?>">
                            <a href="<?php
                            if ($pageno <= 1) {
                                echo '#';
                            } else {
                                echo "?pageno=" . ($pageno - 1);
                            }
                            ?>">Prev</a>
                        </li>


                        <?php
                        for ($i = 1; $i <= $total_pages; ++$i) {
                            ?>
                            <li class="<?php
                            if ($pageno == $i) {
                                echo 'active';
                            }
                            ?>">
                                <a href="<?php echo "?pageno=" . $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php
                        }
                        ?>


                        <li class="<?php
                        if ($pageno >= $total_pages) {
                            echo 'disabled';
                        }
                        ?>">
                            <a href="<?php
                            if ($pageno >= $total_pages) {
                                echo '#';
                            } else {
                                echo "?pageno=" . ($pageno + 1);
                            }
                            ?>">Next</a>
                        </li>

                                                            <!-- <li><a href="?pageno=<?php echo $total_pages; ?>">Last</a></li> -->
                    </ul>
                <?php } ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    function load_rx_options($tax_slug) {
        
        $tax_values = get_terms($tax_slug, array(
            'hide_empty' => false,
            'fields' => 'names'
        ));
        $positive = array_filter($tax_values, function($x) { return $x > 0; });
        $negative = array_filter($tax_values, function($x) { return $x < 0; });
        $sign_value = array_filter($tax_values, function($x) { return $x == 0; });
        rsort($sign_value);
        sort($positive);
        sort($negative);
        $sorted = array_merge(array_merge($negative, $sign_value), $positive);
        
        $options_html = '';
        
        foreach ($sorted as $item) {
            $options_html .= '<option value="' . $item . '">' . $item . '</option>';
        }
        return $options_html;
    }

    function addEditForm($presc_arr = array(), $is_admin = false, $index = 0) {
        // updated by Shoaib and Abdullah
        $productid = get_order_productid($presc_arr->order_id, $presc_arr->order_line_item_id);
        $practiceoptions = get_product_addon_options($productid, 'Choose your Practice');
        // updated by Shoaib and Abdullah
        ?>
        <div class="sp-add-prescription my-account-add-prescription">
            <div class="tc-extra-product-options tm-extra-product-options">
                <form name="add_prescription" action="" method="post" enctype="multipart/form-data" novalidate>
                    <div class="cpf-section tm-row sp-prescription-options  iscpfdependson is-epo-depend">
                        <?php if (!$is_admin) { ?>
                            <div class="cpf_hide_element cpf-type-header">
                                <div class="prescription-type-area">
                                    <div class="type-wrapper">
                                        <input type="radio" id="prescription_for_existing_member" value="prescription_for_existing_member" name="prescription_type">
                                        <label for="prescription_for_existing_member">Prescription for existing member</label>
                                        <div id="existing_memebers">
                                            <select name="filter_by_name" id="filter_by_name" onchange="populatePrescriptionData(this);">
                                                <option value="">Select</option>
                                                <?php foreach ($presc_arr as $pf): ?>
                                                    <option value="<?= $pf->first_name . '*' . $pf->last_name ?>"><?= $pf->rx_group ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="type-wrapper">
                                        <input type="radio" id="prescription_for_new_member" value="prescription_for_new_member" name="prescription_type" checked="checked">
                                        <label for="prescription_for_new_member">Prescription for new member</label>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        $sph_values = load_rx_options('pa_rx-mod-sph');
                        $cyl_values = load_rx_options('pa_rx-mod-cyl');
                        $add_values = load_rx_options('pa_rx-mod-add');
                        //if ($is_admin) {
                        $pd_values = load_rx_options('pa_rx-mod-pd');
                        //}
                        ?>


                        <div class="eye-wrapper right-eye">
                            <div class="cpf_hide_element tm-cell col-12 cpf-type-header js-right-eye-div">
                                <div class="tm-epo-field-label tm-epo-element-label tm-left">RIGHT EYE</div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select sph">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required">SPH</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_2 nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field tm-epo-field tmcp-select" name="sph_right"
                                                        id="sphere_right_<?= $index ?>">
                                                    <option value="">Select</option>
                                                    <?php echo $sph_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select cyl">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required">CYL</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_3 nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field tm-epo-field tmcp-select" name="cyl_right"
                                                        id="cylinder_right_<?= $index ?>">
                                                    <option value="">Select</option>
                                                    <?php echo $cyl_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select axis">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required">AXIS</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_4 nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select ms-input tm-epo-field-label">
                                                <input type="text" class="tmcp-field tm-epo-field tmcp-select" name="axis_right" id="axis_right_<?= $index ?>" 
                                                       value="<?= $presc_arr->axis_right ?>">
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select add">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required">ADD</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_5 nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field tm-epo-field tmcp-select" name="add_right"
                                                        id="add_right_<?= $index ?>">
                                                            <?php echo $add_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select pd">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required">PD</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_5 nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field tm-epo-field tmcp-select" name="pd_right"
                                                        id="pd_right_<?= $index ?>">
                                                    <option value="">Select</option>
                                                    <?php echo $pd_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="eye-wrapper left-eye">
                            <div class="cpf_hide_element tm-cell col-12 cpf-type-header sp-second-header-div">
                                <span class="tm-epo-field-label tm-epo-element-label" style="padding-top: 10px;">LEFT EYE</span> 
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select sp-container-required-hide-div sph">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required tm-disable">&nbsp;</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_6 sp-container-required-hide-ul nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field sp-container-required-hide tm-epo-field tmcp-select"
                                                        name="sph_left" id="sphere_left_<?= $index ?>">
                                                    <option value="">Select</option>
                                                    <?php echo $sph_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select sp-container-required-hide-div cyl">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required tm-disable">&nbsp;</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_7 sp-container-required-hide-ul nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field sp-container-required-hide tm-epo-field tmcp-select"
                                                        name="cyl_left" id="cylinder_left_<?= $index ?>">
                                                    <option value="">Select</option>
                                                    <?php echo $cyl_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select sp-container-required-hide-div axis">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required">&nbsp;</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_4 nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select ms-input tm-epo-field-label">
                                                <input type="text" class="tmcp-field tm-epo-field tmcp-select" name="axis_left" id="axis_left_<?= $index ?>" 
                                                       value="<?= $presc_arr->axis_left ?>">
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select sp-container-required-hide-div add">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required tm-disable">&nbsp;</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_9 sp-container-required-hide-ul nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field sp-container-required-hide tm-epo-field tmcp-select"
                                                        name="add_left" id="add_left_<?= $index ?>">
                                                            <?php echo $add_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="cpf_hide_element tm-cell col-3 cpf-type-select sp-container-required-hide-div pd">
                                <label class="tm-epo-field-label tm-epo-element-label tm-has-required">&nbsp;</label>
                                <div class="tm-extra-product-options-container">
                                    <ul class="tmcp-ul-wrap tmcp-elements tm-extra-product-options-select tm-element-ul-select element_5 nonlist">
                                        <li class="tmcp-field-wrap">
                                            <label class="ms-select tm-epo-field-label">
                                                <select class="tmcp-field tm-epo-field tmcp-select" name="pd_left"
                                                        id="pd_left_<?= $index ?>">
                                                    <option value="">Select</option>
                                                    <?php echo $pd_values; ?>
                                                </select>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="prescription-user-info">
                            <div class="presc-name nameclass" id="fname">
                                <label for="fname">First Name</label>
                                <input type="text" id="first_name_<?= $index ?>" name="first_name" value="<?= $presc_arr->first_name ?>"
                                       required="">
                            </div>
                            <div class="presc-name nameclass" id="lname">
                                <label for="lname">Last Name</label>
                                <input type="text" id="last_name_<?= $index ?>" name="last_name" value="<?= $presc_arr->last_name ?>"
                                       required="">
                            </div>
                            <div class="presc-name nameclass" id="pname">
                                <label for="prescription_name">Prescription Name</label>
                                <input type="text" id="prescription_name_<?= $index ?>" name="prescription_name" value="<?= $presc_arr->prescription_name ?>"
                                       required="">
                            </div>
                            <?php // updated by Shoaib and Abdullah ?>
                            <?php if (!empty($practiceoptions) && is_array($practiceoptions)) { ?>
                                <div class="presc-name nameclass" id="practice">
                                    <label for="rx_prescription_location">Choose Your Practice</label>
                                    <select class="tmcp-field tm-epo-field tmcp-select" name="rx_prescription_location"
                                            id="rx_prescription_location<?= $index ?>">
                                        <option value="">Select</option>
                                        <?php
                                        foreach ($practiceoptions as $practiceoption) {
                                            echo '<option value="' . $practiceoption['label'] . '" ' . (($practiceoption['label'] == $presc_arr->rx_prescription_location) ? 'selected="selected"' : '' ) . '>' . $practiceoption['label'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php } ?>
                            <?php // updated by Shoaib and Abdullah   ?>
                        </div>
                        <div class="presc-date">
                            <label for="rx-date">Prescription Date</label>
                            <?php
                            $prs_date = '';
                            if ($presc_arr->prescription_date != '') {
                                $prs_date = date('d/m/Y', strtotime($presc_arr->prescription_date));
                            }
                            ?>
                            <input type="text" name="prescription_date" id="prescription_date_<?= $index ?>" value="<?= $prs_date ?>"
                                   class="full_date" placeholder="dd/mm/yyyy" required="">
        <!--                        <input type="text" name="prescription_date" value="<?= $presc_arr->prescription_date ?>"
                                   class="full_date" placeholder="yyyy-mm-dd" required="" readonly="true">-->
                        </div>
                        <div class="presc-upload">
                            <?php if ($is_admin) { ?>
                                <input type="hidden" name="prescription_upload" value="<?= $presc_arr->prescription_img_url ?>">
                                <input type="hidden" name="pd_upload" value="<?= $presc_arr->pd_img_url ?>">
                            <?php } else { ?>
                                <label for="rx-date">Upload Prescription </label>
                                <input type="file" name="prescription_upload" accept="image/*, application/pdf" required="rquired" >
                                <div class="file-preview"><span class="clear-img">x</span></div>
                            <?php } ?>
                        </div>
                        <?php if ($is_admin) { ?>
                            <div class="presc-name nameclass">
                                <label for="renewal_<?= $index ?>">Renewal</label>
                                <?php
                                $prescription_renewal = $presc_arr->prescription_renewal;
                                $prescription_renewal_options = array
                                    (
                                    '3m' => '3 Months',
                                    '6m' => '6 Months',
                                    '1y' => '1 Year',
                                    '2y' => '2 Year'
                                );
                                echo '<select name="renewal" id="renewal_' . $index . '">';
                                foreach ($prescription_renewal_options as $value => $option) {
                                    $selected = $value == $prescription_renewal ? 'selected="selected"' : '';
                                    echo "<option {$selected} value=\"{$value}\">{$option}</option>";
                                }
                                echo '</select>';
                                ?>
                            </div>
                            <div class="presc-name nameclass">
                                <label for="comment_<?= $index ?>">Comment</label>
                                <textarea id="comment_<?= $index ?>" name="comment" rows="4" cols="15"><?php echo $presc_arr->prescription_comment; ?></textarea>
                            </div> <div class="clear"></div>
                            <?php if ($presc_arr->pd_img_url) { ?>
                                <hr>
                                <div class="pd-measure">
                                    <div class="presc-name nameclass pd-button">
                                        <div class="pd-img">
                                            <a href="https://pdbolton.optiserver.co.uk?image_url=<?= site_url() . $presc_arr->pd_img_url ?>?pd_left_id=pd_left_<?= $index ?>&pd_right_id=pd_right_<?= $index ?>" target="_new"><img src="<?= site_url() . $presc_arr->pd_img_url ?>"></a>
                                            <a href="https://pdbolton.optiserver.co.uk?image_url=<?= site_url() . $presc_arr->pd_img_url ?>?pd_left_id=pd_left_<?= $index ?>&pd_right_id=pd_right_<?= $index ?>" class="measure-pd" target="_new" style="text-decoration: underline;">MEASURE PD</a>
                                        </div>
                                        <div class="pd-delete-div">
                                            <a href="admin.php?page=saved_invalidate_order&order_id=<?= $presc_arr->order_id; ?>&rx_id=<?= $presc_arr->id ?>&pd_img_url=<?= $presc_arr->pd_img_url ?>" class="delete-pd"  onclick="return confirm('Are you sure you want to delete this image?')" style="display: none;">Delete PD Image</a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <input type="hidden" name="order_id" value="<?= $presc_arr->order_id ?>">
                            <input type="hidden" name="order_line_item_id" value="<?= $presc_arr->order_line_item_id ?>">
                            <input type="hidden" name="user_id" value="<?= $presc_arr->user_id ?>">
                        <?php } ?>
                    </div>
                    <div class="cpfclear"></div>
                    <input type="hidden" name="prescription_id" id="prescription_id_<?= $index ?>" value="<?= $presc_arr->id ?>">
                    <input type="submit" name="add_pres_btn" class="button" onclick="return validate_prescription('<?= $index ?>')" value="<?php
                    if ($presc_arr->id != '') {
                        if ($presc_arr->is_validated) {
                            echo 'Verify Now';
                        } else {
                            echo 'Reverify';
                        }
                    } else {
                        echo 'Add';
                    }
                    ?>">
                </form>
            </div>
        </div>
        <?php
        if ($presc_arr->id) {
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    $('#sphere_right_<?= $index ?> option[value="<?= $presc_arr->sphere_right ?>"]').attr('selected', 'selected');
                    $('#cylinder_right_<?= $index ?> option[value="<?= $presc_arr->cylinder_right ?>"]').attr('selected', 'selected');
                    $('#add_right_<?= $index ?> option[value="<?= $presc_arr->add_right ?>"]').attr('selected', 'selected');
                    $('#pd_right_<?= $index ?> option[value="<?= $presc_arr->pd_right ?>"]').attr('selected', 'selected');
                    $('#sphere_left_<?= $index ?> option[value="<?= $presc_arr->sphere_left ?>"]').attr('selected', 'selected');
                    $('#cylinder_left_<?= $index ?> option[value="<?= $presc_arr->cylinder_left ?>"]').attr('selected', 'selected');
                    $('#add_left_<?= $index ?> option[value="<?= $presc_arr->add_left ?>"]').attr('selected', 'selected');
                    $('#pd_left_<?= $index ?> option[value="<?= $presc_arr->pd_left ?>"]').attr('selected', 'selected');
                });
            </script>
            <?php
        }
        ?>
        <script type="text/javascript">
            function populatePrescriptionData(obj) {
                var cname = obj.value;
                var fields = cname.split('*');
                jQuery('#first_name_0').val(fields[0]);
                jQuery('#last_name_0').val(fields[1]);
            }
        </script>
        <?php
    }

// ajax prescription
    function ajax_prescription_init() {
        // Enable the user with no privileges to run ajax_prescription() in AJAX
        add_action('wp_ajax_nopriv_ajaxprescription', 'ajax_prescription');
        add_action("wp_ajax_ajaxprescription", 'ajax_prescription');
    }

// Execute the action only if the user isn't logged in
//if (is_user_logged_in()) {
    add_action('init', 'ajax_prescription_init');

//}
    function ajax_prescription() {
        $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
        $quantity = empty($_POST['quantity']) ? 1 : apply_filters('woocommerce_stock_amount', $_POST['quantity']);
        $variation_id = $_POST['variation_id'];
        $variation = $_POST['variation'];
        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
        $cart_item_key_frame = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
        if ($passed_validation && $cart_item_key_frame) {
            do_action('woocommerce_ajax_added_to_cart', $product_id);
            //if (get_option('woocommerce_cart_redirect_after_add') == 'yes') {
            //wc_add_to_cart_message($product_id);
            //}
            $data = array(
                'error' => false,
                'frame_key' => $cart_item_key_frame,
                'frame_message' => 'frame is added!'
            );
            // Return fragments
            //WC_AJAX::get_refreshed_fragments();
        } else {
            //$this->json_headers();
            header('Content-Type: application/json');
            // If there was an error adding to the cart, redirect to the product page to show any errors
            $data = array(
                'error' => true,
                'frame_message' => 'frame is not added!' //'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
            );
        }
        $inserted_id = 1; //add_prescription($_POST);
        if ($inserted_id) {
            echo json_encode(array_merge(array('datasave' => true, 'message' => 'prescription saved'), $data));
        } else {
            echo json_encode(array_merge(array('datasave' => false, 'message' => "You can't add more than 20! Or missing prescription values!"), $data));
        }
        die();
    }

    function getUserPrescriptionCount($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'user_prescriptions';
        $user_prescription_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE user_id=$user_id");
        return $user_prescription_count;
    }

// ajax login form
    function ajax_login_init() {
        wp_register_script('ajax-login-script', plugin_dir_path(__FILE__) . '/assets/front-assets/js/ajax-login-script.js', array('jquery'), filemtime(get_stylesheet_directory() . '/assets/js/ajax-login-script.js'), true);
        wp_enqueue_script('ajax-login-script');
        wp_localize_script('ajax-login-script', 'ajax_login_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'redirecturl' => home_url(),
            'loadingmessage' => __('Sending user info, please wait...')
        ));
        // Enable the user with no privileges to run ajax_login() in AJAX
        add_action('wp_ajax_nopriv_ajaxlogin', 'ajax_login');
    }

// Execute the action only if the user isn't logged in
    function execute_if_user_login() {
        if (!is_user_logged_in()) {
            add_action('init', 'ajax_login_init', 20);
        }
    }

    add_action('init', 'execute_if_user_login');

    function ajax_login() {
        // First check the nonce, if it fails the function will break
        check_ajax_referer('ajax-login-nonce', 'security');
        // Nonce is checked, get the POST data and sign user on
        $info = array();
        $info['user_login'] = $_POST['username'];
        $info['user_password'] = $_POST['password'];
        $is_reservation_check = $_POST['is_reservation_check'];
        $info['remember'] = true;
        $user_signon = wp_signon($info, false);
        if (is_wp_error($user_signon)) {
            echo json_encode(array('loggedin' => false, 'message' => __('Wrong username or password.')));
        } else {
            $user_prescription = get_list_prescription($user_signon->ID, true);
            echo json_encode(array('loggedin' => true, 'is_reservation_check' => $is_reservation_check, 'current_user_id' => $user_signon->ID, 'prescription' => $user_prescription, 'message' => __('Login successful, please select the prescription...')));
        }
        die();
    }

    /* RX Module functionality end point */
// attribute label as first value in dropdown.
    add_filter('woocommerce_dropdown_variation_attribute_options_html', 'size_options', 10); //Select Woocommerce hook from wc-template-functions.php

    function size_options($html) {  //Run Arguements
        //$attr = get_taxonomy( $args['attribute'] ); //Select the attribute from the taxonomy
        //$label = $attr->labels->name; //Select the label
        // if Size is label of attribute then add
        /* if(trim($label) === 'Size'){
          $html = __('Testing...', 'fl-builder');
          } */
        global $product;
        //$prdattributes = get_post_meta( $product->id , '_product_attributes', true );
        $discontinuattr = $product->get_attribute('discontinued');
        $arrdisvalues = explode("|", $discontinuattr);
        $arrattrisdiscon = [];
        foreach ($arrdisvalues as $disval) {
            $disval = trim($disval);
            $arrkeyval = explode("--", $disval);
            $arrattrisdiscon['wpid_' . $arrkeyval[0]] = $arrkeyval[1];
        }
// test if product is variable
        if ($product->is_type('variable')) {
            $available_variations = $product->get_available_variations();
            foreach ($available_variations as $key => $value) {
                //get values HERE
                if ($value['attributes'] && !empty($value['attributes']['attribute_pa_size']))
                    $html = str_replace('value="' . $value['attributes']['attribute_pa_size'] . '"', 'value="' . $value['attributes']['attribute_pa_size'] . '" data-discontinued="' . $arrattrisdiscon[$value['sku']] . '"', $html);
            }
        }
        return $html; //Returns "Select a size" or "Select a color" depending on what your attribute name is.
    }

    function cw_change_product_price_display($price) {
        $clear = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($price))))));
        if ($clear == "0 00" && function_exists('is_glasses') && is_glasses()) {
            return 'Not Available';
        }
        return $price;
    }

//add_filter( 'woocommerce_get_price_html', 'cw_change_product_price_display' );
//add_filter( 'woocommerce_cart_item_price', 'cw_change_product_price_display' );
    /* add product cart/order meta */
    add_filter('woocommerce_add_cart_item_data', 'wdm_add_item_data', 1, 2);
    if (!function_exists('wdm_add_item_data')) {

        function wdm_add_item_data($cart_item_data, $product_id) {
            /* Here, We are adding item in WooCommerce session with item meta data */
            extract($_POST);
            session_start();
            $values_arr = array();
            //echo '<pre>'; print_r($_POST); echo '</pre>'; die('here');
            if (isset($frame_cart_item_key)) {
                // set this item key in frame
                $cart = WC()->cart->cart_contents;
                $is_cart_updated = false;
                foreach ($cart as $cart_item_id => $cart_item) {
                    $frame_key = $cart_item['frame_cart_item_key_value'];
                    $product_name = '';
                    if (is_object($cart_item['data'])) {
                        $cart_data = $cart_item['data']->get_data();
                        $product_name = $cart_data['name'];
                    }
                    //echo count($cart_item['composite_children']).' '. $frame_key.'<br>';
                    //echo '<pre>'; print_r($cart_item['data']->get_data()); echo '</pre>';
                    if ($frame_key != '') {
                        if ((is_array($cart_item['composite_children']) && count($cart_item['composite_children']) > 2 ) || $product_name == 'No Prescription') {
                            if (!isset($cart[$frame_key]['lens_cart_item_key_value'])) {  //echo $frame_key; die('sdsd1');
                                $frame_data = $cart[$frame_key];
                                $frame_data['lens_cart_item_key_value'] = $cart_item_id;
                                // set group id for frame product
                                $frame_data['group_id'] = 'grpid_' . $frame_key;
                                WC()->cart->cart_contents[$frame_key] = $frame_data;
                                $is_cart_updated = true;
                                break;
                            }
                        }
                    }
                    // prescription update, remove the old frame
                    if (isset($_GET['update-composite']) && $_GET['update-composite'] != '') {
                        if ($_GET['update-composite'] == $cart_item['lens_cart_item_key_value']) {
                            if (cart_item_exits($cart_item_id)) {
                                WC()->cart->set_quantity($cart_item_id, 0);
                            }
                        }
                    }
                }
                if ($is_cart_updated) {
                    WC()->cart->set_session();
                }
                $values_arr['frame_cart_item_key_value'] = $_SESSION['frame_cart_item_key'] = $frame_cart_item_key;
            }
            if ((isset($frame_cart_item_key) && empty($frame_cart_item_key))) {
                return $cart_item_data;
            } else {
                if (empty($cart_item_data))
                    return $values_arr;
                else
                    return array_merge($cart_item_data, $values_arr);
            }
            unset($_SESSION['frame_cart_item_key']);
            //Unset our custom session variable, as it is no longer needed.
        }

    }
    add_filter('woocommerce_get_cart_item_from_session', 'wdm_get_cart_items_from_session', 1, 3);
    if (!function_exists('wdm_get_cart_items_from_session')) {

        function wdm_get_cart_items_from_session($item, $values, $key) {
            if (array_key_exists('frame_cart_item_key_value', $values)) {
                $item['frame_cart_item_key_value'] = $values['frame_cart_item_key_value'];
                $item['group_id'] = 'grpid_' . $values['frame_cart_item_key_value'];
            }
            return $item;
        }

    }
//add_filter('woocommerce_cart_item_name', 'wdm_add_user_custom_option_from_session_into_cart', 1, 3);
//add_filter('woocommerce_cart_item_child', 'wdm_add_user_custom_option_from_session_into_cart', 1, 3);
    /* if (!function_exists('wdm_add_user_custom_option_from_session_into_cart')) {
      function wdm_add_user_custom_option_from_session_into_cart($string, $values, $cart_item_key) {
      /* code to add custom data on Cart & checkout Page */ /*
      if (!empty($values['frame_cart_item_key_value']) || !empty($values['selected_eye_value']) || !empty($values['eye_pair_value'])) {
      $return_string = $string . "<dl class='variation1'>";
      //$return_string .= "<table class='wdm_options_table' id='" . $values['product_id'] . "'>";
      if (!empty($values['frame_cart_item_key_value'])) {
      $return_string .= "<div><strong>frame_cart_item_key_value:</strong> " . $values['frame_cart_item_key_value'] . "</div>";
      }
      $return_string .= "</dl>";
      return $return_string;
      } else {
      return $string;
      }
      }
      } */
    add_action('woocommerce_add_order_item_meta', 'wdm_add_values_to_order_item_meta', 1, 2);
    if (!function_exists('wdm_add_values_to_order_item_meta')) {

        function wdm_add_values_to_order_item_meta($item_id, $values) {
            global $woocommerce, $wpdb;
            $frame_cart_item_key_value = $values['frame_cart_item_key_value'];
            $group_id = $values['group_id'];
            $lens_cart_item_key_value = $values['lens_cart_item_key_value'];
            //if (!empty($frame_cart_item_key_value)) {
            //wc_add_order_item_meta($item_id, "_frame_cart_item_key_value", $frame_cart_item_key_value);
            //}
            // Fix for refund
            $product_title = "";
            if (is_object($values['data'])) {
                $product_title = $values['data']->get_title();
            }

            if (!empty($group_id)) {
                wc_add_order_item_meta($item_id, "_group_id", $group_id);
            }
            if (!empty($lens_cart_item_key_value)) {
                wc_add_order_item_meta($item_id, "_rx_frame", 'RX');
            } else {
                wc_add_order_item_meta($item_id, "_rx_frame", 'NORX');
            }
            wc_delete_order_item_meta($item_id, 'Measure My PD', '', true);
            if ($product_title === 'Upload Prescription' || $product_title === 'Enter Prescription Now') {
                $presimgurl = wc_get_order_item_meta($item_id, 'Upload your prescription', true);
                //if($presimgurl){
                // fix for prescription image
                if ($presimgurl && strposa($trippedtagval, ['.jpg', '.jpeg', '.png', '.gif', '.pdf']) !== false) {
                    $encryptedimg = generateimagetoencoded($presimgurl);
                    wc_update_order_item_meta($item_id, 'Upload your prescription', $encryptedimg);
                }
            }
            if ($frame_cart_item_key_value != '' && !isset($values['composite_parent'])) {
                $prescription_id = formate_prescription_data_to_save($values, $item_id);
                if ($prescription_id) {
                    wc_add_order_item_meta($item_id, "_prescription_id", $prescription_id);
                }
            }
        }

    }

    function formate_prescription_data_to_save($values, $item_id) {
        $prescription_arr = array();
        foreach ($values['composite_data'] as $value_prescription) {
            //$is_validate = false;
            if ($value_prescription['type'] == 'bundle') {
                // fetch prescription fname, lname and date
                foreach ($value_prescription['addons'] as $mainaddons) {
                    if (strpos($mainaddons['field_name'], 'first') !== false) {
                        $prescription_arr['first_name'] = $mainaddons['value'];
                    }
                    if (strpos($mainaddons['field_name'], 'last') !== false) {
                        $prescription_arr['last_name'] = $mainaddons['value'];
                    }
                    if (strpos($mainaddons['field_name'], 'save-your-prescription-for-future') !== false) {
                        $prescription_arr['prescription_name'] = $mainaddons['value'];
                    }
                    // by shoaib and abdullah
                    if (strpos($mainaddons['field_name'], 'practice') !== false) {
                        $prescription_arr['rx_prescription_location'] = $mainaddons['value'];
                    }
                    // by shoaib and abdullah
                    if (strpos($mainaddons['field_name'], 'date') !== false) {
                        $prescription_arr['prescription_date'] = $mainaddons['value'];
                    }
                    if (strpos($mainaddons['field_name'], 'right-pd') !== false) {
                        $prescription_arr['pd_right'] = $mainaddons['value'];
                    }
                    if (strpos($mainaddons['field_name'], 'left-pd') !== false) {
                        $prescription_arr['pd_left'] = $mainaddons['value'];
                    }

                    /* if right and left pd is empty then get the value of -pd and devide it by 2 and save the right-pd and left-pd in db */
                    if (empty($prescription_arr['pd_right']) && empty($prescription_arr['pd_left']) && strpos($mainaddons['field_name'], '-pd') !== false) {
                        if (is_numeric($mainaddons['value'])) {
                            $pdvalue = ($mainaddons['value'] / 2);
                            $pdvalue = number_format($pdvalue, 1);
                            $prescription_arr['pd_left'] = $pdvalue;
                            $prescription_arr['pd_right'] = $pdvalue;
                        } else {
                            $prescription_arr['pd_left'] = $mainaddons['value'];
                            $prescription_arr['pd_right'] = $mainaddons['value'];
                        }
                    }

                    if (strpos($mainaddons['field_name'], 'upload-your-prescription') !== false) {
                        //$prescription_arr['prescription_img_url'] = $mainaddons['value'];
                        $prescription_arr['prescription_img_url'] = generateimagetoencoded($mainaddons['value']);
                    }
                    if (strpos($mainaddons['field_name'], 'measure-my-pd') !== false) {
                        if ($mainaddons['value']) {
                            $base = dirname(__FILE__);
                            if (!is_dir($base . "/../../uploads/pd_imgs")) {
                                mkdir($base . "/../../uploads/pd_imgs", 0755, true);
                            }
                            $data = $mainaddons['value'];
                            $target_dir = $base . "/../../uploads/pd_imgs/";
                            if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
                                $data = substr($data, strpos($data, ',') + 1);
                                $type = strtolower($type[1]); // jpg, png, gif
                                if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png'])) {
                                    throw new \Exception('invalid image type');
                                }
                                $data = base64_decode($data);
                                if ($data === false) {
                                    throw new \Exception('base64_decode failed');
                                }
                            } else {
                                throw new \Exception('did not match data URI with image data');
                            }
                            $file_name = random_string(50);
                            if (file_put_contents($target_dir . $file_name . ".{$type}", $data)) {
                                $prescription_arr['pd_img_url'] = '/wp-content/uploads/pd_imgs/' . $file_name . ".{$type}";
                            }
                        }
                    }
                }
                // fetch prescription left and right values
                foreach ($value_prescription['stamp'] as $stampbndl) {
                    $eye_type = '';
                    if (strpos($stampbndl['product_id'], RIGHT_EYE_ID) !== false) {
                        $eye_type = 'right';
                    } else if (strpos($stampbndl['product_id'], LEFT_EYE_ID) !== false) {
                        $eye_type = 'left';
                    }
                    foreach ($stampbndl['addons'] as $prescriptionvalues) {
                        $prescription_arr[strtolower($prescriptionvalues['name']) . '_' . $eye_type] = $prescriptionvalues['value'];
                    }
                    /* if(!isset($stampbndl['addons'])) {
                      $is_validate = true;
                      } */
                }
                //die('here');            
                $prescription_arr['is_validated'] = 1;
                $prescription_arr['user_id'] = $user_id = get_current_user_id();
                $order_id = wc_get_order_id_by_order_item_id($item_id);
                // updated by Shoaib and Abdullah
                $prescription_arr['verification_type'] = get_the_title($value_prescription['product_id']); //get_verification_types($order_id);
                // updated by Shoaib and Abdullah$prescription_arr
                if ($prescription_arr['verification_type'] != 'Use Saved Prescription') {
                    $prescription_id = add_prescription($prescription_arr);
                }
                if (isset($prescription_arr['prescription_img_url'])) {
                    wc_update_order_item_meta($item_id, 'Upload your prescription', (( isset($prescription_arr['prescription_img_url']) ) ? $prescription_arr['prescription_img_url'] : ''));
                }
                //if($is_validate && $prescription_id) {
                if ($prescription_id) {
                    // delete any existing order ref already stored in the table for this order (this can only possible
                    // if the order is not successfully processed(failed, or pending)
                    delete_rx_existing_order_ref($order_id);
                    save_prescription_order_ref($prescription_id, $order_id, $item_id);
                }
                return $prescription_id;
            }
        }
        return 0;
    }

    function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $key;
    }

    add_action('woocommerce_before_cart_item_quantity_zero', 'wdm_remove_user_custom_data_options_from_cart', 1, 1);
    if (!function_exists('wdm_remove_user_custom_data_options_from_cart')) {

        function wdm_remove_user_custom_data_options_from_cart($cart_item_key) {
            global $woocommerce;
            // Get cart        
            $cart = $woocommerce->cart->get_cart();
            // For each item in cart, if item is upsell of deleted product, delete it
            foreach ($cart as $key => $values) {
                if ($values['frame_cart_item_key_value'] == $cart_item_key ||
                        $values['group_id'] == $cart_item_key)
                    unset($woocommerce->cart->cart_contents[$key]);
            }
        }

    }
    /* end product cart/order meta */
    add_action('woocommerce_remove_cart_item', 'remove_rx_item', 10, 2);

    function remove_rx_item($cart_item_key, $cart) {
        if (isset($cart->cart_contents[$cart_item_key]['lens_cart_item_key_value'])) {
            //echo "<br> --------1-------- <br>";
            $lens_item_key = $cart->cart_contents[$cart_item_key]['lens_cart_item_key_value'];
            if ($lens_item_key != '' && isset($cart->cart_contents[$lens_item_key])) {
                //WC()->cart->remove_cart_item($lens_item_key);
                if (cart_item_exits($lens_item_key)) {
                    //echo "<br> --------" . $lens_item_key . "-------- <br>";
                    $cartcontent = WC()->cart->cart_contents[$lens_item_key];
                    //echo "<br>---------------------- Groupid = " . $cartcontent['group_id'] . "-------------<br>";
                    WC()->cart->set_quantity($lens_item_key, 0);
                    $cart = WC()->cart->get_cart();
                    // For each item in cart, if item is upsell of deleted product, delete it
                    foreach ($cart as $key => $values) {
                        //echo $values['frame_cart_item_key_value'] . ' == ' . $cartcontent['frame_cart_item_key_value'] . ' || ' . $values['group_id'] . ' == ' . $cartcontent['group_id'] . " ----- key = " . $key . "<br>";
                        if ($values['group_id'] == $cartcontent['group_id']) {
                            WC()->cart->set_quantity($key, 0);
                        }
                    }
                }
            }
        }
        if (isset($_GET['frame_remove']) && $_GET['frame_remove'] == '1') {
            if (isset($cart->cart_contents[$cart_item_key]['frame_cart_item_key_value'])) {
                //echo "<br> --------2-------- <br>";
                $frame_item_key = $cart->cart_contents[$cart_item_key]['frame_cart_item_key_value'];
                if ($frame_item_key != '' && isset($cart->cart_contents[$frame_item_key])) {
                    if (cart_item_exits($frame_item_key)) {
                        WC()->cart->set_quantity($frame_item_key, 0);
                    }
                }
            }
        }
    }

    /*
     * @desc Force individual cart item
     */

    function force_individual_cart_items($cart_item_data, $product_id) {
        $unique_cart_item_key = md5(microtime() . rand());
        $cart_item_data['unique_key'] = $unique_cart_item_key;
        return $cart_item_data;
    }

    add_filter('woocommerce_add_cart_item_data', 'force_individual_cart_items', 10, 2);
    /*
     * @desc Remove quantity selector in all product type
     */

    function remove_all_quantity_fields($return, $product) {
        return true;
    }

//add_filter( 'woocommerce_is_sold_individually', 'remove_all_quantity_fields', 10, 2 );
// change component product per page.
    add_filter('woocommerce_component_options_per_page', 'wc_cp_component_options_per_page', 10, 3);

    function wc_cp_component_options_per_page($results_count, $component_id, $composite) {
        $results_count = 12;
        return $results_count;
    }

    require_once(ROOTDIR . 'admin-section.php');
    require_once(ROOTDIR . 'filter_invalidate_order.php');
    require_once(ROOTDIR . 'saved_invalidate_order.php');
// auto update cart count
    add_filter('woocommerce_add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment');
    if (!function_exists('woocommerce_header_add_to_cart_fragment')) {

        function woocommerce_header_add_to_cart_fragment($fragments) {
            global $settings;
            $cart_items_header = WC()->cart->get_cart();
            $cart_count_top = 0;
            foreach ($cart_items_header as $cart_item_key => $cart_item) {
                if (function_exists('is_cl_product') && is_cl_product($cart_item['product_id'])) {
                    $cart_count_top+= $cart_item['quantity'];
                    continue;
                }
                //by Shoaib CM-42
                if (function_exists('is_accessories') && is_accessories($cart_item['product_id'])) {
                    $cart_count_top+= $cart_item['quantity'];
                    continue;
                }
                // frame product without leses
                if (function_exists('is_frame_only') && is_frame_only($cart_item['product_id']) &&
                        (!isset($cart_item['group_id']))) {
                    $cart_count_top++;
                    continue;
                }
                // frame product with leses
                if (function_exists('is_rxable') && is_rxable($cart_item['product_id']) && (isset($cart_item['group_id']))) {
                    $cart_count_top++;
                    continue;
                }
                // any other product like accessories etc.
                if (!isset($cart_item['group_id'])) {
                    $cart_count_top++;
                }
            }
            ob_start();
            ?>
            <a class="js-cart" href="<?php echo wc_get_cart_url(); ?>" title="<?php _e('View your shopping cart', 'woothemes'); ?>">
                <i class="<?= $settings->cart_icon ?>"></i>
                <span class="cart-menu-items"><?php echo sprintf(_n('%d', '%d', $cart_count_top, 'woothemes'), $cart_count_top); ?></span>        
            </a>
            <?php
            $fragments['a.js-cart'] = ob_get_clean();
            return $fragments;
        }

    }
    /* Shoaib Prescription Image Start */
    /* woocommerce on order saved first move the prescription images to out side of public directory */

// define the woocommerce_saved_order_items callback 
    function generateimagetoencoded($imageurl) {
        $rootonelevel1 = str_replace('public_html/', '', dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR);
        $diroutroot = $rootonelevel1 . 'shopvcs_images';
        /* if (defined('SVNIMGPATH')) {
          $diroutroot = SVNIMGPATH;
          } */

        if (!is_dir($diroutroot)) {
            mkdir($diroutroot, 0755, true);
        }

        $imgpath = str_replace(get_site_url(), $_SERVER['DOCUMENT_ROOT'], $imageurl);
        $returnurl = $imageurl;
        /* if there is parameter in page url */
        // move file from current directory to another directory
        if (file_exists($imgpath)) {
            $imgbase64 = imgtobase64($imgpath);
            $returnurl = $imgpath . ".txt";
            $uploadfilepath = $diroutroot . '/' . basename($returnurl);
            $uploadfilepath = trim($uploadfilepath);

            if (file_exists($uploadfilepath)) {
                $returnurl = $imgpath . time() . ".txt";
                $uploadfilepath = $diroutroot . '/' . basename($returnurl);
                $uploadfilepath = trim($uploadfilepath);

                if (file_put_contents($uploadfilepath, $imgbase64) === false) {
                    //echo "<br> -----  file not saved.. ---- <br>";
                }
            } else {
                if (file_put_contents($uploadfilepath, $imgbase64) === false) {
                    //echo "<br> -----  file not saved.. ---- <br>";
                }
            }
        }

        // Use openssl_encrypt() function to encrypt the data 
        $encryption = encryptstring($uploadfilepath, CIPHERING, ENCRYPTSALT);
        return $encryption;
    }

    function imgtobase64($path) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    function encryptstring($thestring, $chipering, $encryptsalt) {
        return @openssl_encrypt(trim($thestring), $chipering, $encryptsalt);
    }

    function decryptstring($encryptedstring, $chipering, $encryptsalt) {
        return @openssl_decrypt($encryptedstring, $chipering, $encryptsalt);
    }

    function get_prescriptionid_byorderid($order_id, $item_id) {
        global $wpdb;
        $uid = $wpdb->get_results(
                $wpdb->prepare(
                        "SELECT upref.id, upref.user_id, upref.first_name, upref.last_name, upref.rx_group, upref.prescription_name, upref.prescription_name, upref.prescription_img_url FROM " . $wpdb->prefix . "user_prescriptions_order_ref upref INNER JOIN " . $wpdb->prefix . "user_prescriptions up ON upref.rx_id = up.id 
                    WHERE order_id = %s AND order_line_item_id = %s
                    LIMIT 1", $order_id, $item_id
                )
        );
        if ($uid) {
            return $uid;
        }
        return 0;
    }

    /* get the link which will show this file in window of filepath */

    function getencryptedimgbypath($filepath) {
        if (!empty($filepath)) {
            $base64strImg = file_get_contents($filepath);
            $filename = basename($filepath);
            $showingname = str_replace(".txt", '', $filename);
            $encodename = str_replace(".", '%dot%', $filename);
            //return '<img id="my_image" src="' . $base64strImg . '" />';
            return '<a href="' . get_home_url() . '/downloadimage.php?filename=' . $encodename . '" class="popup">' . $showingname . '</a>';
        }
    }

    /* only get the url of the file provided in parameter */

    function getencryptedimgurlbypath($filepath) {
        if (!empty($filepath)) {
            $base64strImg = file_get_contents($filepath);
            $filename = basename($filepath);
            $showingname = str_replace(".txt", '', $filename);
            $encodename = str_replace(".", '%dot%', $filename);
            //return '<img id="my_image" src="' . $base64strImg . '" />';
            return get_home_url() . '/downloadimage.php?filename=' . $encodename;
        }
    }

    /* get the link with pdf icon which will show this file in window of filepath */

    function getencryptedimgbypathwithicon($filepath) {
        $base64strImg = file_get_contents($filepath);
        $filename = basename($filepath);
        $showingname = str_replace(".txt", '', $filename);
        $encodename = str_replace(".", '%dot%', $filename);
        //return '<img id="my_image" src="' . $base64strImg . '" />';
        return '<a href="' . get_home_url() . '/downloadimage.php?filename=' . $encodename . '" target="_new"><img src="' . site_url() . '/wp-content/plugins/opticommerce-rx/assets/img/pdf.png" alt="' . $showingname . '" title="' . $showingname . '"></a>';
    }

    function getencryptionofimg($filepath) {
        return file_get_contents($filepath);
    }

// define the woocommerce_display_item_meta callback 
    function filter_woocommerce_display_item_meta($html, $item, $args) {




        // make filter magic happen here... 
        $arr_metas = explode('Upload Prescription:', $html);
        $arr_metas2 = array();
        $theencimg = '';
        if (!empty($arr_metas[1]))
            $arr_metas2 = explode('</p>', $arr_metas[1]);
        if (!empty($arr_metas2[0])) {
            //echo "<br> -------------------- <br>";
            //echo strip_tags($arr_metas2[0]);
            //echo "<br> -------------------- <br>";
            $theimgpath = strip_tags($arr_metas2[0]);
            $theimgpath = trim($theimgpath);
            // check if the current string is not url then it will be encrypted image
            //if(strpos($theimgpath, '.jpg') === false && strpos($theimgpath, '.jpeg') === false && strpos($theimgpath, '.png') === false && strpos($theimgpath, '.gif') === false){
            if (strposa($theimgpath, ['.jpg', '.jpeg', '.png', '.gif', '.pdf']) === false) {
                $decriptfilename = decryptstring($theimgpath, CIPHERING, ENCRYPTSALT);
                $theimage = getencryptedimgbypath($decriptfilename);
                $html = str_ireplace($theimgpath, $theimage, $html);
            }
        }









        return $html;
    }

// add the filter 
    add_filter('woocommerce_display_item_meta', 'filter_woocommerce_display_item_meta', 10, 3);

    function strposa($haystack, $needles = array(), $offset = 0) {
        $chr = array();
        foreach ($needles as $needle) {
            $res = strpos($haystack, $needle, $offset);
            if ($res !== false)
                $chr[$needle] = $res;
        }
        if (empty($chr))
            return false;
        return min($chr);
    }

    /**
     * Changing a meta value
     * @param  string        $value  The meta value
     * @param  WC_Meta_Data  $meta   The meta object
     * @param  WC_Order_Item $item   The order item object
     * @return string        The title
     */
    function change_order_item_meta_value($value, $meta, $item) {
        // By using $meta-key we are sure we have the correct one.
        //echo 'Upload your prescription === ' . $meta->key;
        if ('Upload your prescription' === $meta->key || 'Upload your prescriptions' === $meta->key) {
            //echo "<br> -------------- <br>";
            //echo $value;
            //echo "<br> -------------- <br>";
            $trippedtagval = strip_tags($value);
            //if (strposa($trippedtagval, ['.jpg', '.jpeg', '.png', '.gif', '.pdf']) === false) {
            // change the condition to fix the edit order and pay page where prescription currept image link is showing
            if ($trippedtagval && strposa($trippedtagval, ['.jpg', '.jpeg', '.png', '.gif', '.pdf']) === false) {
                $decriptfilename = decryptstring($value, CIPHERING, ENCRYPTSALT);
                $value = getencryptedimgbypath($decriptfilename);
            }
        }
        return $value;
    }

    add_filter('woocommerce_order_item_display_meta_value', 'change_order_item_meta_value', 20, 3);
    /* Shoaib Prescription Image End */

    function is_glasses($product_id = 0) {
        if (!$product_id) {
            global $post;
            $product_id = $post->ID;
        }
        $terms = wp_get_post_terms($product_id, 'product_cat');
        $categories = array();
        foreach ($terms as $term) {
            $categories[] = $term->slug;
        }
        if (in_array('glasses', $categories)) {
            return true;
        }
        return false;
    }

    function is_sunglasses($product_id = 0) {
        if (!$product_id) {
            global $post;
            $product_id = $post->ID;
        }
        $terms = wp_get_post_terms($product_id, 'product_cat');
        $categories = array();
        foreach ($terms as $term) {
            $categories[] = $term->slug;
        }
        if (in_array('sunglasses', $categories)) {
            return true;
        }
        return false;
    }

    add_filter('facetwp_query_args', function( $query_args, $class ) {
        $query_args['posts_per_page'] = 12;
        return $query_args;
    }, 10, 2);

    function is_frame($product_id) {
        $terms = wp_get_post_terms($product_id, 'product_cat');
        $categories = array();
        foreach ($terms as $term) {
            if (in_array($term->slug, array('glasses', 'sunglasses'))) {
                return true;
            }
        }
        return false;
    }

    function cart_item_exits($cart_key) {
        $check_cart_items = WC()->cart->get_cart();
        $is_cart_exist = false;
        foreach ($check_cart_items as $cart_item_key => $cart_item) {
            if ($cart_item_key == $cart_key) {
                $is_cart_exist = true;
                break;
            }
        }
        return $is_cart_exist;
    }

    add_filter('woocommerce_hidden_order_itemmeta', 'hidden_order_itemmeta', 50);

    function hidden_order_itemmeta($args) {
        $args[] = '_group_id';
        $args[] = '_rx_frame';
        $args[] = '_prescription_id';
        return $args;
    }

    add_filter('woocommerce_continue_shopping_redirect', 'bbloomer_change_continue_shopping');

    function bbloomer_change_continue_shopping() {
        return wc_get_page_permalink('shop');
    }

// Add IN LAB status
// add another status same like processing
    function wc_renaming_order_status($order_statuses) {
        foreach ($order_statuses as $key => $status) {
            if ('wc-processing' === $key) {
                $order_statuses['wc-in-lab'] = _x('IN LAB', 'Order status', 'woocommerce');
            }
        }
        return $order_statuses;
    }

    add_filter('wc_order_statuses', 'wc_renaming_order_status');

    /**
     * Register new status
     * Tutorial: http://www.sellwithwp.com/woocommerce-custom-order-status-2/
     * */
    function register_processed_order_status() {
        register_post_status('wc-in-lab', array(
            'label' => 'IN LAB',
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('IN LAB <span class="count">(%s)</span>', 'IN LAB <span class="count">(%s)</span>')
        ));
    }

    add_action('init', 'register_processed_order_status');
}

// adding meta box for rx available section on product detail page

function add_rx_available_meta_box() {
    add_meta_box("rx-meta-box", "RXable Frame?", "rx_available_meta_box_markup", "product", "side", "high", null);
}

add_action("add_meta_boxes", "add_rx_available_meta_box");

function rx_available_meta_box_markup($object) {
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");
    $rx_available = get_post_meta($object->ID, "rx_available", true);
    $rx_frame_only = get_post_meta($object->ID, "rx_frame_only", true);
    $rx_sunglasses = get_post_meta($object->ID, "rx_sunglasses", true);
    ?>
    <div>     
        <label for="rx_available"><?php _e('RX Enabled?', 'cutombox'); ?></label>
        <input name="rx_available" type="radio" id="rx_available_yes" value="yes"<?php echo (($rx_available == "yes" || $rx_available == '') ? ' checked' : ""); ?>>
        <label for="rx_available_yes"><?php _e('Yes', 'cutombox'); ?></label>            
        <input name="rx_available" type="radio" id="rx_available_no" value="no"<?php echo ($rx_available == "no" ? ' checked' : ""); ?>>
        <label for="rx_available_no"><?php _e('No', 'cutombox'); ?></label>
    </div>
    <div>
        <label for="rx_frame_only"><?php _e('Frame Only?', 'cutombox'); ?></label>
        <input name="rx_frame_only" type="radio" id="rx_frame_only_yes" value="yes"<?php echo (($rx_frame_only == "yes" || $rx_frame_only == '') ? ' checked' : ""); ?>>
        <label for="rx_frame_only_yes"><?php _e('Yes', 'cutombox'); ?></label>
        <input name="rx_frame_only" type="radio" id="rx_frame_only_no" value="no"<?php echo ($rx_frame_only == "no" ? ' checked' : ""); ?>>
        <label for="rx_frame_only_no"><?php _e('No', 'cutombox'); ?></label>
    </div>
    <div>
        <label for="rx_sunglasses"><?php _e('Sunglasses RX?', 'cutombox'); ?></label>
        <input name="rx_sunglasses" type="radio" id="rx_sunglasses_yes" value="yes"<?php echo (($rx_sunglasses == "yes") ? ' checked' : ""); ?>>
        <label for="rx_sunglasses_yes"><?php _e('Yes', 'cutombox'); ?></label>            
        <input name="rx_sunglasses" type="radio" id="rx_sunglasses_no" value="no"<?php echo (($rx_sunglasses == "no" || $rx_sunglasses == '') ? ' checked' : ""); ?>>
        <label for="rx_sunglasses_no"><?php _e('No', 'cutombox'); ?></label>
    </div>
    <?php
}

function save_rx_available_meta_box($post_id, $post, $update) {
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if (!current_user_can("edit_post", $post_id))
        return $post_id;

    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $slug = "product";
    if ($slug != $post->post_type)
        return $post_id;

    $rx_available_value = "";

    if (isset($_POST["rx_available"])) {
        $rx_available_value = $_POST["rx_available"];
        $rx_frame_only_value = $_POST["rx_frame_only"];
        $rx_sunglasses_value = $_POST["rx_sunglasses"];
        update_post_meta($post_id, "rx_available", $rx_available_value);
        update_post_meta($post_id, "rx_frame_only", $rx_frame_only_value);
        update_post_meta($post_id, "rx_sunglasses", $rx_sunglasses_value);
    }
}

add_action("save_post", "save_rx_available_meta_box", 10, 3);

// end of adding meta box for rx available section on product detail page

function is_rxable($product_id = 0) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    $rx_available = get_post_meta($product_id, "rx_available", true);
    if ($rx_available) {
        if ($rx_available == "yes") {
            return true;
        }
    }
    return false;
}

function is_frame_only($product_id = 0) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    $rx_frame_only = get_post_meta($product_id, "rx_frame_only", true);
    if ($rx_frame_only) {
        if ($rx_frame_only == "yes") {
            return true;
        }
    }
    return false;
}

function is_sunglasses_rx($product_id = 0) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    $rx_sunglasses = get_post_meta($product_id, "rx_sunglasses", true);
    if ($rx_sunglasses) {
        if ($rx_sunglasses == "yes") {
            return true;
        }
    }
    return false;
}
