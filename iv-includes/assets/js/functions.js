$(document).ready( function() {
	$('#barOpen').click(function() {
		var adminBar=$('#adminBar');
		adminBar.slideToggle(200);
		var state = $.cookie('adminBarHidden');
		$.cookie('adminBarHidden', 1-state, {path: '/'});
		return false;
	} );
});

function lightbox(url, params) {
	if (!params) params={};
	defaults = {
		transition : 'fade',
		opacity : 0.8,
		maxWidth: '95%',
		maxHeight: '95%'
	}
	if(params.iframe && params.innerWidth == undefined && params.innerHeight == undefined){defaults.width='95%';defaults.height='95%';}
	params.href = url;
	$.colorbox($.extend(params, defaults));
	if (i=document.getElementById('imagevue')) i.exitfullscreen();
}