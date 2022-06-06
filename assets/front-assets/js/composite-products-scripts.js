var _composite = '';
var selectedOptTrigger = false;
var saved_presc_list = '';
var sphDiffErrorCheck = false;
var sphOppErrorCheck = false;
var cylOppErrorCheck = false;
var $ = jQuery;

jQuery(document).ready(function($){

    $('.composite_data').on( 'wc-composite-initializing', composite_init);

    function composite_init(event, composite){
        _composite = composite;

        // Add Custom Back Button
        $('body').find('.composite_form').append('<a href="#" class="custom-back">Back</a>');
        $('body').find('.composite_form .composite_pagination').after('<a href="#" class="start-over">Start Over</a>');

        // Composite Actions
        composite.actions.add_action( 'component_selection_changed', selection_changed_handler, 100, composite ); 
		composite.actions.add_action( 'component_selection_content_changed', selection_content_changed_handler, 100, composite );
		composite.actions.add_action( 'active_step_changed', active_step_changed_handler, 100, composite ); 

		// Custom Events 
		$('body').on('click', '.wc-pao-addon-confirm', confirm_presc_step);
		$('body').on('change', '.wc-pao-addon-upload-your-prescription input[type="file"]', uploadPresc);
		$('body').on('click', '.wc-pao-addon-file-preview .wc-pao-addon-heading', removeUploadImg);
		$('body').on('focus', '.wc-pao-addon-save-your-prescription-for-future-use input, .wc-pao-addon-date-of-prescription input', clearPlaceholder);
		$('body').on('blur', '.wc-pao-addon-save-your-prescription-for-future-use input, .wc-pao-addon-date-of-prescription input', showPlaceholder);
		$('body').on('change', '.wc-pao-addon-cyl select', axisToggle);
		$('body').on('change', '.wc-pao-addon-pd-checkbox input[type="checkbox"]', pdToggle);
		$('body').on('click', '.switch-presc', switchPresc);
		$('body').on('click', '.wc-pao-addon-send-your-prescription-later .wc-pao-addon-description a', switchEnterPresc);
		$('body').on('change', '.wc-pao-addon-my-pupillary-distance-pd-is-listed-on-my-prescription input[type="checkbox"]', pdAreaToggle);
		$('body').on('click touchend', '.component_wrap .wc-pao-addon-container .wc-pao-addon-image-swatch', function(){ _composite.show_next_step(); });
		$('body').on('click', '.composite_form .custom-back', backNav);
		$('body').on('change', '.wc-pao-addon-pd select', pdSinglePopulate);
		$('body').on('change', '.wc-pao-addon-right-pd select, .wc-pao-addon-left-pd select', pdRLPopulate);
		$('body').on('click', '.wc-pao-addon.wc-pao-addon-measure-my-pd', showMeasurePD);
		$('body').on('click', '.composite_form .start-over', startOver);
		$('#review-sidebar').on('click', '.toggler', detailToggle);

	}
	//Close button
	cross_url = jQuery("#frame_product_url").val();
	jQuery("#cross-rx img").on("click", function(){
		window.location.href = cross_url;
	});

});

