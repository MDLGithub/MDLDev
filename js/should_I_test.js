$(document).ready(function() { 
	
});
$(function() {	
	// $('#app_wrap').keypress(function(e) {
		// if (e.which == 13) {
		// return false;
	  // }
	// });
	if ($("#in_office_print").val() == "1") {			
		window.print();	
	}
	$('#start').on( 'click', '#start_btn', function(){
		$('body').addClass('start');
	});
	
	$('.radio').on('change', 'input', function(){
		var radioName = $(this).attr("name");
		$('input[name="' + radioName + '"]').closest('.input').removeClass('checked');
		$(this).closest('.input').addClass('checked');
	});
	
	$('.cbox').on('click touchstart tap', 'input', function(){
		$(this).closest('.input').toggleClass('checked');
	});
	
	
	$('.accordion').on('click tap', '.acc_btn', function(){
		if($('.acc_btn ').length > 1) {
			$('.acc_btn').removeClass('show');
	        $('.acc_con').slideUp('normal');
			
			if($(this).next().css('display') == 'none') {
     	        $(this).addClass('show');
			    $(this).next().slideDown('normal');
            }
		}
   
	    
	});
	
	$('.no, .unknown.input, .yes.cont').on('click touchstart tap', 'input', function(){		
		//alert($(this).closest('li').nextAll('.parent:first').html());
		var parentli = $(this).closest('li');
		
		if($(this).is(':checked')){
			$('li').not('.show').find('.if').prop('required',false);
			parentli.nextAll('li').find('input[type=radio]').prop('checked', false);
			parentli.nextAll('li').removeClass('show');
			parentli.find('.toggled').removeClass('show');
			
			if(parentli.hasClass('parent') && (($(this).val() === "No") || $(this).val() === "Unknown")){
				parentli.nextAll('li:not(.child):first').addClass('show');
			} else {
				parentli.next('li').addClass('show');
			}
			
			if($(this).closest('.input').hasClass('cont')){
				parentli.find('.toggled .if').prop('required',true);
			}
			
			var cls = $(this).parent().closest('div').prop('class');
			
			if ((cls.indexOf("cont")) > 0) {
				$(this).closest('li').find('.toggled').addClass('show');
				//$(this).closest('li').siblings('.cont-no').removeClass('show');
			} else {
				parentli.next('li').find('.toggled').removeClass('show');
			}
			
			checkComplete();
		}
	});
	
	$('.yes').not('.cont').on('click touchstart tap', 'input', function(){
		var parentli = $(this).closest('li');
		
		if($(this).is(':checked')){
			parentli.nextAll('li').removeClass('show');
			parentli.find('.toggled .if').prop('required',true);
			
			var cls = $(this).parent().closest('div').prop('class');
			
			if (((cls.indexOf("no")) > 0) || ((cls.indexOf("unknown")) > 0)){
				parentli.find('.toggled').removeClass('show');
				//parentli.nextAll('.cont-no:first').addClass('show');
			} else {
				parentli.find('.toggled').addClass('show');
			}
			parentli.nextAll('li').find('.input').removeClass('checked');
			parentli.nextAll('li').find('.toggled').removeClass('show');
			
			checkYes();
			checkComplete();
		}
	});
	
	function checkYes(){
		$('li.show.no-yn').find('.as_fields .yes input').prop('checked', true);
		$('li.show.no-yn .fixed_f').find('.if').prop('required',true);
	}
	
	checkYes();
	
	$('.toggled .if').prop('required',false);
	$('.toggled.show .if').prop('required',true);
	
	$('.btns').on('click touchstart tap', '#next', function(event){	
		if (($('#current_step').val() == "insurance") && ($("input[name='insurance']:checked").val() == "None")) {
			event.preventDefault();
			$('.overlay.none').addClass('show');
		}
		
		if (($('#current_step').val() == "cancer_list_personal") || ($('#current_step').val() == "cancer_list_family")) {			
			if ((($('#current_step').val() == "cancer_list_personal") && ($("[name='personal_cancer[]']:checked").length == 0)) ||
			    (($('#current_step').val() == "cancer_list_family") && ($("[name='family_cancer[]']:checked").length == 0))) {
					$('.display_msg').removeClass('hide');
					$('.display_msg').addClass('show, error');
					event.preventDefault();
										
			}
		}
	});
	
	$('.overlay_box').on('click touchstart tap', '.button[type="submit"]', function(){
		$('#app_wrap').submit();
	});
		
	$('.btns').on('click touchstart tap', '.save', function(event){			
		event.preventDefault();
		
		$('.overlay.exit').addClass('show');
	});
	
	$('.accordion .show .input input, .accordion .field .if').change(function() {   
        setTimeout(function() {
			checkComplete();
		}, 100);
    });
	
	function checkDynamic(){
		var fields = $('.dynamic_f.show'),
			inputs = fields.find('.if');
		
		fields.on('change', inputs, function(){
			checkComplete();
		});
	}
	
	function selectInputs(){
		$('input[type="radio"]').each(function(){
			if($(this).closest('.input').hasClass('checked')){
				$(this).prop("checked", true);
			}
		});
	}
	
	function cancelAnimation(){
		if($('.display_msg').hasClass('error')){
			$('body').addClass('no-anim');
		} else {
			$('body').removeClass('no-anim');
		}
	}
	
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
	
	resizeLine();
	
	if ( $( "#pedigree" ).length ) {
	    var p = $( ".me" );
        var position = p.offset();
	    var contWidth = $('#pedigree').width();
	    var contHeight = $('#pedigree').height();
	    var topPos = position.top - $("#pedigree").offset().top - (contHeight/4);
	    var leftPos = position.left - $("#pedigree").offset().left - (contWidth/2);
	
	    $('#pedigree').kinetic("scrollTop", topPos);
	    $('#pedigree').kinetic("scrollLeft", leftPos);
	    $('#pedigree').kinetic({
            moved: function(){
                $('.pKey').addClass('moved');
            },
		
		    filterTarget: function(target, e){
                if (!/down|start/.test(e.type)){
		            return !(/button|area|a|input/i.test(target.tagName));
	            }
            }
        });
	}
	
	$('#pedigree').on('click touchstart tap', '.person.ch', function(){
		$('.pedigree_info').addClass('show').removeClass('hide');
	});
	
	if ($(".print").length ) {
		var $el = $("#pedigree > ul");
        var elHeight = $el.outerHeight();
        var elWidth = $el.outerWidth();

        //var $wrapper = $('#pedigree');
	    var wrapWidth = '302';
	    var wrapHeight = '302';
		
		var scale2 = Math.min(
          wrapWidth / elWidth,    
          wrapHeight / elHeight
        );
	}
	
	var beforePrintFunc = function() {}
	
	/*var afterPrintFunc = function() {
		if (window.matchMedia("(min-width: 48em)").matches) {
			setTimeout(function () {
				$el.css({
					'-webkit-transform': 'scale(1) translate(0%, 0%)',
					'transform': 'scale(1) translate(0%, 0%)'
				});
			}, 500);
			alert('Hi Dishant');
		}
    }*/

    window.matchMedia('print').addListener(function(mql) {
        if (mql.matches) {
        } else if (!mql.matches) {
			setTimeout(function () {
				$("#summary_app_modal").addClass("show");
			}, 500);
			//$("#summary_app_modal").addClass("show");			
		}
    });

    //window.onbeforeprint = beforePrintFunc;
	//window.onafterprint = afterPrintFunc;
	
	function printSummary(){
		if (navigator.userAgent.match(/(iPod|iPhone|iPad)/)) {
            var printQuery = window.matchMedia('print');
            printQuery.addListener(function() {
                var screenQuery = window.matchMedia('screen');
                screenQuery.addListener(function() {
                    //actions after print dialog close here
					alert('print close');
					setTimeout(function () {
						$("#summary_app_modal").addClass("show");
						alert('show modal');
					}, 500);
                });
            });
        } else {
		}
	}
	
	$(".side_btns").on('click touchstart tap', '.print', function(){
		$('#pedigree').kinetic("scrollTop", 0);
	    $('#pedigree').kinetic("scrollLeft", 0);
		$el.css({
			'-webkit-transform': 'scale(' + scale2 + ') translate(0%, 0%)',
			'transform': 'scale(' + scale2 + ') translate(0%, 0%)'
		});
		
		window.print();
    });
	
	$('.btns.info').on('click touchstart tap', '.print', function(){		
		window.print();
	});
	    
		
	selectInputs();
	cancelAnimation();
	
	function checkComplete(){
		var radioArray = $('.acc_btn.show + .qTree').children('.show').find('.acc_sub input[type=radio]'),
			inputArray = $('.show.acc_btn + .acc_con .toggled.show .if'),
			reqFields = [[radioArray], [inputArray]],
			accordion = $('.accordion .acc_btn'),
			current = accordion.filter('.show'),
			radioFilled = false,
			inputFilled = false,
			e=0,
			r= 0;
		
		if(inputArray.length === 0){
			inputFilled = true;
		}
		
		if(radioArray.length === 0){
			radioFilled = true;
		}
		
		inputArray.each(function() {
            var ele = $(this);
            if (ele.val() !== "") {
                e++;
            }
			
			if(e === inputArray.length){
				inputFilled = true;
			}
        });
		
		radioArray.each(function() {
            var ele = $(this),
				radGroup = ele.attr('name'),
			    asFields = ele.closest('.as_fields');
			
           if ($("input:radio[name=" + radGroup + "]:checked").length) {
			    r++;
            }
			
			if(r === radioArray.length){
				radioFilled = true;
			}
        });
		
		function checkFilled(){
			if(radioFilled === true && inputFilled === true && $('.qTree').length > 1 && current.next().is(':not(:last-child)') && current.is(':not(.completed)')){
			    openNext();
			}
		}
		
		function openNext(){
			var i = accordion.index(current);
			var nextBtn = i + 1;
			
			accordion.prop('disabled', true);
			
			accordion.eq(nextBtn).addClass('show');
			accordion.eq(nextBtn).next().slideDown('normal');
				
			setTimeout(
                function(){
					accordion.eq(i).removeClass('show');
                    accordion.eq(i).next().slideUp('normal');
					accordion.prop('disabled', false);
                }, 1500);
				
			accordion.eq(i).addClass('completed');
		}
		
		checkFilled();
	}
	
	checkComplete();
	
	$('.accordion > .acc_btn:first-child').addClass('show');
	$('.acc_con').not('.acc_con.first').hide();
	
	$('.add_field').not('.cancer_detail').click(function(e){	
		$('#additional_relatives').val("Yes");
		
		$('#app_wrap').submit();
	});
	
	$('.answers,.ps_info').on('click touchstart tap', '.add_field', function(){
		var $ul = $('.extra_input'),
			$li = $ul.find('li:first-child'),
			$remove = "<button type=\"button\" class=\"remove_field iconP\">X</button>";
		
		$li.clone().appendTo($ul);
		ns.checkFields();
		
		var $dyn = $ul.find('li:last-child'),
			$num = $dyn.index() + 1,
			$fields = $dyn.find('.if');
		
		$dyn.find('.valid').removeClass('valid');
		
		$fields.each(function() {
			var $this = $(this),
				$id = $this.attr('id'),
			    $id_gen = $id.replace($id.match(/(\d+)/g)[0], '').trim(); 
			
			$this.attr('id', $id_gen + $num);
			$this.siblings('label').attr('for', $id_gen + $num);
		});
		
		$dyn.append($remove);
	});
	
	$('.answers').on('click touchstart tap', '.remove_field', function(){
		var $this = $(this),
			$li = $this.closest('li');
		
		$li.remove();
	}); 
	
	// $('#summary').click(function(e){	
		
		
		// var summary_email_id = $("#summary_email_id").val();
				
		// var id = $(this).attr("id");
		// var email = $("#email_" + id).val();
		
		// if (($.trim(email).length) > 0) {
			// $.post( "https://www.mdlab.com/dev/ajaxHandler.php", {validate_email: "1", email: email, summary_email_id: summary_email_id}, function(response) {				
				// var result = JSON.parse(response);			
				
				// if ((result.valid_email_format == 0) || (result.valid_email == 0)) {
					// $("#email_" + id).closest('.field').addClass("error");
				// } else {
					// $(".overlay,.summary").removeClass("show");	
				// }
			// });
		// } else {			
			// $(".overlay,.summary").removeClass("show");			
		// }				
	// }); 
	
	$('#no_continue,#finish_later, #summary').click(function(e){		
		e.preventDefault();
		
		var id = $(this).attr("id");

		var email = $("#email_" + id).val();
		var practice_name = $("#practice_name").val();
		var physician_name = $("#physician_name").val();
		var address = $("#address").val();
		var city = $("#city").val();
		var state = $("#state").val();
		var zip = $("#zip").val();
		var phone = $("#phone").val();
		
		var lc = $("#lc").val();
		var co = $("#co").val();
		
		var quaily_id = $("#" + id + "_quaily_id").val();
		
		if ((($.trim(email).length) > 0) || (($.trim(practice_name).length) > 0) || (($.trim(physician_name).length) > 0) || (($.trim(address).length) > 0) || (($.trim(city).length) > 0) || (($.trim(state).length) > 0) || (($.trim(zip).length) > 0) || (($.trim(phone).length) > 0)) {
			$.post( "https://www.mdlab.com/questionnaire/ajaxHandler.php", {validate_input: "1", email: email, zip: zip, phone: phone}, function(response) {
				var result = JSON.parse(response);
				
				if ((result.valid_email_format == 0) || (result.valid_email == 0) || (result.valid_zip == 0) || (result.valid_phone == 0)) {
					$(".display_msg").remove();
					
					$("#email_" + id).closest('.field').addClass("error");							

					var error_html = '<div class="display_msg error"><span class="msg_type"></span><div class="emsg_box">';

					if (result.valid_email == 0) {
						error_html += '<p>Account with that Email already exists</p>';
					} 
					if (result.valid_email_format == 0) {
						error_html += '<p>Email is invalid</p>';
					}
					if (result.valid_zip == 0) {
						error_html += '<p>Zip Code is invalid</p>';
					}
					if (result.valid_phone == 0) {
						error_html += '<p>Phone Number is invalid</p>';
					}
					
					error_html += '</div></div>';

					$("#email_" + id).closest('.field').prepend(error_html);				
				} else {
					if (lc == "PM") {
						$.post( "https://www.mdlab.com/questionnaire/ajaxHandler.php", {send_pm_email: "1", quaily_id:quaily_id, email: email, co:co}, function(response) {
						});
					} else if (lc == "F") {						
						$.post( "https://www.mdlab.com/questionnaire/ajaxHandler.php", {send_hcf_email: "1", quaily_id:quaily_id, email: email, co:co}, function(response) {
							
						});						
						
						$.post( "https://www.mdlab.com/questionnaire/ajaxHandler.php", {update_hcf_provider_info: "1", quaily_id:quaily_id, practice_name:practice_name, physician_name:physician_name, address:address, city:city, state:state, zip:zip, phone:phone}, function(response) {
							
						});						
					} else if (($.trim(email).length) > 0) {
						$.post( "https://www.mdlab.com/questionnaire/ajaxHandler.php", {send_email: "1", quaily_id:quaily_id, email: email}, function(response) {
						});						
					}
					if (id == "finish_later") {
						var finish_later_np = $("#finish_later_np").val();
					}
					if (id == "finish_later") {
						$("#fl_validated").val("1");
						$("#app_wrap").submit();
					} else if (id == "no_continue") {
						$("#nc_validated").val("1");
						$("#app_wrap").submit();
					} else {
						$("#exit_email").remove();
						$(".overlay,.summary").removeClass("show");	
					}				
				}
			});
		} else {
			if (id == "finish_later") {
				$("#fl_validated").val("1");
				$("#app_wrap").submit();
			} else if (id == "no_continue") {
				$("#nc_validated").val("1");
				$("#app_wrap").submit();
			} else {
				$(".overlay,.summary").removeClass("show");			
			}			
		}
	}); 
		
	$('.dynamic_btns').on('click touchstart tap', '.cancer_detail', function(){
		var $fixed = $(this).closest('li.show').find('.fixed_f'),
			$dynamic = $(this).closest('li.show').find('.dynamic_f'),
			$remove = "<button type=\"button\" class=\"remove_field iconP\">X</button>",
			$li = $fixed.find('.field_group:last');
		
		if($dynamic.find('li').length > 0){
			$li = $dynamic.find('.field_group:last');
			$remove = '';
		}
		
		$li.clone().appendTo($dynamic);
		ns.checkFields();
		checkDynamic();
		
		var $dyn = $dynamic.find('li:last-child'),
			$oldID = $dyn.find('.if').attr('id').slice(-2),
			$ID = parseInt($oldID) + 1;
		if (isNaN($ID)) {
			var $dyn = $dynamic.find('li:last-child'),
			$oldID = $dyn.find('.if').attr('id').slice(-1),
			$ID = parseInt($oldID) + 1;
		}
		$dyn.find('.if, input').each(function() {
			var id = $(this).attr('id');
			$(this).attr('id', id.slice(0, -2) + $ID);  
			$(this).siblings('label').attr('for', id.slice(0, -2) + $ID);
		});
		
		$dyn.append($remove);
		
		//cleanup
		$dyn.find('.valid').removeClass('valid');
		$dyn.find('.error').removeClass('error');
		$dyn.find('input').prop('checked', false);
		$dyn.find('.if').val('');
		$dyn.find('.show-label').removeClass('show-label');
	});
	
	$('.qTree').on('click touchstart tap', '.remove_field', function(){
		$(this).closest('li').remove();
	});
	
	if ( $('.check_list > div').length < 6 ) {
		$('.check_list').removeClass('three').addClass('one');
	}
	
	function returningUser(){
		var cbox = $('.toggle_box input'),
			tg1 = cbox.closest('.toggle_group').find('.tg1'),
		    tg2 = cbox.closest('.toggle_group').find('.tg2'),
			pat = $('#account_password').attr('pattern');
		
		$('#account_login strong').text('Register');
		
		if(cbox.is(':checked')){
			tg1.addClass('off');
			tg1.find('input').prop("required", false);
			tg2.addClass('on');
			tg2.find('input').prop("required", true);
			$('#forgot_password').addClass('show');
			$('#account_login strong').text('Login');
			$('#account_password').removeAttr("pattern");
		} else {
			tg1.removeClass('off');
			tg1.find('input').prop("required", true);
			tg2.removeClass('on');
			tg2.find('input').prop("required", false);
			$('#forgot_password').removeClass('show');
			$('#account_login strong').text('Register');
			$('#account_password').attr('pattern', pat);
		}
	}
	
	returningUser();
	
	if(window.location.href){
        var hash_value = window.location.href.split('?'),
			q_value = hash_value[1];

        if (q_value === 'continue=Yes'){
           $('#no_email').prop('checked', true);
		    returningUser();
        }  
    }

	
	$('.toggle_box').on('click touchstart tap', '#no_email', function(){
		returningUser();
	});
	
	
	$("body").on('click touchstart tap', '[id*=cancer]', function(){
		if ($(this).val() == "No Cancer/None of the Above") {
			$(this).closest('div').siblings('.input').removeClass('checked');
			$(this).closest('div').siblings('.input').find("[id*=cancer]").prop('checked', false);
		} else {
			$("#cancer_none").parents('div').removeClass('checked');
			$("#cancer_none").prop('checked', false);
		}
	});	
	$('.field').on('change', 'select', function(){
		if ($(this).val() === 'unknown') {
			$(this).closest('.field').next('.ask_help').addClass('show');
		} else {
			$(this).closest('.field').next('.ask_help').removeClass('show');
		}
	});
	
	$('#pedigree').on('click touchstart tap', '.person', function(){
		var show = $(this).attr('data-qs');
		$('.q_summary li').removeClass('active');
		$(show).addClass('active');
	});
	
	var gl = $('.guideline').val();
	$('#guideline_met > p').html(gl);
	
	/*function moveScroller() {
        var $anchor = $("#scroller-anchor");
        var $scroller = $('.more_info');

        var move = function() {
        var st = $(window).scrollTop();
        var ot = $anchor.offset().top;
            if(st > ot) {
                $scroller.addClass('.stick');
            } else {
                $scroller.removeClass('.stick');
            }
        };
            $(window).scroll(move);
            move();
    }
	
	moveScroller();*/
	
	$(".side_col").stick_in_parent();
	
	
    $('.more_info.cbr').on('click tap', '.ico_info', function(){
		$(this).parent().toggleClass('show');
	});
});


