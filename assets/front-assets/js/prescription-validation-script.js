// for recommended lens
function setRecommendedOption() {
    var className;
    if (jQuery(".js-lensoptions").css("display") == "block") {
        className = '.js-lensoptions';
    } else if (jQuery(".js-bifocal").css("display") == "block") {
        className = '.js-bifocal';
    } else if (jQuery(".js-verifocal").css("display") == "block") {
        className = '.js-verifocal';
    }
    right_sphere = computValue(jQuery('#sphere_right select.sphere-cylinder-dp option:selected').text());
    right_cylinder = computValue(jQuery('#cylinder_right select.sphere-cylinder-dp option:selected').text());
    left_sphere = computValue(jQuery('#sphere_left select.sphere-cylinder-dp option:selected').text());
    left_cylinder = computValue(jQuery('#cylinder_left select.sphere-cylinder-dp option:selected').text());
    right_count = Math.abs(right_sphere) + Math.abs(right_cylinder);
    left_count = Math.abs(left_sphere) + Math.abs(left_cylinder);
    final_val = (right_count > left_count) ? right_count : left_count;
    jQuery(className + ' .js-lens-type-ul li').removeClass('recommended-lens');
    //recommended class
    if (final_val <= 3.25) {
        //bronze
        jQuery(className + ' .js-lens-type-ul li:eq(1)').addClass('recommended-lens');
        var chkbox = jQuery(className + ' .js-lens-type-ul li:eq(1)').find("input[type='radio']");
        if (!(jQuery(chkbox).is(":checked"))) {
            jQuery(chkbox).trigger("click");
        }

    } else if (final_val > 3.25 && final_val <= 4.75) {
        // silver
        jQuery(className + ' .js-lens-type-ul li:eq(2)').addClass('recommended-lens');
        var chkbox = jQuery(className + ' .js-lens-type-ul li:eq(2)').find("input[type='radio']");
        if (!(chkbox.is(":checked"))) {
            chkbox.trigger("click");
        }
    } else if (final_val > 4.75 && final_val <= 6.50) {
        // gold
        jQuery(className + ' .js-lens-type-ul li:eq(3)').addClass('recommended-lens');
        var chkbox = jQuery(className + ' .js-lens-type-ul li:eq(3)').find("input[type='radio']");
        if (!(chkbox.is(":checked"))) {
            chkbox.trigger("click");
        }
    } else {
        // platinum
        jQuery(className + ' .js-lens-type-ul li:eq(4)').addClass('recommended-lens');
        var chkbox = jQuery(className + ' .js-lens-type-ul li:eq(4)').find("input[type='radio']");
        if (!(chkbox.is(":checked"))) {
            chkbox.trigger("click");
        }
    }
}

function computValue(dval) {
    val = isNaN(parseFloat(dval)) ? 0 : parseFloat(dval);
    return val;
}


function populate_prescription(prescription_arr) {
	jQuery(".sp-prescription-loader").fadeIn(200);
    jQuery('ul.products li:first-child .wc-pao-addon-sph input').val(prescription_arr.sphere_right);
    jQuery('ul.products li:first-child .wc-pao-addon-cyl input').val(prescription_arr.cylinder_right);
    jQuery('ul.products li:first-child .wc-pao-addon-axis input').val(prescription_arr.axis_right);
    jQuery('ul.products li:first-child .wc-pao-addon-add input').val(prescription_arr.add_right);
    jQuery('ul.products li:first-child .wc-pao-addon-pd input').val(prescription_arr.pd_right);
    jQuery('ul.products li:nth-child(2) .wc-pao-addon-sph input').val(prescription_arr.sphere_left);
    jQuery('ul.products li:nth-child(2) .wc-pao-addon-cyl input').val(prescription_arr.cylinder_left);
    jQuery('ul.products li:nth-child(2) .wc-pao-addon-axis input').val(prescription_arr.axis_left);
    jQuery('ul.products li:nth-child(2) .wc-pao-addon-add input').val(prescription_arr.add_left);
    jQuery('ul.products li:nth-child(2) .wc-pao-addon-pd input').val(prescription_arr.pd_left);
    jQuery('.wc-pao-addon-first-name input').val(prescription_arr.first_name);
    jQuery('.wc-pao-addon-last-name input').val(prescription_arr.last_name);
    jQuery('.wc-pao-addon-save-your-prescription-for-future-use input').val(prescription_arr.prescription_name); 
    jQuery('.wc-pao-addon-date-of-prescription input').val(prescription_arr.prescription_date);
    //jQuery('#inline-1 .wrapper-popup header.header').text('Prescription is populated!');
    //setRecommendedOption();
    	
	setTimeout(function () {
		jQuery(".sp-prescription-loader").fadeOut(500);
		_composite.show_next_step(); 
    }, 1000);
    return false;
}

 var isSubmitted = false;
