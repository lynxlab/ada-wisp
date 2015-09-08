function initDoc(maxSize) {
	$j("#importfile").pekeUpload({
		allowedExtensions : "csv",
		onFileSuccess : function(file) {
			importStepTwo(file);
		},
		btnText : "Sfoglia Files..",
		maxSize : maxSize,
		field : 'uploaded_file',
		url : 'ajax/upload.php'
	});
	
	$j('#import_users_steptwo').hide();
}

function importStepTwo(file) {
	$j('#helpForm').hide();
	$j('form[name=import_users]').parents('div.fform.form').fadeOut(
			function() { $j('#import_users_steptwo').fadeIn(); }
	);
	
	$j.ajax({
		type	:	'POST',
		url		:	'ajax/doImportUsers.php',
		data	:	{ file: file.name, import_user_type : $j('#import_user_type').val() },
		dataType:	'json'
	}).done (function(JSONObj){
		if (JSONObj) {
			if ('undefined' != typeof JSONObj.OK) $j('#import_users_steptwo').addClass((JSONObj.OK ? 'success' : 'fail'));
			if ('undefined' != typeof JSONObj.message) $j('#import_users_steptwo > span.importtext').html(JSONObj.message);
			$j('#import_users_steptwo > img').remove();
		}
	})
	.always(function() { 
		setTimeout (function() { $j('#import_users_steptwo').delay(2000).fadeOut(
				function() { $j('#import_users_button').fadeIn(); }
		); } ,2000);
		});
}