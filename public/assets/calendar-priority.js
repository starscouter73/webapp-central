(function () {
  'use strict';

  if (!document.querySelector('.calendar-page')) return;

  var storageKey = 'webapp-central-calendar-events';
  var layout = document.querySelector('.calendar-primary-layout');
  var overview = document.querySelector('.calendar-overview-panel');
  var form = document.querySelector('.calendar-panel');
  var toolbar = document.querySelector('.calendar-overview-toolbar');
  var statusLine = document.getElementById('voice-status');
  var titleInput = document.getElementById('event-title');
  var editId = document.getElementById('event-edit-id');
  var visualPanel = document.getElementById('calendar-visual-panel');
  var gridViewport = document.getElementById('calendar-grid-viewport');
  var selectedPanel = document.getElementById('selected-panel');
  var monthButton = document.getElementById('view-month');
  var weekButton = document.getElementById('view-week');

  if (!layout || !overview || !form || !toolbar) return;

  document.documentElement.classList.add('calendar-priority-mode');

  var css = document.createElement('style');
  css.textContent = '.calendar-priority-mode .calendar-primary-layout{display:block}.calendar-priority-mode .calendar-overview-panel{width:100%}.calendar-priority-mode .calendar-panel{display:none;margin-top:18px}.calendar-priority-mode .calendar-panel.is-entry-open{display:block}.calendar-entry-action-row,.calendar-import-export-row{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:14px 0 0}.calendar-entry-hint,.calendar-import-export-hint{color:var(--muted);font-size:13px;line-height:1.45}.calendar-import-file{position:absolute;left:-9999px;width:1px;height:1px;opacity:0}.calendar-priority-mode #calendar-visual-panel{display:block!important;margin:12px 0 18px}.calendar-priority-mode #calendar-visual-panel[hidden]{display:block!important}.calendar-priority-mode #calendar-grid-viewport{width:100%}.calendar-priority-mode .calendar-weekdays{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:10px;margin-bottom:6px}.calendar-priority-mode .calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:10px}.calendar-priority-mode .calendar-day{min-height:92px;padding:12px 12px 10px;overflow:hidden}.calendar-priority-mode .calendar-day strong{font-size:16px}.calendar-priority-mode .calendar-day span{font-size:13px}.calendar-priority-mode .calendar-week-summary{margin-top:26px;display:block;max-height:38px;overflow:hidden}.calendar-priority-mode .calendar-week-summary-item{display:block;padding:5px 8px;border-radius:10px;background:rgba(0,0,0,.05);font-size:12px;line-height:1.15;white-space:normal}.calendar-priority-mode #selected-panel{display:none!important}@media(max-width:900px){.calendar-priority-mode .calendar-weekdays,.calendar-priority-mode .calendar-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.calendar-priority-mode .calendar-day{min-height:86px}}';
  document.head.appendChild(css);

  if (visualPanel && overview) {
    overview.insertAdjacentElement('beforebegin', visualPanel);
    visualPanel.hidden = false;
  }

  if (selectedPanel) {
    selectedPanel.hidden = true;
  }

  if (weekButton) {
    window.setTimeout(function () { weekButton.click(); }, 80);
  }

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

  var importExportRow = document.createElement('div');
  importExportRow.className = 'calendar-import-export-row';

  var importInput = document.createElement('input');
  importInput.className = 'calendar-import-file';
  importInput.type = 'file';
  importInput.accept = '.json,application/json';

  var importButton = document.createElement('button');
  importButton.className = 'btn btn-secondary';
  importButton.type = 'button';
  importButton.textContent = 'JSON importieren';

  var exportButton = document.createElement('button');
  exportButton.className = 'btn btn-ghost';
  exportButton.type = 'button';
  exportButton.textContent = 'JSON exportieren';

  var importHint = document.createElement('span');
  importHint.className = 'calendar-import-export-hint';
  importHint.textContent = 'Import/Export nutzt das Webapp-Kalenderformat und synchronisiert danach mit dem Server.';

  importExportRow.appendChild(importInput);
  importExportRow.appendChild(importButton);
  importExportRow.appendChild(exportButton);
  importExportRow.appendChild(importHint);
  actionRow.insertAdjacentElement('afterend', importExportRow);

  function readEvents() {
    try {
      var parsed = JSON.parse(window.localStorage.getItem(storageKey) || '[]');
      return Array.isArray(parsed) ? parsed.filter(normalizeEvent) : [];
    } catch (error) {
      return [];
    }
  }

  function normalizeEvent(eventItem) {
    if (!eventItem || typeof eventItem !== 'object') return null;
    var title = String(eventItem.title || '').trim();
    var date = String(eventItem.date || '').trim();
    if (!title || !date) return null;
    return {
      id: String(eventItem.id || ('import-' + Date.now() + '-' + Math.random().toString(16).slice(2))).trim(),
      title: title,
      date: date,
      time: String(eventItem.time || '').trim(),
      address: String(eventItem.address || '').trim(),
      note: String(eventItem.note || '').trim(),
      updatedAt: String(eventItem.updatedAt || new Date().toISOString()).trim()
    };
  }

  function writeEvents(events) {
    var normalized = events.map(normalizeEvent).filter(Boolean);
    window.localStorage.setItem(storageKey, JSON.stringify(normalized));
    return fetch('/calendar.php?api=events', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ events: normalized })
    }).then(function () {
      window.location.reload();
    }).catch(function () {
      window.location.reload();
    });
  }

  function mergeEvents(currentEvents, importEvents) {
    var byId = {};
    currentEvents.forEach(function (item) {
      if (item && item.id) byId[item.id] = item;
    });
    importEvents.forEach(function (item) {
      if (item && item.id) byId[item.id] = item;
    });
    return Object.keys(byId).map(function (id) { return byId[id]; });
  }

  importButton.addEventListener('click', function () {
    importInput.click();
  });

  importInput.addEventListener('change', function () {
    var file = importInput.files && importInput.files[0];
    if (!file) return;

    var reader = new FileReader();
    reader.onload = function () {
      try {
        var parsed = JSON.parse(String(reader.result || '[]'));
        var incoming = Array.isArray(parsed) ? parsed : (Array.isArray(parsed.events) ? parsed.events : []);
        var normalizedImport = incoming.map(normalizeEvent).filter(Boolean);
        if (!normalizedImport.length) throw new Error('empty');
        var merged = mergeEvents(readEvents(), normalizedImport);
        if (statusLine) statusLine.textContent = normalizedImport.length + ' Termine importiert. Kalender wird aktualisiert.';
        writeEvents(merged);
      } catch (error) {
        if (statusLine) statusLine.textContent = 'Import fehlgeschlagen. Bitte JSON-Datei im Webapp-Kalenderformat pruefen.';
      }
    };
    reader.readAsText(file, 'utf-8');
  });

  exportButton.addEventListener('click', function () {
    var data = JSON.stringify(readEvents(), null, 2);
    var blob = new Blob([data], { type: 'application/json;charset=utf-8' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'webapp-central-calendar-export.json';
    link.click();
    window.setTimeout(function () { URL.revokeObjectURL(link.href); }, 1000);
    if (statusLine) statusLine.textContent = 'Kalenderexport wurde erstellt.';
  });

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
