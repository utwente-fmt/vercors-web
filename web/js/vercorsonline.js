const VERIFICATION_SERVER = 'wss://vercors-online.apps.utwente.nl/';
const PROGRESS_BADGE = '[progress] ';

$('#verifythis').click(function() {
	$('#verification-log').text('');
	$('#verification-progress').text('Connecting to verification server...');

	var ws = new WebSocket(VERIFICATION_SERVER, 'vercors');

	ws.onerror = function(err) {
		$('#verification-progress').text('An error occurred: cannot connect to verification server');
		console.log(err);
	};

	ws.onmessage = function(e) {
		try {
			var message = JSON.parse(e.data);

			switch(message.type) {
				case 'stdout':
				case 'stderr':
					var parts = message.data.split("\n");
					for(var i = 0; i < parts.length; i++) {
						if(parts[i] === '') continue;

						if(parts[i].startsWith(PROGRESS_BADGE)) {
							$('#verification-progress').text(parts[i].substring(PROGRESS_BADGE.length));
						} else {
							$('#verification-log').text($('#verification-log').text() + parts[i] + '\n');
						}
					}
					break;
				case 'finished':
					if(message.error) {
						$('#verification-progress').text('An error occurred: ' + message.errorDescription);
					} else {
						$('#verification-progress').text('VerCors exited with exit code ' + message.exitCode);
					}
					ws.close();
					break;
			}
		} catch(e) {
			$('#verification-progress').text('An error occurred: ' + err);
			console.log(e);
		}
	}

	ws.onopen = function(e) {
		$('#verification-progress').text('Connected; sending file...');
		ws.send(JSON.stringify({
			type: 'submit',
			lang: $('input[name=language]').val(),
			data: $('textarea[name=examplecode]').val()
		}));
	}
})
