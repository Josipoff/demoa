
$(document).ready(function(){
	$("#fileuploader").uploadFile({
	url:baseDir+'modules/orderfiles/ajax/upload.php',
	fileName:"myfile"
	});
});