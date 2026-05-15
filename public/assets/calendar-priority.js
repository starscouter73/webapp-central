(function () {
  'use strict';

  if (!document.querySelector('.calendar-page')) return;

  var layout = document.querySelector('.calendar-primary-layout');
  var overview = document.querySelector('.calendar-overview-panel');
  var form = document.querySelector('.calendar-panel');
  var toolbar = document.querySelector('.calendar-overview-toolbar');
  var mainToolbar = document.querySelector('.calendar-toolbar');
  var statusLine = document.getElementById('voice-status');
  var titleInput = document.getElementById('event-title');
  var editId = document.getElementById('event-edit-id');

  if (!layout || !overview || !form || !toolbar) return;

  document.documentElement.classList.add('calendar-priority-mode');

  var css = document.createElement('style');
  css.textContent = '.calendar-priority-mode .calendar-primary-layout{display:block}.calendar-priority-mode .calendar-overview-panel{width:100%}.calendar-priority-mode .calendar-panel{display:none;margin-top:18px}.calendar-priority-mode .calendar-panel.is-entry-open{display:block}.calendar-entry-action-row{display:flex;flex-wrap:wrap;gap:10px;align-items:center;margin:14px 0 0}.calendar-entry-hint{color:var(--muted);font-size:13px}.calendar-live-ticker{overflow:hidden;border:1px solid rgba(124,156,255,.24);background:rgba(124,156,255,.08);border-radius:999px;margin:14px 0 18px;padding:9px 0;white-space:nowrap}.calendar-live-ticker-track{display:inline-block;padding-left:100%;animation:calendarTicker 38s linear infinite;font-size:13px;font-weight:700}.calendar-live-dot{display:inline-block;width:9px;height:9px;border-radius:999px;background:#3ddc97;margin:0 8px 0 16px}@keyframes calendarTicker{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}';
  document.head.appendChild(css);

  var ticker = document.createElement('div');
  ticker.className = 'calendar-live-ticker';
  var tickerTrack = document.createElement('div');
  tickerTrack.className = 'calendar-live-ticker-track';
  ticker.appendChild(tickerTrack);

  function tickerText() {
    var time = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    var formState = form.classList.contains('is-entry-open') ? 'Formular sichtbar' : 'Formular eingeklappt';
    var editState = editId && editId.value ? 'Bearbeitung aktiv' : 'Listenmodus aktiv';
    return 'LIVE STATUS ' + time + ' • Kalenderliste priorisiert • ' + formState + ' • ' + editState + ' • Neuer Termin ueber Button oberhalb der Liste • Mapvorschau und Spracheingabe bleiben erhalten';
  }

  function updateTicker() {
    tickerTrack.innerHTML = '<span class="calendar-live-dot"></span>' + tickerText();
  }

  updateTicker();
  window.setInterval(updateTicker, 300000);

  if (mainToolbar) {
    mainToolbar.insertAdjacentElement('afterend', ticker);
  } else {
    overview.insertAdjacentElement('beforebegin', ticker);
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

  function openForm(message) {
    form.classList.add('is-entry-open');
    openButton.textContent = 'Formular ausblenden';
    openButton.className = 'btn btn-secondary';
    updateTicker();
    if (message && statusLine) statusLine.textContent = message;
    window.setTimeout(function () {
      form.scrollIntoView({ behavior: 'smooth', block: 'start' });
      if (titleInput && !titleInput.value) titleInput.focus();
    }, 40);
  }

  function closeForm() {
    form.classList.remove('is-entry-open');
    openButton.textContent = 'Neuen Termin eintragen / verwalten';
    openButton.className = 'btn btn-primary';
    updateTicker();
  }

  openButton.addEventListener('click', function () {
    if (form.classList.contains('is-entry-open')) closeForm();
    else openForm('Formular geoeffnet.');
  });

  document.addEventListener('click', function (event) {
    var target = event.target;
    if (!(target instanceof Element)) return;
    if (target.matches('#event-cancel')) window.setTimeout(closeForm, 120);
    if (target.matches('#event-save')) window.setTimeout(function () { if (!editId || !editId.value) closeForm(); }, 260);
    if ((target.closest('.overview-item-actions') || target.closest('.agenda-actions')) && /Bearbeiten/i.test(target.textContent || '')) openForm('Bearbeitungsformular geoeffnet.');
  }, true);
}());
