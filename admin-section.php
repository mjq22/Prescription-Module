<?php

//menu items
add_action('admin_menu','classes_register_modifymenu');
function classes_register_modifymenu() {
	
	//this is the main item for the menu
	add_menu_page('RX Verification', //page title
	'RX Verification', //menu title
	'manage_woocommerce', //capabilities
	'filter_invalidate_order', //menu slug
	'filter_invalidate_order', //function
                '', // icon url
                40
	);
	//this submenu is HIDDEN, however, we need to add it anyways
	add_submenu_page(null, //parent slug
	'Save Invalidate Order', //page title
	'Save Invalidate Order', //menu title
	'manage_woocommerce', //capability
	'saved_invalidate_order', //menu slug
	'saved_invalidate_order'); //function
}