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
  if (explicit) {
    return explicit;
  }

  const selected = container.find('[name=lang]').val();
  if (selected) {
    return selected;
  }

  const codeNode = container.find('pre.playground code').first();
  if (codeNode.length) {
    const className = codeNode.attr('class') || '';
    const match = className.match(/(?:^|\s)language-([a-zA-Z0-9_+-]+)/);
    if (match) {
      return match[1];
    }
  }

  return 'pvl';
}

function syncVerificationEditorMode(container) {
  if (!window.ace) {
    return;
  }

  const codeNode = container.find('pre.playground code').first();
  if (!codeNode.length) {
    return;
  }

  const editor = getOrCreateAceEditor(codeNode.get(0));
  editor.getSession().setMode(detectAceModeFromLanguage(getLanguageExtension(container)));
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

  if (fullCodeBase64 && !(window.ace && container.find('pre.playground code').first().hasClass('editable'))) {
    try {
      return atob(fullCodeBase64);
    } catch (err) {
      console.log(err);
    }
  }

  const codeNode = container.find('pre.playground code').first();
  if (codeNode.length) {
    if (window.ace && codeNode.hasClass('editable')) {
      try {
        const editor = window.ace.edit(codeNode.get(0));
        const snippetCode = editor.getValue();
        if (templateKind && typeof editor.originalCode !== 'undefined' && snippetCode !== editor.originalCode) {
          return renderTemplateCase(templateKind, caseName, verdict, getLanguageExtension(container), snippetCode);
        }
        if (fullCodeBase64) {
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

  const resetRunState = function () {
    self.data('verificationRunning', false);
    self.removeData('verificationSocket');
    setRunButtonRunning(button, false);
  };

  ws.onerror = function (err) {
    progress.text('An error occurred: cannot connect to verification server');
    resetRunState();
    console.log(err);
  };

  ws.onmessage = function (e) {
    try {
      var message = JSON.parse(e.data);

      switch (message.type) {
        case 'error':
          setProgress(progress, 'An error occurred: ' + message.errorDescription, 'times');
          ws.close();
          resetRunState();
          break;
        case 'stdout':
        case 'stderr':
          var parts = message.data.split("\n");
          for (var i = 0; i < parts.length; i++) {
            const line = parts[i].trim();
            if (line === '') {
              continue;
            }

            if (PROGRESS_RE.test(line)) {
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
    } catch (err) {
      setProgress(progress, 'An error occurred: ' + err, 'times');
      resetRunState();
      console.log(err);
    }
  };

  ws.onclose = function () {
    resetRunState();
  };

  ws.onopen = function (e) {
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

function playground_text(playground, hidden = true) {
    const code_block = playground.querySelector('code');

    if (window.ace && code_block.classList.contains('editable')) {
        const editor = window.ace.edit(code_block);
        return editor.getValue();
    } else if (hidden) {
        return code_block.textContent;
    } else {
        return code_block.innerText;
    }
}

function clipboard() {
    const clipButtons = document.querySelectorAll('.clip-button');

    function hideTooltip(elem) {
        elem.firstChild.innerText = '';
        elem.className = 'clip-button';
    }

    function showTooltip(elem, msg) {
        elem.firstChild.innerText = msg;
        elem.className = 'clip-button tooltipped';
    }

    const clipboardSnippets = new ClipboardJS('.clip-button', {
        text: function(trigger) {
            hideTooltip(trigger);
            const playground = trigger.closest('pre');
            return playground_text(playground, false);
        },
    });

    Array.from(clipButtons).forEach(function(clipButton) {
        clipButton.addEventListener('mouseout', function(e) {
            hideTooltip(e.currentTarget);
        });
    });

    clipboardSnippets.on('success', function(e) {
        e.clearSelection();
        showTooltip(e.trigger, 'Copied!');
    });

    clipboardSnippets.on('error', function(e) {
        showTooltip(e.trigger, 'Clipboard error!');
    });
}

// Process playground code blocks
function addButtons(playground_copyable = true) {
  $(document)
    .off('change.vercorsonline', '.verification-container [name=lang]')
    .on('change.vercorsonline', '.verification-container [name=lang]', function () {
      syncVerificationEditorMode($(this).closest('.verification-container'));
    });

  if (playground_copyable) {
        Array.from(document.querySelectorAll('pre code')).forEach(function(block) {
            const pre_block = block.parentNode;
            if (!pre_block.classList.contains('playground')) {
                let buttons = pre_block.querySelector('.buttons');
                if (!buttons) {
                    buttons = document.createElement('div');
                    buttons.className = 'buttons';
                    pre_block.insertBefore(buttons, pre_block.firstChild);
                }

                const clipButton = document.createElement('button');
                clipButton.className = 'clip-button';
                clipButton.title = 'Copy to clipboard';
                clipButton.setAttribute('aria-label', clipButton.title);
                clipButton.innerHTML = '<i class="tooltiptext"></i>';

                buttons.insertBefore(clipButton, buttons.firstChild);
            }
        });
    }

  Array.from(document.querySelectorAll('.playground')).forEach(function (pre_block) {
    // Add play button
    let buttons = pre_block.querySelector('.buttons');
    if (!buttons) {
      buttons = document.createElement('div');
      buttons.className = 'buttons';
      pre_block.insertBefore(buttons, pre_block.firstChild);
    }

    const runCodeButton = document.createElement('button');
    runCodeButton.className = 'play-button';
    runCodeButton.hidden = false;
    runCodeButton.title = 'Verify this code';
    runCodeButton.setAttribute('aria-label', runCodeButton.title);
    runCodeButton.innerHTML = document.getElementById('fa-play').innerHTML;

    buttons.insertBefore(runCodeButton, buttons.firstChild);
    runCodeButton.addEventListener('click', () => {
      verify_code(runCodeButton);
    });

    
    if(playground_copyable) {
        const copyCodeClipboardButton = document.createElement('button');
        copyCodeClipboardButton.className = 'clip-button';
        copyCodeClipboardButton.innerHTML = '<i class="tooltiptext"></i>';
        copyCodeClipboardButton.title = 'Copy to clipboard';
        copyCodeClipboardButton.setAttribute('aria-label', copyCodeClipboardButton.title);

        buttons.insertBefore(copyCodeClipboardButton, buttons.firstChild);
    }
    const code_block = pre_block.querySelector('code');
    if (window.ace && code_block.classList.contains('editable')) {
      const undoChangesButton = document.createElement('button');
      undoChangesButton.className = 'reset-button';
      undoChangesButton.title = 'Undo changes';
      undoChangesButton.setAttribute('aria-label', undoChangesButton.title);
      undoChangesButton.innerHTML +=
        document.getElementById('fa-clock-rotate-left').innerHTML;

      buttons.insertBefore(undoChangesButton, buttons.firstChild);

      undoChangesButton.addEventListener('click', function () {
        const editor = window.ace.edit(code_block);
        editor.setValue(editor.originalCode);
        editor.clearSelection();
      });
    }
  });
  
  clipboard();
}