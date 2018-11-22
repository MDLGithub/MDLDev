/* 
	Script File
	File Name : custom-script.js
 */

$(document).ready(function(){

    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1;
    var yyyy = today.getFullYear();
    if( dd < 10 ) { dd = '0'+dd }
    if( mm < 10 ) { mm = '0'+mm }


    $("#setDate").find(".datepicker").datepicker({
		dateFormat: "mm/dd/yy"
    });

	$('#followup').click(function(e){
		e.preventDefault();
		$("#setDate").css("display","block");
	});

	$("#modal_close").click(function(e){
		e.preventDefault();
		$("#setDate").css("display","none");
	});

	function date_revers(str){
		//YYYY-MM-DD
        str = str.replace(/\//gi,"-").split("-");
        str = str[2]+'-'+str[0]+'-'+str[1];

		return str;
	}

	$("#reset_date").click(function (e) {
        e.preventDefault();
        var today = mm + '/' + dd + '/' + yyyy;
        $("#to_date").val(today);
        $("#from_date").val(today);
    });

	$("#print").click(function(e){
		e.preventDefault();

		var account = $('input[name="account"]').val();
		var guid_account = $('input[name="guid_account"]').val();

        var from_date = date_revers( $("#from_date").val() );
        var to_date = date_revers( $("#to_date").val() );

        //YYYY-MM-DD
        var today = yyyy + '-' + mm + '-' + dd;

		$.ajax({
			url: 'form.php',
			type: 'POST',
			data: {
			    today: today,
                account: account,
                guid_account: guid_account,
                from_date: from_date,
                to_date: to_date
            },success: function(res){
                var toPrint = res;
                var popupWin = window.open('', '_blank','width=1000,height=900,location=no,left=200px');
                popupWin.document.open();
                popupWin.document.write(toPrint);
                /*$( popupWin.document ).find("#body").html("").html(
                    $( popupWin.document ).find("#printarea").html()
                );*/
                //console.log($( popupWin.document ).find("#printarea").html());
                popupWin.document.close();
			}
		});
	});

    $("#reset_date").click();
});




