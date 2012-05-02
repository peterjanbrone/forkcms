/**
 * Interaction for the settings index-action
 *
 * @author	Tijs Verkoyen <tijs@sumocoders.be>
 */
jsBackend.settings =
{
	init: function()
	{
		$('#facebookAdminIds').multipleTextbox(
		{
			emptyMessage: '{$msgNoAdminIds}',
			addLabel: '{$lblAdd|ucfirst}',
			removeLabel: '{$lblDelete|ucfirst}',
			canAddNew: true
		});

		$('#testEmailConnection').on('click', jsBackend.settings.testEmailConnection);

		$('#activeLanguages input:checkbox').on('change', jsBackend.settings.changeActiveLanguage).change();

		$('#languageDependency').on('change', jsBackend.settings.changeLanguageDependency).change();

		jsBackend.settings.initControls();
	},

	changeActiveLanguage: function(e)
	{
		var $this = $(this);

		// only go on if the item isn't disabled by default
		if(!$this.attr('disabled'))
		{
			// grab other element
			var $other = $('#' + $this.attr('id').replace('active_', 'redirect_'));

			if($this.is(':checked')) $other.attr('disabled', false);
			else $other.attr('checked', false).attr('disabled', true);
		}
	},

	changeLanguageDependency: function(dependency)
	{
		if($('#languageDependency').val() == 1)
		{
			// prevent form from submitting (ajax)
			$('form').unbind('submit');
			$('form').submit(function(e){e.preventDefault();});

			$('.dataGridHolder').show();
			$('.options').has('.dataGridHolder').find('p').has('label').hide();
		}
		else
		{
			// undo e.preventDefault
			$('form').submit(function(e){$(this).unbind('submit').submit();});

			$('.dataGridHolder').hide();
			$('.options').has('.dataGridHolder').find('p').has('label').show();
		}
	},

	initControls: function()
	{
		if($('.dataGrid td.translationValue').length > 0)
		{
			// bind
			$.each($('.dataGrid td.translationValue'), function(key, value) {
				$(this).inlineTextEdit(
				{
					params: { fork: { action: 'save_settings' }, language: $(this).prevAll("td.language:first").html()},
					tooltip: '{$msgClickToEdit}',
					afterSave: function(item)
					{
						if(item.find('span:empty').length == 1) item.addClass('highlighted');
						else item.removeClass('highlighted');
					},
				});
			});

			// highlight all empty items
			$('.dataGrid td.translationValue span:empty').parents('td.translationValue').addClass('highlighted');
		}
	},

	testEmailConnection: function(e)
	{
		// prevent default
		e.preventDefault();

		$spinner = $('#testEmailConnectionSpinner');
		$error = $('#testEmailConnectionError');
		$success = $('#testEmailConnectionSuccess');
		$email = $('#settingsEmail');

		// show spinner
		$spinner.show();

		// hide previous results
		$error.hide();
		$success.hide();

		// fetch email parameters
		var settings = new Object();
		$.each($email.serializeArray(), function() { settings[this.name] = this.value; });

		// make the call
		$.ajax(
		{
			data: $.extend({ fork: { action: 'test_email_connection' } }, settings),
			success: function(data, textStatus)
			{
				// hide spinner
				$spinner.hide();

				// show success
				if(data.code == 200) $success.show();
				else $error.show();
			},
			error: function(XMLHttpRequest, textStatus, errorThrown)
			{
				// hide spinner
				$spinner.hide();

				// show error
				$error.show();
			}
		});
	}
}

$(jsBackend.settings.init);