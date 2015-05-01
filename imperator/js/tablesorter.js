jQuery(function($) {
	$('table.sortable').tablesorter();
	$('table.sortable .header').append(' <span class="glyphicon glyphicon-chevron-down"></span><span class="glyphicon glyphicon-chevron-up"></span>');
});