(function($) {
    $(document).ready(function() {
        $input_id = $('#lbk_list_faqs_inner .collapsible:last-child .faqs-content input.faqs-id');
        var count = $input_id.val();
        if ( typeof(count) === 'undefined' ) count = 1;
        else count++;

        $("#add_new_faqs_button").click(function(e) {
            e.preventDefault();

            $("#lbk_list_faqs_inner").append(`
                <div class="collapsible state-open" id="faqs-${count}">
                    <div class="collapsible_heading">
                        <h4>Question ${count}</h4>
                        <a href="#" class="button" id="lbk_delete_faqs" data-id="${count}">Remove</a>
                        <span class="collapsible-icon-toggle"></span>
                    </div>
                    <div class="faqs-content collapsible_content">
                        <input type="hidden" class="faqs-id" value="${count}">
                        <div class="faqsData">
                            <h4>Question</h4>
                            <input name="lbk_faqs_custom[${count}][question]" value="" placeholder="Question?" style="width:100%">
                        </div>
                        <div class="faqsData">
                            <h4>Answer</h4>
                            <textarea name="lbk_faqs_custom[${count}][answer]" id="answer-${count}" placeholder="Answer" style="width:100%" rows="5"></textarea>
                        </div>
                    </div>
                </div>
            `);

            wp.editor.initialize("answer-"+count, {
                tinymce: {
                    wpautop: true,
                    toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,wp_adv,listbuttons,forecolor',
                },
                quicktags: true,
                mediaButtons : true
            });

            count++;
        });

        $("#lbk_list_faqs_inner").on('click', '#lbk_delete_faqs', function(e) {
            e.preventDefault();

            var action = confirm("Are you sure you want to delete this user?");
            var faqs_id = $(this).attr('data-id');
            if (action != false) {
                $("#lbk_list_faqs_inner #faqs-" + faqs_id).remove();
            }
        });

        $('#lbk_list_faqs').on('click', '.collapsible_heading', function() {
            $(this).parent().toggleClass('state-open');
            $(this).parent().toggleClass('state-close');

            $('body').on('scroll mousewheel touchmove', stopScrolling);
        });

        function stopScrolling(e) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        $('#lbk_list_faqs').on('click', '.collapsible_button', function(e) {
			e.preventDefault();
		});
    });
})(jQuery);