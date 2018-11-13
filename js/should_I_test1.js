$(function() {
	$('#start').on( 'click', '#start_btn', function(){
		$('body').addClass('start');
	});
	
	$('.radio').on('change', 'input', function(){
		var radioName = $(this).attr("name");
		$('input[name="' + radioName + '"]').closest('.input').removeClass('checked');
		$(this).closest('.input').addClass('checked');
	});
	
	$('.cbox').on('click', 'input', function(){
		$(this).closest('.input').toggleClass('checked');
	});
	
	
	$('.accordion').on('click', '.acc_btn', function(){
	    $('.acc_btn').removeClass('show');
	    $('.acc_con').slideUp('normal');
   
	    if($(this).next().is(':hidden') === true) {
     	    $(this).addClass('show');
			$(this).next().slideDown('normal');
        }
	});
	
	$('.no').on('click', 'input', function(){
		if($(this).is(':checked')){
			//alert('No');
			$(this).closest('li').next('li').addClass('show');
			$(this).closest('.as_fields').find('.toggled').removeClass('show');
			checkComplete();
		}
	});
	
	$('.yes').on('click', 'input', function(){
		if($(this).is(':checked')){
			//alert('Yes');
			$(this).closest('li').nextAll('li').removeClass('show');
			$(this).closest('li').nextAll('li').find('input').prop('checked', false);
			$(this).closest('.as_fields').find('.toggled').addClass('show');
			$(this).closest('li').nextAll('li').find('.input').removeClass('checked');
		}
	});
	 
	$('.accordion .show .input input, .accordion .field input').on('change',checkComplete);
	//$('.qTree').on('click',checkComplete);
	
	function checkComplete(){
		var accArray = $('.accordion .acc_btn');
		var current = accArray.filter('.show');
		//alert(accArray.index(current));
		
		$(".accordion .qTree li").each(function() {
			if ($('.acc_sub:not(:has(:radio:checked))').filter(":visible").length) {
				//alert('1');
                return false;
			} else if( $(this).find('.field').is(':visible') && $(this).find('.field input').val().length === 0) {
				return false;
				//alert('2');
			} else if($(this).is('.completed')) {
				return false;
				//alert('3');
            } else {
				//alert('4');
				var i = accArray.index(current);
				var nextBtn = i + 1;
				accArray.eq(nextBtn).addClass('show');
				accArray.eq(nextBtn).next().slideDown('normal');
				
				setTimeout(
                    function(){
						accArray.eq(i).removeClass('show');
                        accArray.eq(i).next().slideUp('normal');
                    }, 700);
				
				$(this).addClass('completed');
			}
		});
	}
	
});