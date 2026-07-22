const VERIFICATION_SERVER = 'wss://vercors-server.apps.utwente.nl/';
const PROGRESS_BADGE = '[progress] ';
const PROGRESS_RE = /^\[[0-9.%]+\]/;
const LOG_RE = /^\s*\[[A-Z]+\]/
const FIRST_NON_LOG_RE = /^\s*=+/

function setProgress(progress, text, icon) {
	progress.find('.fa').removeClass().addClass('fa').addClass('fa-' + icon);
	progress.find('.verification-progress-text').text(text);
}

function setRunButtonRunning(button, isRunning) {
	button.context.innerHTML = 
		document.getElementById(isRunning ? 'fa-xmark' : 'fa-play').innerHTML;

	button.attr('title', isRunning ? 'Stop verification' : 'Verify this code');
	button.attr('aria-label', isRunning ? 'Stop verification' : 'Verify this code');
}

function getLanguageExtension(container) {
	const explicit = container.attr('data-language-ext');
	if(explicit) {
		return explicit;
	}

	const selected = container.find('[name=lang]').val();
	if(selected) {
		return selected;
	}

	const codeNode = container.find('pre.playground code').first();
	if(codeNode.length) {
		const className = codeNode.attr('class') || '';
		const match = className.match(/(?:^|\s)language-([a-zA-Z0-9_+-]+)/);
		if(match) {
			return match[1];
		}
	}

	return 'pvl';
}

function indentBlock(amount, text) {
	const prefix = '    '.repeat(amount);
	return text.split('\n').map((line) => prefix + line).join('\n');
}

function renderTemplateCase(templateKind, caseName, verdict, languageExt, snippetCode) {
	const header = `//:: cases ${caseName}\n//:: verdict ${verdict}\n//:: tools silicon\n`;

	if (templateKind === 'test') {
		return header + snippetCode;
	}

	if (templateKind === 'testMethod') {
		const maybeFinal = languageExt === 'java' ? 'final ' : '';
		return `${header}${maybeFinal}class Test {\n${indentBlock(1, snippetCode)}\n}`;
	}

	if (templateKind === 'testBlock') {
		const maybeFinal = languageExt === 'java' ? 'final ' : '';
		return `${header}${maybeFinal}class Test {\n    void test() {\n${indentBlock(2, snippetCode)}\n    }\n}`;
	}

	return snippetCode;
}

function getCodeToVerify(container) {
	const fullCodeBase64 = container.attr('data-examplecode-b64');
	const templateKind = container.attr('data-template-kind') || '';
	const caseName = container.attr('data-case-name') || '';
	const verdict = container.attr('data-case-verdict') || 'Pass';

	if(fullCodeBase64 && !(window.ace && container.find('pre.playground code').first().hasClass('editable'))) {
		try {
			return atob(fullCodeBase64);
		} catch (err) {
			console.log(err);
		}
	}

	const codeNode = container.find('pre.playground code').first();
	if(codeNode.length) {
		if(window.ace && codeNode.hasClass('editable')) {
			try {
				const editor = window.ace.edit(codeNode.get(0));
				const snippetCode = editor.getValue();
				if(templateKind && typeof editor.originalCode !== 'undefined' && snippetCode !== editor.originalCode) {
					return renderTemplateCase(templateKind, caseName, verdict, getLanguageExtension(container), snippetCode);
				}
				if(fullCodeBase64) {
					try {
						return atob(fullCodeBase64);
					} catch (err) {
						console.log(err);
					}
				}
				return snippetCode;
			} catch (err) {
				console.log(err);
			}
		}
		return codeNode.text();
	}

	const textArea = container.find('textarea[name=examplecode]').first();
	return textArea.length ? textArea.val() : '';
}

function verify_code(raw_button) {
	const button = $(raw_button);
	const self = button.closest('.verification-container');
	const log = self.find('.verification-log');
	const progress = self.find('.verification-progress');

	if (self.data('verificationRunning')) {
		const runningWs = self.data('verificationSocket');
		if (runningWs) {
			runningWs.close();
		}
		self.data('verificationRunning', false);
		self.removeData('verificationSocket');
		setRunButtonRunning(button, false);
		setProgress(progress, 'Verification stopped by user', 'times');
		return;
	}

	self.data('verificationRunning', true);
	setRunButtonRunning(button, true);
	log.show().text('');
	progress.show();
	setProgress(progress, 'Connecting to verification server...', 'spinner');

	var ws = new WebSocket(VERIFICATION_SERVER, 'fmt-tool');
	self.data('verificationSocket', ws);

	const resetRunState = function() {
		self.data('verificationRunning', false);
		self.removeData('verificationSocket');
		setRunButtonRunning(button, false);
	};

	ws.onerror = function(err) {
		progress.text('An error occurred: cannot connect to verification server');
		resetRunState();
		console.log(err);
	};

	ws.onmessage = function(e) {
		try {
			var message = JSON.parse(e.data);

			switch(message.type) {
				case 'error':
					setProgress(progress, 'An error occurred: ' + message.errorDescription, 'times');
					ws.close();
					resetRunState();
					break;
				case 'stdout':
				case 'stderr':
					var parts = message.data.split("\n");
					for(var i = 0; i < parts.length; i++) {
                                                const line = parts[i].trim();
						if(line === '') {
							continue;
						}

                                                if(PROGRESS_RE.test(line)) {
							setProgress(progress, line.replaceAll("?", "›"), 'spinner');
						} else if (LOG_RE.test(parts[i]) || FIRST_NON_LOG_RE.test(parts[i])) {
							log.text(log.text() + line + '\n');
						} else {
							log.text(log.text() + parts[i] + '\n');
                                                }
					}
					break;
				case 'finished':
					setProgress(progress, 'VerCors exited with exit code ' + message.exitCode, message.exitCode === 0 ? 'check' : 'times');
					ws.close();
					resetRunState();
					break;
			}
		} catch(err) {
			setProgress(progress, 'An error occurred: ' + err, 'times');
			resetRunState();
			console.log(err);
		}
	};

	ws.onclose = function() {
		resetRunState();
	};

	ws.onopen = function(e) {
		setProgress(progress, 'Connected; sending file...', 'spinner');
		const fileName = 'test.' + getLanguageExtension(self);
		const sourceCode = getCodeToVerify(self);
		ws.send(JSON.stringify({
			type: 'submit',
			files: {
				[fileName]: sourceCode
			},
			arguments: {
				'files': [fileName],
				'backend': 'silicon',
			}
		}));
	};
}