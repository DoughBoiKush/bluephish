$(function(){
	var $field = $('#id_profile_field_a2fasecret');
	var $newBtn = $('#id_newsecret');
	var baseUrl = $('input[name="a2fa_baseurl"]').val();

	$newBtn.on('click', function(){
		$.getJSON(baseUrl + '/auth/a2fa/generate.php', function(res){
			if(res.status = "success"){
				$field.val(res.secret);
			}
			else{
				alert('There was an error generating a new secret');
			}
		});
	});
});
