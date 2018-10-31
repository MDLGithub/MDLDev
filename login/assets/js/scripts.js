$(document).ready(function () {
    
    /** Salutation message for Physicians */
    if($('#salutation').length != 0){
        var userTimeZone = moment.tz.guess();
        var thisUserId = $('#salutation').attr('data-user-id');
        var thisUserRole = $('#salutation').attr('data-role');    
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        $.ajax( ajaxUrl , {
            type: 'POST',
            data: {
               get_salutation_message: '1',
               userId: thisUserId,
               userRole: thisUserRole,
               userTimeZone: userTimeZone
            },
            success: function(response) {
                var result = JSON.parse(response);
                if(result['salutation']){
                    $('#salutation').html(result['salutation']);
                }             
            },
            error: function() {
                console.log('0');
            }
        });   
    }
    /** Salutation message for Physicians END */
    
    var opt = {
	beforeShow: function() {
	    setTimeout(function(){
		$('.ui-datepicker').css('z-index', 1001);
	    }, 0);
	},
	showOn:'focus',
	dateFormat: 'm/d/yy'
    };

    $('.datepicker_from').not('.hasDatePicker').datepicker(opt);
    $('.datepicker_to').not('.hasDatePicker').datepicker(opt);
    $('.phone_us').mask('(000) 000-0000');
   // $('.h-filters .date').mask("00/00/0000", {placeholder: "__/__/____"});
    $('.h-filters .stat_mdl_number').mask("0000000");
    
    $('#file.accountLogoInput').inputFileText( {
        text: 'Account Logo',  
        buttonCLass: 'cooseFileBtn',
        textClass: 'chooseFileTxt' 
    });
    $('#file.userLogoInput').inputFileText( {
        text: 'Upload User\'s Photo',  
        buttonCLass: 'cooseFileBtn',
        textClass: 'chooseFileTxt' 
    });
    
    /**
     * Dashboard Calendar Date Dropdown filter
     * used on dashboard2.php dashboard calendar
     */
    $(".stats_dropdown_arrow").click(function(){
        //Changes the width of the filters
        $(".stats_dropdown").toggleClass("dropdown_hide");
        $(".chart_header .stats_date").toggleClass("hide");
        $(".stats_dropdown_arrow").toggleClass("dropdown_arrow_show");
        $(".chart_header .button").toggleClass("hide"); 
    }); 

    /**
     * Dashboard Calendar Sales Rep Dropdown filter
     * used on dashboard2.php dashboard calendar
     */
    /*$(".info_block_arrow").bind('click tap', function(){
        $(".salesrep_dropdown").toggleClass("dropdown_hide");
        $(".info_block h1").toggleClass("hide");
        $(".info_block_arrow").toggleClass("info_block_arrow_show");
    }); */ 
    
    $('.toggleRoles').on('click', function(){
        if($('.edit-status-form .rolesBlock').hasClass('hidden')){
            $('.edit-status-form .rolesBlock').removeClass('hidden');
            $('.toggleThisRoles').removeClass('fa-eye-slash').addClass('fa-eye');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');            
        } else {
            $('.edit-status-form .rolesBlock').addClass('hidden');
            $('.toggleThisRoles').removeClass('fa-eye').addClass('fa-eye-slash');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        }       
    });
    $('.toggleThisRoles').on('click', function(){
        var rolesBlock = $(this).parent().parent().find( ".rolesBlock" );
        if(rolesBlock.hasClass('hidden')){
            rolesBlock.removeClass('hidden');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            rolesBlock.addClass('hidden');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        }       
    });
    //Accounts page show account Details on click
    $(document).on('click', 'td.clickable .details', function(e){
        e.preventDefault();
        $('td.clickable .moreInfo').hide();
        $(this).parent().find('.moreInfo').show();
    });
    $(document).on('click','td.clickable .moreInfo', function(){       
        $('.moreInfo').hide();
    });
    
    $('#dataTable').delegate('.locked-user','click', function(){
        var email = $(this).attr('data-user-email');        
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        $.ajax( ajaxUrl , {
            type: 'POST',
            data: {
               get_loced_user_data: '1',
               email: email
            },
            success: function(response) {
                $('#login-attempt-log-box').show();
                $('#locked-user-email').html(email);
                $('#unlock-user').attr('data-unlock-user-email', email);
                var result = JSON.parse(response);
                console.log(result);
                if(result['content']){
                    $('#login-attempt-log-content').html(result['content']);
                }                
            },
            error: function() {
                console.log('0');
            }
        });
    });   
    
    $('#unlock-user').on('click', function(e){
        var email = $(this).attr('data-unlock-user-email');
        var message = "Are you sure you want to unlock this user?";        
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        var redirectUrl = baseUrl+'/user-management.php';
        var conf = confirm(message);
        if(conf){            
            $.ajax( ajaxUrl , {
                type: 'POST',
                data: {
                   unlock_this_user: '1',
                   email: email
                },
                success: function(response) {                   
                    var result = JSON.parse(response);
                    console.log(result); 
                    window.location.replace(redirectUrl);
                },
                error: function() {
                    console.log('0');
                }
            });
        }
    });
    
    $('#login-attempt-log-box .close').on('click', function(e){
        e.preventDefault();
        $('#login-attempt-log-box ').hide();
    });
    
    //formatting number to currency
    Number.prototype.formatMoney = function(c, d, t){
        var n = this, 
        c = isNaN(c = Math.abs(c)) ? 2 : c, 
        d = d == undefined ? "," : d, 
        t = t == undefined ? "." : t, 
        s = n < 0 ? "-" : "", 
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
        j = (j = i.length) > 3 ? j % 3 : 0;
       return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    };    
    $( ".money2" ).each(function( index ) {
        var thisNum =  parseFloat($( this ).text());
        $( this ).text((thisNum).formatMoney(2));
    });
    
    //patient info page status 
    $("#mdl-status").on('change', function() {
        var optionTxt = $(this).find("option:selected").text();
        if( optionTxt == 'Declined' ){
            $('#status-declined-reasons').removeClass('hidden');
        } else {
            $('#status-declined-reasons').addClass('hidden');
            $('#status-declined-reasons option').prop('selected',false);
        }
    });
    
    //show calendar on click to input with .datepicker class 
    var opt = { showOn:'focus', dateFormat: 'm/d/yy' };
    $('body').on('click','.datepicker', function() {
       // $(this).datepicker('destroy').datepicker({showOn:'focus',dateFormat: 'm/d/yy'}).focus();
       $(this).not('.hasDatePicker').datepicker(opt).focus();
    });
    
    //number only fields
    $('body').delegate( ".numberonly", "input", function() {       
        var val = $(this).val(); 
        $(this).val(val.replace(/\D/g,''));
    });  
    
    /**
     * MDL# allow only input 7 digits
     * used on patient info page
     */
    $('body').delegate( ".mdlnumber", "input", function() {       
        var thisVal = $(this).val(); 
        $('#mdlNumber').val(thisVal);
        if(thisVal.length>7){
            $(this).val(thisVal.slice(0, 7));
            $('#mdlNumber').val(thisVal.slice(0, 7));
        }else{
            if(thisVal.length==7 || thisVal.length==0){
                $(this).removeClass('error error-border');
                $('#message').html("");
            } else {
                $(this).addClass('error error-border');  
                $('#message').html("MDL# must contain 7 digits only.");
            }
        }
    });
    /**
     * MDL# validation
     */
    $( "form#mdlInfoForm" ).submit(function( event ) {
        if ($(".mdlnumber").hasClass('error')) {
            if($('#mark-as-test').prop('checked', true)){
                $(".mdlnumber").removeClass('error');
            }  else {
                event.preventDefault();
                $('#message').html("MDL# must contain 7 digits only.");
            }
        } else {
            return;
        }
    });
    /*
     * validate forms, don't allow submit if there is an error
     */
    $( "form" ).submit(function( event ) {
        if ($(".f2").hasClass('error')) {
            event.preventDefault();
            $('#message').html("Please fix all errors.");
        } else {
            return;
        }
    });
    
    $('.dmdlRefresh').on('click', function(){
        $('.preloader').removeClass('hidden');
    });
        
    /**
     * Home page toggle for search sidebar
     */
    $("#action_palette_toggle").click(function(){
        //Changes the width of the filters
        $("#action_palette").toggleClass("action_palette_width");
        //Changes the width of the table
        $("main").toggleClass("wider-main");
        $("#dataTableHome thead").toggleClass("wider-bar");
        $("#dataTableHome tbody").toggleClass("wider-tbody");
        $(".bottom").toggleClass("wider-bottom");

        //Moves the toggle left and right
        $(this).toggleClass("toggle_move");

        $("#palette_top .submit").toggleClass("smaller_button")

        $("#palette_top").toggleClass("shorter_palette_top");
        $(".formContent").toggleClass("form-remove");

        //Flips the toggle arrow
        if($("#action_palette_toggle i").hasClass("fa-angle-left")){
            $("#action_palette_toggle i").removeClass('fa-angle-left').addClass('fa-angle-right');
        }else{
            $("#action_palette_toggle i").removeClass('fa-angle-right').addClass('fa-angle-left');
        }            
    });    


    /**
     *  Homepage Print All toggle 
     *  check or uncheck all checkboxes on click to #selectAllPrintOptions
     */
    $("#selectAllPrintOptions").change(function() {
        if(this.checked) {
            $('.printSelectBlock .print1').prop('checked', true);
            //$('#switchLabel').text('Remove All').css('margin-left', '-40px');
            $('#switchLabel').text('Remove All');
        }else{
            $('.printSelectBlock .print1').prop('checked', false);
            //$('#switchLabel').text('Select All').css('margin-left', '-32px');;
            $('#switchLabel').text('Select All');
        }
    });

    $(".selectAllCheckboxes").change(function() {
        if(this.checked) {
            $('.checkboxSelect').prop('checked', true);
            $('.switchLabel').text('Remove All');
        }else{
            $('.checkboxSelect').prop('checked', false);
            $('.switchLabel').text('Select All');
        }
    });  
    /**
     * Check all checkboxes on click to select All checkbox
     * by given data-id
     */
    $('.checkAll').change(function() {
        var dataID = $(this).attr('data-id');
        if(this.checked) {
            $('#'+dataID+' input[type="checkbox"]').prop('checked', true);
        }else{
            $('#'+dataID+' input[type="checkbox"]').prop('checked', false);
        }
    });
    /**
     * Close modal box
     */
    $('.closeModal').on('click', function(e){
        e.preventDefault();
        $('.modalBlock').removeClass('show').addClass('hidden');
    });
    $('.openUserInfoModal').on('click', function(e){
        e.preventDefault();
        $('.modalBlock').removeClass('hidden').addClass('show');
    });
    
    /**
     *  Salesrep page open color Box 
     */
    $('.color-block').delegate( ".openColorBox", "click", function() {
        if($(this).parent().find('.colorBox').hasClass('closed')){
            $(this).parent().find('.colorBox').removeClass('closed').show();
        }else{
            $(this).parent().find('.colorBox').addClass('closed').hide();
        }        
    });
    $('.color-block .colorBox label').on( "click", function() {
        $(this).parent().parent().find('label').removeClass('checked');
        $(this).addClass('checked');
        var thisColor = $(this).attr('data-color');
        $(this).parent().parent().parent().parent().find(".selected-color-box span").removeClass('active');
        $(this).parent().parent().parent().parent().find(".selected-color-box span").css("background-color", thisColor).addClass('active');
        $(this).parent().parent().addClass('closed').hide();
    });
    
    /**
     *  When Location Selected Web
     *  disable Pin checkbox and set Password to checked
     */
    $('#url-config-settings #location').on('change', function() {
        var location = this.value;
        if(location=='W'){
            $('#url-config-settings #pin').prop('checked', false);
            $('#url-config-settings #pass').prop('checked', true);
            $("#url-config-settings #pin").prop("disabled", true);
        } else {
            $("#url-config-settings #pin").prop("disabled", false);
        }
        if(location=='F'){
            $('#url-config-settings #pass').prop('checked', false);
            $('#url-config-settings #pin').prop('checked', true);
            $("#url-config-settings #pass").prop("disabled", true);
        } else {
            $("#url-config-settings #pass").prop("disabled", false);
        }
        var locaArr = ['D', 'DE', 'O', 'L', 'PM'];
        for (var i=0; i<locaArr.length; i++ ){
            if(locaArr[i]==location){
                //#noemail must be active
                $('#url-config-settings #pin, #url-config-settings #pass').prop('checked', false);
                $('#url-config-settings #noemail').prop('checked', true);
            } 
        }
    });
    /**
     * account dropdown on change function
     * update account info when account selected
     */
    $('#url-config-settings #account').on('change', function() {
        var accountId =  this.value;
        //console.log(accountId);
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        $.ajax( ajaxUrl , {
            type: 'POST',
            data: {
               get_account_info: '1',
               account_id: accountId
            },
            success: function(response) {
                var result = JSON.parse(response);
                //console.log(result);
                var accountData = result['accountInfo'];
                var providers = result['providers']
                updateAccountInfo(accountData['0'], providers);
            },
            error: function() {
                console.log('0');
            }
        });
    });
    
    /**
     * account dropdown on change function
     * update account info when account selected
     */
    $('#status-dropdowns-box').delegate( ".status-dropdown", "change", function() { 
        var val =  this.value;
        //console.log(accountId);
        $(this).parent().parent().nextAll().remove();
        getGetSubDropdown(val);
    }); 
    
    function getGetSubDropdown(val){        
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        if(val && val!="0"){
            $.ajax( ajaxUrl , {
                type: 'POST',
                data: {
                   status_dropdown: '1',
                   parent_id: val,
                },
                success: function(response) {
                    var result = JSON.parse(response);                    
                    if(result.content !=""){
                        var content = result.content
                        $('#status-dropdowns-box').append(content);
                    }
                    var newVal = result['statusID'];
                    getGetSubDropdown(newVal)                     
                },
                error: function() {
                    console.log('0');
                }
            });
        }
    }
    
    /**
     * Homepage Filter 
     * Account-Provider-Salesrep dropdown change
     */        
    $("#filter_form #account, #filter_form #provider, #filter_form #salesrep").on('change', function() {
        var thisName = this.name;
        var accountVal  = $('#filter_form #account').val();        
        var providerVal = $('#filter_form #provider').val();
        var salesRepVal = $('#filter_form #salesrep').val();
        
         
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        $.ajax( ajaxUrl , {
            type: 'POST',
            data: {
                get_account_correlations: thisName, 
                id: this.value,
                account: accountVal,
                provider: providerVal,
                salesRep: salesRepVal
            },
            success: function(response) {
                var result = JSON.parse(response);  
                console.log(result);
                
                if(result.name == 'account' ){ 
                    if( (providerVal=="" && salesRepVal=="") || accountVal == "" ) {
                        updateCorrelations(result.accounts_html, result.provider_html, result.salesrep_html);
                    }
                }
                
                if(result.name == 'provider'){                    
                    //updateCorrelations("",  result.provider_html, "");
                    if(accountVal=="" && salesRepVal==""){
                        updateCorrelations(result.accounts_html,  result.provider_html, "");
                        if(salesRepVal == ""){
                            updateCorrelations("", "", result.salesrep_html);
                        }
                    }
                    
                }
                if(result.name == 'salesrep'){
                    //updateCorrelations("",  "", result.salesrep_html); 
                    if(accountVal == "" && providerVal == ""){
                        updateCorrelations(result.accounts_html,  "",  result.salesrep_html);
                        if(providerVal == ""){
                            updateCorrelations("",  result.provider_html,  result.salesrep_html);
                        }
                    }
                    
                }  
                
                if(accountVal==""){ $("#filter_form #account").addClass('no-selection'); }else{$("#filter_form #account").removeClass('no-selection');}
                if(providerVal==""){ $("#filter_form #provider").addClass('no-selection'); }else{$("#filter_form #provider").removeClass('no-selection');}
                if(salesRepVal==""){ $("#filter_form #salesrep").addClass('no-selection'); }else{$("#filter_form #salesrep").removeClass('no-selection');}

            },
            error: function() {
                console.log('0');
            }
        });        
    });
    
    function updateCorrelations(accounts_html, provider_html, salesrep_html){
        if(accounts_html!=""){
            $("#filter_form #account").empty();
            $("#filter_form #account").removeClass('no-selection');
            $("#filter_form #account").parent().parent().removeClass('show-label valid');
            $("#filter_form #account").append(accounts_html);
        }
        if(provider_html!=""){
            $("#filter_form #provider").empty();
            $("#filter_form #provider").removeClass('no-selection');
            $("#filter_form #provider").parent().parent().removeClass('show-label valid');
            $("#filter_form #provider").append(provider_html);
        }
        if(salesrep_html!=""){
            $("#filter_form #salesrep").empty();        
            $("#filter_form #salesrep").removeClass('no-selection');
            $("#filter_form #salesrep").parent().parent().removeClass('show-label valid');
            $("#filter_form #salesrep").append(salesrep_html);
        }
       
    }
    
    $("#filter_form #account--, #filter_form #provider--, #filter_form #salesrep--").on('change', function() {
        var thisName = this.name;
        var accountVal  = $('#filter_form #account').val();        
        var providerVal = $('#filter_form #provider').val();
        var salesRepVal = $('#filter_form #salesrep').val();
        
        accountVal = (accountVal)?accountVal:'none';
        providerVal = (providerVal)?providerVal:'none';
        salesRepVal = (salesRepVal)?salesRepVal:'none';
      
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        $.ajax( ajaxUrl , {
            type: 'POST',
            data: {
                get_account_correlations: thisName, 
                id: this.value,
                account: accountVal,
                provider: providerVal,
                salesRep: salesRepVal
            },
            success: function(response) {
                var result = JSON.parse(response);  
                console.log(result);
                
                if(result.name == 'account' ){ // || salesRepVal == 'none' || providerVal=='none'
                    $("#provider, #salesrep").empty();
                    $("#provider").append(result.provider_html);
                    $("#salesrep").append(result.salesrep_html);
                    if(accountVal=='none'){
                        $("#provider, #salesrep").empty();
                        $("#provider").append(result.provider_html);
                        $("#salesrep").append(result.salesrep_html);
                        $('#filter_form #provider, #filter_form #salesrep').parent().parent().removeClass('show-label valid'); 
                        $('#filter_form #provider, #filter_form #salesrep').addClass('no-selection').val('').attr("selected", "selected");                        
                    } 
                }
                
                if(result.name == 'provider'){
                    if(providerVal == 'none'){
                        $('#filter_form #account').parent().parent().removeClass('show-label valid'); 
                        $('#filter_form #account').addClass('no-selection').val('').attr("selected", "selected");                        
                        $("#provider, #salesrep").empty();
                        $("#provider").append(result.provider_html);
                        $("#salesrep").append(result.salesrep_html);
                    } else {
                        $('#filter_form #account').parent().parent().addClass('show-label valid');
                        $('#filter_form #account').val(result.accountID).attr("selected", "selected");
                    }                    
                    $('#filter_form #provider').val(result.providerID).attr("selected", "selected");
                    if(!salesRepVal || salesRepVal==''){
                        $("#provider, #salesrep").empty();
                        $("#provider").append(result.provider_html);
                        $("#salesrep").append(result.salesrep_html);
                    }
                }
                if(result.name == 'salesrep'){
                    if(accountVal == 'none'){
                        $('#filter_form #account').parent().parent().addClass('show-label valid');
                        $('#filter_form #account').val(result.accountID).attr("selected", "selected");
                    }                    
                    $('#filter_form #salesrep').val(result.salesRepID).attr("selected", "selected");
                    if(!providerVal || providerVal==''){
                        $("#provider, #salesrep").empty();
                        $("#provider").append(result.provider_html);
                        $("#salesrep").append(result.salesrep_html);
                    }
                }  
                
            },
            error: function() {
                console.log('0');
            }
        });        
    });
      
    
    /**
     * Update Account info and providers 
     * @param {type} accountData
     * @param {type} providers
     * @returns 
     */
    function updateAccountInfo(accountData, providers){
        var officeLogo = accountData['logo'];
        if(officeLogo){
            setImage('#officeLogo', 'width="100"', officeLogo);
        } else {
            setImage('#officeLogo', 'width="100"', "default.png", "assets/images/");
        }
         var account = accountData['account'];
        var name = accountData['name'];
        var address = accountData['address'];
        var state = accountData['state'];
        var city = accountData['city'];
        var zip = accountData['zip'];
        $('#officeAddress, #officeAddressLabel').html("");
        
        //$('#officeAddressLabel').prepend('Address');
        //$('#officeAddress').prepend(address+"<br/>"+city+", "+state+" "+zip);
            
        var addressInfo = "";
        if(address || city){
            $('#officeAddressLabel').prepend(account+" "+capitalizeWord(name,true)); 
            addressInfo += "<div>";
            
            if(address){
                addressInfo += address+"<br/>";
            }
            if(city){
               addressInfo += city; 
               if(state || zip){
                   addressInfo += ", ";
               }
            }
            if(state){
                addressInfo += state;
            }
            if(zip){
                addressInfo += " " + zip;
            }
            addressInfo += "</div>";
        }
        if(accountData['phone_number']){
            addressInfo += "<div><i class='fas fa-phone'></i> <a class='phone_us' href='tel:"+accountData['phone_number']+"'>"+accountData['phone_number']+"</a></div>";
        }
        if(accountData['fax']){
            addressInfo += "<div><i class='fas fa-fax'></i> <a class='phone_us' href='tel:"+accountData['fax']+"'>"+accountData['fax']+"</a></div>";
        }
        if(accountData['website']){
            addressInfo += "<div><i class='fas fa-globe'></i> <a target='_blank' href='"+accountData['website']+"'>"+accountData['website']+"</a></div>";
        }
        $('#officeAddress').prepend(addressInfo);  
        
        $('#physiciansList, #physiciansListLabel').html("");
        for ( var i=0; i<providers.length; i++){
            $('#physiciansList').append('<p>'+providers[i]['first_name']+" "+providers[i]['last_name']+", "+providers[i]['title']+'</p>');
        }
        $('#physiciansListLabel').append('<p class="providersTitle">Health Care Providers</p>');

    }
    
    /**
     *  Generate Url button on click foncion
     */
    $('.url_config').on('click', function (event) {      
        var id = $(this).attr('id'); 
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        $.ajax( ajaxUrl, {
            type: 'POST',
            data: {
               url_config: '1',
               id: id
            },
            success: function(response) {
                var result = JSON.parse(response);
                var configData = result.urlConfigs[0];
                //console.log(configData);
                updateSelectSettings(configData);
            },
            error: function() {
                console.log('0');
            }
        });
    });
    
    /**
     * update Selected Settings function
     * @param {type} configData
     * @returns 
     */
    function updateSelectSettings(configData){
        //console.log(configData);
        var configDataId = configData['id'];
        var account = configData['account'];
        var location = configData['code'];
        var genevedaLogo = configData['geneveda'];
        var accountName = configData['name'];
        var pin = configData['pin'];
        var deviceId = configData['device_id'];
        var officeLogo = configData['logo'];
        
        if(genevedaLogo=='1'){
            $('#url-config-settings #geneveda').prop('checked', true);
        } else {
            $('#url-config-settings #mdl').prop('checked', true);
        }
        if(pin=='1'){
            $('#url-config-settings #pin').prop('checked', true);
        } else if (pin=='2') {
            $('#url-config-settings #noemail').prop('checked', true);
        } else {
            $('#url-config-settings #pass').prop('checked', true);
        }
        setSelectedAttrByVal('#url-config-settings #account option', account);
        setSelectedAttrByVal('#url-config-settings #location option', location);
        setSelectedAttrByVal('#url-config-settings #deviceId option', deviceId);
        if(officeLogo){
            setImage('#officeLogo', 'width="100"', officeLogo);
        } else {
            setImage('#officeLogo', 'width="100"', "default.png", "assets/images/");
        }
        if(account !== null){
            var account = configData['account'];
            var name = configData['name'];
            var address = configData['address'];
            var state = configData['state'];
            var city = configData['city'];
            var zip = configData['zip'];
            
            $('#officeAddress, #officeAddressLabel').html("");  
            var addressInfo = "";
            if(address || city){
                $('#officeAddressLabel').prepend(account+" "+capitalizeWord(name,true)); 
                addressInfo += "<div>";
                if(address){
                    addressInfo += address+"<br/>";
                }
                if(city){
                   addressInfo += city; 
                   if(state || zip){
                       addressInfo += ", ";
                   }
                }
                if(state){
                    addressInfo += state;
                }
                if(zip){
                    addressInfo += " " + zip;
                }
                addressInfo += "</div>";
            }
            
            if(configData['phone_number']){
                addressInfo += "<div><i class='fas fa-phone'></i> <a class='phone_us' href='tel:"+configData['phone_number']+"'>"+configData['phone_number']+"</a></div>";
            }
            if(configData['fax']){
                addressInfo += "<div><i class='fas fa-fax'></i> <a class='phone_us' href='tel:"+configData['fax']+"'>"+configData['fax']+"</a></div>";
            }
            if(configData['website']){
                addressInfo += "<div><i class='fas fa-globe'></i>  <a target='_blank' href='"+configData['website']+"'>"+configData['website']+"</a></div>";
            }
            $('#officeAddress').prepend(addressInfo);        
            $('#physiciansList, #physiciansListLabel').html("");  
            var ajaxUrl = baseUrl+'/ajaxHandler.php';
            $.ajax( ajaxUrl, {
                type: 'POST',
                data: {
                   get_providers: '1',
                   accountId: account
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    var providers = result['providers'];
                    for ( var i=0; i<providers.length; i++){
                        $('#physiciansList').append('<p>'+providers[i]['first_name']+" "+providers[i]['first_name']+", "+providers[i]['title']+'</p>');
                    }
                    $('#physiciansListLabel').append('<p class="providersTitle">Health Care Providers</p>');

                },
                error: function() {
                    console.log('0');
                }
             });
            } else{
               $('#officeLogo').html("");
               $('#officeAddress, #officeAddressLabel').html("");
               $('#physiciansList, #physiciansListLabel').html(""); 
            }
        
    }
    
    /**
     * Set dropdown option selected
     * @param {type} selector
     * @param {type} matchValue
     * @returns 
     */
    function setSelectedAttrByVal(selector, matchValue){
        $(selector).removeAttr('selected');  
        $(selector).each(function(index,value) {
            if($(this).val() == matchValue){
                $(this).attr('selected','selected');
            } else {
                $(this).removeAttr('selected');
            }
        });
    }
    
    /**
     * Set image to given selecoto container
     * @param {type} selectorDiv
     * @param {type} attr
     * @param {type} imageName
     * @returns 
     */
    function setImage(selectorDiv, attr, imageName, uploadUrl=""){
        if(uploadUrl==""){
            uploadUrl = baseUrl+"/../images/practice/";
        }
        $(selectorDiv).html("");
        $(selectorDiv).prepend('<img '+attr+' src="'+uploadUrl+imageName+'" />')
    }
    
    
    // Setup - add a text input to each footer cell
    
    
    
    //$('#dataTable thead tr').clone(true).appendTo( '#dataTable thead' );
    $('#dataTable thead tr:eq(0) th, #tableHeaderFixed  thead tr:eq(0) th').each( function (i) {
        if( !$(this).hasClass('noFilter') ){
            var title = $(this).text();
            $(this).html( '<input class="dataTableFilterInput" type="text" placeholder="'+title+'" />' );

            $( 'input', this ).on( 'keyup change', function () {
                if ( table.column(i).search() !== this.value ) {
                    table
                        .column(i)
                        .search( this.value )
                        .draw();
                }
            } );
        } 
    } );
    
    //don't sort tabel column on click to this input
    $('.dataTableFilterInput').on('click', function (e) {
         e.stopPropagation();
    });
    
    $('#accounts #selectAccount').on('change', function() {
        var accountId =  this.value;
        var accountGuid = $(this).find(':selected').attr('data-guid');
        var accountUrl = baseUrl+"/accounts.php?account_id="+accountGuid;
        window.location = accountUrl;
    });
 
 
    $('.patientInfo .patientAccount').on('change', function() {
        var accountId =  this.value;
        console.log(accountId);
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        $.ajax( ajaxUrl, {
            type: 'POST',
            data: {
               get_patient_info_providers: '1',
               account_id: accountId
            },
            success: function(response) {
                var result = JSON.parse(response);
                console.log(result);
                if(result['options']){
                    $('select#pInfoAccountProviders').html(result['options']);
                }
            },
            error: function() {
                console.log('0');
            }
        });
    });
    
    function updateAccountFullInfo(accountData, providers){
        
        var dataEditUrl = $("#edit-selected-account").attr("data-edit-url");
        $("#edit-selected-account").attr("href", dataEditUrl+accountData['Guid_account']);
        
        var officeLogo = accountData['logo'];
        setImage('#officeLogo', 'class="salesrepLogo"', officeLogo);
        var address = accountData['address'];
        var state = accountData['state'];
        var city = accountData['city'];
        var zip = accountData['zip'];
        var site = accountData['website'];
        var phone = accountData['phone_number'];       
 
        $('#officeAddress, #salesrepInfo1').html("");
        var addressInfo = address+"<br/>"+city+", "+state+" "+zip;
        addressInfo += "<div><a class='phone_us' href='tel:"+phone+"'>"+phone+"</a></div>";
        addressInfo += "<div><a href='"+site+"'>"+site+"</a></div>";
        $('#officeAddress').prepend(addressInfo);
        
        var salesrepAddrInfo = accountData['salesrepRegion']+"<br/>"+accountData['salesrepAddress']+"<br/>"+accountData['salesrepCity']+", "+accountData['salesrepState']+" "+accountData['salesrepZip'];
        salesrepAddrInfo += "<div><a href='mailto:"+accountData['salesrepEmail']+"'>"+accountData['salesrepEmail']+"</a></div>";
        salesrepAddrInfo += "<div><a class='phone_us'  href='"+accountData['salesrepPhone']+"'>"+accountData['salesrepPhone']+"</a></div>";
        $('#salesrepInfo1').prepend(salesrepAddrInfo);
        //salesrepPhoto
        
        $('#salesrepInfo2 .pic, #salesrepInfo2 .name').html("");
        setImage('#salesrepInfo2 .pic', 'class="salesrepProfilePic"', accountData['salesrepPhoto']);
        $('#salesrepInfo2 .name').prepend(accountData['salesrepFName']+" "+accountData['salesrepLName']);
        
        var providersInfo = "";
        for(var i=0; i<providers.length; i++ ){           
            providersInfo += "<tr>";
            providersInfo += "<td>"+providers[i]['Guid_provider']+"</td>";
            providersInfo += "<td>"+providers[i]['title']+"</td>";
            providersInfo += "<td>"+providers[i]['first_name']+"</td>";
            providersInfo += "<td>"+providers[i]['last_name']+"</td>";
            providersInfo += "<td><img width=30' src='uploads/"+providers[i]['photo_filename']+"'></td>";
            providersInfo += "<td class='text-center'><a class='edit-provider' data-provider-guid='"+providers[i]['Guid_provider']+"'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></a>";
            providersInfo += " <a href=''><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></a></td>";
            providersInfo += "</tr>";
        }
        $('.providersTable tbody').html(" ");
        $('.providersTable tbody').prepend(providersInfo);
    } 
       
    $("#add-account-provider").on('click', function (){
        $("#add-account-provider-box").removeClass("hide");
        $(".providersTitle").html("Add Provider");
        //remove input values
        $("#add-account-provider-box form input").val("");
        $("#add-account-provider-box form #profile-pic").html("");        
    });
    
    
    //specimen checkbox actions
    $('#specimen-collected-cbox, #specimen-notcollected-cbox').on('click', function() {
        var specimen = this.value;        
        if(specimen=='No'){
            $('#specimen').addClass('show-modal');
            $('.specimenCollected.not').show();
            $('.specimenCollected.yes').hide();
        } 
        if(specimen=='Yes'){
            $('#specimen').addClass('show-modal');
            $('.specimenCollected.yes').show();
            $('.specimenCollected.not').hide();
        }
    });
    
    $('.cancel-specimen-collected').on('click', function(){
         $('#specimen').removeClass('show-modal');
         $('.specimenCollected ').hide();
         $('#specimen-collected-cbox').prop('checked', false);
         $('#specimen-notcollected-cbox').prop('checked', false);
    });
        
    
    $('#add-patient-deductable').on('click', function(){
        $('#total-deductible').toggleClass('hidden');
    });
    
    //edit_text
    $('.edit_text').on('click', function() {
        var original_text = $(this).parent().find('.editable_text').text();
        var new_input = $("<input type=\"email\" name=\"email\" class=\"text_editor\"/>");
        new_input.val(original_text);
        $(this).parent().find('.editable_text').replaceWith(new_input);
        //new_input.focus();
    });
    $('.edit_this_text').on('click', function() {
        var original_text = $(this).parent().find('.editable_text').text();
        var name = $(this).attr('data-name');
        var className = $(this).attr('data-class');
        var new_input = $("<input  autocomplete=\"off\" type=\"text\" name=\""+name+"\" class=\"text_editor "+className+"\"/>");
        new_input.val(original_text);
        $(this).parent().find('.editable_text').replaceWith(new_input);
        //new_input.focus();
    });
    
    //edit revinue item
    $('.edit_reveue').on('click', function() {
        var dataID = $(this).attr('data-id');
        
        var datePaydTxt = $('#revenue-table #'+dataID+" .editable_date_payd").text();
        var datePayorTxt = $('#revenue-table #'+dataID+" .editable_payor").text();
        var dateInsuranceTxt = $('#revenue-table #'+dataID+" .editable_insurance").text();
        dateInsuranceTxt = dateInsuranceTxt.replace(/\,/g,"");
        var datePatientTxt = $('#revenue-table #'+dataID+" .editable_patient").text();
        datePatientTxt = datePatientTxt.replace(/\,/g,"");
        
        var datePaydInput = $("<input required autocomplete=\"off\" type=\"text\" name=\"revenueEdit["+dataID+"][date_paid]\" class=\"datepicker\" />");
        var datePayorInput = $("<input required autocomplete=\"off\" type=\"text\" name=\"revenueEdit["+dataID+"][payor]\" />");
        var dateInsuranceInput = $("<input required autocomplete=\"off\"  min=\"0.00\" step=\"0.01\" type=\"number\"  name=\"revenueEdit["+dataID+"][insurance]\"  />");
        var datePatientInput = $("<input required autocomplete=\"off\"  min=\"0.00\" step=\"0.01\" type=\"number\"  name=\"revenueEdit["+dataID+"][patient]\"  />");
        
        datePaydInput.val(datePaydTxt);
        datePayorInput.val(datePayorTxt);
        dateInsuranceInput.val(dateInsuranceTxt);
        datePatientInput.val(datePatientTxt);
        
        $('#revenue-table #'+dataID+" .editable_date_payd").replaceWith(datePaydInput);
        $('#revenue-table #'+dataID+" .editable_payor").replaceWith(datePayorInput);
        $('#revenue-table #'+dataID+" .editable_insurance").replaceWith(dateInsuranceInput);
        $('#revenue-table #'+dataID+" .editable_patient").replaceWith(datePatientInput);
    });
    
    //edit deductable item
    $('.edit_deductable').on('click', function() {
        var dataID = $(this).attr('data-id');
        
        var dateCheckedTxt = $('#deductable-table #'+dataID+" .editable_date_checked").text();
        var checkedByTxt = $('#deductable-table #'+dataID+" .editable_checked_by").text();
        var deductableTxt = $('#deductable-table #'+dataID+" .editable_deductable").text();
        deductableTxt = deductableTxt.replace(/\,/g,"");
        var dateCheckedInput = $("<input required autocomplete=\"off\" type=\"text\" name=\"deductableEdit["+dataID+"][date_checked]\" class=\"datepicker\" />");
        var checkedByInput = $("<input required autocomplete=\"off\" type=\"text\" name=\"deductableEdit["+dataID+"][checked_by]\" />");
        var deductableInput = $("<input required autocomplete=\"off\" min=\"0.00\" step=\"0.01\" type=\"number\" name=\"deductableEdit["+dataID+"][deductable]\" />");
        
        dateCheckedInput.val(dateCheckedTxt);
        checkedByInput.val(checkedByTxt);
        deductableInput.val(deductableTxt);
        
        $('#deductable-table #'+dataID+" .editable_date_checked").replaceWith(dateCheckedInput);
        $('#deductable-table #'+dataID+" .editable_checked_by").replaceWith(checkedByInput);
        $('#deductable-table #'+dataID+" .editable_deductable").replaceWith(deductableInput);
        
        //$('#deductable-table #'+dataID+" .action_second").removeClass('hide');
        //$('#deductable-table #'+dataID+" .action_first").addClass('hide');
        
    });   
    
    
    $('table').delegate( ".removeTableRow", "click", function() {       
        var val = $(this).parent().parent().remove();
    });
    /**
     * Add revenue table row
     */
    $('#add-revenue').on('click', function(){       
        var formData = '<tr><td><input required name="revenueAdd[date_paid][]" class="deductable-first datepicker" autocomplete="off" class="revenue-first" placeholder="Date Paid" type="text" /></td><td><input required name="revenueAdd[payor][]" placeholder="Payor" type="text" /></td><td>$ <input required name="revenueAdd[insurance][]" placeholder="Insurance" type="number"  min=\"0.00\" step=\"0.01\"/></td><td>$ <input required name="revenueAdd[patient][]" placeholder="Patient" type="number"  min=\"0.00\" step=\"0.01\"/></td><td class="text-center"><a class="color-red removeTableRow"><span class="fas fa-minus-circle" aria-hidden="true"></span></a></td></tr>';
        $('#revenue-table .priceSum').before(formData);
    });
    /**
     * Add deductable table row
     */
    $('#add-deductable-log').on('click', function(){       
        var formData = '<tr><td><input required name="deductableAdd[date_checked][]" class="deductable-first datepicker" autocomplete="off" placeholder="Date Checked" type="text" /></td><td><input required name="deductableAdd[checked_by][]" placeholder="Checked By" type="text" /></td><td>$ <input required name="deductableAdd[deductable][]" placeholder="Deductible" type="number" min=\"0.00\" step=\"0.01\" /></td><td class="text-center"><a class="color-red removeTableRow"><span class="fas fa-minus-circle" aria-hidden="true"></span></a></td></tr>';        
        $('#deductable-table .priceSum').before(formData);
    });
    
    /**
     * Stats page parent - sub carusel
     */
    $('.stats-table .parent').on('click', function(e){
        var id = $(this).attr('id');
        $('[data-parent-id="'+id+'"]').toggleClass('show');
        $(this).find('span').toggleClass('opened');
    });
    
    
    /**
     *  Delete Market test user
     */
    $('.deleteUser').on('click', function (event) {     
        var thisUserClass = $(this);
        var userType = $(this).attr('id');  //mdl-user or test-user
        var userId = $(this).attr('data-user-id');  
        var message = 'Are you sure you want to delete #'+userId+' MDL User History?';
        if(userType=='test-user'){
            message = 'Are you sure you want to delete #'+userId+' Test User Data and All The History of that User?';
        }        
        var conf = confirm(message);
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        if(conf){
        $.ajax( ajaxUrl, {
            type: 'POST',
            data: {
               deleteUser: '1',
               userType: userType,
               Guid_user: userId
            },
            success: function(response) {
                var result = JSON.parse(response);               
                console.log(result);
                console.log($(this).parent().parent());
                if(userType=="test-user"){
                    thisUserClass.parent().parent().hide();
                    alert('#'+userId+' Test User Data and All The History Deleted.');
                }        
                if(userType=="mdl-user"){
                    alert('#'+userId+' MDL User History Deleted.');
                }
            },
            error: function() {
                console.log('0');
            }
        });
        }
    });
    //Delete All marked test users and history
    $('#delete-marked-test-users').on('click', function (event) { 
        var message = 'Are you sure you want to delete All Marked Test Users Data and History?';
        var ajaxUrl = baseUrl+'/ajaxHandler.php';
        var redirectUrl =  baseUrl+'/user-management.php'
        var conf = confirm(message);
        if(conf){
            $.ajax( ajaxUrl, {
                type: 'POST',
                data: { deleteMarkedTestUsers: '1'},
                success: function(response) {
                    var result = JSON.parse(response);               
                    console.log(result);
                    window.location.replace(redirectUrl);
                },
                error: function() {
                    console.log('0');
                }
            });
        }
    });
    
});