function selection_changed_handler(comp){

    let compConf = _composite.api.get_composite_configuration();
	let step_id = comp.step_id;
	let step_title = compConf[step_id].title;
	let sel_title = compConf[step_id].selection_title;
	let prod_type = compConf[step_id].product_type;
	let has_addons = _composite.get_current_step().has_addons();

	// No Prescription
	if(sel_title == 'No Prescription'){

		// Resetting Fields
		$('body').find('ul.products li:first-child .wc-pao-addon-sph select option[value=""]').prop('selected', true);
		$('body').find('ul.products li:nth-child(2) .wc-pao-addon-sph select option[value=""]').prop('selected', true);
		
		$('body').find('ul.products li:first-child .wc-pao-addon-cyl select option[value=""]').prop('selected', true);
		$('body').find('ul.products li:nth-child(2) .wc-pao-addon-cyl select option[value=""]').prop('selected', true);
		
		$('body').find('ul.products li:first-child .wc-pao-addon-add select option[value=""]').prop('selected', true);
		$('body').find('ul.products li:nth-child(2) .wc-pao-addon-add select option[value=""]').prop('selected', true);

		$('body').find('ul.products li:first-child .wc-pao-addon-axis input').val('');
		$('body').find('ul.products li:nth-child(2) .wc-pao-addon-axis input').val('');

		$('body').find('ul.products li:first-child .wc-pao-addon-pd select option[value=""]').prop('selected', true);
		$('body').find('ul.products li:nth-child(2) .wc-pao-addon-pd select option[value=""]').prop('selected', true);

	}

	// Addons "Light Adaptive"
	if(has_addons && prod_type == 'simple'){

		let winWidth = $(window).width();
		let leftCorr = (winWidth > 992) ? 10 : 0 ;
		let selComp = $('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail.selected');
		let selCompParent = $('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail.selected').parent('li');

		let selCompWidth = selComp.innerWidth();
		let selCompHeight = selCompParent.innerHeight();
		let selCompTop = selCompParent.position().top + 5;
		let selCompLeft = selCompParent.position().left + leftCorr;

		setTimeout(function(){
			let selMargin = parseInt(selComp.innerHeight());
			
			$('.composite_component').addClass('active_content');
			$('body').find('#component_' + step_id + ' .component_data').addClass('swatches-addon');

			$('body').find('#component_' + step_id + ' .component_content').slideDown();

			$('body').find('#component_' + step_id + ' .component_wrap .wc-pao-addon-image-swatch').each(function(){
				let _this = $(this);

				if(_this.find('span').length == 0){
					_this.append('<span>' + _this.data('value').split('-')[0] + '</span>');
				}
			});

			$('body').find('#component_' + step_id + ' .component_wrap').css({'width' : (selCompWidth + 0), 'top' : (selCompTop + selCompHeight), 'left' : selCompLeft, 'position' : 'absolute'});
			$('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail_container').css({ 'height' : selCompHeight });
			selCompParent.css({ 'marginBottom' : selMargin });

		}, 300);

	}else{

		$('body').find('#component_' + step_id + ' .component_wrap').slideUp(function(){
			$('body').find('#component_' + step_id + ' .component_wrap').attr('style', '');
			$('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail_container').attr('style', '');
		});

	}

	// Variable Swatches Products
	if(prod_type == 'variable'){

		$('.composite_component').addClass('active_content');
		$('body').find('#component_' + step_id + ' .component_data').addClass('swatches-addon');

		$('#component_' + step_id).find('.component_options').slideUp(function(){
			$('#component_' + step_id).find('.component_content').slideDown();
		});

		let step_variations = _composite.get_current_step().component_selection_model.get_active_variations_data();
		if(step_variations){
			step_variations.forEach(function(k, i){
				
				let attrName = $("#component_" + step_id + " .variations ul").attr('data-attribute_name');
				let attrib = k.attributes[attrName];
				let thumbnails = $("#component_" + step_id + " .variations li");

				thumbnails.each(function(t_i, t_k){
					let vari = $(t_k).attr('data-value');
					console.log(k.image.src);
					if(vari == attrib){
						if($(t_k).find('.variable-item-contents .vari-title').length < 1){
							$(t_k).find('.variable-item-contents').prepend('<span class="vari-title">' + vari.replace("-", " ") + '</span>');
							$(t_k).find('.variable-item-contents').append('<img src="'+k.image.src+'">');
							$(t_k).find('.variable-item-contents').append('<div class="vari-desc">' + k.variation_description + '</div>');
							$(t_k).find('.variable-item-contents').append('<div class="vari-price">' + k.price_html + '</div>');
						}
					}
				});
			});
		}

		$('body').find('.component_content table.variations tr.attribute_options:nth-child(1) ul.variable-items-wrapper li').on('click', showVariations);

	}
}

function selection_content_changed_handler(comp){
	
	let compConf = _composite.api.get_composite_configuration();
	let step_id = comp.step_id;
	let step_title = compConf[step_id].title;
	let sel_title = compConf[step_id].selection_title;
	let prod_type = compConf[step_id].product_type;
	
	if(prod_type == 'bundle'){
		$('#component_' + step_id).addClass('active_content');

		$('#component_' + step_id).find('.component_options').slideUp(function(){
			$('#component_' + step_id).find('.component_content').slideDown();
		});
	}

	// Date Field
	let prescDate = $('body').find('.wc-pao-addon-date-of-prescription');
	if(prescDate.length > 0){
		prescDate.find('input').datepicker({
			changeMonth: true,
      		changeYear: true,
      		yearRange: 'c-40:c+40',
			dateFormat: "dd/mm/yy",
			maxDate : 0
		});
		prescDate.find('input').attr('readonly','readonly');
	}

	// Prescription Bundle Default Selections
	let isBundleItem = ($('body').find('ul.products li').hasClass('bundled_item_hidden')) ? false : true;

	if(isBundleItem){

		let sphRight = $('body').find('ul.products li:first-child .wc-pao-addon-sph select').val();
		let sphLeft = $('body').find('ul.products li:nth-child(2) .wc-pao-addon-sph select').val();

		let cylRight = $('body').find('ul.products li:first-child .wc-pao-addon-cyl select').val();
		let cylLeft = $('body').find('ul.products li:nth-child(2) .wc-pao-addon-cyl select').val();

		let addRight = $('body').find('ul.products li:first-child .wc-pao-addon-add select').val();
		let addLeft = $('body').find('ul.products li:nth-child(2) .wc-pao-addon-add select').val();

		let axisRight = $('body').find('ul.products li:first-child .wc-pao-addon-axis input').val();
		let axisLeft = $('body').find('ul.products li:nth-child(2) .wc-pao-addon-axis input').val();

		if(!sphRight){ 
			$('body').find('ul.products li:first-child .wc-pao-addon-sph select option[data-label="0.00"]').prop('selected', true) 
		}
		if(!sphLeft){ 
			$('body').find('ul.products li:nth-child(2) .wc-pao-addon-sph select option[data-label="0.00"]').prop('selected', true) 
		}

		if(!cylRight){ 
			$('body').find('ul.products li:first-child .wc-pao-addon-cyl select option[data-label="0.00"]').prop('selected', true) 
		}
		if(!cylLeft){ 
			$('body').find('ul.products li:nth-child(2) .wc-pao-addon-cyl select option[data-label="0.00"]').prop('selected', true) 
		}

		if(!addRight){ 
			$('body').find('ul.products li:first-child .wc-pao-addon-add select option[data-label="n/a"]').prop('selected', true) 
		}
		if(!addLeft){ 
			$('body').find('ul.products li:nth-child(2) .wc-pao-addon-add select option[data-label="n/a"]').prop('selected', true) 
		}

		if(!axisRight){
			$('body').find('ul.products li:first-child .wc-pao-addon-axis input').addClass('disabled');
		}
		if(!axisLeft){
			$('body').find('ul.products li:nth-child(2) .wc-pao-addon-axis input').addClass('disabled');	
		}

	}

	// Saved Prescription
	if(sel_title == 'Use Saved Prescription'){

		saved_presc_list = (saved_presc_list) ? saved_presc_list : $("#inline-1");

		$('body #component_' + step_id).find('.component_content .component_data').prepend(saved_presc_list);
		$('body #component_' + step_id).find('ul.products').hide();
		$('body #component_' + step_id).find('.bundle_data').hide();
			
	}

}

