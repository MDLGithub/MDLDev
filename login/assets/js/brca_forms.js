$(document).ready(function(){
  firsttab = $("#accordion");
  firsttab.hide();
  secondtab = $("#form-option-table");
  secondtab.show();

  $("#forms").click(function(){
    firsttab.show();
    secondtab.hide();
  });

   $("#info_button").click(function(){
    firsttab.show();
    secondtab.hide();
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
});