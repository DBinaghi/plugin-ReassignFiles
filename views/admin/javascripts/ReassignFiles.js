jQuery(document).ready(function () {
	var $ = jQuery;
	
	init();

	function init() {
		$('#reassignFilesFilter').keyup(function () {
			var valthis = $(this).val().toLowerCase();
			var num = 0;
			$('select#reassignFilesFiles>option').each(function () {
				var text = $(this).text().toLowerCase();
				if (text.indexOf(valthis) !== -1) {
					$(this).show();
				} else{
					$(this).hide();
				}
			});
		});
	}
});
