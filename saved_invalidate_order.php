<?php

function saved_invalidate_order() {
    ?>
    <!-- <link type="text/css" href="<?php //echo WP_PLUGIN_URL;   ?>/opticommerce-rx/assets/css/datepicker.min.css" rel="stylesheet" /> -->
    <link type="text/css" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css" rel="stylesheet">
    <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/opticommerce-rx/assets/css/style-admin.css" rel="stylesheet" />
    <link type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/opticommerce-rx/assets/css/rx_style.css" rel="stylesheet" />
    <!-- <script type="text/javascript" src="<?php //echo WP_PLUGIN_URL;   ?>/opticommerce-rx/assets/js/datepicker.min.js" /></script> -->
    <script type="text/javascript" src="https://code.jquery.com/ui/1.13.0/jquery-ui.js"></script>
    <script type="text/javascript" src="<?php echo WP_PLUGIN_URL; ?>/opticommerce-rx/assets/js/form-validation.js" /></script>
    <link type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <div class="wrap">
        <?php
        $order_id = filter_input(INPUT_GET, 'order_id');
        $rx_id = filter_input(INPUT_GET, 'rx_id');
        $filter_validated = filter_input(INPUT_GET, 'filter_validated');
        $pd_img_url = filter_input(INPUT_GET, 'pd_img_url');
        if (isset($order_id) && $order_id != '') {
            // delete pd image and remove it from db rx record
            if ((isset($rx_id) && $rx_id != '') && (isset($pd_img_url) && $pd_img_url != '')) {
                if (remove_pd_img_from_db($rx_id)) {
                    unlink(getcwd() . '/..' . $pd_img_url);
                    echo '<span style="color:green;">PD image has been deleted</span>';
                    // need to verify either its deleted or not
                    /* if(!unlink($pd_img_url)) {
                      echo '<span style="color:green;">PD image has been deleted</span>';
                      } else {
                      echo '<span style="color:red;">PD image cannot be deleted due to an error</span>';
                      } */
                }
            }
            ?>
            <h2>Verify Orders</h2><br>
            <?php
            $order_line_item_id = filter_input(INPUT_POST, 'order_line_item_id');
            $order = wc_get_order($order_id);
            $order_items = $order->get_items();
            if (isset($order_line_item_id) && $order_line_item_id != '') {
                $rx_data = filter_input_array(INPUT_POST);
                $rx_data['is_validated'] = 0;
                extract($rx_data);

                // save it under order line item                
                //$order_data = $order->get_data();

                $i = 0;
                foreach ($order_items as $item_key => $item_values) {
                    if ($item_key > $rx_data['order_line_item_id']) {
                        $item_data = $item_values->get_data();
                        if (in_array(strtolower($item_data['name']), array('enter prescription now', 'upload prescription', 'existing patient', 'send prescription later'))) {
                            if ($rx_data['first_name'] != '') {
                                wc_update_order_item_meta($item_key, "First Name", $rx_data['first_name']);
                            }
                            if ($rx_data['last_name'] != '') {
                                wc_update_order_item_meta($item_key, "Last Name", $rx_data['last_name']);
                            }
                            if ($rx_data['prescription_name'] != '') {
                                wc_update_order_item_meta($item_key, "Save your prescription for future use", $rx_data['prescription_name']);
                            }
                            if ($rx_data['prescription_date'] != '') {
                                wc_update_order_item_meta($item_key, "Date of prescription", $rx_data['prescription_date']);
                            }
                            if ($rx_data['renewal'] != '') {
                                wc_update_order_item_meta($item_key, "Renewal", $rx_data['renewal']);
                            }
                            if ($rx_data['comment'] != '') {
                                wc_update_order_item_meta($item_key, "Comment", $rx_data['comment']);
                            }
                            $i++;
                        }
                        if ($item_data['name'] == 'Right Eye') {
                            wc_update_order_item_meta($item_key, "SPH", $rx_data['sph_right']);
                            wc_update_order_item_meta($item_key, "CYL", $rx_data['cyl_right']);
                            wc_update_order_item_meta($item_key, "Axis", $rx_data['axis_right']);
                            wc_update_order_item_meta($item_key, "Add", $rx_data['add_right']);
                            wc_update_order_item_meta($item_key, "PD", $rx_data['pd_right']);

                            $i++;
                        }
                        if ($item_data['name'] == 'Left Eye') {
                            wc_update_order_item_meta($item_key, "SPH", $rx_data['sph_left']);
                            wc_update_order_item_meta($item_key, "CYL", $rx_data['cyl_left']);
                            wc_update_order_item_meta($item_key, "Axis", $rx_data['axis_left']);
                            wc_update_order_item_meta($item_key, "Add", $rx_data['add_left']);
                            wc_update_order_item_meta($item_key, "PD", $rx_data['pd_left']);
                            $i++;
                        }
                    }
                    if ($i >= 3) {
                        break;
                    }
                }

                if ($i) {
                    // update rx in rx table
                    if (update_prescription($rx_data) !== false) {
                        echo '<span style="color:green;">Prescription updated!</span><br>';
                        // remove rx ref from ref table
                        //remove_rx_user_ref($rx_data);
                    } else {
                        echo '<span style="color:red;">There is some issue while updating prescription. Please try again later.</span><br>';
                    }
                }
            }
            $invalidate_order_items = get_invalidate_order_items($order_id, $filter_validated);
            ?>
            <table class='wp-list-table widefat fixed striped posts'>               
                <?php
                $cnt = 0;
                foreach ($invalidate_order_items as $item_obj) {
                    $itemgroupid = wc_get_order_item_meta($item_obj->order_line_item_id, "_group_id", true);
                    ?>
                    <tr>
                        <td class="manage-column ss-list-width">
                            <div class="rx-validation">
                                <div class="left-container">
                                    <b><?php echo $item_obj->verification_type; ?></b>
                                    <div class="frame-info">
                                        <span class="lens-details">
                                            <?php
                                            foreach ($order_items as $item_key => $item_values) {
                                                //if ($suborderid == $item_values->get_meta('sub-orderID', true)) {
                                                if ($itemgroupid == $item_values->get_meta('_group_id', true)) {
                                                    $item_name = $item_values->get_name();
                                                    if (!in_array($item_name, array('Right Eye', 'Left Eye'))) {
                                                        echo ' <br>' . $item_values->get_name();
                                                    }
                                                }
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <?php
                                    addEditForm($item_obj, true, $cnt);
                                    ?>
                                </div>
                                <div class="right-container"><?php
                                    if ($item_obj->prescription_img_url != '') {
                                        //$filename = '/home/shopvcs/shopvcs_images/Loosen-1.jpg.txt'; 
                                        //$filename = '/home/shopvcs/shopvcs_images/Elizabeth-Murray-12-1-19.pdf.txt'; 
                                        $img_url = '';
                                        if (filter_var($item_obj->prescription_img_url, FILTER_VALIDATE_URL)) {
                                            $img_url = $item_obj->prescription_img_url;
                                            echo '<a href="' . $img_url . '" class="popup" target="_new"><img src="' . $img_url . '" ></a><br>';
                                        } else {
                                            $filename = decryptstring($item_obj->prescription_img_url, CIPHERING, ENCRYPTSALT);
                                            //$filename = str_replace('public_html/', '', $filename);
                                            if (strpos($filename, '.pdf') !== false) {
                                                echo getencryptedimgbypathwithicon($filename);
                                            } else {
                                                $img_stream = getencryptionofimg($filename);
                                                $img_url = getencryptedimgurlbypath($filename);
                                                echo '<a href="' . $img_url . '" class="popup" target="_new"><img src="' . $img_stream . '" ></a><br>';
                                            }
                                        }
                                    }
                                    $cnt++;
                                    ?>
                                </div>
                            </div>
                            <div class="clear"></div>
                            <?php
                            if ($cnt < count($invalidate_order_items)) {
                                echo '<hr>';
                            }
                            ?>                            
                        </td>
                    </tr>
                    <?php
                }
                if (!count($invalidate_order_items)) {
                    $ord = new WC_Order($order_id);
                    if ($ord->get_status() == 'processing') {
                        $ord->update_status('in-lab');
                    }
                    ?>
                    <tr>
                        <td class="manage-column ss-list-width">
                            No more validation item left under this order, <a href="admin.php?page=filter_invalidate_order">go back</a> to validate other orders.
                        </td>
                    </tr>
            <?php } ?>        
            </table>
    <?php } ?>
    </div>
    <?php
}

function remove_pd_img_from_db($rx_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_prescriptions';
    return $wpdb->update(
                    $table_name, array(
                'pd_img_url' => ''
                    ), array(
                'id' => $rx_id
                    )
    );
}

// updated by Shoaib and Abdullah
/* for getting the current product id based on order */
function get_order_productid($order_id, $line_item_id) {
    $order = new WC_Order($order_id);
    $items = $order->get_items();
    $product_id = 0;
    foreach ($items as $item) {
        $itemdata = $item->get_data();
        if ($line_item_id == $itemdata['id']) {
            //print_r($itemdata);
            // get item meta_data array
            foreach ($itemdata['meta_data'] as $itemeta) {

                //print_r($itemeta->value);
                if (is_array($itemeta->value) || is_object($itemeta->value)) {
                    foreach ($itemeta->value as $compositeprod) {
                        if (is_array($compositeprod) && array_key_exists('addons', $compositeprod)) {
                            $product_id = $compositeprod['product_id'];
                        }
                    }
                }
            }
            break;
        }
    }

    return $product_id;
}

// updated by Shoaib and Abdullah
/* for getting the current order products in array */
function get_verification_types($order_id) {
    $order = new WC_Order($order_id);
    $items = $order->get_items();
    $product_ids = [];
    $currentprodid = 0;
    $itemcountre = 0;
    $dosetcurrentid = true;
    $product_titles = [];
    $doincludeintitles = true;
    foreach ($items as $item) {
        $itemdata = $item->get_data();
        $itemcountre++;
        if ($dosetcurrentid && $itemdata['variation_id'] > 0 && $itemdata['subtotal'] > 0) {
            $dosetcurrentid = false;
            $currentprodid = $itemdata['product_id'];
        }

        foreach ($itemdata['meta_data'] as $itemeta) {
            //print_r($itemeta->value);
            if (is_array($itemeta->value) || is_object($itemeta->value)) {
                foreach ($itemeta->value as $compositeprod) {
                    //if (is_array($compositeprod) && array_key_exists('addons', $compositeprod)) {
                    $prescriptiontitle = $item->get_name();
                    //$product_titles[] = $currentprodid . " --  " .$item->get_name();
                    // first check if one of the prescription type is in title of product then there will be 3 possibilities
                    // 1- differnt products with many prescription types
                    // 2- same product with different prescription types
                    // 3- same product with same prescription type
                    if (stripos($prescriptiontitle, "No Prescription") !== false || stripos($prescriptiontitle, "Existing Patient") !== false || stripos($prescriptiontitle, "Prescription Now") !== false || stripos($prescriptiontitle, "upload prescription") !== false || stripos($prescriptiontitle, "Saved Prescription") !== false || stripos($prescriptiontitle, "Send Prescription Later") !== false) {
                        if (!in_array($currentprodid, $product_ids) && $doincludeintitles) {
                            $product_ids[] = $currentprodid;
                            $product_titles[] = $item->get_name();
                            $dosetcurrentid = true;
                        } else if ($doincludeintitles) {
                            $product_titles[] = $item->get_name();
                            $dosetcurrentid = true;
                        }
                        $doincludeintitles = false;
                    } else {
                        $doincludeintitles = true;
                    }

                    //}
                }
            }
        }
    }

    return implode(", ", $product_titles);
}

// updated by Shoaib and Abdullah
// get the addons for specific product and specific addon field
function get_product_addon_options($productid, $fieldname) {

    $arraddon = get_post_meta($productid, '_product_addons', true);
    //print_r($arraddon);
    $options = [];
    if (is_array($arraddon)) {
        foreach ($arraddon as $theaddon) {
            if (!empty($theaddon['name']) && stripos($theaddon['name'], $fieldname) !== false) {
                $options = $theaddon['options'];
                break;
            }
        }
    }


    return $options;
}

function get_invalidate_order_items($order_id, $filter_validated) {
    global $wpdb;
    if ($filter_validated) {
        $order_query .= ' AND up.is_validated=0';
    } else {
        $order_query .= ' AND up.is_validated=1';
    }
    $results = $wpdb->get_results("SELECT up.*, upor.order_id, upor.order_line_item_id "
            . "FROM " . $wpdb->prefix . "user_prescriptions AS up, "
            . $wpdb->prefix . "user_prescriptions_order_ref AS upor "
            . "WHERE up.id=upor.rx_id AND upor.order_id=" . $order_id . $order_query, OBJECT);

    return $results;
}

function remove_rx_user_ref($rx_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . "user_prescriptions_order_ref";
    $is_deleted = $wpdb->query("DELETE FROM $table_name WHERE rx_id =" . $rx_data['prescription_id'] . " AND "
            . "order_id=" . $rx_data['order_id'] . " AND order_line_item_id=" . $rx_data['order_line_item_id']);
    if ($is_deleted) {
        //echo '<span style="color:green;">Prescription reference deleted updated!</span>';
    } else {
        //echo '<span style="color:red;">There is some issue while deleting prescription reference. Please try again later.</span>';
    }
}

// delete pd images on order complete

add_action('woocommerce_order_status_completed', 'delete_order_pd_image');
add_action('woocommerce_order_status_cancelled', 'delete_order_pd_image');
add_action('woocommerce_order_status_refunded', 'delete_order_pd_image');

function delete_order_pd_image($order_id) {
    global $wpdb;
    $invalidate_order_items = $wpdb->get_results("SELECT up.id, up.pd_img_url, upor.order_id, upor.order_line_item_id "
            . "FROM " . $wpdb->prefix . "user_prescriptions AS up, "
            . $wpdb->prefix . "user_prescriptions_order_ref AS upor "
            . "WHERE up.id=upor.rx_id AND upor.order_id=" . $order_id, OBJECT);
    foreach ($invalidate_order_items as $item_obj) {
        if ($item_obj->pd_img_url != '') {
            if (remove_pd_img_from_db($item_obj->id)) {
                unlink(getcwd() . '/..' . $item_obj->pd_img_url);
                echo '<span style="color:green;">PD image has been deleted</span>';
                // need to verify either its deleted or not
                /* if(!unlink($pd_img_url)) {
                  echo '<span style="color:green;">PD image has been deleted</span>';
                  } else {
                  echo '<span style="color:red;">PD image cannot be deleted due to an error</span>';
                  } */
            }
        }
    }
}
