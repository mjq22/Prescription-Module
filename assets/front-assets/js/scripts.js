var fitmixInstance = "";
var img_data = "";
var pdparams = {
	//apiKey: 'fJRSrYTr99ThnGz9xOAimpNVy9oyc42aAb9EfFtW',    //With glassID
    apiKey: '3p0n1cO5EoNQ0yQdqcBPHkZdgiojZgQTXoDjFDH6',
	uiConfiguration: {
		liveDetectionFailure: false,
	},
	onUiStatus: function(data) {
		if(data.liveDetectionFailure || data.liveGuide) { 
		  //fitmixInstance.resetDetection();
		  //console.log("haeee oee");
		}
	},	
	onSnapshot: function(data) {
        img_data = data.dataURL;
    	jQuery('.capture-img .img-holder').html('<img src="' + img_data + '"/>');
        jQuery('.capture-img').show();
        jQuery('.pd-top-area').fadeOut(function () {
    		jQuery('.pd-bottom-area').fadeIn();
        });
	},
	css: templateUrl + "/assets/css/pdtool-fitmix.css",
}; 

function onfitmixready() {
	fitmixInstance.resetLive;
	fitmixInstance.resetSession();
}

function checkCamera() {
    fitmixInstance = FitMix.createWidget('my-fitmix-container', pdparams, onfitmixready);
  //   navigator.getMedia = ( navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia);
  //   navigator.getMedia({video: true}, function() {
  //       // webcam is available
  //       console.log("---------------yes---------------------");
  //       jQuery(".no-camera").hide();
		// jQuery('.take-shot').removeClass("no-click");
		// fitmixInstance = FitMix.createWidget('my-fitmix-container', pdparams, onfitmixready);
  //   }, function() {
  //       // webcam is not available
  //       console.log("------------------no-------------------");
  //       jQuery(".no-camera").show();
		// jQuery('.take-shot').addClass("no-click");
  //   });
}    

jQuery(document).ready(function () {
    var $ = jQuery;	

	jQuery('.pd-close').on("click", function(e){
		e.preventDefault();
        jQuery('#wrapper').fadeOut(200); 
	    jQuery('.step1-wrapper').fadeIn();
        jQuery('.pd-area').fadeOut();
		//onfitmixready();
		fitmixInstance.remove();
		fitmixInstance = "";
    }); 
    jQuery('.wc-pao-addon-container.wc-pao-addon.wc-pao-addon-measure-my-pd').on("click", function(){
		jQuery('#wrapper').fadeIn(200);
		checkCamera();	
        // fitmixInstance = FitMix.createWidget('my-fitmix-container', pdparams, onfitmixready);			
    });
	$('.use-photo').on('click', function (e) {
		e.preventDefault();
		jQuery('.wc-pao-addon-container.wc-pao-addon.wc-pao-addon-measure-my-pd input').val(img_data);
		if(!jQuery('.wc-pao-addon-container.wc-pao-addon.wc-pao-addon-measure-my-pd .pd-taken').length) {
			jQuery('.wc-pao-addon-container.wc-pao-addon.wc-pao-addon-measure-my-pd').append('<span class="pd-taken">Image added</span>');
		}	
		jQuery('#wrapper').fadeOut(200); 
	    jQuery('.step1-wrapper').fadeIn();
        jQuery('.pd-area').fadeOut();
		//onfitmixready();
		fitmixInstance.remove();
		fitmixInstance = "";
	});

    jQuery('.m-pd').on('click', function (e) {
        e.preventDefault();
        jQuery('.step1-wrapper').fadeOut(function () {
            jQuery('.pd-area').fadeIn();
        });
    });

    $('.take-shot').on('click', function (e) {
        e.preventDefault();
        $('.pd-top-area').find('.txt-block').fadeOut(function () {
            $('.timer').fadeIn();
            startCounter();
        });
    });

    $('.retake').on('click', function (e) {
        e.preventDefault();
        $('.timer').fadeOut(function () {
            $('.pd-top-area').find('.txt-block').fadeIn();
            $('.pd-top-area').fadeIn();
        });
        $('.pd-bottom-area').fadeOut();
        $('.capture-img .img-holder').html('');
        $('.capture-img').hide();
    });
	
	$(".try-again").on("click", function (e) {
        checkCamera();
        // fitmixInstance = FitMix.createWidget('my-fitmix-container', pdparams, onfitmixready);
    });	
	
    // Counter
    var countInterval;
    var countdownNumberEl = $('.timer').find('.counter');

    function startCounter() {
        var countdown = 1;
        if (jQuery(countdownNumberEl).length) {
            countdownNumberEl.html(countdown);
            countInterval = setInterval(function () {
                countdown = ++countdown > 3 ? 30 : countdown;
                countdownNumberEl.html(countdown);

                if (countdown == 3) {
                    clearInterval(countInterval);

                    // take snapshot and get image data
					fitmixInstance.getSnapshot();

                }
            }, 1000);
        }
    }

});