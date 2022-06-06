jQuery( document ).ready(function($) {
	/*----- Date Field -----*/
	jQuery('input.full_date').datepicker({ 
        dateFormat: 'dd/mm/yy',
        maxDate: '+3Y',
        changeMonth: true,
        changeYear: true,
        yearRange: 'c-20:c+20',
	});
	jQuery('input[name="prescription_date"]').attr('readonly','readonly');
		
});

function validate_prescription(form_index) {
    /* start prescription validation */ 
    is_prescription_valid = true;
    sphere_right_text = jQuery('#sphere_right_'+form_index+' option:selected').text();
    if (sphere_right_text === 'Select') {
        jQuery('#sphere_right_'+form_index).parent().css("border-color", "#F00");
        is_prescription_valid = false;
    } else {
        jQuery('#sphere_right_'+form_index).parent().css("border-color", "#c0c0c0");
    }
    cylinder_right_text = jQuery('#cylinder_right_'+form_index+' option:selected').text();
    axis_right_text = jQuery('#axis_right_'+form_index).val();          /* check for right axis  */
    if(axis_right_text === '' || isNaN(axis_right_text)) {
        axis_right_text = '';
    } else {
        jQuery('#axis_right_'+form_index).val((axis_right_text * 1).toFixed(2));
    }
    if (axis_right_text === '' && (cylinder_right_text != 'Select' && cylinder_right_text != '0.00')) {
        jQuery('#axis_right_'+form_index).parent().css({"border-color": "#F00", 
             "border-weight":"1px", 
             "border-style":"solid"});
        is_prescription_valid = false;
    } else {
        jQuery('#axis_right_'+form_index).parent().css("border-color", "#c0c0c0");
    }          /* check for right cylinder   */
    if (axis_right_text != '' && (cylinder_right_text == 'Select' || cylinder_right_text == '0.00')) {
        jQuery('#cylinder_right_'+form_index).parent().css("border-color", "#F00");
        is_prescription_valid = false;
    } else {
        jQuery('#cylinder_right_'+form_index).parent().css("border-color", "#c0c0c0");
    }
    sphere_left_text = jQuery('#sphere_left_'+form_index+' option:selected').text();
    if (sphere_left_text === 'Select') {
        jQuery('#sphere_left_'+form_index).parent().css("border-color", "#F00");
        is_prescription_valid = false;
    } else {
        jQuery('#sphere_left_'+form_index).parent().css("border-color", "#c0c0c0");
    }
    cylinder_left_text = jQuery('#cylinder_left_'+form_index+' option:selected').text();
    axis_left_text = jQuery('#axis_left_'+form_index).val();          /* check for left axis     */
    if(axis_left_text === ''  || isNaN(axis_left_text)) {
        axis_left_text = '';
    } else {
        jQuery('#axis_left_'+form_index).val((axis_left_text * 1).toFixed(2));
    }
    if (axis_left_text === ''  && (cylinder_left_text != 'Select' && cylinder_left_text != '0.00')) {
        jQuery('#axis_left_'+form_index).parent().css({"border-color": "#F00", 
             "border-weight":"1px", 
             "border-style":"solid"});
        is_prescription_valid = false;
    } else {
        jQuery('#axis_left_'+form_index).parent().css("border-color", "#c0c0c0");
    }          /* check for left cylinder     */
    if (axis_left_text != '' && (cylinder_left_text === 'Select' || cylinder_left_text === '0.00')) {
        jQuery('#cylinder_left_'+form_index).parent().css("border-color", "#F00");
        is_prescription_valid = false;
    } else {
        jQuery('#cylinder_left_'+form_index).parent().css("border-color", "#c0c0c0");
    }
    first_name = jQuery('#first_name_'+form_index).val();
    if (first_name === "") {
        jQuery('#first_name_'+form_index).css("border-color", "#F00"); 
        is_prescription_valid = false;
    } else {
        jQuery('#first_name_'+form_index).css("border-color", "#c0c0c0");
    }
    last_name = jQuery('#last_name_'+form_index).val();
    if (last_name === "") {
        jQuery('#last_name_'+form_index).css("border-color", "#F00");
        is_prescription_valid = false;
    } else {
        jQuery('#last_name_'+form_index).css("border-color", "#c0c0c0"); 
    }
    prescription_name = jQuery('#prescription_name_'+form_index).val();
    if (prescription_name === "") {
        jQuery('#prescription_name_'+form_index).css("border-color", "#F00");
        is_prescription_valid = false;
    } else {
        jQuery('#prescription_name_'+form_index).css("border-color", "#c0c0c0");
    }
    pres_date = jQuery('#prescription_date_'+form_index).val();
    if (pres_date === "") {
        jQuery('#prescription_date_'+form_index).css("border-color", "#F00");
        is_prescription_valid = false;
    } else {
        jQuery('#prescription_date_'+form_index).css("border-color", "#c0c0c0");
    }
	
	if (typeof axis_left_text !== 'undefined' && axis_left_text != "" && (axis_left_text > 180 || axis_left_text < 0 || isNaN(axis_left_text))) {
		jQuery(".axis-txt").remove();
		is_prescription_valid = false; 
		jQuery('#axis_left_'+form_index).parent().css("border-color", "#F00");
		jQuery('#axis_left_'+form_index).closest(".left-container").append('<p class="prompt-text axis-txt axis-txt-left">Please select axis value between 0 - 180.</p>'); 
	}
	else if(axis_left_text !== "") {		
		jQuery('#axis_left_'+form_index).parent().css("border-color", "#c0c0c0");
		jQuery(".axis-txt-left").remove();
	}
	if (typeof axis_right_text !== 'undefined' && axis_right_text != "" && (axis_right_text > 180 || axis_right_text < 0 || isNaN(axis_right_text))) {
		jQuery(".axis-txt").remove();
		is_prescription_valid = false; 
		jQuery('#axis_right_'+form_index).parent().css("border-color", "#e31937");
		jQuery('#axis_right_'+form_index).closest(".left-container").append('<p class="prompt-text axis-txt axis-txt-right">Please select axis value between 0 - 180.</p>');
	}
	else if(axis_right_text !== "") {
		jQuery('#axis_right_'+form_index).parent().css("border-color", "#c0c0c0");
		jQuery(".axis-txt-right").remove();
	}
    
    if (!is_prescription_valid) {
        return false;
    } 
    
    return true;
}