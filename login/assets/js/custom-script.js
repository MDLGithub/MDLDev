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

	$("#today_date").click(function(e){
		e.preventDefault();
		var today = $(this).val();
		var account = $('input[name="account"]').val();
		var guid_account = $('input[name="guid_account"]').val();
		$.ajax({
			url: 'form.php',
			type: 'POST',
			data: { today: today, account: account, guid_account: guid_account },
			success: function(res){
				var toPrint = res
				var popupWin = window.open('', '_blank','width=900,height=900,location=no,left=200px');
		        popupWin.document.open();
		        popupWin.document.write('<html><title>::Print Preview::</title><link rel="stylesheet" type="text/css" href="Print.css" media="screen"/></head><body">')
		        popupWin.document.write(toPrint);
		        popupWin.document.write('</html>');
		        popupWin.document.close();
			}
		})
	})
});




