(function($) {
  $(document).ready(function() {
    $('.faqs-item__question').click(function() {
      $(this).next().toggleClass('hidden');
    });
  });
})(jQuery);