function checkFields() {
    $('.f2').each(function () {
        var parent = $(this),
            field = parent.find('input[type="text"], input[type="url"], input[type="tel"], input[type="password"], input[type="email"], select, textarea');

        function showLabel() {
            if (field.val() === '') {
                parent.removeClass('show-label');
            } else {
                parent.addClass('show-label');
            }
        }

        function validation() {
            if (field.is(":valid") && field.siblings().is(":invalid")) {
                parent.removeClass('valid');
                parent.addClass('error');
            } else if (field.is(":valid") && field.val() !== '') {
                parent.removeClass('error');
                parent.addClass('valid');
            } else if (field.is(":invalid")) {
                parent.removeClass('valid');
                parent.addClass('error');
            }
        }

        field.focus(function () {
            parent.addClass('show-label');
        });

        field.on('change', showLabel);        
        field.on('input', validation);
        
        field.on('blur', showLabel);
        field.on('blur', validation);
        field.on('input', function() {
            if(field.hasClass('zip')){
                field.val(field.val().replace(/\D/g,'')); 
            }
        });
        field.on('blur', function () {
            showLabel();
            if(field.val()===''){
                parent.removeClass('valid');
            }
            if(field.hasClass('phone_us')){
                var phoneVal = field.val().replace(/\D+/g, '');
                if(phoneVal!=""){                                    
                    if(phoneVal.length != '10'){
                        parent.removeClass('valid').addClass('error');
                    }
                }
            }
            if(field.hasClass('zip')){
                var zipVal = field.val().replace(/\D+/g, ''); 
                if(zipVal!=""){
                    if(zipVal.length < 5 || zipVal.length > 10){
                        parent.removeClass('valid').addClass('error');
                    }
                }
            }
            
        });
        
        $('select').on('change', selectF);
        function selectF() {
            var field = $(this);
            if (field.val() === '') {
                field.addClass('no-selection');
                field.parent().parent('.f2').removeClass('valid');
            } else {
                field.removeClass('no-selection');
            }

            if (field.val() === 'Other') {
                field.parent().parent('.switch_field').addClass('show');
            } else {
                field.parent().parent('.switch_field').removeClass('show');
            }
        }
    });
}

