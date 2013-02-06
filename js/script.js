;(function($) {
    var $placeholder = $('.ip-rating-hover-value'),
        placeholderText = $placeholder.text();

    /**
     * Rating hover effect
     */
    $('.ip-rating-stars span').hover(function() {
        $placeholder.text($(this).data('i18n'));

        $(this).prevAll().andSelf().addClass('hover');
        $(this).nextAll().removeClass('hover');

    }, function() {
        $placeholder.text(placeholderText);

        $(this).prevAll().andSelf().removeClass('hover');
    });

    /**
     * Submit or update vote
     */
    $('.ip-rating-stars').on('click', 'span', function(event) {
        event.preventDefault();

        var $self = $(this);

        if (ipr.loggedin === 'true') {
            var data = {
                post_id: $self.data('id'),
                vote: $self.data('vote'),
                nonce: ipr.nonce,
                action: ipr.action
            };

            $('.ipr-loading').removeClass('ipr-hide');

            $.post(ipr.ajaxurl, data, function(res) {
                if (res.success) {
                    $placeholder.text(res.data.vote_i18n).data('vote', res.data.vote);
                    placeholderText = res.data.vote_i18n;

                    // show the delete button
                    if ( $('.ip-delete').hasClass('ipr-hide') ) {
                        $('.ip-delete').removeClass('ipr-hide');
                    }

                } else {
                    alert(ipr.errorMessage);
                }

                // refresh vote class
                fillVote();

                // remove loader
                $('.ipr-loading').addClass('ipr-hide');
            });

        } else {
            alert(ipr.loginMessage);
        }
    });

    /**
     * Delete vote
     */
    $('.ip-rating-cancel').on('click', 'a.ip-delete', function(event) {
        event.preventDefault();

        var $self = $(this);

        if (ipr.loggedin === 'true') {
            var data = {
                post_id: $self.data('id'),
                nonce: ipr.nonce,
                action: ipr.del_action
            };

            $('.ipr-loading').removeClass('ipr-hide');

            $.post(ipr.ajaxurl, data, function(res) {
                if (res.success) {
                    $placeholder.text(res.data.vote_i18n).data( 'vote', res.data.vote);
                    placeholderText = res.data.vote_i18n;
                } else {
                    alert(ipr.errorMessage);
                }

                // hide the delete button
                if ( !$('.ip-delete').hasClass('ipr-hide') ) {
                    $('.ip-delete').addClass('ipr-hide');
                }

                // remove loader
                $('.ipr-loading').addClass('ipr-hide');

                // refresh vote class
                fillVote();
            });

        } else {
            alert(ipr.loginMessage);
        }
    });

    function fillVote() {
        var vote = $placeholder.data('vote'),
            $stars = $('.ip-rating-stars'),
            $item = $stars.find('span[data-vote="' + vote + '"]');

        if ($item) {
            $stars.find('span.ipr-vote').removeClass('ipr-vote');
            $item.prevAll().andSelf().addClass('ipr-vote');
        };
    }

    // refresh vote class
    fillVote();
})(jQuery);