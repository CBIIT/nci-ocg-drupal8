(function ($) {
Drupal.behaviors.wardead = {
    attach: function(context) {

				// Close the announcement block with the click of an X.
      	$('#closeAnnouncement', context).click(function(event) {
      		$('#block-views-announcement-block').hide("slow");
      	});
      	
				$('.ui-accordion-header').attr('tabindex','0');
				
				// Hide/show the accordion content on events
				$('#accordion').live('mouseenter mouseleave', function(e) {
					var target = $(e.currentTarget).next('div.ui-accordion-content');
					if ( e.type === 'mouseenter' && !$(e.currentTarget).hasClass('open') ) {
						$('.ui-accordion-content').show();
						$(e.currentTarget).addClass('open');
					} else if ( e.type === 'mouseleave' ) {
						$('.ui-accordion-content').hide();
						$(e.currentTarget).removeClass('open');
					}
				});
				
				// Show the accordion content on focus
				$('#accordion').live('focus', function(e) {
					var prev = $(e.currentTarget);
					var target = $(e.currentTarget).find('div.ui-accordion-content');
					if ( e.type === 'focusin' ) {
						prev.addClass('open');
						target.show();						
					}
				});
				
				// Hide the accordion content on blur
				$('#accordion').find('div.ui-accordion-content').find('p:last-child a').live('blur', function(e) {
					var prev = $('#accordion');
					var target = $(e.currentTarget).parents('div.ui-accordion-content');
					if ( e.type === 'focusout' ) {
						prev.removeClass('open');
						target.hide();
					}
				});
        
        $('.pgdi-column input[type=checkbox]:checked').siblings('label').css({"background-color":"#900"});
        $('.pgdi-column input[type=checkbox]:checked').siblings('label').css({"color":"#faec7a"});
        $('.pgdi-column input[type=checkbox]:checked').siblings('label').css({"font-weight":"bold"});
        
        $('.pgdi-column input[type=checkbox]').on('change', function (e) {
          if ($('input[type=checkbox]:checked').length > 10) {
            $(this).prop('checked', false);
          }
          if ($(this).is(':checked')) {
            $(this).attr('checked', 'checked');
            $(this).siblings('label').css({"background-color":"#900"});
            $(this).siblings('label').css({"color":"#faec7a"});
            $(this).siblings('label').css({"font-weight":"bold"});
          } else {
            $(this).siblings('label').css({"background-color":"#e5e3e3"});
            $(this).siblings('label').css({"color":"#323232"});
          }
        });
        
        $('#edit-field-cancer-diagnosis-code-und option').hide();
        
        $('#edit-field-icd-10-code-und').change(function() {
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C17 Malignant neoplasm of small intestine') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C17")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C18 Malignant neoplasm of colon') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C18")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C19 Malignant neoplasm of rectosigmoid junction') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C19")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C22 Malignant neoplasm of liver and intrahepatic bile ducts') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C22")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C40 Malignant neoplasm of bone and articular cartilage of limbs') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C40")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C41 Malignant neoplasm of bone and articular cartilage of other and unspecified sites') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C41")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C43 Malignant melanoma of skin') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C43")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C47 Malignant neoplasm of peripheral nerves and autonomic nervous system') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C47")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C48 Malignant neoplasm of retroperitoneum and peritoneum') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C48")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C49 Malignant neoplasm of other connective and soft tissue') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C49")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C69 Malignant neoplasm of eye and adnexa') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C69")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C70 Malignant neoplasm of meninges') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C70")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C71 Malignant neoplasm of brain') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C71")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C72 Malignant neoplasm of spinal cord, cranial nerves and other parts of central nervous system') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C72")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C74 Malignant neoplasm of adrenal gland') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C74")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C7A Malignant neuroendocrine tumors') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C7A")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C81 Hodgkin lymphoma') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C81")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C82 Follicular lymphoma') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C82")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C83 Non-follicular lymphoma') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C83")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C91 Lymphoid leukemia') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C91")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C92 Myeloid leukemia') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C92")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'C94 Other leukemias of specified cell type') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("C94")').each(function(){
              $(this).show();
            });
          }
          if ($('#edit-field-icd-10-code-und option:selected').text() === 'D43 Neoplasm of uncertain behavior of brain and central nervous system') {
            $('#edit-field-cancer-diagnosis-code-und option').hide();
            $('#edit-field-cancer-diagnosis-code-und option:contains("D43")').each(function(){
              $(this).show();
            });
          }
        });
        
			}					
	};
})(jQuery);