function active_step_changed_handler(comp){

	setTimeout(function(){

		let lastStep = _composite.get_current_step().is_last();

		if(!lastStep){
			selectedOptTrigger = false;

			let compConf = _composite.api.get_composite_configuration();
			let step_id = comp.step_id;
			let currConf = compConf[step_id];
			let prod_type = currConf.product_type;
			let prod_id = currConf.product_id;
			let sel_title = currConf.selection_title;
			let has_addons = _composite.get_current_step().has_addons();

			// Show Bundle Items for Prescription
			if(sel_title !== 'No selection'){
				$('body #component_' + step_id).removeClass('active_content');
				$('body #component_' + step_id).find('.component_options').show();
			}

			$('body').find('#component_' + step_id + ' .component_option_thumbnail.selected button').unbind('click');
			$('body').find('#component_' + step_id + ' .component_option_thumbnail.selected button').on('click', selectedOption);
		
			// Add Total Price in front of main prescription product 
			let price_value = $('.widget_composite_summary_price .composite_price p.price').html();
			$('#pres-totals-wrapper .price').html(price_value);

			// Addons "Light Adaptive"
			if(has_addons && prod_type == 'simple'){
				$('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail_container').attr('style', '');
			}

			// Add Custom Back Button
	        let hasPrevStep = _composite.get_previous_step();

			if(!hasPrevStep){ 
				$('body').find('.composite_form .custom-back').hide(); 
			}else{
				$('body').find('.composite_form .custom-back').show(); 
			}

			resetHiddenComponents();
		}

	}, 300);
}

