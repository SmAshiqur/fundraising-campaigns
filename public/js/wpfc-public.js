/**
 * All of the JavaScript for your public-facing functionality should be
 * included in this file.
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize lightbox for video links
        if (typeof $.fn.magnificPopup !== 'undefined') {
            $('.wpfc-campaign-video').magnificPopup({
                type: 'iframe',
                mainClass: 'wpfc-mfp-fade',
                removalDelay: 160,
                preloader: false,
                fixedContentPos: false
            });
        }
    });

})(jQuery);