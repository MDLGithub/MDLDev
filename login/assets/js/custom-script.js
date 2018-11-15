/* 
	Script File
	File Name : custom-script.js
 */

$(document).ready(function(){
	$('#followup').click(function(e){
		e.preventDefault();
		$("#setDate").css("display","block");
	});

	$("#modal_close").click(function(e){
		e.preventDefault();
		$("#setDate").css("display","none");
	});

	function date_revers(str){
        str = str.replace(/\//gi,"-").split("-");
        for(i=0;i<2;i++){
            if(str[i] < 10){
                str[i] = '0'+str[i];
            }
        }
        str = str[2]+'-'+str[0]+'-'+str[1];

		return str;
	}

	$("#reset_date").click(function (e) {
        e.preventDefault();
        $("#to_date").val("");
        $("#from_date").val("");
    });

	$("#print").click(function(e){
		e.preventDefault();

		var today = '00:00:00';
		var account = $('input[name="account"]').val();
		var guid_account = $('input[name="guid_account"]').val();

		if( $("#from_date").val() && $("#to_date").val() ){
            var from_date = date_revers( $("#from_date").val() );
            var to_date = date_revers( $("#to_date").val() );
		} else {
			return false;
		}

		$.ajax({
			url: 'form.php',
			type: 'POST',
			data: { today: today, account: account, guid_account: guid_account, from_date: from_date, to_date: to_date },
			success: function(res){
                var toPrint = res;
                var popupWin = window.open('', '_blank','width=1000,height=900,location=no,left=200px');
                popupWin.document.open();
                //popupWin.document.write('<html><link rel="stylesheet" type="text/css" href="assets/css/forms.css" media="screen"/></head><body">')
                popupWin.document.write(toPrint);
                //popupWin.document.write('</body></html>');
                popupWin.document.close();
			}
		})
	});

});