// Selected Thumbnail
function selectedOption(e){

	e.preventDefault();

	let is_Selected = $(this).parents('.component_option_thumbnail').hasClass('selected');

	setTimeout(function(){
		
		if(!selectedOptTrigger && is_Selected){

			let currStep = _composite.get_current_step(); 
			let step_id = currStep.step_id;
			let compConf = _composite.api.get_composite_configuration();
			let currConf = compConf[step_id];
			let prodType = currConf.product_type;
			let isStepValid = currStep.step_validation_model.attributes.passes_validation;
			let sel_title = currConf.selection_title;
			let has_addons = currStep.has_addons();

			// Bundle for "Prescriptions"
			if(prodType == 'bundle'){

				// Saved Prescription
				if(sel_title == 'Use Saved Prescription'){
					saved_presc_list = (saved_presc_list) ? saved_presc_list : $("#inline-1");

					$('body #component_' + step_id).find('.component_content .component_data').prepend(saved_presc_list);
					$('body #component_' + step_id).find('ul.products').hide();
					$('body #component_' + step_id).find('.bundle_data').hide();
				}

				// Hide Bundle Items for Prescription
				let compParent = $('#component_' + step_id);
				let isContentActive = compParent.hasClass('active_content');

				if(!isContentActive){
					$('#component_' + step_id).addClass('active_content');

					$('#component_' + step_id).find('.component_options').slideUp(function(){
						$('#component_' + step_id).find('.component_content').slideDown();
					});	
				}

			}

			// Addons "Light Adaptive"
			if(has_addons && prodType == 'simple'){

				let winWidth = $(window).width();
				let leftCorr = (winWidth > 992) ? 10 : 0 ;
				let selComp = $('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail.selected');
				let selCompParent = $('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail.selected').parent('li');

				let selCompWidth = selComp.innerWidth();
				let selCompHeight = selCompParent.innerHeight();
				let selCompTop = selCompParent.position().top + 5;
				let selCompLeft = selCompParent.position().left + leftCorr;

				setTimeout(function(){
					let selMargin = parseInt(selComp.innerHeight());
					
					$('.composite_component').addClass('active_content');
					$('body').find('#component_' + step_id + ' .component_data').addClass('swatches-addon');
					$('body').find('#component_' + step_id + ' .component_content').slideDown();

					$('body').find('#component_' + step_id + ' .component_wrap .wc-pao-addon-image-swatch').each(function(){
						let _this = $(this);

						if(_this.find('span').length == 0){
							_this.append('<span>' + _this.data('value').split('-')[0] + '</span>');
						}
					});

					$('body').find('#component_' + step_id + ' .component_wrap').css({'width' : (selCompWidth + 0), 'top' : (selCompTop + selCompHeight), 'left' : selCompLeft, 'position' : 'absolute'});
					$('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail_container').css({ 'height' : selCompHeight });
					selCompParent.css({ 'marginBottom' : selMargin });

				}, 300);

			}else{

				$('body').find('#component_' + step_id + ' .component_wrap').slideUp(function(){
					$('body').find('#component_' + step_id + ' .component_wrap').attr('style', '');
					$('body').find('#component_' + step_id + '.composite_component .component_options .component_option_thumbnail_container').attr('style', '');
				});

			}

			// Variable for "Swatches"
			if(prodType == 'variable'){
				$('body #component_' + step_id).find('.reset_variations').click();

				$('.composite_component').addClass('active_content');
				$('body').find('#component_' + step_id + ' .component_data').addClass('swatches-addon');

				$('#component_' + step_id).find('.component_options').slideUp(function(){
					$('#component_' + step_id).find('.component_content').slideDown();
				});

				$('body').find('#component_' + step_id + ' .component_data table.variations tr.attribute_options:nth-child(2)').attr('style', '');
				$('body').find('#component_' + step_id + ' .component_data table.variations tr.attribute_options:first-child li').attr('style', '');

			}

			if(prodType !== 'bundle' && prodType !== 'variable' && !has_addons){
				_composite.show_next_step();
			}

			selectedOptTrigger = true;
		}

	}, 200);
}

// Measure My PD
function showMeasurePD(){
	$('#wrapper').fadeIn(200); 
	checkCamera();
}

// Reset Hidden Components
function resetHiddenComponents() {

	let hidden_comp = _composite.scenarios.get_hidden_components();	
	for (i = 0; i < hidden_comp.length; i++) {
		$("body #component_" + hidden_comp[i]).find(".reset_variations").click();
		$("body #component_" + hidden_comp[i]).find('.clear_component_options').trigger('click');
	}

}

// Switch Prescription
function switchPresc(e){
	e.preventDefault();

	let currStep = _composite.get_current_step(); 
	let step_id = currStep.step_id;

	$('#component_' + step_id).addClass('active_content');

	$('#component_' + step_id).find('.component_content').slideUp(function(){
		$('#component_' + step_id).find('.component_options').slideDown();
	});
}

// Switch to Enter New Prescription
function switchEnterPresc(e){
	e.preventDefault();

	let _this = $(this);
	let _parent = _this.parents('.composite_component');

	_parent.find('.component_options #component_option_thumbnail_container_1414 button').trigger('click');

}

// Show Placeholder
function showPlaceholder(){

	let _this = $(this);

	setTimeout(function(){
		
		let fieldVal = _this.val();

		if(!fieldVal){
			_this.parents('.wc-pao-addon').find('.wc-pao-addon-description').fadeIn('fast');
		}

	}, 200);
	
}

// Clear Placeholder
function clearPlaceholder(){
	let _this = $(this);
	_this.parents('.wc-pao-addon').find('.wc-pao-addon-description').fadeOut('fast');
}

// Axis Toggle
function axisToggle(){

	let _this = $(this);
	let _parent = _this.parents('.wc-pao-addons-container');

	let cylVal = _this.find('option:selected').text().trim();
	let axisField = _parent.find('.wc-pao-addon-axis input');

	if(cylVal != 'Select' && cylVal != '0.00' && cylVal != 'None' && cylVal != 'Plano' && cylVal != '∞'){
		axisField.removeClass('disabled');
	}else{
		axisField.addClass('disabled');
		axisField.val('');
		_parent.find('.wc-pao-addon-axis').removeClass('field-error');
		_parent.find('.wc-pao-addon-axis span.error').remove();
	}

}

