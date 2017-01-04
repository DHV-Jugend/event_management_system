/**
 * Created by Christoph Bessei on 27.12.16.
 */
jQuery(document).ready(function () {
    jQuery(".toggle-header").on('click', function (e) {
        if (jQuery(e.target).is('a')) {
            return;
        }
        var $container = jQuery(this).closest(".toggle-container");
        var $content = $container.find(".toggle-content");
        $content.slideToggle(200, function () {
            $container.toggleClass('active');
        });
    });

    jQuery('.action').on('click', function (e) {
        e.preventDefault();

        var r = confirm("Wirklich löschen?");
        if (r == true) {
            var self = this;

            var eventID = jQuery(this).data('event-id');
            var participantID = jQuery(this).data('participant-id');
            var action = jQuery(this).data('action');
            var data = {ajax: 'true', eventID: eventID, participantID: participantID, action: action};
            jQuery.ajax({
                type: "POST",
                data: data,

                success: function (data) {
                    if ('OK' == jQuery.trim(data)) {
                        alert("Gelöscht");
                        self.closest(".toggle-container").remove();
                    } else {
                        alert("Fehlgeschlagen.");
                    }
                }
            });
        }
    });
});