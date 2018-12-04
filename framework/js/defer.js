$(function() {
    /* Toggle Show and Hide */
    $('.toggle').click(function(){		
    	var show = $(this).attr('data-on');
		
		if($(show).hasClass('show')) {
            $(show).removeClass('show');
			$(show).addClass('hide');
			$(this).removeClass('active');
		} else if($(show).hasClass('hide')) {
			$(show).removeClass('hide');
			$(show).addClass('show');
			$(this).addClass('active');
		} else {
			$(show).addClass('show');
			$(this).addClass('active');
		}
    });
	
	$('.toggler').change(function(){
        var target = $(this).data('target');
    	$(target).children('.category').addClass('hide');
		if($(this).is('select.toggler')){
    	    var show = $("option:selected", this).data('show');
            var tValue = $("option:selected", this).val();
            $( ".state" ).html(tValue);
		} else {
			var group = $('input[type=radio][name=forms_cats]'),
			    show = group.filter(':checked').data('show');
		}
    	$(show).removeClass('hide');
	});

	
	$('#content').on( 'click', '.radio_tog', function(){
		var classOn = $(this).attr('data-on');
		var radio = $(this).find('input');
		var fieldOn = $('.rt_content').find(classOn);
		var inputOn = $(fieldOn).find('input, select');
		//Remove active for all radio toggles
		$('.radio_tog').removeClass('active');
		$('.radio_tog').parent().addClass('collapse');
		$('.rt_field').removeClass('show');
		$('.rt_content').removeClass('hide');
		$('.rt_field input, .rt_field select').prop('disabled', true);
		
		if ($(radio).is(':checked')){
			$(this).addClass('active');
			$(classOn).addClass('show');
			$(inputOn).prop('disabled', false);
		}
	});
	
	$('.toggleN').click(function(){
		$(this).parent().toggleClass('show');
	});
	
	$('.prevent > a').click(function(event){
		event.preventDefault();
	});
	
	var $frame = $('#banners');
    var $frame2 = $('#emrs');
	var $wrap  = $frame.parent();
    var $wrap2  = $frame2.parent();
	$("#banners li").css({ width: $frame.width()});
    $("#emrs li").css({ width: $frame2.width()});

		// Call Sly on frame
		$frame.sly({
			horizontal: 1,
			itemNav: 'forceCentered',
			smart: 1,
			activateMiddle: 1,
			mouseDragging: 1,
			touchDragging: 1,
			releaseSwing: 1,
			startAt: 0,
			pagesBar: $frame.find('.pages'),
			scrollBy: 1,
			speed: 700,
			elasticBounds: 1,
			cycleBy: 'pages',
			cycleInterval: 7000, 

			// Buttons
			prev: $wrap.find('.prev'),
			next: $wrap.find('.next')
		});
    
		// Call Sly on frame
		$frame2.sly({
			horizontal: 1,
			itemNav: 'forceCentered',
			smart: 1,
			activateMiddle: 1,
			mouseDragging: 1,
			touchDragging: 1,
			releaseSwing: 1,
			startAt: 0,
			pagesBar: $frame2.find('.pages'),
			scrollBy: 1,
			speed: 700,
			elasticBounds: 1,
			cycleBy: 'pages',
			cycleInterval: 7000, 

			// Buttons
			prev: $wrap2.find('.prev'),
			next: $wrap2.find('.next')
		});
		
    $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(evn){
    	evn.preventDefault();
		$('#site_wrap').scrollTo(this.hash, 900, {offset: 0});
    });
	
	
	var preloader = "<span class='loader'><icon class='icon'>$</icon></span>";
	
	function addPreloader(){
		$('dyLoad').prepend(preloader);
        window.addPreloader=addPreloader;
	}
	
	
	function delayLoad(){
		var img = $(".preload img");
        var total  = 0;
		
		img.each(function(){
			total++;
			var imageSrc = $(this);
			imgParent = $(this).closest(".preload");
            
			if(total < 2) {
                imgParent.prepend(preloader);
			}
			
		    imageSrc.attr("src", imageSrc.data('src'));
            imageSrc.removeAttr('data-src');
		
		    imageSrc.on('load', function() {
                // do stuff on success
			    $(this).closest(".preload").addClass('loaded');
            });
		});
	}
	
	delayLoad();
    
    $('.g_toggle').on( 'click', '.g_link', function () {
        var activeEle = $(this).attr('data-on');
        
        if($(activeEle).hasClass('active')){
            $(activeEle).removeClass('active');
        } else {
            $('.g_toggle .active').removeClass('active');
            $(activeEle).addClass('active');
        }
    });
    
    $('.gTog').on( 'click, mouseover', 'button', function () {
        var activeEle = $(this).attr('data-on');
        $(this).parent().parent().find('.active').removeClass('active');
        $(this).addClass('active');
        $(activeEle).addClass('active');
    });
    
    $('body').on( 'change', '.qty', function () {
        var text_value=$(this).val();
        if(text_value!='') {
            $(this).parent().addClass('input_ok');
        } else {
            $(this).parent().removeClass('input_ok');
        }
    });
    
    $('#site_features').on( 'click', '.feature_link', function () {
        var activeEle = $(this).attr('data-on');
        
        $('#feature_box, #hitBox').addClass('show');
        $('.feature_link').removeClass('active');
        
        if($(activeEle).hasClass('active')){
            $(activeEle).removeClass('active');
            $('#feature_box, #hitBox').removeClass('show');
        } else {
            $('.feature').removeClass('active');
            $(activeEle).addClass('active');
            $(this).addClass('active');
        }
    });
    
    $('header').on( 'click', '#hitBox', function () {
        $('#feature_box, #hitBox').removeClass('show');
        $('.feature').removeClass('active');
        $('.feature_link').removeClass('active');
    });
    
    function showSubMenu(){
        $('.current-menu-item, .current-menu-parent').addClass('show');
    }
    
    showSubMenu();
    
    function newsFeed(){
        var numLow = 1;
        var numHigh = 3;
        var adjustedHigh = (parseFloat(numLow))+1;
        var numRand = Math.floor(Math.random()*adjustedHigh)+parseFloat(numLow);
        $('#dynamic').removeClass('hide');

    $.ajax({
        type: 'GET',
        cache: false,
        url: _mother + _fwPath + '/xml/news.xml',
        dataType: 'xml',
        success: function(xml_list) {
            var xmlArr = [];
            var totalXML = $(xml_list).find("entry").length;
            var random = Math.floor((Math.random() * totalXML));
            
            $(xml_list).find('entry').eq(random).each(function() {
                var xml_type = $(this).find('type').text();
                var xml_item = $(this).find('item').text();
                var xml_url = $(this).find('url').text();
 
                $('#dynamic strong').html(xml_type);
                $('#dynamic a').html(xml_item);
				if( xml_url === ''){
					$('#dynamic a').attr('href', '').css({'cursor': 'pointer', 'pointer-events' : 'none'});
				}else {
					$('#dynamic a').attr('href', xml_url).removeAttr("style");
				}
                $('#dynamic').addClass('active');
            });
        }
    }); // end each loop
}
    
    function pageTitle(){
        $('#pageTitle').addClass('active');
        
        function removeTitle(){
            $('#pageTitle').removeClass('active').addClass('hide');
        }
        
        setTimeout(removeTitle, 10000);
    }
    
    function hideItem(){
        $('#dynamic').removeClass('active').addClass('hide');
        setTimeout(showItem, 1000);
    }
    
    function showItem(){
        newsFeed();
    }
    
    function liveBar(){
        hideItem();
        showItem();
        
        setInterval(hideItem, 30000);
    }

    if (window.matchMedia("(min-width: 48em)").matches && $('#live_bar').length) {
        pageTitle();
        setTimeout(liveBar, 12000);
    }
    
    var circles ="<div id='circles'><span id='circle_lg'></span><span id='circle_sm'></span></div>";
    
    $('#menu-header-menu > .current-menu-item > a').prepend(circles);
    
    
    //http://jsfiddle.net/rahzy/1/
    $('#site_wrap, #main_menu').on( 'click', 'a[href]', function (event) {
        hostname = new RegExp(location.host);

        // Store current link's url
        var url = $(this).attr("href");
		
        if($(this).parent('li').hasClass("coming-soon")){
			event.preventDefault();
			$('#site_wrap').addClass('blur');
            $("#external_link").addClass('show coming-soon');
			$(".btn_confirm").attr("href", '');
        // Test if current host (domain) is in it
        }else if(hostname.test(url)){
            // If it's local...
        }else if(url.slice(0, 1) === "#"){
            // It's an anchor link
        }else if($(this).hasClass("result")){
            
        }else{
            // a link that does not contain the current host
            event.preventDefault();
            $('#site_wrap').addClass('blur');
            $("#external_link").addClass('show');
            $(".btn_confirm").attr("href", url);
        }
    });
	
	$('.overlay').on( 'click', '.btn_cancel, .btn_close', function () {
		$('#site_wrap').removeClass('blur');
        $("#external_link").removeClass('show');
		
		if($(this).is('.btn_close')){
			$("#external_link").removeClass('coming-soon');
		}
	});
       
    $('.category').on( 'click', '.expand', function () {
		$(this).parent().children('.expand').removeClass('show');
        $(this).addClass('show');
    });
	
	$('.category .expand:first-child').addClass('show');
    
    $('#rm').on( 'mouseover', 'li', function () {
        var methodName = $(this).attr("title");
        $('.rm').removeClass('active');
        $(this).addClass('active');
        $('#rm > h3').text(methodName);
    });
	
	ns.checkFields();
});