// PD Toggle
function pdToggle(){

	let _this = $(this);
	let _parents = _this.parents('.wc-pao-addon-pd-checkbox').find('.form-row label');

	_parents.toggleClass('active');

	if(_parents.hasClass('active')){
		$('body').find('.wc-pao-addon-pd').slideUp(100, function(){
			setTimeout(function(){
				$('body').find('.wc-pao-addon-right-pd, .wc-pao-addon-left-pd').slideDown(100);
			}, 100);
		});
	}else{
		$('body').find('.wc-pao-addon-right-pd, .wc-pao-addon-left-pd').slideUp(100, function(){
			setTimeout(function(){
				$('body').find('.wc-pao-addon-pd').slideDown(100);
			}, 100);
		});
	}
}

// PD Area Toggle 
function pdAreaToggle(){
	
	let _this = $(this);
	let _parents = _this.parents('.wc-pao-addon-my-pupillary-distance-pd-is-listed-on-my-prescription').find('.form-row label');

	_parents.toggleClass('active');

	if(_parents.hasClass('active')){
		$('body').find('.wc-pao-addon-pupillary-distance-pd').slideUp(100);
		$('body').find('.wc-pao-addon-pd').slideUp(100);
		$('body').find('.wc-pao-addon-measure-my-pd').slideUp(100);
		$('body').find('.wc-pao-addon-pd-checkbox').slideUp(100);

		if($('body').find('.wc-pao-addon-pd-checkbox input').is(':checked')){
			$('body').find('.wc-pao-addon-right-pd, .wc-pao-addon-left-pd').slideUp(100);
		}
	}else{
		$('body').find('.wc-pao-addon-pupillary-distance-pd').slideDown(100);
		$('body').find('.wc-pao-addon-measure-my-pd').slideDown(100);
		$('body').find('.wc-pao-addon-pd-checkbox').slideDown(100);

		if($('body').find('.wc-pao-addon-pd-checkbox input').is(':checked')){
			$('body').find('.wc-pao-addon-right-pd, .wc-pao-addon-left-pd').slideDown(100);
		}else{
			$('body').find('.wc-pao-addon-pd').slideDown(100);
		}
	}

}

// PD Single Populate
function pdSinglePopulate(){
	let pdVal = $(this).find('option:selected').data('label');
	let halfVal = (pdVal / 2).toFixed(1);

	$('body').find('ul.products li:first-child .wc-pao-addon-pd select option[data-label="' + halfVal + '"]').prop('selected', true);
	$('body').find('ul.products li:nth-child(2) .wc-pao-addon-pd select option[data-label="' + halfVal + '"]').prop('selected', true);
}

// PD Right Left Populate
function pdRLPopulate(){
	let pdVal = $(this).find('option:selected').data('label');
	let rightPD = $(this).parents('.wc-pao-addon').hasClass('wc-pao-addon-right-pd');
	let leftPD = $(this).parents('.wc-pao-addon').hasClass('wc-pao-addon-left-pd');

	if(rightPD){
		$('body').find('ul.products li:first-child .wc-pao-addon-pd select option[data-label="' + pdVal + '"]').prop('selected', true);
	}

	if(leftPD){
		$('body').find('ul.products li:nth-child(2) .wc-pao-addon-pd select option[data-label="' + pdVal + '"]').prop('selected', true);
	}
}

// Remove Upload Image
function removeUploadImg(){
	let _preview = $('body').find('.wc-pao-addon.wc-pao-addon-file-preview');
	let uploadWrap = $('body').find('.wc-pao-addon-upload-your-prescription');

	_preview.fadeOut(100);
	_preview.find('.file-name').remove();
	_preview.find('img.dynamic').remove();

	setTimeout(function() {
		uploadWrap.find('input[type="file"]').val('');
		uploadWrap.slideDown("fast");
	}, 100);
}

// Upload Precription
function uploadPresc(){
	let _this = this;
	let _preview = $('body').find('.wc-pao-addon.wc-pao-addon-file-preview');
	let prevClass = 'preview-img';

	_preview.find('img').remove();
	_preview.append('<img class="' + prevClass + '" src="" alt="">');

	readURL(_this, prevClass);
}

