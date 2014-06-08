//Comment form handler

define([], function() {
        "use strict";

        var $ = jQuery,
            $respond = $('#respond'), $respondWrap = $('#respond-wrap'),
            $cancelCommentReply = $respond.find('#cancel-comment-reply-link'),
            $commentParent = $respond.find('input[name="comment_parent"]');

        $('.comment-reply-link').each(function () {
            var $this   = $(this),
                $parent = $this.parents().eq(2);

            $this.click(function () {
                var commId = $this.parents('.comment').attr('data-id');

                $commentParent.val(commId);
                $respond.insertAfter($parent);
                $cancelCommentReply.show();

                return false;
            });
        });

        $cancelCommentReply.click(function (e) {
            e.preventDefault();

            $cancelCommentReply.hide();

            $respond.appendTo($respondWrap);
            $commentParent.val(0);
        });

    }
);