function submitAddToCartForm(user_id, ajaxurl) {  
    /* start prescription validation */ /*is_prescription_valid = true;          type_of_glasses = jQuery('.composite_component.first .component_options select.component_options_select option:selected').text();          console.log(type_of_glasses);          sphere_right_text = jQuery('#sphere_right select option:selected').text();          if (sphere_right_text == 'Select') {          jQuery('#sphere_right label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#sphere_right label.tm-epo-field-label').css("border-color", "#c6c6c6");          }     cylinder_right_text = jQuery('#cylinder_right select option:selected').text();          axis_right_text = jQuery('#axis_right select option:selected').text();          /* check for right axis  */ /*        if (axis_right_text == 'Select' && cylinder_right_text != 'Select') {     jQuery('#axis_right label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#axis_right label.tm-epo-field-label').css("border-color", "#c6c6c6");          }          /* check for right cylinder   */ /*       if (axis_right_text != 'Select' && cylinder_right_text == 'Select') {          jQuery('#cylinder_right label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#cylinder_right label.tm-epo-field-label').css("border-color", "#c6c6c6");          }     /* validate it if bifocal or varifocal is selected   */ /*       add_right_text = jQuery('#add_right select option:selected').text();          if (add_right_text == 'Select' && (type_of_glasses == 'VARIFOCAL')) {     jQuery('#add_right label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#add_right label.tm-epo-field-label').css("border-color", "#c6c6c6");          }          sphere_left_text = jQuery('#sphere_left select option:selected').text();          if (sphere_left_text == 'Select') {          jQuery('#sphere_left label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#sphere_left label.tm-epo-field-label').css("border-color", "#c6c6c6");          }          cylinder_left_text = jQuery('#cylinder_left select option:selected').text();          axis_left_text = jQuery('#axis_left select option:selected').text();          /* check for left axis     */ /*     if (axis_left_text == 'Select' && cylinder_left_text != 'Select') {          jQuery('#axis_left label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#axis_left label.tm-epo-field-label').css("border-color", "#c6c6c6");          }          /* check for left cylinder     */ /*     if (axis_left_text != 'Select' && cylinder_left_text == 'Select') {          jQuery('#cylinder_left label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#cylinder_left label.tm-epo-field-label').css("border-color", "#c6c6c6");          }     /* validate it if bifocal or varifocal is selected   */ /*       add_left_text = jQuery('#add_left select option:selected').text()          if (add_left_text == 'Select' && (type_of_glasses == 'VARIFOCAL')) {          jQuery('#add_left label.tm-epo-field-label').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#add_left label.tm-epo-field-label').css("border-color", "#c6c6c6");          }     pres_date = jQuery('#prescription_date input.full_date').val();          if(pres_date === "") {          jQuery('#prescription_date label.tm-epo-field-label input').css("border-color", "#F00");          is_prescription_valid = false;          } else {          jQuery('#prescription_date label.tm-epo-field-label input').css("border-color", "#c6c6c6");          }     if (!is_prescription_valid) {          jQuery('.pagination_element_1563460009 a').trigger("click");          /*jQuery('html, body').animate({          scrollTop: jQuery(".sp-prescription-options").offset().top          }, 1200);*/ /*            return false;            } */ /* validating frame and getting its form values */
    //console.log('clicked here');
    //return false;
    if(!isSubmitted){
        var presc_step_id = 1584107435;
    	var valid = validation_check();
    	if(!valid) {
    		_composite.navigate_to_step(_composite.get_step_by( 'id', presc_step_id));
    		setTimeout( function(){
    			jQuery("#component_" + presc_step_id + " .component_option_thumbnail.selected button").click();
    		}, 1000);
    		jQuery('html, body').animate({
    			scrollTop: jQuery(".composite_pagination").offset().top          
    		}, 1200);
    		
    		return false;
    	}
    	
        $variation_form = jQuery('.variations_frame_form');
        var var_id = $variation_form.find('input[name=variation_id]').val();
        var product_id = $variation_form.find('input[name=product_id]').val();
        var quantity = $variation_form.find('input[name=quantity]').val(); /*attributes = []; */
        jQuery('.ajaxerrors').remove();
        var item = {},
            check = true;
        variations = $variation_form.find('select[name^=attribute]'); /* Updated code to work with radio button */
        if (!variations.length) {
            variations = $variation_form.find('[name^=attribute]:checked');
        } /* Backup Code for getting input variable */
        if (!variations.length) {
            variations = $variation_form.find('input[name^=attribute]');
        }
        variations.each(function() {
            var $this = jQuery(this),
                attributeName = $this.attr('name'),
                attributevalue = $this.val(),
                index, attributeTaxName;
            $this.removeClass('error');
            if (attributevalue.length === 0) {
                index = attributeName.lastIndexOf('_');
                attributeTaxName = attributeName.substring(index + 1);
                $this /*.css( 'border', '1px solid red' ) */ .addClass('required error') /*.addClass( 'barizi-class' ) */ .before('<div class="ajaxerrors"><p>Please select ' + attributeTaxName + '</p></div>');
                check = false;
            } else {
                item[attributeName] = attributevalue;
            } /* Easy to add some specific code for select but doesn't seem to be needed*/ /* if ( $this.is( 'select' ) ) {         } else {         } */
        });
        if (!check) {
            jQuery('.close-rx-pop').trigger('click');
            return false;
        } /* end prescription validation */ /*if (user_id) { */
        isSubmitted = true;
        savePrescriptionAndAddFrameToCart(user_id, ajaxurl, product_id, quantity, var_id, item); /*} else {        jQuery('form.cart').submit();    } */
        return false;
    }
    return false;
}