//Input Fields
(function($) {
    var namespace;
    
    namespace = {
        checkFields : function() {
	        //start of checkFields function
	jQuery.extend(jQuery.expr[':'], {
    	invalid : function(elem, index, match){
        	var invalids = document.querySelectorAll(':invalid'),
            	result = false,
            	len = invalids.length;

        	if (len) {
            	for (var i=0; i<len; i++) {
                	if (elem === invalids[i]) {
                    	result = true;
                    	break;
                	}
            	}
        	}
        	return result;
    	}
	});

	jQuery.extend(jQuery.expr[':'], {
    	valid : function(elem, index, match){
        	var valids = document.querySelectorAll(':valid'),
            	result2 = false,
            	len2 = valids.length;

        	if (len2) {
            	for (var i=0; i<len2; i++) {
                	if (elem === valids[i]) {
                    	result2 = true;
                    	break;
                	}
            	}
        	}
        	return result2;
    	}
	});
            
            $('.field').each(function(){
                var parent = $(this),
                field = parent.find('input[type="text"], input[type="tel"], input[type="password"], input[type="email"], input[type="number"], select, textarea,input[type="date"]'),
                otherField = parent.find('.sel_other');

                function showLabel() {
	                if (field.val() === '') {
                        parent.removeClass('show-label');
                    } else {
		                parent.addClass('show-label');
	                }
	            }

                showLabel();
            
	            function validation() {
                    if (field.is(":valid") && field.val() !== '')  {
		                parent.removeClass('error');
                        parent.addClass('valid');
                    } else if (field.is(":invalid")) {
                        parent.removeClass('valid');
                        parent.addClass('error');
                    } else if (field.val() === '') {
                        parent.removeClass('valid');
                    }
                    
                    if (otherField.is(":valid") && otherField.val() !== '')  {
		                otherField.removeClass('error');
                        otherField.addClass('valid');
                    } else if (otherField.is(":invalid")) {
                        otherField.removeClass('valid');
                        otherField.addClass('error');
                    } else if (otherField.val() === '') {
                        otherField.removeClass('valid');
                    }
					
					if(parent.is('.EX.show')){
						if (otherField.is(":valid") && field.val() !== ''){
							parent.removeClass('error');
                            parent.addClass('valid');
						} else if (otherField.is(":invalid")){
							parent.removeClass('valid');
                            parent.addClass('error');
						}
						
						otherField.prop('required',true);
					}
					
					if(parent.is('.EX:not(.show)')){
						otherField.prop('required',false);
						otherField.val('');
					}
					
					if(field.is('.same')){
						var $field = $(this).closest('.field'),
						    $sameP = $field.prev('.field').find('.sameP');
						
						if($(this).val() == $sameP.val() && $sameP.val() !== ''){
							$field.removeClass("error").addClass("valid");
						}else {
							$field.removeClass("valid").addClass("error");
						}
					}
                
	            }

                field.focus(function(){
                    parent.addClass('show-label');
                });
				
				parent.on('focus', '.if', function(){
		            parent.addClass('focus');
	            });
	
	            parent.on('blur', '.if', function(){
		            parent.removeClass('focus');
	            }); 


	            field.on('change',showLabel);
	            field.on('blur',showLabel);
	            field.on('blur',validation);
	            field.on('input',validation);
                $('select').on('change',selectF);
				if(parent.is('.EX')){
				    parent.on('change', validation);
				}

	            function selectF() {
                    var field = $(this);
                    var ddValue = field.val();
			
                    if (field.val() === '') {
                        field.addClass('no-selection');
                        field.removeClass('valid');
                    } else {
                        field.removeClass('no-selection');
                    }

                    if (field.val() === 'Other') {
                        field.parent().parent().addClass('show');
                    } else {
                        field.parent().parent().not('.patientF').removeClass('show');
                    }
                }
				
				if( field.is("[data-reqs]") && !parent.find('.requirements').length){
			        var fieldType = "field";
			        var parentF = field.closest('.field');
			        var reqList = field.attr('data-reqs').split(',');
					var reqTitle = field.attr('data-title');
			        var reqHTML = "<section class='requirements'><h6>The <span class='ftype'></span> must:</h6><ul class='req_list'></ul></section>";
		
					parentF.append(reqHTML);
			
			        var container = parentF.find('.req_list');
					
					if (field.data('title')) {
						parentF.find('.requirements h6').html(reqTitle);
					}
			
			        if (field.data('reqs')) {
			            container.html($.map(reqList, function(v) { 
                            return '<li>' + v + '</li>';
                        }).join(''));
			        }
			
			        if( field.is('input[type=password]')){
				        fieldType = "password";
			        } else if (field.is('input[type=number]')){
				        fieldType = "age";
				        var minAge = field.attr('min');
				        var maxAge = field.attr('max');
				
				        if( field.attr('min') && field.attr('max')){
					        parentF.find('.req_list').append("<li>Be between the ages of " + minAge + " and " + maxAge + " years old</li>");
				        } else if( field.attr('max')){
					        parentF.find('.req_list').append("<li>Be " + maxAge + " years old or younger</li>");
				        } else if( field.attr('min')){
					        parentF.find('.req_list').append("<li>Be at least " + minAge + " years old</li>");
				        }
				
			        } else if (field.is('input[type=tel]')){
				        fieldType = "phone number";
			        }
			
			        parentF.find('.ftype').text(fieldType);
		        }
            });
        }
    };
    
    window.ns = namespace;
    
})(this.jQuery);







