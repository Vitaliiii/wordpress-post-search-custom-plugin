/*------------------------ 
Backend related javascript
------------------------*/

(function( $ ) {

	"use strict";

	$(document).ready(function () {

		function ajaxLoading(searchValue, newKeyword, el) {
			$.ajax({
				url: my_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'search_posts',
					search: searchValue,
					replace: newKeyword,
					element: el
				},
				success: function(response) {
					$('#search-posts-results tbody').html(response);
					
					$('#replace-button-title').removeClass('active');
					$('#replace-button-content').removeClass('active');
					$('#replace-button-meta-title').removeClass('active');
					$('#replace-button-meta-description').removeClass('active');
					
					$('.row-posts').each(function () {
						const hasTitle = $(this).find('.post-title').hasClass('has');
						const hasContent = $(this).find('.post-content').hasClass('has');
						const hasMetaTitle = $(this).find('.post-meta-title').hasClass('has');
						const hasMetaDescription = $(this).find('.post-meta-description').hasClass('has');
						console.log('check');
						
						if (hasTitle == true) {
							$('#replace-button-title').addClass('active');
						};
						if (hasContent == true) {
							$('#replace-button-content').addClass('active');
						};
						if (hasMetaTitle == true) {
							$('#replace-button-meta-title').addClass('active');
						};
						if (hasMetaDescription == true) {
							$('#replace-button-meta-description').addClass('active');
						};
					});
				}
			});
		};

		$('#search-posts-button').click(function () {
			if (!$(this)) {
				return;
			}

			let messageEl = $('#search-posts-message');
			let searchValue = $('#search-posts-input').val();
			$('.post-replace-button').attr('data-search', searchValue);
			let newKeyword = null;
			let element = null;
			if (!searchValue) {
				messageEl.text('Please add some keyword!');
				$('#current-word span').text('');
				return;
			} else {
				messageEl.text('');
			}
			
			$('#current-word span').text(searchValue);
			ajaxLoading(searchValue, newKeyword, element);
			$('.post-replace-input').val('');
		});

		$('.post-replace-button').click(function () {
			if (!$(this)) {
				return;
			}

			let thisDataType = $(this).attr('data-replace');
			let searchValue = $(this).attr('data-search');
			let inputEl = $(`#replace-${thisDataType}`);
			let messageEl = $(`#replace-message-${thisDataType}`);
			let newKeyword = inputEl.val();
			// console.log(searchValue + " | " + newKeyword + " | " + thisDataType);
			if (!newKeyword) {
				messageEl.text('Please add some new keyword!');
				$('#current-word span').text('');
				return;
			} else if(searchValue == newKeyword) {
				messageEl.text('Please change to some new keyword!');

				return;
			} else {
				messageEl.text('');
			}
			
			$('#current-word span').text(newKeyword);
			ajaxLoading(searchValue, newKeyword, thisDataType);
			$(this).attr('data-search', newKeyword);
			$('#search-posts-input').val('');
		});
	});

})( jQuery );
