function showFields(forms) {
  $('.form_field').hide()

  if (forms.length > 0) {
    $.each(forms, function(index, value) {
      switch (value) {
        case 'informed_consent':
          $('.patient_demographics .first_name, .last_name').show();
          break;
        case 'prior_authorization':
          $('.patient_demographics .first_name, .last_name, .dob').show();
          break;
        case 'genetic_counseling':
          $('.patient_demographics .first_name, .last_name, .dob, .addr1, .addr2, .city, .state, .zip, .phone').show();
          break;
        case 'cancer_genetic_counseling':
          $('.patient_demographics .first_name, .last_name, .dob, .addr1, .addr2, .city, .state, .zip, .phone').show();
          break;
        case 'aetna_precertification':
          $('.patient_demographics .first_name, .last_name, .dob, .addr1, .addr2, .city, .state, .zip, .phone').show();
          break;
        case 'aim':
          $('.patient_demographics .first_name, .last_name, .dob, .addr1, .addr2, .city, .state, .zip, .phone').show();
          break;
        case 'beacon':
          $('.patient_demographics .first_name, .last_name, .dob, .addr1, .addr2, .city, .state, .zip, .phone').show();
          break;
        case 'test_req_form':
          $('.patient_demographics .form_field').show();
          break;
      }
    })
  }
}

$(document).ready(function(){
  tabCounter = 0;
  firsttab = $("#form-option-table");
  firsttab.show();
  $("#forms").addClass("active-tab");
  secondtab = $("#accordion");
  secondtab.hide();
  if ($('input[name=forms]:checked').length == 0) { 
    $("#form-details").addClass('disabled');
  }

  $("#forms").click(function(){
    firsttab.show();
    secondtab.hide();

    $(this).addClass("active-tab");
    $("#form-details").removeClass("active-tab");

    $(".contentBlock.patientForms").addClass("patientForms-open");
    $(".contentBlock.patientForms").removeClass("formdetails-open");
  });

   $("#info_button").click(function(){
    firsttab.show();
    secondtab.hide();
  });

  $('input[name=forms]').change(function() {
    var checked = []
    $.each($('input[name=forms]:checked'), function(index, value) {
      checked.push($(value).val())
    })
    showFields(checked)

    if ($('input[name=forms]:checked').length > 0) { 
      $("#form-details").removeClass('disabled');
    } else {
      $("#form-details").addClass('disabled');
    }
  });

  $('.slider').on('click', function() {
    if ($('#switchLabel').text() == 'Select All') { 
      $("#form-details").removeClass('disabled');
    } else {
      $("#form-details").addClass('disabled');
    }
  });

  $("#form-details").click(function(){
    firsttab.hide();
    secondtab.show();

    secondtab.css('display', 'flex');

      $("#forms").removeClass("active-tab");
      $(this).addClass("active-tab");

      $(".contentBlock.patientForms").addClass("formdetails-open");
      $(".contentBlock.patientForms").removeClass("patientForms-open");

  });

   activeItem = $("#accordion li:first");
   $(activeItem).addClass('active');

   $("#accordion #form-bar").click(function(){
    tabCounter = $( "#accordion #form-bar" ).index( this );
    $(activeItem).css('width', '50px');
    $(this).parent().css('width', '90%');

    activeItem = $(this).parent();
});

 $(".next-button").click(function(){
      if(tabCounter != ($('ul#accordion li').length-1)){
        tabCounter ++;
        $(activeItem).css('width', '50px');
        $(activeItem).next().css('width', '90%');
        activeItem = $(activeItem).next();
      }
 });


 $(".prev-button").click(function(){
      if(tabCounter > 0){
        tabCounter--;
       $(activeItem).css('width', '50px');
       $(activeItem).prev().css('width', '90%');
       activeItem = $(activeItem).prev();
     }
 });

       var onKeyDown = function ( event ) {
        switch ( event.keyCode ) {
          case 39:
              if(tabCounter != ($('ul#accordion li').length-1)){
                tabCounter ++;
                $(activeItem).css('width', '50px');
                $(activeItem).next().css('width', '90%');
                activeItem = $(activeItem).next();
              }
            break;
          case 37:
          if(tabCounter > 0){
                tabCounter--;
                $(activeItem).css('width', '50px');
                $(activeItem).prev().css('width', '90%');
                activeItem = $(activeItem).prev();
              }
            break;
        }
      };
      document.addEventListener( 'keydown', onKeyDown, false );


     $(".patient_forms").on('click', function() {
       $('#patient_brca_forms').css('display', 'block');
         if($("#form-details").hasClass("active-tab")){

            $(".contentBlock.patientForms").addClass("formdetails-open");
            $(".contentBlock.patientForms").removeClass("patientForms-open");
         }else{
            $(".contentBlock.patientForms").addClass("patientForms-open");
            $(".contentBlock.patientForms").removeClass("formdetails-open");
         }
     });


     // When the user clicks on <span> (x), close the modal
     $('.close').on('click', function () {
       $('#patient_brca_forms').css('display', 'none');
     });

     // When the user clicks anywhere outside of the modal, close it
     $(document).on('click', function (event) {
       var target = $(event.target);
       if (target.is( "#patient_brca_forms" )) {
         $('#patient_brca_forms').css('display', 'none');
       }
     });

  $('.openPdf').on('click', function() {
    var request = new XMLHttpRequest();
    request.open('POST', 'ajaxHandler.php', true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.responseType = 'blob';
    request.send('openPdf=ok&pdf_name=' + $(this).attr('pdf_name') + '&patientInfo=' + $('#post').val());
    

    request.onload = function() {
      // Only handle status code 200
      if(request.status === 200) {
        var tabWindow = window.open('', '_blank');
        var a = tabWindow.document.createElement('a');
        tabWindow.document.body.appendChild(a);
        var blob = new Blob([request.response], { type: 'application/pdf' });

        if (window.navigator.msSaveOrOpenBlob) {
          spinnerService.hide('html5spinner');
          window.navigator.msSaveOrOpenBlob(blob, filename);
        } else {
          a.href = window.URL.createObjectURL(blob);
          a.click();
          a.download = 'filled.pdf';
        }
      }
    };
  });
});