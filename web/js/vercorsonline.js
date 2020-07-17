const VERIFICATION_SERVER = 'wss://vercors-server.apps.utwente.nl/';
const PROGRESS_BADGE = '[progress] ';

$('.code-run-button').click(function() {
	const self = $(this).closest('.verification-container');
	self.find('.plain-close').show();
	const log = self.find('.verification-log');
	const progress = self.find('.verification-progress');
	log.show().text('');
	progress.show().text('Connecting to verification server...');

	var ws = new WebSocket(VERIFICATION_SERVER, 'fmt-tool');

	ws.onerror = function(err) {
		progress.text('An error occurred: cannot connect to verification server');
		console.log(err);
	};

	ws.onmessage = function(e) {
		try {
			var message = JSON.parse(e.data);

			switch(message.type) {
				case 'error':
					progress.text('An error occurred: ' + message.errorDescription);
					ws.close();
					break;
				case 'stdout':
				case 'stderr':
					var parts = message.data.split("\n");
					for(var i = 0; i < parts.length; i++) {
						if(parts[i] === '') {
							continue;
						}

						if(parts[i].startsWith(PROGRESS_BADGE)) {
							progress.text(parts[i].substring(PROGRESS_BADGE.length));
						} else {
							log.text(log.text() + parts[i] + '\n');
						}
					}
					break;
				case 'finished':
					progress.text('VerCors exited with exit code ' + message.exitCode);
					ws.close();
					break;
			}
		} catch(err) {
			progress.text('An error occurred: ' + err);
			console.log(err);
		}
	};

	ws.onopen = function(e) {
		progress.text('Connected; sending file...');
		const fileName = 'test.' + self.find('[name=lang]').val();
		ws.send(JSON.stringify({
			type: 'submit',
			files: {
				[fileName]: self.find('textarea[name=examplecode]').val()
			},
			arguments: {
				'files': [fileName],
				'backend': 'silicon',
			}
		}));
	};
});