$(document).ready(function () {
    $('.toggle').click(function () {
        var show = $(this).attr('data-on'),
                $this = $(this);

        if ($(show).hasClass('show')) {
            $(show).removeClass('show').addClass('hide');
            $this.removeClass('active');
        } else if ($(show).hasClass('hide')) {
            $(show).removeClass('hide').addClass('show');
            $this.addClass('active');
        } else {
            $(show).addClass('show');
            $this.addClass('active');
        }
    });

    $("input[type='text'], input[type='tel'], input[type='password'], input[type='email'], select, textarea").on('change, blur', function () {
        checkFields();
    });

    checkFields();

    /* Table Highlighting */
    $(".pseudo_t").delegate('p', 'mouseover mouseleave', function (e) {
        if (e.type == 'mouseover') {
            $(this).parent().addClass("hover");
            $(".col_group").eq($(this).index()).addClass("hover");
        } else {
            $(this).parent().removeClass("hover");
            $(".col_group").eq($(this).index()).removeClass("hover");
        }
    });

    

    $(".print").click(function(e) {
        e.preventDefault();

        $('body').addClass('loading');

        $.post("genreport.php", {selected_questionnaire: $(this).data("selected_questionnaire"), selected_date: $(this).data("selected_date")}, function (data, status) {
            $("#admin_print").html(data);
            scalePedigree();
        });
		
		$("#admin_print").printThis({
            debug: true,
            importCSS: false,
            importStyle: false,
            printContainer: false,
            loadCSS: "dev/style.min.css",
            printDelay: 1000,
			removeScripts: true,
            base: "/dev/login/assets/",
			afterPrint: testFun
        });
		
		var interval = null;
		
		function reload(){
            if(window_focus === true) {
				$('body').removeClass('loading');
				window.clearInterval(interval);
            }
			
			if( /iPhone|iPad/i.test(navigator.userAgent) ) {
				$('body').removeClass('loading');
				window.clearInterval(interval);
            }
			
		}
		
		function testFun(){
			if ( /^((?!chrome|android).)*safari/i.test(navigator.userAgent)) {
				interval = window.setInterval(reload, 500);
			} else {
				$('body').removeClass('loading');
				window.clearInterval(interval);
			}
		}
    });
	
	var window_focus;

	$(window).focus(function() {
		window_focus = true;
	}).blur(function() {
		window_focus = false;
	});

    //$("#bulkPrint").click(function (event) {
    $("#bulkPrint").on('click', function () {
        //event.preventDefault();
        
        var newArr = [];
        var searchIDs = $("input:checkbox:checked").map(function () {
            newArr = [[$(this).data("selected_date"), $(this).data("selected_questionnaire"), $(this).data("prinatble")]];
            return newArr;
        }).toArray();
        
        var iCount = searchIDs.length;
        var notPrintableMsg = "";
        var pritableCount = 0;
        for (var i = 0; i < iCount; i++) {            
            var data_printable= searchIDs[i][2];
            if(data_printable=='1'){
                pritableCount++;
            }
        }
        
        if(pritableCount>0){
            $('body').addClass('loading');
            $("#admin_print").html('');
        }
        
        for (var i = 0; i < iCount; i++) {
            var selected_questionnaire = searchIDs[i][1];
            var selected_date = searchIDs[i][0];
            var data_printable= searchIDs[i][2];
            if(data_printable=='1'){
                $.post("genreport.php", {selected_questionnaire: selected_questionnaire, selected_date: selected_date
                //searchIDs
                }, function (data, status) { 
                    $("#admin_print").append(data);
                    scalePedigree();
                });
            } else {
                notPrintableMsg = "The summary report for questionnaires with Unknown medical necessity result cannot be printed.";
            }            
        }
        if(notPrintableMsg!=""){
            alert(notPrintableMsg);
        }
        if(pritableCount>0){
            $("#admin_print").printThis({
                debug: true,
                importCSS: false,
                importStyle: false,
                printContainer: false,
                loadCSS: "/dev/style.min.css",
                printDelay: 1000,
                            removeScripts: true,
                base: "https://www.mdlab.com",
                            afterPrint: testFun
            });
        }
		
        var interval = null;
		
        function reload(){
            if(window_focus === true) {
                $('body').removeClass('loading');
                window.clearInterval(interval);
            }			
            if( /iPhone|iPad/i.test(navigator.userAgent) ) {
                $('body').removeClass('loading');
                window.clearInterval(interval);
            }			
        }
		
        function testFun(){
            if ( /^((?!chrome|android).)*safari/i.test(navigator.userAgent)) {
                interval = window.setInterval(reload, 500);
            } else {
                $('body').removeClass('loading');
                window.clearInterval(interval);
            }
        }
        return false;
    });
	
	function scalePedigree(){
		var pedigrees = $('#admin_print .tree > ul');
		
		resizeLine();
		
		pedigrees.each(function () {
			var $el = $(this);
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
		});
	}
	
    function resizeLine() {
        $(".child").each(function () {
            if ($(this).children('li:last-child').children('.parents').find('.person:last-of-type').hasClass('spouse')) {
                $(this).addClass('right1');
            }

            if ($(this).children('li:first-child').children('.parents').find('.person:first-of-type').hasClass('spouse')) {
                $(this).addClass('left1');
            }
        });

        $(".parents").next('.child').children('li:only-of-type').each(function () {
            if ($(this).children('.parents').find('.person:first-child').hasClass('blood')) {
                $(this).addClass('male_child');
            }

            if ($(this).children('.parents').find('.person:last-child').hasClass('blood')) {
                $(this).addClass('fem_child');
            }
        });

        $("ul.child").each(function () {
            if ($(this).children('li').length > 1) {
                $(this).addClass('multi');
            }
        });

        $(".child > li:only-of-type").each(function () {
            if ($(this).children('.person').length === 1) {
                $(this).closest('.child').addClass('no-line');
            }
        });
    }
});