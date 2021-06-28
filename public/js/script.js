$(function() {

   // Password show hide
   $('.toggle-password').on('click', function() {
      $(this).toggleClass('fa-eye fa-eye-slash')

      let input = $($(this).data('toggle'))

      if (input.attr('type') == 'password') {
         input.attr('type', 'text')
      } else {
         input.attr('type', 'password')
      }

   })
});