// ReadURL
function readURL(input, target){

	if(input.files && input.files[0]){

		let reader = new FileReader();

		reader.onload = function(e) {
			let fileData = input.files[0];
			let fileType = fileData.type;
			let fileSize = fileData.size;
			fileSize = Math.round(fileSize / 1024);
			let fileExt = $(input).val().split('.').pop().toLowerCase();
			let validExt = ['pdf','doc','docx','txt'];
			let uploadWrap = $('body').find('.wc-pao-addon-upload-your-prescription');
			let preview = $('body').find('.wc-pao-addon.wc-pao-addon-file-preview');
			let isOtherType = ($.inArray(fileExt, validExt) != '-1') ? true : false;
			let isImgType = (fileType.split('/')[0] === 'image') ? true : false;

			// If File type "Other" or "Image"
			if (isOtherType || isImgType) {

				if (fileSize > 10240) {

					uploadWrap.addClass('field-error');
					if(uploadWrap.find('span.error').length == 0){
						uploadWrap.append('<span class="error">File too large. Please upload a file less than 10MB.</span>');
					}else{
						uploadWrap.find('span.error').html('File too large. Please upload a file less than 10MB.');
					}
					return false;

				}else{

					if(isImgType){
						$('.' + target).attr('src', e.target.result);
					}else{
						$('.' + target).attr('src', "/wp-content/themes/bb-theme-child/assets/images/doc-placeholder.jpg");
						preview.append('<span class="file-name">' + $(input).val().split('\\').pop() + '</span>');
					}

					uploadWrap.fadeOut(100, function(){
						preview.slideDown('fast');
					});

					uploadWrap.removeClass('field-error');
					uploadWrap.find('span.error').remove();

					return true;

				}

			}else{

				uploadWrap.addClass('field-error');
				if(uploadWrap.find('span.error').length == 0){
					uploadWrap.append('<span class="error">Please upload a valid file type.</span>');
				}else{
					uploadWrap.find('span.error').html('Please upload a valid file type.');
				}
				return false;

			}

		}

		reader.readAsDataURL(input.files[0]);
	}

}

// Prescription Confirm
function confirm_presc_step(){
	let valid = validation_check();

	if(valid){
		_composite.show_next_step();
	}else{
		$('html, body').animate({
			scrollTop: $(".composite_pagination").offset().top          
		}, 1200);
	}
}

// Show Variations
function showVariations(){

	let winWidth = $(window).width();
	let _this = $(this);
	let currStep = _composite.get_current_step(); 
	let step_id = currStep.step_id;
	let swatchesRow = $('body').find('#component_' + step_id + ' .component_data table.variations tr.attribute_options:nth-child(2)');

	let compInner = _this.find('.variable-item-contents');
	let selCompWidth = compInner.innerWidth();
	let selCompHeight = _this.innerHeight();
	let swatchesHeight = swatchesRow.innerHeight();
	let resetHeight = (winWidth > 480) ? selCompHeight : 'auto';

	swatchesRow.slideUp('fast');

	$('body').find('.component_data table.variations tr.attribute_options:nth-child(1) ul.variable-items-wrapper li').css({'height' : resetHeight, 'marginBottom' : '20px'});
	
	setTimeout(function() {

		let selCompTop = _this.position().top;
		let selCompLeft = _this.position().left + 9;
		let selMargin = _this.innerHeight();

		_this.css({'marginBottom': (swatchesHeight + 20)});
		swatchesRow.css({'top' : (selCompTop + selCompHeight), 'left' : selCompLeft, 'position' : 'absolute'});

		swatchesRow.slideDown('fast');

		swatchesRow.find('.woo-variation-raw-select').on('change', function() { 
			setTimeout(function(){ 
				_composite.show_next_step(); 
			}, 300); 
		});

	}, 500);

}

// Toggle Sidebar Details
function detailToggle(){
	$('.widget_composite_summary .composite_summary ul.summary_elements').slideToggle('medium');
	$(this).toggleClass('active');
}