function savePrescriptionAndAddFrameToCart(user_id, ajaxurl, product_id, quantity, var_id, item) {
    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: ajaxurl,
        data: {
            'action': 'ajaxprescription',
            /*calls wp_ajax_nopriv_ajaxprescription */ 'user_id': user_id,
            /*          'prescription_name': jQuery('#prescription_name input').val(),            'prescription_date': jQuery('#prescription_date input').val(),            'sphere_left': jQuery('#sphere_left select').val(),            'cylinder_left': jQuery('#cylinder_left select').val(),            'axis_left': jQuery('#axis_left select').val(),            'add_left': jQuery('#add_left select').val(),            'sphere_right': jQuery('#sphere_right select').val(),            'cylinder_right': jQuery('#cylinder_right select').val(),            'axis_right': jQuery('#axis_right select').val(),            'add_right': jQuery('#add_right select').val(),*/ /* onward data is related to frame*/ 
            product_id: product_id,
            quantity: quantity,
            variation_id: var_id,
            variation: item
        },
        success: function(data) {
            console.log(data.frame_key);
            //return false;
            if (data.frame_key) {
                jQuery('<input>').attr({
                    type: 'hidden',
                    id: 'frame_cart_item_key',
                    name: 'frame_cart_item_key',
                    value: data.frame_key
                }).appendTo('form.composite_form');
                jQuery('form.composite_form').submit();
            } else {
                location.reload();
            }
        }
    });
}

function remove_frame(obj) {
    console.log(obj.href);
    is_remove_frame = confirm('Do you want to remove frame as well?');
    if (is_remove_frame) {
        obj.href = obj.href + '&frame_remove=1';
    }
    return false;
}

function setProductValues() {
    var pid = jQuery("#product_id").val();
	
    frame_variation_id = jQuery('.variations_form[data-product_id=' + pid + '] input[name=variation_id]').val();
    if (frame_variation_id === '') {
        jQuery('.frame-validation').html('Please select the frame size.');
        return false;
    }
    jQuery('.frame_button #variation_id').val(frame_variation_id);
    jQuery('form.frame_button').submit();
    return false;
}