/*!
 * jQuery.ScrollTo
 * Copyright (c) 2007-2014 Ariel Flesler - aflesler<a>gmail<d>com | http://flesler.blogspot.com
 * Licensed under MIT
 * http://flesler.blogspot.com/2007/10/jqueryscrollto.html
 * @projectDescription Easy element scrolling using jQuery.
 * @author Ariel Flesler
 * @version 1.4.12
 */

;(function(plugin) {
    // AMD Support
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], plugin);
    } else {
        plugin(jQuery);
    }
}(function($) {

	var $scrollTo = $.scrollTo = function( target, duration, settings ) {
		return $(window).scrollTo( target, duration, settings );
	};

	$scrollTo.defaults = {
		axis:'xy',
		duration: parseFloat($.fn.jquery) >= 1.3 ? 0 : 1,
		limit:true
	};

	// Returns the element that needs to be animated to scroll the window.
	// Kept for backwards compatibility (specially for localScroll & serialScroll)
	$scrollTo.window = function( scope ) {
		return $(window)._scrollable();
	};

	// Hack, hack, hack :)
	// Returns the real elements to scroll (supports window/iframes, documents and regular nodes)
	$.fn._scrollable = function() {
		return this.map(function() {
			var elem = this,
				isWin = !elem.nodeName || $.inArray( elem.nodeName.toLowerCase(), ['iframe','#document','html','body'] ) != -1;

				if (!isWin)
					return elem;

			var doc = (elem.contentWindow || elem).document || elem.ownerDocument || elem;

			return /webkit/i.test(navigator.userAgent) || doc.compatMode == 'BackCompat' ?
				doc.body :
				doc.documentElement;
		});
	};

	$.fn.scrollTo = function( target, duration, settings ) {
		if (typeof duration == 'object') {
			settings = duration;
			duration = 0;
		}
		if (typeof settings == 'function')
			settings = { onAfter:settings };

		if (target == 'max')
			target = 9e9;

		settings = $.extend( {}, $scrollTo.defaults, settings );
		// Speed is still recognized for backwards compatibility
		duration = duration || settings.duration;
		// Make sure the settings are given right
		settings.queue = settings.queue && settings.axis.length > 1;

		if (settings.queue)
			// Let's keep the overall duration
			duration /= 2;
		settings.offset = both( settings.offset );
		settings.over = both( settings.over );

		return this._scrollable().each(function() {
			// Null target yields nothing, just like jQuery does
			if (target == null) return;

			var elem = this,
				$elem = $(elem),
				targ = target, toff, attr = {},
				win = $elem.is('html,body');

			switch (typeof targ) {
				// A number will pass the regex
				case 'number':
				case 'string':
					if (/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)) {
						targ = both( targ );
						// We are done
						break;
					}
					// Relative/Absolute selector, no break!
					targ = win ? $(targ) : $(targ, this);
					if (!targ.length) return;
				case 'object':
					// DOMElement / jQuery
					if (targ.is || targ.style)
						// Get the real position of the target
						toff = (targ = $(targ)).offset();
			}
			
			var offset = $.isFunction(settings.offset) && settings.offset(elem, targ) || settings.offset;
			
			$.each( settings.axis.split(''), function( i, axis ) {
				var Pos	= axis == 'x' ? 'Left' : 'Top',
					pos = Pos.toLowerCase(),
					key = 'scroll' + Pos,
					old = elem[key],
					max = $scrollTo.max(elem, axis);

				if (toff) {// jQuery / DOMElement
					attr[key] = toff[pos] + ( win ? 0 : old - $elem.offset()[pos] );

					// If it's a dom element, reduce the margin
					if (settings.margin) {
						attr[key] -= parseInt(targ.css('margin'+Pos)) || 0;
						attr[key] -= parseInt(targ.css('border'+Pos+'Width')) || 0;
					}

					attr[key] += offset[pos] || 0;

					if(settings.over[pos])
						// Scroll to a fraction of its width/height
						attr[key] += targ[axis=='x'?'width':'height']() * settings.over[pos];
				} else {
					var val = targ[pos];
					// Handle percentage values
					attr[key] = val.slice && val.slice(-1) == '%' ?
						parseFloat(val) / 100 * max
						: val;
				}

				// Number or 'number'
				if (settings.limit && /^\d+$/.test(attr[key]))
					// Check the limits
					attr[key] = attr[key] <= 0 ? 0 : Math.min( attr[key], max );

				// Queueing axes
				if (!i && settings.queue) {
					// Don't waste time animating, if there's no need.
					if (old != attr[key])
						// Intermediate animation
						animate( settings.onAfterFirst );
					// Don't animate this axis again in the next iteration.
					delete attr[key];
				}
			});

			animate( settings.onAfter );

			function animate( callback ) {
				$elem.animate( attr, duration, settings.easing, callback && function() {
					callback.call(this, targ, settings);
				});
			};

		}).end();
	};

	// Max scrolling position, works on quirks mode
	// It only fails (not too badly) on IE, quirks mode.
	$scrollTo.max = function( elem, axis ) {
		var Dim = axis == 'x' ? 'Width' : 'Height',
			scroll = 'scroll'+Dim;

		if (!$(elem).is('html,body'))
			return elem[scroll] - $(elem)[Dim.toLowerCase()]();

		var size = 'client' + Dim,
			html = elem.ownerDocument.documentElement,
			body = elem.ownerDocument.body;

		return Math.max( html[scroll], body[scroll] )
			 - Math.min( html[size]  , body[size]   );
	};

	function both( val ) {
		return $.isFunction(val) || typeof val == 'object' ? val : { top:val, left:val };
	};

    // AMD requirement
    return $scrollTo;
}));