// Prescription Validation
function validation_check() {
	let errors = false;
	let fname = $('body').find('.wc-pao-addon-first-name');  
	let lname = $('body').find('.wc-pao-addon-last-name');
	let email = $('body').find('.wc-pao-addon-email');
	let practice = $('body').find('.wc-pao-addon-choose-your-practice');
	let prescDate = $('body').find('.wc-pao-addon-date-of-prescription');
	let savePresc = $('body').find('.wc-pao-addon-save-your-prescription-for-future-use');
	let uploadPresc = $('body').find('.wc-pao-addon-upload-your-prescription');

	// Right Eye / Left Eye
	let isBundleItem = ($('body').find('ul.products li').hasClass('bundled_item_hidden')) ? false : true;
	let compDetails = $('.woocommerce .composite_form .component_data');

	if(isBundleItem){

		let sphRight = $('body').find('ul.products li:first-child .wc-pao-addon-sph');
		let sphLeft = $('body').find('ul.products li:nth-child(2) .wc-pao-addon-sph');

		let sphRightVal = sphRight.find('select option:selected').text().trim();
		let sphLeftVal = sphLeft.find('select option:selected').text().trim();

		let cylRight = $('body').find('ul.products li:first-child .wc-pao-addon-cyl');
		let cylLeft = $('body').find('ul.products li:nth-child(2) .wc-pao-addon-cyl');

		let cylRightVal = cylRight.find('select option:selected').text().trim();
		let cylLeftVal = cylLeft.find('select option:selected').text().trim();

		let axisRight = $('body').find('ul.products li:first-child .wc-pao-addon-axis');
		let axisLeft = $('body').find('ul.products li:nth-child(2) .wc-pao-addon-axis');

		let oppCheck = false;
		let emptyCheck = false;

		let messages = {
			'sph_diff' : 'You have entered a prescription that is much higher in one eye than the other. Double check that this is correct before continuing.',
			'sph_opp' : 'The SPH parameters entered include a positive value for one eye and a negative value for the other. Please verify that this is correct before continuing.',
			'cyl_opp' : 'You have entered + and - values for your CYL (Cylinder). Please check your prescription and ensure that both CYL values are either positive or negative.',
			'axis' : 'Please select axis value between 0 - 180.'
		}

		// SPH Value Empty
		if (sphRightVal == 'Select' || sphRightVal == 'None') {

			errors = true;
			emptyCheck = true;

			compDetails.find('.error-msg').remove();

			sphRight.addClass('field-error');
			if(sphRight.find('span.error').length == 0){
				sphRight.append('<span class="error">Sphere required</span>');
			}

		}else{
			
			sphRight.removeClass('field-error');
			sphRight.find('span.error').remove();

		}

		if (sphLeftVal == 'Select' || sphLeftVal == 'None') {

			errors = true;
			emptyCheck = true;

			compDetails.find('.error-msg').remove();

			sphLeft.addClass('field-error');
			if(sphLeft.find('span.error').length == 0){
				sphLeft.append('<span class="error">Sphere required</span>');
			}

		}else{
			sphLeft.removeClass('field-error');
			sphLeft.find('span.error').remove();
		}

		// SPH Opposite Check
		if(!sphOppErrorCheck){

			sphOppErrorCheck = true;

			if((sphRightVal < 0 && sphLeftVal > 0) || (sphLeftVal < 0 && sphRightVal > 0)){

				errors = true;
				oppCheck = true;

				sphRight.addClass('field-error');
				sphLeft.addClass('field-error');

				if(compDetails.find('.sph-msg').length == 0){
					compDetails.prepend('<p class="error-msg sph-msg">' + messages.sph_opp + '</p>');
				}

			}else{
				if(!emptyCheck){
					compDetails.find('.sph-msg').remove();

					sphRight.removeClass('field-error');
					sphLeft.removeClass('field-error');
				}
			}
		}else{
			if(!emptyCheck){
				compDetails.find('.sph-msg').remove();

				sphRight.removeClass('field-error');
				sphLeft.removeClass('field-error');
			}
		}

		// SPH Difference Check
		sphRightVal = (sphRightVal) ? sphRightVal.replace('+', '').replace('-', '') : sphRightVal;
		sphLeftVal = (sphLeftVal) ? sphLeftVal.replace('+', '').replace('-', '') : sphLeftVal;

		if((sphRightVal - sphLeftVal >= 3) || (sphRightVal - sphLeftVal <= -3)){

			if(!sphDiffErrorCheck){
				sphDiffErrorCheck = true;

				if(!oppCheck){
					errors = true;
					
					sphRight.addClass('field-error');
					sphLeft.addClass('field-error');

					if(compDetails.find('.sph-msg').length == 0){
						compDetails.prepend('<p class="error-msg sph-msg">' + messages.sph_diff + '</p>');
					}
				}
			}else{
				if(!emptyCheck){
					compDetails.find('.sph-msg').remove();

					sphRight.removeClass('field-error');
					sphLeft.removeClass('field-error');
				}
			}

		}else{
			if(!emptyCheck){
				compDetails.find('.sph-msg').remove();

				sphRight.removeClass('field-error');
				sphLeft.removeClass('field-error');
			}
		}

		// CYL Opposite Check
		if(!cylOppErrorCheck){
			cylOppErrorCheck = true;
			if((cylRightVal < 0 && cylLeftVal > 0) || (cylLeftVal < 0 && cylRightVal > 0)){

				errors = true;
				oppCheck = true;

				cylRight.addClass('field-error');
				cylLeft.addClass('field-error');

				if(compDetails.find('.cyl-msg').length == 0){
					compDetails.prepend('<p class="error-msg cyl-msg">' + messages.cyl_opp + '</p>');
				}

			}else{
				if(!emptyCheck){
					compDetails.find('.cyl-msg').remove();

					cylRight.removeClass('field-error');
					cylLeft.removeClass('field-error');
				}
			}
		}else{
			if(!emptyCheck){
				compDetails.find('.cyl-msg').remove();

				cylRight.removeClass('field-error');
				cylLeft.removeClass('field-error');
			}
		}

		// Axis Validation
		if(axisRight.length > 0){

			let axisRightVal = axisRight.find('input').val();
			let isDisabled = (axisRight.find('input').hasClass('disabled')) ? true : false;

			if(!isDisabled){
				if(!axisRightVal){
					errors = true;

					axisRight.addClass('field-error');

					if(axisRight.find('span.error').length == 0){
						axisRight.append('<span class="error">Axis required</span>');
					}
				}else{
					axisRight.removeClass('field-error');
					axisRight.find('span.error').remove();

					if(axisRightVal > 180 || axisRightVal < 0 || isNaN(axisRightVal)){
						errors = true;

						axisRight.addClass('field-error');
						if(compDetails.find('.axis-msg').length == 0){
							compDetails.prepend('<p class="error-msg axis-msg">' + messages.axis + '</p>');
						}
					}else{
						compDetails.find('.axis-msg').remove();

						axisRight.removeClass('field-error');
						axisRight.find('span.error').remove();
					}
				}

			}

		}

		if(axisLeft.length > 0){

			let axisLeftVal = axisLeft.find('input').val();
			let isDisabled = (axisLeft.find('input').hasClass('disabled')) ? true : false;

			if(!isDisabled){
				if(!axisLeftVal){
					errors = true;

					axisLeft.addClass('field-error');

					if(axisLeft.find('span.error').length == 0){
						axisLeft.append('<span class="error">Axis required</span>');
					}
				}else{
					axisLeft.removeClass('field-error');
					axisLeft.find('span.error').remove();

					if(axisLeftVal > 180 || axisLeftVal < 0 || isNaN(axisLeftVal)){
						errors = true;

						axisLeft.addClass('field-error');
						if(compDetails.find('.axis-msg').length == 0){
							compDetails.prepend('<p class="error-msg axis-msg">' + messages.axis + '</p>');
						}
					}else{
						compDetails.find('.axis-msg').remove();

						axisLeft.removeClass('field-error');
						axisLeft.find('span.error').remove();
					}
				}

			}

		}

	}

	// Other Fields Validations
	errors = (isFieldValid(fname, 'input', 'Please enter your first name')) ? true : errors;
	errors = (isFieldValid(lname, 'input', 'Please enter your last name')) ? true : errors;
	errors = (isFieldValid(email, 'input', 'Please enter your email')) ? true : errors;
	errors = (isFieldValid(practice, 'select', 'Please select a practice')) ? true : errors;
	errors = (isFieldValid(prescDate, 'input', 'Please enter Prescription Date')) ? true : errors;
	errors = (isFieldValid(savePresc, 'input', 'Please enter Prescription Name')) ? true : errors;
	errors = (isFieldValid(uploadPresc, 'input', 'Please upload a copy of prescription')) ? true : errors;

	return (errors) ? false : true;
}

