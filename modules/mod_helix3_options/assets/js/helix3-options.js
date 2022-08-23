jQuery(function($) {
	$('.helix3-toggler').on('click', function(e) {
        e.preventDefault();
        $('.template-options').toggleClass('closed');
    });

	//Presets
	var presets = $('.helix3-presets').find('li');
	presets.each(function(){
		$(this).find('a').on('click', function(event){

			event.preventDefault();
			var currentPreset = $(this).parent().data('preset');
			presets.removeClass('active');
			$(this).parent().addClass('active');
			$('.sp-default-logo').removeAttr('src').attr('src', helix3_template_uri + '/images/presets/preset'+ currentPreset +'/logo.png');
			$('.sp-retina-logo').removeAttr('src').attr('src', helix3_template_uri + '/images/presets/preset'+ currentPreset +'/logo@2x.png');
			$('.sppb-tabs-image').removeAttr('src').attr('src', helix3_template_uri + '/images/presets/preset'+ currentPreset +'/tab-inside-img.png');
			$('.sp-bottom-logo').removeAttr('src').attr('src', helix3_template_uri + '/images/presets/preset'+ currentPreset +'/logo-footer.png');
			$('.preset').removeAttr('href').attr('href', helix3_template_uri + '/css/presets/preset' + currentPreset + '.css');

			$.removeCookie(helix3_template + '_preset');
			$.cookie(helix3_template + '_preset', 'preset' + currentPreset, { expires : 1 });

		});
	});

	//Boxed
	$('#helix3-boxed').on('change', function(){
		if($(this).is(':checked')) {
			$('body').addClass('layout-boxed');
		} else {
			$('body').removeClass('layout-boxed');
		}
	});

	//Boxed
	$('.helix3-bg-images li').on('click', function(event){
		event.preventDefault();

		var $this = $(this),
			body = $('body');

		if($('input#helix3-boxed').is(':checked')) {
			$('body').removeAttr('style').css({
				'background': 'url(' + $this.data('bg') + ') repeat 50% 50%',
				'background-attachment': 'fixed',
				'background-size': 'cover',
			});

			$('.helix3-bg-images li').removeClass('active');
			$this.addClass('active');

			if ($this.is('.pattern')) {
				body.addClass('pattern');
			} else {
				if (body.is('.pattern')) {
					body.removeClass('pattern');
				}

			}

		} else {
			alert('Select Boxed Layout');
		}

	});
	
	$('input#helix3-boxed').on('change', function(){
		if($(this).is(':checked')) {
			$('body').addClass('layout-boxed');
		} else {
			$('body').removeClass('layout-boxed').removeAttr('style');
			$('.helix3-bg-images li').removeClass('active');
		}
	});

});
