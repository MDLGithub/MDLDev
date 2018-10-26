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
  firsttab = $("#form-option-table");
  firsttab.show();
  secondtab = $("#accordion");
  secondtab.hide();
  if ($('input[name=forms]:checked').length == 0) { 
    $("#form-details").addClass('disabled');
  }

  $("#forms").click(function(){
    firsttab.show();
    secondtab.hide();
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
  });

   activeItem = $("#accordion li:first");
   $(activeItem).addClass('active');

   $("#accordion #form-bar").click(function(){
       $(activeItem).animate({width: "50px"}, {duration:300, queue:false});
       $(this).parent().animate({width: "80%"}, {duration:300, queue:false});
       activeItem = $(this).parent();
   });

    $(".next-button").click(function(){
      $(this).parent().parent().parent().animate({width: "50px"}, {duration:300, queue:false});
      $(activeItem).next().animate({width: "80%"}, {duration:300, queue:false});
      activeItem = $(activeItem).next();
    });


    $(".prev-button").click(function(){
      /*$(activeItem).animate({width: "50px"}, {duration:300, queue:false});
      $(activeItem).prev().animate({width: "80%"}, {duration:300, queue:false});*/
      $(this).parent().parent().parent().animate({width: "50px"}, {duration:300, queue:false});
      $(activeItem).prev().animate({width: "80%"}, {duration:300, queue:false});
      activeItem = $(activeItem).prev();
    });

  $(".patient_forms").on('click', function() {
    $('#patient_brca_forms').css('display', 'block');
  });

  $('.close').on('click', function () {
    $('#patient_brca_forms').css('display', 'none');
  });

  $(document).on('click', function (event) {
    var target = $(event.target);
    if (target.is( "#patient_brca_forms" )) {
      $('#patient_brca_forms').css('display', 'none');
    }
  });

  $('.openPdf').on('click', function() {
    $.ajax('ajaxHandler.php', {
      type: 'POST',
      data: {
        openPdf: 'ok',
        pdf_name: $(this).attr('pdf_name'),
        patientInfo: $('#post').val()
      },
      success: function (response) {
          var result = JSON.parse(response);

          $('#form-option-table').append('<a href="'+ result.file +'" class="open_filled_pdf" type="hidden">open</a>');
          window.location = $(".open_filled_pdf").attr("href");

          // $.ajax('ajaxHandler.php', {
          //   type: 'POST',
          //   data: {
          //     unlinkPdf: 'ok',
          //     pdf_name: result.filename,
          //     patient_id: $('#guid_patient').val()
          //   },
          //   success: function (response) {}
          // });
        }
      });
  });

});