/*! sly 1.6.1 - 8th Aug 2015 | https://github.com/darsain/sly */
!function(a,b,c){"use strict";function d(b,p,q){function K(c){var d=0,e=Gb.length;if(yb.old=a.extend({},yb),wb=tb?0:ub[rb.horizontal?"width":"height"](),Bb=zb[rb.horizontal?"width":"height"](),xb=tb?b:vb[rb.horizontal?"outerWidth":"outerHeight"](),Gb.length=0,yb.start=0,yb.end=H(xb-wb,0),Rb){d=Ib.length,Hb=vb.children(rb.itemSelector),Ib.length=0;var f,g=j(vb,rb.horizontal?"paddingLeft":"paddingTop"),h=j(vb,rb.horizontal?"paddingRight":"paddingBottom"),i="border-box"===a(Hb).css("boxSizing"),l="none"!==Hb.css("float"),m=0,n=Hb.length-1;xb=0,Hb.each(function(b,c){var d=a(c),e=c.getBoundingClientRect(),i=G(rb.horizontal?e.width||e.right-e.left:e.height||e.bottom-e.top),k=j(d,rb.horizontal?"marginLeft":"marginTop"),o=j(d,rb.horizontal?"marginRight":"marginBottom"),p=i+k+o,q=!k||!o,r={};r.el=c,r.size=q?i:p,r.half=r.size/2,r.start=xb+(q?k:0),r.center=r.start-G(wb/2-r.size/2),r.end=r.start-wb+r.size,b||(xb+=g),xb+=p,rb.horizontal||l||o&&k&&b>0&&(xb-=I(k,o)),b===n&&(r.end+=h,xb+=h,m=q?o:0),Ib.push(r),f=r}),vb[0].style[rb.horizontal?"width":"height"]=(i?xb:xb-g-h)+"px",xb-=m,Ib.length?(yb.start=Ib[0][Pb?"center":"start"],yb.end=Pb?f.center:xb>wb?f.end:yb.start):yb.start=yb.end=0}if(yb.center=G(yb.end/2+yb.start/2),V(),Ab.length&&Bb>0&&(rb.dynamicHandle?(Cb=yb.start===yb.end?Bb:G(Bb*wb/xb),Cb=k(Cb,rb.minHandleSize,Bb),Ab[0].style[rb.horizontal?"width":"height"]=Cb+"px"):Cb=Ab[rb.horizontal?"outerWidth":"outerHeight"](),Db.end=Bb-Cb,ec||N()),!tb&&wb>0){var o=yb.start,p="";if(Rb)a.each(Ib,function(a,b){Pb?Gb.push(b.center):b.start+b.size>o&&o<=yb.end&&(o=b.start,Gb.push(o),o+=wb,o>yb.end&&o<yb.end+wb&&Gb.push(yb.end))});else for(;o-wb<yb.end;)Gb.push(o),o+=wb;if(Eb[0]&&e!==Gb.length){for(var q=0;q<Gb.length;q++)p+=rb.pageBuilder.call(sb,q);Fb=Eb.html(p).children(),Fb.eq(Jb.activePage).addClass(rb.activeClass)}}if(Jb.slideeSize=xb,Jb.frameSize=wb,Jb.sbSize=Bb,Jb.handleSize=Cb,Rb){c&&null!=rb.startAt&&(T(rb.startAt),sb[Qb?"toCenter":"toStart"](rb.startAt));var r=Ib[Jb.activeItem];L(Qb&&r?r.center:k(yb.dest,yb.start,yb.end))}else c?null!=rb.startAt&&L(rb.startAt,1):L(k(yb.dest,yb.start,yb.end));ob("load")}function L(a,b,c){if(Rb&&cc.released&&!c){var d=U(a),e=a>yb.start&&a<yb.end;Qb?(e&&(a=Ib[d.centerItem].center),Pb&&rb.activateMiddle&&T(d.centerItem)):e&&(a=Ib[d.firstItem].start)}cc.init&&cc.slidee&&rb.elasticBounds?a>yb.end?a=yb.end+(a-yb.end)/6:a<yb.start&&(a=yb.start+(a-yb.start)/6):a=k(a,yb.start,yb.end),ac.start=+new Date,ac.time=0,ac.from=yb.cur,ac.to=a,ac.delta=a-yb.cur,ac.tweesing=cc.tweese||cc.init&&!cc.slidee,ac.immediate=!ac.tweesing&&(b||cc.init&&cc.slidee||!rb.speed),cc.tweese=0,a!==yb.dest&&(yb.dest=a,ob("change"),ec||M()),Z(),V(),W(),O()}function M(){if(sb.initialized){if(!ec)return ec=t(M),void(cc.released&&ob("moveStart"));ac.immediate?yb.cur=ac.to:ac.tweesing?(ac.tweeseDelta=ac.to-yb.cur,D(ac.tweeseDelta)<.1?yb.cur=ac.to:yb.cur+=ac.tweeseDelta*(cc.released?rb.swingSpeed:rb.syncSpeed)):(ac.time=I(+new Date-ac.start,rb.speed),yb.cur=ac.from+ac.delta*a.easing[rb.easing](ac.time/rb.speed,ac.time,0,1,rb.speed)),ac.to===yb.cur?(yb.cur=ac.to,cc.tweese=ec=0):ec=t(M),ob("move"),tb||(m?vb[0].style[m]=n+(rb.horizontal?"translateX":"translateY")+"("+-yb.cur+"px)":vb[0].style[rb.horizontal?"left":"top"]=-G(yb.cur)+"px"),!ec&&cc.released&&ob("moveEnd"),N()}}function N(){Ab.length&&(Db.cur=yb.start===yb.end?0:((cc.init&&!cc.slidee?yb.dest:yb.cur)-yb.start)/(yb.end-yb.start)*Db.end,Db.cur=k(G(Db.cur),Db.start,Db.end),_b.hPos!==Db.cur&&(_b.hPos=Db.cur,m?Ab[0].style[m]=n+(rb.horizontal?"translateX":"translateY")+"("+Db.cur+"px)":Ab[0].style[rb.horizontal?"left":"top"]=Db.cur+"px"))}function O(){Fb[0]&&_b.page!==Jb.activePage&&(_b.page=Jb.activePage,Fb.removeClass(rb.activeClass).eq(Jb.activePage).addClass(rb.activeClass),ob("activePage",_b.page))}function P(){bc.speed&&yb.cur!==(bc.speed>0?yb.end:yb.start)||sb.stop(),hc=cc.init?t(P):0,bc.now=+new Date,bc.pos=yb.cur+(bc.now-bc.lastTime)/1e3*bc.speed,L(cc.init?bc.pos:G(bc.pos)),cc.init||yb.cur!==yb.dest||ob("moveEnd"),bc.lastTime=bc.now}function Q(a,b,d){if("boolean"===e(b)&&(d=b,b=c),b===c)L(yb[a],d);else{if(Qb&&"center"!==a)return;var f=sb.getPos(b);f&&L(f[a],d,!Qb)}}function R(a){return null!=a?i(a)?a>=0&&a<Ib.length?a:-1:Hb.index(a):-1}function S(a){return R(i(a)&&0>a?a+Ib.length:a)}function T(a,b){var c=R(a);return!Rb||0>c?!1:((_b.active!==c||b)&&(Hb.eq(Jb.activeItem).removeClass(rb.activeClass),Hb.eq(c).addClass(rb.activeClass),_b.active=Jb.activeItem=c,W(),ob("active",c)),c)}function U(a){a=k(i(a)?a:yb.dest,yb.start,yb.end);var b={},c=Pb?0:wb/2;if(!tb)for(var d=0,e=Gb.length;e>d;d++){if(a>=yb.end||d===Gb.length-1){b.activePage=Gb.length-1;break}if(a<=Gb[d]+c){b.activePage=d;break}}if(Rb){for(var f=!1,g=!1,h=!1,j=0,l=Ib.length;l>j;j++)if(f===!1&&a<=Ib[j].start+Ib[j].half&&(f=j),h===!1&&a<=Ib[j].center+Ib[j].half&&(h=j),j===l-1||a<=Ib[j].end+Ib[j].half){g=j;break}b.firstItem=i(f)?f:0,b.centerItem=i(h)?h:b.firstItem,b.lastItem=i(g)?g:b.centerItem}return b}function V(b){a.extend(Jb,U(b))}function W(){var a=yb.dest<=yb.start,b=yb.dest>=yb.end,c=(a?1:0)|(b?2:0);if(_b.slideePosState!==c&&(_b.slideePosState=c,Yb.is("button,input")&&Yb.prop("disabled",a),Zb.is("button,input")&&Zb.prop("disabled",b),Yb.add(Vb)[a?"addClass":"removeClass"](rb.disabledClass),Zb.add(Ub)[b?"addClass":"removeClass"](rb.disabledClass)),_b.fwdbwdState!==c&&cc.released&&(_b.fwdbwdState=c,Vb.is("button,input")&&Vb.prop("disabled",a),Ub.is("button,input")&&Ub.prop("disabled",b)),Rb&&null!=Jb.activeItem){var d=0===Jb.activeItem,e=Jb.activeItem>=Ib.length-1,f=(d?1:0)|(e?2:0);_b.itemsButtonState!==f&&(_b.itemsButtonState=f,Wb.is("button,input")&&Wb.prop("disabled",d),Xb.is("button,input")&&Xb.prop("disabled",e),Wb[d?"addClass":"removeClass"](rb.disabledClass),Xb[e?"addClass":"removeClass"](rb.disabledClass))}}function X(a,b,c){if(a=S(a),b=S(b),a>-1&&b>-1&&a!==b&&(!c||b!==a-1)&&(c||b!==a+1)){Hb.eq(a)[c?"insertAfter":"insertBefore"](Ib[b].el);var d=b>a?a:c?b:b-1,e=a>b?a:c?b+1:b,f=a>b;null!=Jb.activeItem&&(a===Jb.activeItem?_b.active=Jb.activeItem=c?f?b+1:b:f?b:b-1:Jb.activeItem>d&&Jb.activeItem<e&&(_b.active=Jb.activeItem+=f?1:-1)),K()}}function Y(a,b){for(var c=0,d=$b[a].length;d>c;c++)if($b[a][c]===b)return c;return-1}function Z(){cc.released&&!sb.isPaused&&sb.resume()}function $(a){return G(k(a,Db.start,Db.end)/Db.end*(yb.end-yb.start))+yb.start}function _(){cc.history[0]=cc.history[1],cc.history[1]=cc.history[2],cc.history[2]=cc.history[3],cc.history[3]=cc.delta}function ab(a){cc.released=0,cc.source=a,cc.slidee="slidee"===a}function bb(b){var c="touchstart"===b.type,d=b.data.source,e="slidee"===d;cc.init||!c&&eb(b.target)||("handle"!==d||rb.dragHandle&&Db.start!==Db.end)&&(!e||(c?rb.touchDragging:rb.mouseDragging&&b.which<2))&&(c||f(b),ab(d),cc.init=0,cc.$source=a(b.target),cc.touch=c,cc.pointer=c?b.originalEvent.touches[0]:b,cc.initX=cc.pointer.pageX,cc.initY=cc.pointer.pageY,cc.initPos=e?yb.cur:Db.cur,cc.start=+new Date,cc.time=0,cc.path=0,cc.delta=0,cc.locked=0,cc.history=[0,0,0,0],cc.pathToLock=e?c?30:10:0,u.on(c?x:w,cb),sb.pause(1),(e?vb:Ab).addClass(rb.draggedClass),ob("moveStart"),e&&(fc=setInterval(_,10)))}function cb(a){if(cc.released="mouseup"===a.type||"touchend"===a.type,cc.pointer=cc.touch?a.originalEvent[cc.released?"changedTouches":"touches"][0]:a,cc.pathX=cc.pointer.pageX-cc.initX,cc.pathY=cc.pointer.pageY-cc.initY,cc.path=E(F(cc.pathX,2)+F(cc.pathY,2)),cc.delta=rb.horizontal?cc.pathX:cc.pathY,cc.released||!(cc.path<1)){if(!cc.init){if(cc.path<rb.dragThreshold)return cc.released?db():c;if(!(rb.horizontal?D(cc.pathX)>D(cc.pathY):D(cc.pathX)<D(cc.pathY)))return db();cc.init=1}f(a),!cc.locked&&cc.path>cc.pathToLock&&cc.slidee&&(cc.locked=1,cc.$source.on(z,g)),cc.released&&(db(),rb.releaseSwing&&cc.slidee&&(cc.swing=(cc.delta-cc.history[0])/40*300,cc.delta+=cc.swing,cc.tweese=D(cc.swing)>10)),L(cc.slidee?G(cc.initPos-cc.delta):$(cc.initPos+cc.delta))}}function db(){clearInterval(fc),cc.released=!0,u.off(cc.touch?x:w,cb),(cc.slidee?vb:Ab).removeClass(rb.draggedClass),setTimeout(function(){cc.$source.off(z,g)}),yb.cur===yb.dest&&cc.init&&ob("moveEnd"),sb.resume(1),cc.init=0}function eb(b){return~a.inArray(b.nodeName,B)||a(b).is(rb.interactive)}function fb(){sb.stop(),u.off("mouseup",fb)}function gb(a){switch(f(a),this){case Ub[0]:case Vb[0]:sb.moveBy(Ub.is(this)?rb.moveBy:-rb.moveBy),u.on("mouseup",fb);break;case Wb[0]:sb.prev();break;case Xb[0]:sb.next();break;case Yb[0]:sb.prevPage();break;case Zb[0]:sb.nextPage()}}function hb(a){return dc.curDelta=(rb.horizontal?a.deltaY||a.deltaX:a.deltaY)||-a.wheelDelta,dc.curDelta/=1===a.deltaMode?3:100,Rb?(o=+new Date,dc.last<o-dc.resetTime&&(dc.delta=0),dc.last=o,dc.delta+=dc.curDelta,D(dc.delta)<1?dc.finalDelta=0:(dc.finalDelta=G(dc.delta/1),dc.delta%=1),dc.finalDelta):dc.curDelta}function ib(a){a.originalEvent[r]=sb;var b=+new Date;if(J+rb.scrollHijack>b&&Sb[0]!==document&&Sb[0]!==window)return void(J=b);if(rb.scrollBy&&yb.start!==yb.end){var c=hb(a.originalEvent);(rb.scrollTrap||c>0&&yb.dest<yb.end||0>c&&yb.dest>yb.start)&&f(a,1),sb.slideBy(rb.scrollBy*c)}}function jb(a){rb.clickBar&&a.target===zb[0]&&(f(a),L($((rb.horizontal?a.pageX-zb.offset().left:a.pageY-zb.offset().top)-Cb/2)))}function kb(a){if(rb.keyboardNavBy)switch(a.which){case rb.horizontal?37:38:f(a),sb["pages"===rb.keyboardNavBy?"prevPage":"prev"]();break;case rb.horizontal?39:40:f(a),sb["pages"===rb.keyboardNavBy?"nextPage":"next"]()}}function lb(a){return eb(this)?void(a.originalEvent[r+"ignore"]=!0):void(this.parentNode!==vb[0]||a.originalEvent[r+"ignore"]||sb.activate(this))}function mb(){this.parentNode===Eb[0]&&sb.activatePage(Fb.index(this))}function nb(a){rb.pauseOnHover&&sb["mouseenter"===a.type?"pause":"resume"](2)}function ob(a,b){if($b[a]){for(qb=$b[a].length,C.length=0,pb=0;qb>pb;pb++)C.push($b[a][pb]);for(pb=0;qb>pb;pb++)C[pb].call(sb,a,b)}}if(!(this instanceof d))return new d(b,p,q);var pb,qb,rb=a.extend({},d.defaults,p),sb=this,tb=i(b),ub=a(b),vb=rb.slidee?a(rb.slidee).eq(0):ub.children().eq(0),wb=0,xb=0,yb={start:0,center:0,end:0,cur:0,dest:0},zb=a(rb.scrollBar).eq(0),Ab=zb.children().eq(0),Bb=0,Cb=0,Db={start:0,end:0,cur:0},Eb=a(rb.pagesBar),Fb=0,Gb=[],Hb=0,Ib=[],Jb={firstItem:0,lastItem:0,centerItem:0,activeItem:null,activePage:0},Kb=new l(ub[0]),Lb=new l(vb[0]),Mb=new l(zb[0]),Nb=new l(Ab[0]),Ob="basic"===rb.itemNav,Pb="forceCentered"===rb.itemNav,Qb="centered"===rb.itemNav||Pb,Rb=!tb&&(Ob||Qb||Pb),Sb=rb.scrollSource?a(rb.scrollSource):ub,Tb=rb.dragSource?a(rb.dragSource):ub,Ub=a(rb.forward),Vb=a(rb.backward),Wb=a(rb.prev),Xb=a(rb.next),Yb=a(rb.prevPage),Zb=a(rb.nextPage),$b={},_b={},ac={},bc={},cc={released:1},dc={last:0,delta:0,resetTime:200},ec=0,fc=0,gc=0,hc=0;tb||(b=ub[0]),sb.initialized=0,sb.frame=b,sb.slidee=vb[0],sb.pos=yb,sb.rel=Jb,sb.items=Ib,sb.pages=Gb,sb.isPaused=0,sb.options=rb,sb.dragging=cc,sb.reload=function(){K()},sb.getPos=function(a){if(Rb){var b=R(a);return-1!==b?Ib[b]:!1}var c=vb.find(a).eq(0);if(c[0]){var d=rb.horizontal?c.offset().left-vb.offset().left:c.offset().top-vb.offset().top,e=c[rb.horizontal?"outerWidth":"outerHeight"]();return{start:d,center:d-wb/2+e/2,end:d-wb+e,size:e}}return!1},sb.moveBy=function(a){bc.speed=a,!cc.init&&bc.speed&&yb.cur!==(bc.speed>0?yb.end:yb.start)&&(bc.lastTime=+new Date,bc.startPos=yb.cur,ab("button"),cc.init=1,ob("moveStart"),s(hc),P())},sb.stop=function(){"button"===cc.source&&(cc.init=0,cc.released=1)},sb.prev=function(){sb.activate(null==Jb.activeItem?0:Jb.activeItem-1)},sb.next=function(){sb.activate(null==Jb.activeItem?0:Jb.activeItem+1)},sb.prevPage=function(){sb.activatePage(Jb.activePage-1)},sb.nextPage=function(){sb.activatePage(Jb.activePage+1)},sb.slideBy=function(a,b){a&&(Rb?sb[Qb?"toCenter":"toStart"](k((Qb?Jb.centerItem:Jb.firstItem)+rb.scrollBy*a,0,Ib.length)):L(yb.dest+a,b))},sb.slideTo=function(a,b){L(a,b)},sb.toStart=function(a,b){Q("start",a,b)},sb.toEnd=function(a,b){Q("end",a,b)},sb.toCenter=function(a,b){Q("center",a,b)},sb.getIndex=R,sb.activate=function(a,b){var c=T(a);rb.smart&&c!==!1&&(Qb?sb.toCenter(c,b):c>=Jb.lastItem?sb.toStart(c,b):c<=Jb.firstItem?sb.toEnd(c,b):Z())},sb.activatePage=function(a,b){i(a)&&L(Gb[k(a,0,Gb.length-1)],b)},sb.resume=function(a){rb.cycleBy&&rb.cycleInterval&&("items"!==rb.cycleBy||Ib[0]&&null!=Jb.activeItem)&&!(a<sb.isPaused)&&(sb.isPaused=0,gc?gc=clearTimeout(gc):ob("resume"),gc=setTimeout(function(){switch(ob("cycle"),rb.cycleBy){case"items":sb.activate(Jb.activeItem>=Ib.length-1?0:Jb.activeItem+1);break;case"pages":sb.activatePage(Jb.activePage>=Gb.length-1?0:Jb.activePage+1)}},rb.cycleInterval))},sb.pause=function(a){a<sb.isPaused||(sb.isPaused=a||100,gc&&(gc=clearTimeout(gc),ob("pause")))},sb.toggle=function(){sb[gc?"pause":"resume"]()},sb.set=function(b,c){a.isPlainObject(b)?a.extend(rb,b):rb.hasOwnProperty(b)&&(rb[b]=c)},sb.add=function(b,c){var d=a(b);Rb?(null==c||!Ib[0]||c>=Ib.length?d.appendTo(vb):Ib.length&&d.insertBefore(Ib[c].el),null!=Jb.activeItem&&c<=Jb.activeItem&&(_b.active=Jb.activeItem+=d.length)):vb.append(d),K()},sb.remove=function(b){if(Rb){var c=S(b);if(c>-1){Hb.eq(c).remove();var d=c===Jb.activeItem;null!=Jb.activeItem&&c<Jb.activeItem&&(_b.active=--Jb.activeItem),K(),d&&(_b.active=null,sb.activate(Jb.activeItem))}}else a(b).remove(),K()},sb.moveAfter=function(a,b){X(a,b,1)},sb.moveBefore=function(a,b){X(a,b)},sb.on=function(a,b){if("object"===e(a))for(var c in a)a.hasOwnProperty(c)&&sb.on(c,a[c]);else if("function"===e(b))for(var d=a.split(" "),f=0,g=d.length;g>f;f++)$b[d[f]]=$b[d[f]]||[],-1===Y(d[f],b)&&$b[d[f]].push(b);else if("array"===e(b))for(var h=0,i=b.length;i>h;h++)sb.on(a,b[h])},sb.one=function(a,b){function c(){b.apply(sb,arguments),sb.off(a,c)}sb.on(a,c)},sb.off=function(a,b){if(b instanceof Array)for(var c=0,d=b.length;d>c;c++)sb.off(a,b[c]);else for(var e=a.split(" "),f=0,g=e.length;g>f;f++)if($b[e[f]]=$b[e[f]]||[],null==b)$b[e[f]].length=0;else{var h=Y(e[f],b);-1!==h&&$b[e[f]].splice(h,1)}},sb.destroy=function(){return d.removeInstance(b),Sb.add(Ab).add(zb).add(Eb).add(Ub).add(Vb).add(Wb).add(Xb).add(Yb).add(Zb).off("."+r),u.off("keydown",kb),Wb.add(Xb).add(Yb).add(Zb).removeClass(rb.disabledClass),Hb&&null!=Jb.activeItem&&Hb.eq(Jb.activeItem).removeClass(rb.activeClass),Eb.empty(),tb||(ub.off("."+r),Kb.restore(),Lb.restore(),Mb.restore(),Nb.restore(),a.removeData(b,r)),Ib.length=Gb.length=0,_b={},sb.initialized=0,sb},sb.init=function(){if(!sb.initialized){if(d.getInstance(b))throw new Error("There is already a Sly instance on this element");d.storeInstance(b,sb),sb.on(q);var a=["overflow","position"],c=["position","webkitTransform","msTransform","transform","left","top","width","height"];Kb.save.apply(Kb,a),Mb.save.apply(Mb,a),Lb.save.apply(Lb,c),Nb.save.apply(Nb,c);var e=Ab;return tb||(e=e.add(vb),ub.css("overflow","hidden"),m||"static"!==ub.css("position")||ub.css("position","relative")),m?n&&e.css(m,n):("static"===zb.css("position")&&zb.css("position","relative"),e.css({position:"absolute"})),rb.forward&&Ub.on(A,gb),rb.backward&&Vb.on(A,gb),rb.prev&&Wb.on(z,gb),rb.next&&Xb.on(z,gb),rb.prevPage&&Yb.on(z,gb),rb.nextPage&&Zb.on(z,gb),Sb.on(y,ib),zb[0]&&zb.on(z,jb),Rb&&rb.activateOn&&ub.on(rb.activateOn+"."+r,"*",lb),Eb[0]&&rb.activatePageOn&&Eb.on(rb.activatePageOn+"."+r,"*",mb),Tb.on(v,{source:"slidee"},bb),Ab&&Ab.on(v,{source:"handle"},bb),u.on("keydown",kb),tb||(ub.on("mouseenter."+r+" mouseleave."+r,nb),ub.on("scroll."+r,h)),sb.initialized=1,K(!0),rb.cycleBy&&!tb&&sb[rb.startPaused?"pause":"resume"](),sb}}}function e(a){return null==a?String(a):"object"==typeof a||"function"==typeof a?Object.prototype.toString.call(a).match(/\s([a-z]+)/i)[1].toLowerCase()||"object":typeof a}function f(a,b){a.preventDefault(),b&&a.stopPropagation()}function g(b){f(b,1),a(this).off(b.type,g)}function h(){this.scrollLeft=0,this.scrollTop=0}function i(a){return!isNaN(parseFloat(a))&&isFinite(a)}function j(a,b){return 0|G(String(a.css(b)).replace(/[^\-0-9.]/g,""))}function k(a,b,c){return b>a?b:a>c?c:a}function l(a){var b={};return b.style={},b.save=function(){if(a&&a.nodeType){for(var c=0;c<arguments.length;c++)b.style[arguments[c]]=a.style[arguments[c]];return b}},b.restore=function(){if(a&&a.nodeType){for(var c in b.style)b.style.hasOwnProperty(c)&&(a.style[c]=b.style[c]);return b}},b}var m,n,o,p="sly",q="Sly",r=p,s=b.cancelAnimationFrame||b.cancelRequestAnimationFrame,t=b.requestAnimationFrame,u=a(document),v="touchstart."+r+" mousedown."+r,w="mousemove."+r+" mouseup."+r,x="touchmove."+r+" touchend."+r,y=(document.implementation.hasFeature("Event.wheel","3.0")?"wheel.":"mousewheel.")+r,z="click."+r,A="mousedown."+r,B=["INPUT","SELECT","BUTTON","TEXTAREA"],C=[],D=Math.abs,E=Math.sqrt,F=Math.pow,G=Math.round,H=Math.max,I=Math.min,J=0;u.on(y,function(a){var b=a.originalEvent[r],c=+new Date;(!b||b.options.scrollHijack<c-J)&&(J=c)}),d.getInstance=function(b){return a.data(b,r)},d.storeInstance=function(b,c){return a.data(b,r,c)},d.removeInstance=function(b){return a.removeData(b,r)},function(a){function b(a){var b=(new Date).getTime(),d=Math.max(0,16-(b-c)),e=setTimeout(a,d);return c=b,e}t=a.requestAnimationFrame||a.webkitRequestAnimationFrame||b;var c=(new Date).getTime(),d=a.cancelAnimationFrame||a.webkitCancelAnimationFrame||a.clearTimeout;s=function(b){d.call(a,b)}}(window),function(){function a(a){for(var d=0,e=b.length;e>d;d++){var f=b[d]?b[d]+a.charAt(0).toUpperCase()+a.slice(1):a;if(null!=c.style[f])return f}}var b=["","Webkit","Moz","ms","O"],c=document.createElement("div");m=a("transform"),n=a("perspective")?"translateZ(0) ":""}(),b[q]=d,a.fn[p]=function(b,c){var f,g;return a.isPlainObject(b)||(("string"===e(b)||b===!1)&&(f=b===!1?"destroy":b,g=Array.prototype.slice.call(arguments,1)),b={}),this.each(function(a,e){var h=d.getInstance(e);h||f?h&&f&&h[f]&&h[f].apply(h,g):h=new d(e,b,c).init()})},d.defaults={slidee:null,horizontal:!1,itemNav:null,itemSelector:null,smart:!1,activateOn:null,activateMiddle:!1,scrollSource:null,scrollBy:0,scrollHijack:300,scrollTrap:!1,dragSource:null,mouseDragging:!1,touchDragging:!1,releaseSwing:!1,swingSpeed:.2,elasticBounds:!1,dragThreshold:3,interactive:null,scrollBar:null,dragHandle:!1,dynamicHandle:!1,minHandleSize:50,clickBar:!1,syncSpeed:.5,pagesBar:null,activatePageOn:null,pageBuilder:function(a){return"<li>"+(a+1)+"</li>"},forward:null,backward:null,prev:null,next:null,prevPage:null,nextPage:null,cycleBy:null,cycleInterval:5e3,pauseOnHover:!1,startPaused:!1,moveBy:300,speed:0,easing:"swing",startAt:null,keyboardNavBy:null,draggedClass:"dragged",activeClass:"active",disabledClass:"disabled"}}(jQuery,window);



