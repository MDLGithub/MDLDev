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

        // Get the modal
        var modal = document.getElementById('myModal');

        $(".patient_forms").click(function(){
            modal.style.display = "block";
        });

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks on <span> (x), close the modal
        span.onclick = function () {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function (event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

});