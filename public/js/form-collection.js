$(function() {

   removeItem();
   disableAddButton();
     
   // Remove Item
   function removeItem() {

      $('.js--remove-item-slider').click(function() {

         const AD_REMOVE_IMAGE_ID = $(this).data('id');

         let confirm = window.confirm("Voulez-vous vraiment supprimer cet objet ?");

         if (confirm === true) {
            $('#' + AD_REMOVE_IMAGE_ID).remove();
         }
         
         disableAddButton();
      });
   }

   // Remove Prototype
   function disableAddButton() {

      // Max subform
      const MAX_ENTRY = 4;

      // Add button
      let addButton = $('.js--add-item-slider');
      let lengthOfEntry = $('#ad_images > .row').length;

      if (lengthOfEntry == MAX_ENTRY) {
         addButton.attr('disabled', 'disabled');
      } else {
         addButton.removeAttr('disabled');
      }

      switch (lengthOfEntry) {

         case 1: 
         $('.js--ad-create__images-max-image').text('Il vous reste 3 images')
         break;
         case 2: 
            $('.js--ad-create__images-max-image').text('Il vous reste 2 images')
            break;
         case 3: 
            $('.js--ad-create__images-max-image').text('Il vous reste une image')
            break;
         case 4: 
            $('.js--ad-create__images-max-image').text('Ca y est, vous avez terminé')
            break;
         default: 
            $('.js--ad-create__images-max-image').text('(4 images au maximum)')
            break;
      }

   }

   // Add Entry form
   $('.js--add-item-slider').on('click', function() {
      // Class That contains the prototype
      const AD_NEW_IMAGE_CLASS = $(this).data('class');

      let index = +$('.' + AD_NEW_IMAGE_CLASS).data('index') || $('#ad_images > .row').length;
      // Subform
      let prototype = $('.' + AD_NEW_IMAGE_CLASS).data('prototype').replace(/__name__/g, index);
      let adImage = $('#ad_images');

      $('.' + AD_NEW_IMAGE_CLASS).data('index', index + 1);
      // Append to the form
      adImage .append(prototype);

      removeItem();
      disableAddButton();
   });

});
