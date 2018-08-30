function checkFields(){
    $('.f2').each(function(){
	var parent = $(this),
	    field = parent.find('input[type="text"], input[type="tel"], input[type="password"], input[type="email"], select, textarea');

	function showLabel() {
		if (field.val() === '') {
		parent.removeClass('show-label');
	    } else {
			parent.addClass('show-label');
		}
	    }

	    function validation() {
		if (field.is(":valid") && field.siblings().is(":invalid"))  {
		    parent.removeClass('valid');
		parent.addClass('error');
	    } else if (field.is(":valid") && field.val() !== '')  {
			parent.removeClass('error');
		parent.addClass('valid');
	    } else if (field.is(":invalid")) {
		parent.removeClass('valid');
		parent.addClass('error');
	    }
	    }

	field.focus(function(){
	  parent.addClass('show-label');
	});

	    field.on('change',showLabel);
	    field.on('blur',showLabel);
	    field.on('blur',validation);
	    field.on('input',validation);
	$('select').on('change',selectF);

	    function selectF() {
	    var field = $(this);

	    if (field.val() === '') {
		field.addClass('no-selection');
	    } else {
		field.removeClass('no-selection');
	    }

	    if (field.val() === 'Other') {
		field.parent().parent('.switch_field').addClass('show');
	    }else {
		field.parent().parent('.switch_field').removeClass('show');
	    }
	}
    });
}

$(document).ready(function(){
	$('.toggle').click(function(){
	var show = $(this).attr('data-on'),
			$this = $(this);

		if($(show).hasClass('show')) {
	    $(show).removeClass('show').addClass('hide');
			$this.removeClass('active');
		} else if($(show).hasClass('hide')) {
			$(show).removeClass('hide').addClass('show');
			$this.addClass('active');
		} else {
			$(show).addClass('show');
			$this.addClass('active');
		}
    });

	$("input[type='text'], input[type='tel'], input[type='password'], input[type='email'], select, textarea").on('change, blur', function(){
	checkFields();
    });

    checkFields();

    /* Table Highlighting */
	$(".pseudo_t").delegate('p','mouseover mouseleave', function(e) {
		if (e.type == 'mouseover') {
			$(this).parent().addClass("hover");
			$(".col_group").eq($(this).index()).addClass("hover");
		} else {
			$(this).parent().removeClass("hover");
			$(".col_group").eq($(this).index()).removeClass("hover");
		}
	});

	$("#account").change(function() {
		var accountId =  this.value;
		var all_accounts = ""
		$("#account option").each(function() {
			if ($(this).val().length) {
				all_accounts +=  $(this).val() + ",";
			}
		});

		all_accounts = all_accounts.slice(0,-1);

		$.post( "https://www.mdlab.com/dev/login/ajaxHandler.php", {get_providers: "1", accountId: accountId, all_accounts: all_accounts}, function(response) {
			var result = JSON.parse(response);
			$("#provider").empty();
			$("#provider").append(result.provider_html);

			$("#salesrep").empty();
			$("#salesrep").append(result.salesrep_html);
		});
	});

	$(".print").click(function(e) {
		e.preventDefault();

		$('body').addClass('loading');

		$("#pedigree > ul").css({
			'-webkit-transform': 'scale(1) translate(0%, 0%)',
			'transform': 'scale(1) translate(0%, 0%)'
		});

		// var selected_questionnaire = $(this).data("selected_questionnaire");
		// var selected_date = $(this).data("selected_date");

		// $("#selected_questionnaire").val(selected_questionnaire);
		// $("#selected_date").val(selected_date);
		// $("#patient_information").submit();
		$.post("genreport.php",	{selected_questionnaire: $(this).data("selected_questionnaire"), selected_date: $(this).data("selected_date")},	function(data, status){
			$("#admin_print").html(data);

			resizeLine();

			var $el = $("#pedigree > ul");
	    var elHeight = $el.outerHeight();
	    var elWidth = $el.outerWidth();

		var wrapWidth = '302';
		var wrapHeight = '302';

		    var scale2 = Math.min(
	      wrapWidth / elWidth,
	      wrapHeight / elHeight
	    );


		    $el.css({
			    '-webkit-transform': 'scale(' + scale2 + ') translate(0%, 0%)',
			    'transform': 'scale(' + scale2 + ') translate(0%, 0%)'
		    });

			window.onafterprint = function(e){
		$(window).off('mousemove', window.onafterprint);
		console.log('Print Dialog Closed..');
				$('body').removeClass('loading');
	    };

			$("#admin_print").printThis({
				debug: false,
				importCSS: false,
				importStyle: false,
				printContainer: false,
				loadCSS: "style.min.css",
				printDelay: 700,
				base: "../../dev/"
			});

			setTimeout(function(){
	$(window).one('mousemove', window.onafterprint);
    }, 1);

		});
	});

	function resizeLine(){
		$(".child").each(function() {
			if($(this).children('li:last-child').children('.parents').find('.person:last-of-type').hasClass('spouse')){
				$(this).addClass('right1');
			}

			if($(this).children('li:first-child').children('.parents').find('.person:first-of-type').hasClass('spouse')){
				$(this).addClass('left1');
			}
		});

		$(".parents").next('.child').children('li:only-of-type').each(function() {
			if($(this).children('.parents').find('.person:first-child').hasClass('blood')){
				$(this).addClass('male_child');
			}

			if($(this).children('.parents').find('.person:last-child').hasClass('blood')){
				$(this).addClass('fem_child');
			}
		});

		$("ul.child").each(function() {
			if ($(this).children('li').length > 1) {
			$(this).addClass('multi');
			}
		});

		$(".child > li:only-of-type").each(function() {
			if($(this).children('.person').length === 1){
			$(this).closest('.child').addClass('no-line');
		    }
		});
	}
});