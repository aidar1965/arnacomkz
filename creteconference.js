<script>
function saveconference(source, room) {
  var formData = {
	'source' : source,
  'room'   : room
	}
	$.ajax({
            type        : 'POST', 
            url         : 'saveconference.php', 
            data        : formData, 
		      	dataType	: 'json',
            encode      : true 
        })
            
}

  $(document).on('click', '.createconference', function(e){
	e.preventDefault()
	
	var formData = {
	'name' : $(this).attr('data-name')
	}
	$.ajax({
            type        : 'POST', 
            url         : 'https://arnacom.kz/api/krhlKPZDQX42G8fMrHLq.php', 
            data        : formData, 
		      	dataType	: 'json',
            encode      : true 
        })
            // using the done promise callback 
            .done(function(data) {
				
				if(data.success == true) {
				  saveconference(data.source, data.room)
				} else alert(data.message)
			})
	})
  