/***
 * not working for iPad
 * check this option
 */
$(document).click(function(e) { 

    var ele = $(e.toElement); 
    if (!ele.hasClass("hasDatepicker") && !ele.hasClass("ui-datepicker") && !ele.hasClass("ui-icon") && !$(ele).parent().parents(".ui-datepicker").length)
       $(".hasDatepicker").datepicker("hide"); 
});


/**
 *  Confirmation functions for delete items
 * @param {type} anchor
 * @returns {undefined}
 */
function confirmationDelete(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("="); 
    var conf = confirm('Are you sure you want to delete setting #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteDevice(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("="); 
    var conf = confirm('Are you sure you want to delete device with Serial #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteAccount(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("="); 
    var conf = confirm('Are you sure you want to delete account #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteSalesReps(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("="); 
    var conf = confirm('Are you sure you want to delete Salesrep #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteProvider(anchor){    
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");    
    var conf = confirm('Are you sure you want to delete provider #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteDeductible(anchor){    
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");    
    var conf = confirm('Are you sure you want to delete Deductible Log #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteRevenue(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");    
    var conf = confirm('Are you sure you want to delete Revenue #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteStatusLog(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");    
    var conf = confirm('Are you sure you want to delete Test Status Log #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteUser(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");    
    var conf = confirm('Are you sure you want to delete User #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteNooteLog(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");    
    var conf = confirm('Are you sure you want to delete Note #'+$str[1]+'?');   
    if(conf)  window.location=anchor.attr("href");
}



/**
 *  After upload show image in thumbnail 
 *  edd/edt pages for accunts, providers etc
 * @returns {undefined}
 */
var fileInput =  document.getElementById("file");
if (typeof(fileInput) != 'undefined' && fileInput != null)
{
    document.getElementById("file").onchange = function () {
        var reader = new FileReader();

        reader.onload = function (e) {
            // get loaded data and render thumbnail.
            document.getElementById("image").src = e.target.result;
        };

        // read the image file as a data URL.
        reader.readAsDataURL(this.files[0]);
    };
}

/**
 *  JS history back for Cancel buttons
 * @returns {undefined}
 */
function goBack() {
    window.history.back()
}
/**
 *  Capitalize first letter
 * @param {type} str
 * @param {type} force
 * @returns {unresolved}
 */
 function capitalizeWord(str,force){
    str=force ? str.toLowerCase() : str;
    return str.replace(/^(.)|\s(.)/g, function($1){ return $1.toUpperCase( ); });
}

$('.export_filters #salesrep').on('change', function() {
    var ajaxUrl = baseUrl+'/ajaxHandler.php';
    $.ajax(ajaxUrl, {
    type: 'POST',
    data: {
       updateAccounts: 'ok',
       salesrep: $('.export_filters #salesrep').val()
    },
      success: function (response) {
		var result = JSON.parse(response);
		$("#matrix_parameters #account").empty();
		$("#matrix_parameters #account").removeClass('no-selection');
		$("#matrix_parameters #account").parent().parent().removeClass('show-label valid');
		$("#matrix_parameters #account").append(result.accounts_html);
      }
    });
});
