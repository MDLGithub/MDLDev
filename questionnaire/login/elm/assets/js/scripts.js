$(document).ready(function () {
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
    });
    /**
     * account dropdown on change function
     * update account info when account selected
     */
    $('#url-config-settings #account').on('change', function() {
	var accountId =  this.value;
	console.log(accountId);
	$.ajax( 'ajaxHandler.php', {
	    type: 'POST',
	    data: {
	       get_account_info: '1',
	       account_id: accountId
	    },
	    success: function(response) {
		var result = JSON.parse(response);
		console.log(result);
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
     * Update Account info and providers
     * @param {type} accountData
     * @param {type} providers
     * @returns {undefined}
     */
    function updateAccountInfo(accountData, providers){
	var officeLogo = accountData['logo_filename'];
	setImage('#officeLogo', 'width="100"', officeLogo);
	var address = accountData['address'];
	var state = accountData['state'];
	var city = accountData['city'];
	var zip = accountData['zip'];
	$('#officeAddress, #officeAddressLabel').html("");

	$('#officeAddressLabel').prepend('Address');
	$('#officeAddress').prepend(address+"<br/>"+city+", "+state+" "+zip);
	$('#physiciansList, #physiciansListLabel').html("");
	for ( var i=0; i<providers.length; i++){
	    $('#physiciansList').append('<p>'+providers[i]['name']+'</p>');
	}
	$('#physiciansListLabel').append('<p class="providersTitle">Health Care Providers</p>');

    }

    /**
     *  Generate Url button on click foncion
     */
    $('.url_config').on('click', function (event) {
	var id = $(this).attr('id');
	$.ajax( 'ajaxHandler.php', {
	    type: 'POST',
	    data: {
	       url_config: '1',
	       id: id
	    },
	    success: function(response) {
		var result = JSON.parse(response);
		var configData = result.urlConfigs[0];
		console.log(configData);
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
     * @returns {undefined}
     */
    function updateSelectSettings(configData){
	console.log(configData);
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
	} else {
	    $('#url-config-settings #pass').prop('checked', true);
	}
	setSelectedAttrByVal('#url-config-settings #account option', account);
	setSelectedAttrByVal('#url-config-settings #location option', location);
	setSelectedAttrByVal('#url-config-settings #deviceId option', deviceId);
	setImage('#officeLogo', 'width="100"', officeLogo);
	if(account !== null){
	    var address = configData['address'];
	    var state = configData['state'];
	    var city = configData['city'];
	    var zip = configData['zip'];

	    $('#officeAddress, #officeAddressLabel').html("");
	    $('#officeAddressLabel').prepend('Address');
	    $('#officeAddress').prepend(address+"<br/>"+city+", "+state+" "+zip);
	    $('#physiciansList, #physiciansListLabel').html("");

	    $.ajax( 'ajaxHandler.php', {
		type: 'POST',
		data: {
		   get_providers: '1',
		   accountId: account
		},
		success: function(response) {
		    var result = JSON.parse(response);
		    var providers = result['providers'];
		    for ( var i=0; i<providers.length; i++){
			$('#physiciansList').append('<p>'+providers[i]['name']+'</p>');
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
     * @returns {undefined}
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
     * @returns {undefined}
     */
    function setImage(selectorDiv, attr, imageName){
	$(selectorDiv).html("");
		if (imageName.length) {
			$(selectorDiv).prepend('<img '+attr+' src="/dev/images/practice//'+imageName+'" />')
		}
    }



});


function confirmationDelete(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");
    var conf = confirm('Are you sure you want to delete setting #'+$str[1]+'?');
    if(conf)  window.location=anchor.attr("href");
}
function confirmationDeleteDevice(anchor){
    $str = anchor.attr("href").split("&");
    $str = $str[1].split("=");
    var conf = confirm('Are you sure you want to delete device #'+$str[1]+'?');
    if(conf)  window.location=anchor.attr("href");
}