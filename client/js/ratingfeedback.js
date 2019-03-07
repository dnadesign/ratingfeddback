(function($) {

	$(document).ready(function() {

		function init() {
			// Rating Only and Rating And Feedback
			var form = $('.ratingfeedback-form');

			if (form.length > 0) {
				// When either a star or the comment field is interacted with,
				// THe submit button and the comment field (if hidden) are revealed
				var inputs = form.find('input, textarea');
				inputs.on('change input', function(e) {
					form.not('.interacted-with').addClass('interacted-with');
				});

				// The comment field can be made required if the minimum star limit is not met
				var stars = form.find('input'),
					conditionallyRequiredComment = form.find('[data-require-if-less-than]');
				
				if (conditionallyRequiredComment.length == 1) {
					var requireIfLessThan = conditionallyRequiredComment.data('require-if-less-than');

					stars.on('change', function (e) {
						var rating = $(this).val();

						if (rating < requireIfLessThan) {
							conditionallyRequiredComment.attr('required', 'required').attr('aria-required', 'true');
						} else {
							conditionallyRequiredComment.removeAttr('required').removeAttr('aria-required');
						}
					});
				}
				
				// When comment field is hidden by default
				// Clicking on the label will reveal it.
				var commentLabel = form.find('.rating-comment label');
				if (commentLabel.length > 0) {
					commentLabel.on('click', function(e) {
						form.addClass('interacted-with');
						commentLabel.off('click');
					});
				}

			}
		}

		init();
	});

})(jQuery)