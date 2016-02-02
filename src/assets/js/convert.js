
// On form submit, post the form to POST/generate with AJAX

$('#generate_form').submit( function(e){

	e.preventDefault();

	var reportDiv = $('#reportDiv');

	// Display a waiting bar
	reportDiv.html('Loading');

	jQuery.ajax({
	    url: '/POST/generate/',
	    data: new FormData( this ),
	    cache: false,
	    contentType: false,
	    processData: false,
	    type: 'POST',
	    success: function(data){

	        if( data.success ){
	        	reportDiv.html('File created at <a target="_blank" href="' + data.address + '">' + data.address + '</a>');
	        } else {
	        	reportDiv.html('Error');
	        }
	    }
	});

});

