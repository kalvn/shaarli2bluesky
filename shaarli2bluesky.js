(function () {

var linkForms = document.querySelectorAll('[name="linkform"]');

linkForms.forEach(function (linkForm) {
  var privateInput = linkForm.querySelector('[name="lf_private"]');

  var blueskyInput = linkForm.querySelector('[name="shaarli2bluesky-message"]');
  var blueskyButton = linkForm.querySelector('.shaarli2bluesky-button');
  var blueskyConfigure = linkForm.querySelector('.shaarli2bluesky-configure');
  var blueskyPreview = linkForm.querySelector('.shaarli2bluesky-preview');
  var reactiveFields = linkForm.querySelectorAll('input, textarea');

  // Disables publication if private flag is selected.
  privateInput.addEventListener('click', function (event) {
    if (!blueskyInput) {
      return;
    }

    blueskyInput.disabled = privateInput.checked;
  });

  // Toggles configuration panel.
  blueskyButton.addEventListener('click', function (event) {
    renderPreview(linkForm);
    blueskyConfigure.classList.toggle('shaarli2bluesky-hidden');
  });

  // Updates preview when something changes.
  var timeout;
  ['change', 'keyup'].forEach(function (event) {
    reactiveFields.forEach(function (field) {
      field.addEventListener(event, function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
          renderPreview(linkForm);
        }, 500);
      });
    });
  });
});

var placeholders = [
  'url',
  'permalink',
  'title',
  'tags',
  'description'
];

function renderPreview (linkForm) {
  var link = {
    'url': linkForm.querySelector('[name="lf_url"]').value,
    'permalink': '<permalink>',
    'title': linkForm.querySelector('[name="lf_title"]').value,
    'description': linkForm.querySelector('[name="lf_description"]').value,
    'tags': linkForm.querySelector('[name="lf_tags"]').value,
  };

  var format = linkForm.querySelector('[name="shaarli2bluesky-format"]').value;
  var tagsSeparator = linkForm.querySelector('.shaarli2bluesky-parameter-tags-separator').innerText;
  var isNote = linkForm.querySelector('.shaarli2bluesky-parameter-is-note').innerText === 'true';

  if (isNote) {
    link.url = link.permalink;
  }

  link['tags'] = tagify(link['tags'], tagsSeparator);
  link['description'] = link['description'].replace(/\n/g, '\\n');

  var output = format;
  for (i in placeholders) {
    var placeholder = placeholders[i];

    output = output.replace(new RegExp('\\$\\{' + placeholder + '\\}', 'g'), escapeHtml(link[placeholder]));
  }

  output = output.replace(/\\n/g, '<br>');

  linkForm.querySelector('.shaarli2bluesky-preview').innerHTML = output;
}

function tagify (tags, tagsSeparator) {
  var parts = tags.trim().split(tagsSeparator);
  var output = [];

  for (i in parts) {
    if (parts[i].length > 0) {
      output.push('#' + parts[i].replace(/[^0-9_\p{L}]/gu, ''));
    }
  }

  return output.join(' ');
}

function escapeHtml (text) {
  return text.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '"');
}

})();