/*
 Sticky-kit v1.1.2 | WTFPL | Leaf Corcoran 2015 | http://leafo.net
*/
(function(){var b,f;b=this.jQuery||window.jQuery;f=b(window);b.fn.stick_in_parent=function(d){var A,w,J,n,B,K,p,q,k,E,t;null==d&&(d={});t=d.sticky_class;B=d.inner_scrolling;E=d.recalc_every;k=d.parent;q=d.offset_top;p=d.spacer;w=d.bottoming;null==q&&(q=0);null==k&&(k=void 0);null==B&&(B=!0);null==t&&(t="is_stuck");A=b(document);null==w&&(w=!0);J=function(a,d,n,C,F,u,r,G){var v,H,m,D,I,c,g,x,y,z,h,l;if(!a.data("sticky_kit")){a.data("sticky_kit",!0);I=A.height();g=a.parent();null!=k&&(g=g.closest(k));
if(!g.length)throw"failed to find stick parent";v=m=!1;(h=null!=p?p&&a.closest(p):b("<div />"))&&h.css("position",a.css("position"));x=function(){var c,f,e;if(!G&&(I=A.height(),c=parseInt(g.css("border-top-width"),10),f=parseInt(g.css("padding-top"),10),d=parseInt(g.css("padding-bottom"),10),n=g.offset().top+c+f,C=g.height(),m&&(v=m=!1,null==p&&(a.insertAfter(h),h.detach()),a.css({position:"",top:"",width:"",bottom:""}).removeClass(t),e=!0),F=a.offset().top-(parseInt(a.css("margin-top"),10)||0)-q,
u=a.outerHeight(!0),r=a.css("float"),h&&h.css({width:a.outerWidth(!0),height:u,display:a.css("display"),"vertical-align":a.css("vertical-align"),"float":r}),e))return l()};x();if(u!==C)return D=void 0,c=q,z=E,l=function(){var b,l,e,k;if(!G&&(e=!1,null!=z&&(--z,0>=z&&(z=E,x(),e=!0)),e||A.height()===I||x(),e=f.scrollTop(),null!=D&&(l=e-D),D=e,m?(w&&(k=e+u+c>C+n,v&&!k&&(v=!1,a.css({position:"fixed",bottom:"",top:c}).trigger("sticky_kit:unbottom"))),e<F&&(m=!1,c=q,null==p&&("left"!==r&&"right"!==r||a.insertAfter(h),
h.detach()),b={position:"",width:"",top:""},a.css(b).removeClass(t).trigger("sticky_kit:unstick")),B&&(b=f.height(),u+q>b&&!v&&(c-=l,c=Math.max(b-u,c),c=Math.min(q,c),m&&a.css({top:c+"px"})))):e>F&&(m=!0,b={position:"fixed",top:c},b.width="border-box"===a.css("box-sizing")?a.outerWidth()+"px":a.width()+"px",a.css(b).addClass(t),null==p&&(a.after(h),"left"!==r&&"right"!==r||h.append(a)),a.trigger("sticky_kit:stick")),m&&w&&(null==k&&(k=e+u+c>C+n),!v&&k)))return v=!0,"static"===g.css("position")&&g.css({position:"relative"}),
a.css({position:"absolute",bottom:d,top:"auto"}).trigger("sticky_kit:bottom")},y=function(){x();return l()},H=function(){G=!0;f.off("touchmove",l);f.off("scroll",l);f.off("resize",y);b(document.body).off("sticky_kit:recalc",y);a.off("sticky_kit:detach",H);a.removeData("sticky_kit");a.css({position:"",bottom:"",top:"",width:""});g.position("position","");if(m)return null==p&&("left"!==r&&"right"!==r||a.insertAfter(h),h.remove()),a.removeClass(t)},f.on("touchmove",l),f.on("scroll",l),f.on("resize",
y),b(document.body).on("sticky_kit:recalc",y),a.on("sticky_kit:detach",H),setTimeout(l,0)}};n=0;for(K=this.length;n<K;n++)d=this[n],J(b(d));return this}}).call(this);