// https://github.com/customd/jquery-visible
!function(t){var i=t(window);t.fn.visible=function(t,e,o){if(!(this.length<1)){var r=this.length>1?this.eq(0):this,n=r.get(0),f=i.width(),h=i.height(),o=o?o:"both",l=e===!0?n.offsetWidth*n.offsetHeight:!0;if("function"==typeof n.getBoundingClientRect){var g=n.getBoundingClientRect(),u=g.top>=0&&g.top<h,s=g.bottom>0&&g.bottom<=h,c=g.left>=0&&g.left<f,a=g.right>0&&g.right<=f,v=t?u||s:u&&s,b=t?c||a:c&&a;if("both"===o)return l&&v&&b;if("vertical"===o)return l&&v;if("horizontal"===o)return l&&b}else{var d=i.scrollTop(),p=d+h,w=i.scrollLeft(),m=w+f,y=r.offset(),z=y.top,B=z+r.height(),C=y.left,R=C+r.width(),j=t===!0?B:z,q=t===!0?z:B,H=t===!0?R:C,L=t===!0?C:R;if("both"===o)return!!l&&p>=q&&j>=d&&m>=L&&H>=w;if("vertical"===o)return!!l&&p>=q&&j>=d;if("horizontal"===o)return!!l&&m>=L&&H>=w}}}}(jQuery);
