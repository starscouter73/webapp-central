(function () {
  'use strict';

  if (!document.querySelector('.calendar-page')) return;

  var layout = document.querySelector('.calendar-primary-layout');
  var overview = document.querySelector('.calendar-overview-panel');
  var form = document.querySelector('.calendar-panel');
  var toolbar = document.querySelector('.calendar-overview-toolbar');
  var statusLine = document.getElementById('voice-status');
  var titleInput = document.getElementById('event-title');
  var editId = document.getElementById('event-edit-id');

  if (!layout || !overview || !form || !toolbar) return;

  document.documentElement.classList.add('calendar-priority-mode');

  var css = document.createElement('style');
  css.textContent = '.calendar-priority-mode .calendar-primary-layout{display:block}.calendar-priority-mode .calendar-overview-panel{width:100%}.calendar-priority-mode .calendar-panel{display:none;margin-top:18px}.calendar-priority-mode .calendar-panel.is-entry-open{display:block}.calendar-entry-action-row{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:14px 0 0}.calendar-entry-hint{color:var(--muted);font-size:13px}';
  document.head.appendChild(css);

  var actionRow = document.createElement('div');
  actionRow.className = 'calendar-entry-action-row';

  var openButton = document.createElement('button');
  openButton.className = 'btn btn-primary';
  openButton.type = 'button';
  openButton.textContent = 'Neuen Termin eintragen / verwalten';

  var hint = document.createElement('span');
  hint.className = 'calendar-entry-hint';
  hint.textContent = 'Die chronologische Liste bleibt frei. Das Formular erscheint nur bei Bedarf.';

  actionRow.appendChild(openButton);
  actionRow.appendChild(hint);
  toolbar.insertAdjacentElement('afterend', actionRow);

  function openForm(message) {
    form.classList.add('is-entry-open');
    openButton.textContent = 'Formular ausblenden';
    openButton.className = 'btn btn-secondary';

    if (message && statusLine) {
      statusLine.textContent = message;
    }

    window.setTimeout(function () {
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (titleInput && !titleInput.value) {
        titleInput.focus();
      }
    }, 40);
  }

  function closeForm() {
    form.classList.remove('is-entry-open');
    openButton.textContent = 'Neuen Termin eintragen / verwalten';
    openButton.className = 'btn btn-primary';
  }

  openButton.addEventListener('click', function () {
    if (form.classList.contains('is-entry-open')) {
      closeForm();
    } else {
      openForm('Formular geoeffnet.');
    }
  });

  document.addEventListener('click', function (event) {
    var target = event.target;

    if (!(target instanceof Element)) {
      return;
    }

    if (target.matches('#event-cancel')) {
      window.setTimeout(closeForm, 120);
    }

    if (target.matches('#event-save')) {
      window.setTimeout(function () {
        if (!editId || !editId.value) {
          closeForm();
        }
      }, 260);
    }

    if ((target.closest('.overview-item-actions') || target.closest('.agenda-actions')) && /Bearbeiten/i.test(target.textContent || '')) {
      openForm('Bearbeitungsformular geoeffnet.');
    }
  }, true);
}());