/**
 jQuery.kinetic v2.2.4
 Dave Taylor http://davetayls.me

 @license The MIT License (MIT)
 @preserve Copyright (c) 2012 Dave Taylor http://davetayls.me
 */
(function ($){
  'use strict';

  var ACTIVE_CLASS = 'kinetic-active';

  /**
   * Provides requestAnimationFrame in a cross browser way.
   * http://paulirish.com/2011/requestanimationframe-for-smart-animating/
   */
  if (!window.requestAnimationFrame){

    window.requestAnimationFrame = ( function (){

      return window.webkitRequestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.oRequestAnimationFrame ||
        window.msRequestAnimationFrame ||
        function (/* function FrameRequestCallback */ callback, /* DOMElement Element */ element){
          window.setTimeout(callback, 1000 / 60);
        };

    }());

  }

  // add touch checker to jQuery.support
  $.support = $.support || {};
  $.extend($.support, {
    touch: 'ontouchend' in document
  });


  // KINETIC CLASS DEFINITION
  // ======================

  var Kinetic = function (element, settings) {
    this.settings = settings;
    this.el       = element;
    this.$el      = $(element);

    this._initElements();

    return this;
  };

  Kinetic.DATA_KEY = 'kinetic';
  Kinetic.DEFAULTS = {
    cursor: 'move',
    decelerate: true,
    triggerHardware: false,
    threshold: 0,
    y: true,
    x: true,
    slowdown: 0.9,
    maxvelocity: 40,
    throttleFPS: 60,
    invert: false,
    movingClass: {
      up: 'kinetic-moving-up',
      down: 'kinetic-moving-down',
      left: 'kinetic-moving-left',
      right: 'kinetic-moving-right'
    },
    deceleratingClass: {
      up: 'kinetic-decelerating-up',
      down: 'kinetic-decelerating-down',
      left: 'kinetic-decelerating-left',
      right: 'kinetic-decelerating-right'
    }
  };


  // Public functions

  Kinetic.prototype.start = function (options){
    this.settings = $.extend(this.settings, options);
    this.velocity = options.velocity || this.velocity;
    this.velocityY = options.velocityY || this.velocityY;
    this.settings.decelerate = false;
    this._move();
  };

  Kinetic.prototype.end = function (){
    this.settings.decelerate = true;
  };

  Kinetic.prototype.stop = function (){
    this.velocity = 0;
    this.velocityY = 0;
    this.settings.decelerate = true;
    if ($.isFunction(this.settings.stopped)){
      this.settings.stopped.call(this);
    }
  };

  Kinetic.prototype.detach = function (){
    this._detachListeners();
    this.$el
      .removeClass(ACTIVE_CLASS)
      .css('cursor', '');
  };


  Kinetic.prototype.attach = function (){
    if (this.$el.hasClass(ACTIVE_CLASS)) {
      return;
    }
    this._attachListeners(this.$el);
    this.$el
      .addClass(ACTIVE_CLASS)
      .css('cursor', this.settings.cursor);
  };


  Kinetic.prototype.destroy = function (){
    this.detach();
    this.$el=null;
    this.el=null;
    this.settings=null;
  };


  // Internal functions

  Kinetic.prototype._initElements = function (){
    this.$el.addClass(ACTIVE_CLASS);

    $.extend(this, {
      xpos: null,
      prevXPos: false,
      ypos: null,
      prevYPos: false,
      mouseDown: false,
      throttleTimeout: 1000 / this.settings.throttleFPS,
      lastMove: null,
      elementFocused: null
    });

    this.velocity = 0;
    this.velocityY = 0;

    // make sure we reset everything when mouse up
    $(document)
      .mouseup($.proxy(this._resetMouse, this))
      .click($.proxy(this._resetMouse, this));

    this._initEvents();

    this.$el.css('cursor', this.settings.cursor);

    if (this.settings.triggerHardware){
      this.$el.css({
        '-webkit-transform': 'translate3d(0,0,0)',
        '-webkit-perspective': '1000',
        '-webkit-backface-visibility': 'hidden'
      });
    }
  };

  Kinetic.prototype._initEvents = function(){
    var self = this;
    this.settings.events = {
      touchStart: function (e){
        var touch;
        if (self._useTarget(e.target, e)){
          touch = e.originalEvent.touches[0];
          self.threshold = self._threshold(e.target, e);
          self._start(touch.clientX, touch.clientY);
          e.stopPropagation();
        }
      },
      touchMove: function (e){
        var touch;
        if (self.mouseDown){
          touch = e.originalEvent.touches[0];
          self._inputmove(touch.clientX, touch.clientY);
          if (e.preventDefault){
            e.preventDefault();
          }
        }
      },
      inputDown: function (e){
        if (self._useTarget(e.target, e)){
          self.threshold = self._threshold(e.target, e);
          self._start(e.clientX, e.clientY);
          self.elementFocused = e.target;
          if (e.target.nodeName === 'IMG'){
            e.preventDefault();
          }
          e.stopPropagation();
        }
      },
      inputEnd: function (e){
        if (self._useTarget(e.target, e)){
          self._end();
          self.elementFocused = null;
          if (e.preventDefault){
            e.preventDefault();
          }
        }
      },
      inputMove: function (e){
        if (self.mouseDown){
          self._inputmove(e.clientX, e.clientY);
          if (e.preventDefault){
            e.preventDefault();
          }
        }
      },
      scroll: function (e){
        if ($.isFunction(self.settings.moved)){
          self.settings.moved.call(self, self.settings);
        }
        if (e.preventDefault){
          e.preventDefault();
        }
      },
      inputClick: function (e){
        if (Math.abs(self.velocity) > 0){
          e.preventDefault();
          return false;
        }
      },
      // prevent drag and drop images in ie
      dragStart: function (e){
        if (self._useTarget(e.target, e) && self.elementFocused){
          return false;
        }
      },
      // prevent selection when dragging
      selectStart: function (e){
        if ($.isFunction(self.settings.selectStart)){
          return self.settings.selectStart.apply(self, arguments);
        } else if (self._useTarget(e.target, e)) {
          return false;
        }
      }
    };

    this._attachListeners(this.$el, this.settings);

  };

  Kinetic.prototype._inputmove = function (clientX, clientY){
    var $this = this.$el;
    var el = this.el;

    if (!this.lastMove || new Date() > new Date(this.lastMove.getTime() + this.throttleTimeout)){
      this.lastMove = new Date();

      if (this.mouseDown && (this.xpos || this.ypos)){
        var movedX = (clientX - this.xpos);
        var movedY = (clientY - this.ypos);
        if (this.settings.invert) {
          movedX *= -1;
          movedY *= -1;
        }
        if(this.threshold > 0){
          var moved = Math.sqrt(movedX * movedX + movedY * movedY);
          if(this.threshold > moved){
            return;
          } else {
            this.threshold = 0;
          }
        }
        if (this.elementFocused){
          $(this.elementFocused).blur();
          this.elementFocused = null;
          $this.focus();
        }

        this.settings.decelerate = false;
        this.velocity = this.velocityY = 0;

        var scrollLeft = this.scrollLeft();
        var scrollTop = this.scrollTop();

        this.scrollLeft(this.settings.x ? scrollLeft - movedX : scrollLeft);
        this.scrollTop(this.settings.y ? scrollTop - movedY : scrollTop);

        this.prevXPos = this.xpos;
        this.prevYPos = this.ypos;
        this.xpos = clientX;
        this.ypos = clientY;

        this._calculateVelocities();
        this._setMoveClasses(this.settings.movingClass);

        if ($.isFunction(this.settings.moved)){
          this.settings.moved.call(this, this.settings);
        }
      }
    }
  };

  Kinetic.prototype._calculateVelocities = function (){
    this.velocity = this._capVelocity(this.prevXPos - this.xpos, this.settings.maxvelocity);
    this.velocityY = this._capVelocity(this.prevYPos - this.ypos, this.settings.maxvelocity);
    if (this.settings.invert) {
      this.velocity *= -1;
      this.velocityY *= -1;
    }
  };

  Kinetic.prototype._end = function (){
    if (this.xpos && this.prevXPos && this.settings.decelerate === false){
      this.settings.decelerate = true;
      this._calculateVelocities();
      this.xpos = this.prevXPos = this.mouseDown = false;
      this._move();
    }
  };

  Kinetic.prototype._useTarget = function (target, event){
    if ($.isFunction(this.settings.filterTarget)){
      return this.settings.filterTarget.call(this, target, event) !== false;
    }
    return true;
  };

  Kinetic.prototype._threshold = function (target, event){
    if ($.isFunction(this.settings.threshold)){
      return this.settings.threshold.call(this, target, event);
    }
    return this.settings.threshold;
  };

  Kinetic.prototype._start = function (clientX, clientY){
    this.mouseDown = true;
    this.velocity = this.prevXPos = 0;
    this.velocityY = this.prevYPos = 0;
    this.xpos = clientX;
    this.ypos = clientY;
  };

  Kinetic.prototype._resetMouse = function (){
    this.xpos = false;
    this.ypos = false;
    this.mouseDown = false;
  };

  Kinetic.prototype._decelerateVelocity = function (velocity, slowdown){
    return Math.floor(Math.abs(velocity)) === 0 ? 0 // is velocity less than 1?
      : velocity * slowdown; // reduce slowdown
  };

  Kinetic.prototype._capVelocity = function (velocity, max){
    var newVelocity = velocity;
    if (velocity > 0){
      if (velocity > max){
        newVelocity = max;
      }
    } else {
      if (velocity < (0 - max)){
        newVelocity = (0 - max);
      }
    }
    return newVelocity;
  };

  Kinetic.prototype._setMoveClasses = function (classes){
    // FIXME: consider if we want to apply PL #44, this should not remove
    // classes we have not defined on the element!
    var settings = this.settings;
    var $this = this.$el;

    $this.removeClass(settings.movingClass.up)
      .removeClass(settings.movingClass.down)
      .removeClass(settings.movingClass.left)
      .removeClass(settings.movingClass.right)
      .removeClass(settings.deceleratingClass.up)
      .removeClass(settings.deceleratingClass.down)
      .removeClass(settings.deceleratingClass.left)
      .removeClass(settings.deceleratingClass.right);

    if (this.velocity > 0){
      $this.addClass(classes.right);
    }
    if (this.velocity < 0){
      $this.addClass(classes.left);
    }
    if (this.velocityY > 0){
      $this.addClass(classes.down);
    }
    if (this.velocityY < 0){
      $this.addClass(classes.up);
    }

  };


  // do the actual kinetic movement
  Kinetic.prototype._move = function (){
    var $scroller = this._getScroller();
    var scroller = $scroller[0];
    var self = this;
    var settings = self.settings;

    // set scrollLeft
    if (settings.x && scroller.scrollWidth > 0){
      this.scrollLeft(this.scrollLeft() + this.velocity);
      if (Math.abs(this.velocity) > 0){
        this.velocity = settings.decelerate ?
          self._decelerateVelocity(this.velocity, settings.slowdown) : this.velocity;
      }
    } else {
      this.velocity = 0;
    }

    // set scrollTop
    if (settings.y && scroller.scrollHeight > 0){
      this.scrollTop(this.scrollTop() + this.velocityY);
      if (Math.abs(this.velocityY) > 0){
        this.velocityY = settings.decelerate ?
          self._decelerateVelocity(this.velocityY, settings.slowdown) : this.velocityY;
      }
    } else {
      this.velocityY = 0;
    }

    self._setMoveClasses(settings.deceleratingClass);

    if ($.isFunction(settings.moved)){
      settings.moved.call(this, settings);
    }

    if (Math.abs(this.velocity) > 0 || Math.abs(this.velocityY) > 0){
      if (!this.moving) {
        this.moving = true;
        // tick for next movement
        window.requestAnimationFrame(function (){
          self.moving = false;
          self._move();
        });
      }
    } else {
      self.stop();
    }
  };

  // get current scroller to apply positioning to
  Kinetic.prototype._getScroller = function(){
    var $scroller = this.$el;
    if (this.$el.is('body') || this.$el.is('html')){
      $scroller = $(window);
    }
    return $scroller;
  };

  // set the scroll position
  Kinetic.prototype.scrollLeft = function(left){
    var $scroller = this._getScroller();
    if (typeof left === 'number'){
      $scroller.scrollLeft(left);
      this.settings.scrollLeft = left;
    } else {
      return $scroller.scrollLeft();
    }
  };
  Kinetic.prototype.scrollTop = function(top){
    var $scroller = this._getScroller();
    if (typeof top === 'number'){
      $scroller.scrollTop(top);
      this.settings.scrollTop = top;
    } else {
      return $scroller.scrollTop();
    }
  };

  Kinetic.prototype._attachListeners = function (){
    var $this = this.$el;
    var settings = this.settings;

    if ($.support.touch){
      $this
        .bind('touchstart', settings.events.touchStart)
        .bind('touchend', settings.events.inputEnd)
        .bind('touchmove', settings.events.touchMove);
    }
    
    $this
      .mousedown(settings.events.inputDown)
      .mouseup(settings.events.inputEnd)
      .mousemove(settings.events.inputMove);

    $this
      .click(settings.events.inputClick)
      .scroll(settings.events.scroll)
      .bind('selectstart', settings.events.selectStart)
      .bind('dragstart', settings.events.dragStart);
  };

  Kinetic.prototype._detachListeners = function (){
    var $this = this.$el;
    var settings = this.settings;
    if ($.support.touch){
      $this
        .unbind('touchstart', settings.events.touchStart)
        .unbind('touchend', settings.events.inputEnd)
        .unbind('touchmove', settings.events.touchMove);
    }

    $this
      .unbind('mousedown', settings.events.inputDown)
      .unbind('mouseup', settings.events.inputEnd)
      .unbind('mousemove', settings.events.inputMove);

    $this
      .unbind('click', settings.events.inputClick)
      .unbind('scroll', settings.events.scroll)
      .unbind('selectstart', settings.events.selectStart)
      .unbind('dragstart', settings.events.dragStart);
  };


  // EXPOSE KINETIC CONSTRUCTOR
  // ==========================
  $.Kinetic = Kinetic;

  // KINETIC PLUGIN DEFINITION
  // =======================

  $.fn.kinetic = function (option, callOptions) {
    return this.each(function () {
      var $this    = $(this);
      var instance = $this.data(Kinetic.DATA_KEY);
      var options  = $.extend({}, Kinetic.DEFAULTS, $this.data(), typeof option === 'object' && option);

      if (!instance) {
        $this.data(Kinetic.DATA_KEY, (instance = new Kinetic(this, options)));
      }

      if (typeof option === 'string') {
        instance[option](callOptions);
      }

    });
  };

}(window.jQuery || window.Zepto));
