/********************************************/
/* FAZ O REQUEST PARA O PHP
/********************************************/
var post;
$(document).ready(function(){
	$("form").submit(function (_event_) {	
		_event_.preventDefault();
		var meuForm = this;
		var meuajax;
		meuajax = $.param($(meuForm).serializeArray());
		post = meuajax;
		$.postJSON = function(url, data, func) { $.post(url+(url.indexOf("?") == -1 ? "?" : "&")+"callback=?", data, func, "json"); }
		$.postJSON(meuForm.action, { ajax: meuajax }, Resposta);

	});
});