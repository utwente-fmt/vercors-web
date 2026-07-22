function detectAceModeFromLanguage(language) {
	switch ((language || '').toLowerCase()) {
	case 'java':
		return 'ace/mode/java';
	case 'pvl':
		return 'ace/mode/java';
	case 'c':
	case 'cu':
	case 'cuda':
		return 'ace/mode/c_cpp';
	default:
		return 'ace/mode/text';
	}
}

function detectAceModeFromClass(codeNode) {
	const className = codeNode.getAttribute('class') || '';
	const match = className.match(/(?:^|\s)language-([a-zA-Z0-9_+-]+)/);
	return detectAceModeFromLanguage(match ? match[1] : '');
}

function getOrCreateAceEditor(editorNode) {
	if (editorNode.env && editorNode.env.editor) {
		return editorNode.env.editor;
	}

	return window.ace.edit(editorNode);
}

function configureAceEditor(editor, mode) {
	editor.setTheme('ace/theme/chrome');
	editor.setOptions({
		highlightActiveLine: true,
		showPrintMargin: false,
		showLineNumbers: true,
		showGutter: true,
		maxLines: Infinity,
		fontSize: '0.875em'
	});
	editor.$blockScrolling = Infinity;
	editor.getSession().setUseSoftTabs(false);
	editor.getSession().setTabSize(2);
	editor.getSession().setMode(mode);
}

function initAceOnDemand(codeNode) {
	if (!window.ace) {
		return null;
	}

	codeNode.classList.add('editable');
	const editor = getOrCreateAceEditor(codeNode);
	configureAceEditor(editor, detectAceModeFromClass(codeNode));
	if(typeof editor.originalCode === 'undefined') {
		editor.originalCode = editor.getValue();
	}
	return editor;
}

function updateVerificationWidgetMode(containerNode) {
	if (!window.ace) {
		return;
	}

	const editorNode = containerNode.querySelector('[data-ace-editor=true]');
	if (!editorNode || editorNode.dataset.aceInitialized !== 'true') {
		return;
	}

	const editor = getOrCreateAceEditor(editorNode);
	editor.getSession().setMode(detectAceModeFromLanguage(getLanguageExtension($(containerNode))));
}

function initVerificationWidgetEditor(containerNode) {
	if (!window.ace) {
		return null;
	}

	const textArea = containerNode.querySelector('textarea[name=examplecode]');
	const editorNode = containerNode.querySelector('[data-ace-editor=true]');
	if (!textArea || !editorNode) {
		return null;
	}

	editorNode.style.display = 'block';
	const editor = getOrCreateAceEditor(editorNode);
	if (editorNode.dataset.aceInitialized !== 'true') {
		editor.setValue(textArea.value, -1);
		configureAceEditor(editor, 'ace/mode/text');
		editor.getSession().on('change', function() {
			textArea.value = editor.getValue();
		});
		editor.originalCode = editor.getValue();
		editorNode.dataset.aceInitialized = 'true';
	}

	textArea.style.display = 'none';
	textArea.value = editor.getValue();
	updateVerificationWidgetMode(containerNode);
	return editor;
}

function decodeBase64Utf8(value) {
	try {
		return atob(value);
	} catch (err) {
		console.log(err);
		return '';
	}
}

if (window.location.pathname.startsWith('/wiki/')) {
	// Let mdBook's own editor.js render line numbers on editable playground blocks.
	window.playground_line_numbers = true;
}

function shouldAutoInitPlaygroundAce() {
	// mdBook pages already provide syntax highlighting for static code blocks.
	// Avoid eagerly replacing them with Ace editors there.
	// return !window.location.pathname.startsWith('/wiki-mdbook/');
	return true;
}

function initVerificationPlaygroundEditors() {
	if (!shouldAutoInitPlaygroundAce()) {
		return;
	}

	$('.verification-container pre.playground code.editable').each(function () {
		initAceOnDemand(this);
	});
}

function initVerificationWidgetEditors() {
	$('.verification-container').each(function () {
		const container = $(this);
		if (container.find('[data-ace-editor=true][data-ace-auto-init=true]').length) {
			initVerificationWidgetEditor(this);
		}

		container.find('[name=lang]').change(() => {
			updateVerificationWidgetMode(this);
		});
	});
}

if(window.location.pathname === "/" && window.location.hash.startsWith("#")) {
	window.location.href = '/wiki/#' + window.location.hash.substring(1);
}

(function($) {
	$(function() {
		$('.data-table').each(function () {
			let self = $(this);
			let count = self.find('th').length;
			self.DataTable({
				lengthMenu: [[50, -1], [50, "All"]],
				columns: Array(count - 1).fill(null).concat([{orderable: false}]),
			});
		});

		initVerificationWidgetEditors();
		initVerificationPlaygroundEditors();

		$('.code-edit-button').click(function () {
			const root = $(this).closest('.verification-container');
			const widgetEditor = initVerificationWidgetEditor(root.get(0));
			if (widgetEditor) {
				root.find('.verification-text').hide();
				root.find('.verification-non-plain').show();
				root.find('.plain-close').show();
				widgetEditor.focus();
				return;
			}

			const codeNode = root.find('pre.playground code').get(0);
			if (codeNode) {
				const editor = initAceOnDemand(codeNode);
				if (editor) {
					editor.focus();
				}
				return;
			}
		});

		$('.code-close-button').click(function () {
			const root = $(this).closest('.verification-container');
			root.find('.verification-non-plain').hide();
			root.find('.verification-text').show();
			root.find('.plain-close').hide();
		});

		const sideMenu = $('.wiki-side-menu');
		sideMenu.addClass('wiki-side-menu-js');
	});

	$(window).load(function() {
		initVerificationPlaygroundEditors();

		const sideMenu = $('.wiki-side-menu');
		let offsets = [];
		sideMenu.find('a').each(function() {
			let self = $(this);
			offsets.push({
				y: $('#' + self.attr('href').split('#')[1]).offset().top,
				obj: self,
			});
		});

		const $window = $(window);

		$window.scroll(function() {
			const center = $window.scrollTop() + $window.height() / 2;
			sideMenu.find('.focus').removeClass('focus');

			let toSet = null;

			for(let {y, obj} of offsets) {
				if(y < center) {
					toSet = obj;
				}
			}

			if(toSet) {
				toSet.addClass('focus');
				toSet.parent('li').parent('ul').siblings('a').addClass('focus');
			}
		});

		$window.scroll();
	});
})(jQuery);