// Check Fields Validations
function isFieldValid(fieldParent, fieldType, fieldMsg){

	let error = false;

	if(fieldParent.length > 0 && fieldParent.hasClass('wc-pao-required-addon')){

		let fieldVal = fieldParent.find(fieldType).val();

		if(!fieldVal){

			error = true;

			fieldParent.addClass('field-error');
			if(fieldParent.find('span.error').length == 0){
				fieldParent.append('<span class="error">' + fieldMsg + '</span>');
			}
		}else{
			fieldParent.removeClass('field-error');
			fieldParent.find('span.error').remove();
		}

	}

	return (error) ? true : false;
}

// Back Nav
function backNav(e){ 
	e.preventDefault();

	let currStep = _composite.get_current_step();
	let lastStep = currStep.is_last();
	let prod_type = '';

	if(!lastStep){
		let compConf = _composite.api.get_composite_configuration();
		let step_id = currStep.step_id;
		let currConf = compConf[step_id];
		prod_type = currConf.product_type;
		
		if(prod_type == 'bundle'){
			
			//Hide Bundle Items for Prescription
			let compParent = $('#component_' + step_id);
			let isContentActive = compParent.hasClass('active_content');
			
			if(isContentActive){

				$('#component_' + step_id).find('.component_content').slideUp(function(){
					$('#component_' + step_id).find('.component_options').slideDown();
					$('#component_' + step_id).removeClass('active_content');

					selectedOptTrigger = false;
					$('body').find('#component_' + step_id + ' .component_option_thumbnail.selected button').unbind('click');
					$('body').find('#component_' + step_id + ' .component_option_thumbnail.selected button').on('click', selectedOption);
				});	
			}else{
				_composite.show_previous_step();
			}

		}

		if(prod_type == 'variable'){

			let compParent = $('#component_' + step_id);
			let isContentActive = compParent.hasClass('active_content');

			if(isContentActive){
				
				$('#component_' + step_id).find('.component_content').slideUp(function(){
					$('#component_' + step_id).find('.component_options').slideDown();

					$('.composite_component').removeClass('active_content');
					$('body').find('#component_' + step_id + ' .component_data').removeClass('swatches-addon');

					selectedOptTrigger = false;
					$('body').find('#component_' + step_id + ' .component_option_thumbnail.selected button').unbind('click');
					$('body').find('#component_' + step_id + ' .component_option_thumbnail.selected button').on('click', selectedOption);
				});

			}else{
				_composite.show_previous_step();
			}
		}
	}

	if(prod_type == 'simple' || prod_type == 'none' || lastStep){
		_composite.show_previous_step();
	}

}

// Start Over
function startOver(e){

	e.preventDefault();

	let hasPrevStep = _composite.get_previous_step();
	let frameUrl = $('#frame_product_url').val();

	if(hasPrevStep){
		$('.pagination_elements').find('li:first-child a.element_link').trigger('click');
	}else{
		window.location.href = frameUrl;
	}

}