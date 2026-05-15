<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

if (isset($_GET['api']) && $_GET['api'] === 'events') {
    $storageFile = app_calendar_storage_file();
    $storageDir = dirname($storageFile);

    $sendJson = static function (array $payload, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    };

    $normalizeEvent = static function (array $event): ?array {
        $id = trim((string)($event['id'] ?? ''));
        $title = trim((string)($event['title'] ?? ''));
        $date = trim((string)($event['date'] ?? ''));
        $time = trim((string)($event['time'] ?? ''));
        $address = trim((string)($event['address'] ?? ''));
        $note = trim((string)($event['note'] ?? ''));
        $updatedAt = trim((string)($event['updatedAt'] ?? ''));

        if ($id === '' || $title === '' || $date === '') {
            return null;
        }

        return [
            'id' => $id,
            'title' => $title,
            'date' => $date,
            'time' => $time,
            'address' => $address,
            'note' => $note,
            'updatedAt' => $updatedAt !== '' ? $updatedAt : gmdate('c'),
        ];
    };

    if (!is_dir($storageDir) && !@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
        $sendJson(['ok' => false, 'message' => 'Speicherverzeichnis konnte nicht erstellt werden.'], 500);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!is_file($storageFile)) {
            $sendJson(['events' => []]);
        }

        $raw = file_get_contents($storageFile);
        $decoded = json_decode((string)$raw, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $events = [];
        foreach ($decoded as $event) {
            if (is_array($event)) {
                $normalized = $normalizeEvent($event);
                if ($normalized !== null) {
                    $events[] = $normalized;
                }
            }
        }

        $sendJson(['events' => $events]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        $decoded = json_decode((string)$rawInput, true);

        if (!is_array($decoded)) {
            $sendJson(['ok' => false, 'message' => 'Ungueltiges JSON.'], 400);
        }

        $incoming = $decoded['events'] ?? $decoded;
        if (!is_array($incoming)) {
            $sendJson(['ok' => false, 'message' => 'Ungueltiges Event-Format.'], 400);
        }

        $events = [];
        foreach ($incoming as $event) {
            if (is_array($event)) {
                $normalized = $normalizeEvent($event);
                if ($normalized !== null) {
                    $events[] = $normalized;
                }
            }
        }

        $written = file_put_contents(
            $storageFile,
            json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );

        if ($written === false) {
            $sendJson(['ok' => false, 'message' => 'Termine konnten nicht gespeichert werden.'], 500);
        }

        $sendJson(['ok' => true, 'count' => count($events)]);
    }

    $sendJson(['ok' => false, 'message' => 'Methode nicht erlaubt.'], 405);
}

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Kalender', 'Termine', static function (): void {
    ?>
    <section class="calendar-page">
      <article class="card calendar-board calendar-board-full">
        <div class="calendar-toolbar">
          <div>
            <span class="card-label">Terminverwaltung</span>
            <h3 id="calendar-month">Chronologische Terminliste</h3>
            <p id="calendar-view-summary">Bestehende Termine zuerst, Monats- und Wochenansicht nur bei Bedarf.</p>
          </div>
          <div class="calendar-toolbar-actions">
            <div class="calendar-view-toggle" role="tablist" aria-label="Ansicht wechseln">
              <button class="btn btn-secondary is-active" id="view-list" type="button">Liste</button>
              <button class="btn btn-ghost" id="view-month" type="button">Monat</button>
              <button class="btn btn-ghost" id="view-week" type="button">Woche</button>
            </div>
            <div class="button-row" id="calendar-range-actions">
            <button class="btn btn-ghost" id="calendar-prev" type="button">Zurueck</button>
            <button class="btn btn-secondary" id="calendar-today" type="button">Heute</button>
            <button class="btn btn-ghost" id="calendar-next" type="button">Weiter</button>
            </div>
          </div>
        </div>
        <div class="calendar-primary-layout">
          <article class="calendar-subpanel calendar-overview-panel">
            <div class="calendar-overview-toolbar">
              <div>
                <span class="card-label">Termine</span>
                <h3 id="overview-title">Anstehende Termine</h3>
                <p id="overview-summary">Die naechsten Termine werden geladen.</p>
              </div>
              <div class="button-row">
                <button class="btn btn-secondary" id="overview-pdf" type="button">PDF exportieren</button>
                <button class="btn btn-secondary" id="overview-print" type="button">Uebersicht drucken</button>
              </div>
            </div>
            <label class="field calendar-search-field">
              <span>Termin-Suche</span>
              <input id="overview-search" type="search" placeholder="Nach Titel, Adresse, Notiz oder Datum suchen">
            </label>
            <div class="calendar-overview-metrics" id="overview-metrics"></div>
            <div class="calendar-overview-list" id="overview-list">
              <p class="empty-state">Noch keine Termine vorhanden.</p>
            </div>
          </article>

          <article class="calendar-subpanel calendar-panel">
            <span class="card-label" id="event-form-label">Neuer Termin</span>
            <h3 id="event-form-title">Per Sprache oder manuell eintragen</h3>
            <p>Sage zum Beispiel: "Morgen 14 Uhr Zahnarzt in Winterberg". Du kannst Termine schnell in einem Satz erfassen oder dich gefuehrt Feld fuer Feld durchsprechen lassen.</p>

            <div class="calendar-form">
              <input id="event-edit-id" name="edit-id" type="hidden" value="">
              <label class="field">
                <span>Titel</span>
                <input id="event-title" name="title" type="text" placeholder="Terminname">
              </label>
              <div class="calendar-form-row">
                <label class="field">
                  <span>Datum</span>
                  <input id="event-date" name="date" type="date">
                </label>
                <label class="field">
                  <span>Uhrzeit</span>
                  <input id="event-time" name="time" type="time">
                </label>
              </div>
              <label class="field">
                <span>Adresse</span>
                <input id="event-address" name="address" type="text" placeholder="Ort oder Adresse fuer Karte">
              </label>
              <div class="map-preview" id="event-map-preview" hidden>
                <strong>Kartenvorschau</strong>
                <iframe
                  id="event-map-frame"
                  title="Kartenvorschau"
                  loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
              </div>
              <label class="field">
                <span>Notiz</span>
                <textarea id="event-note" name="note" rows="3" placeholder="Optionale Details"></textarea>
              </label>
              <div class="button-row">
                <button class="btn btn-primary" id="voice-quick" type="button">Schnell per Satz</button>
                <button class="btn btn-secondary" id="voice-guided" type="button">Gefuehrt per Feld</button>
                <button class="btn btn-ghost" id="voice-stop" type="button" hidden>Voice stoppen</button>
              </div>
              <div class="button-row">
                <button class="btn btn-secondary" id="event-save" type="button">Termin speichern</button>
                <button class="btn btn-ghost" id="event-cancel" type="button" hidden>Bearbeitung beenden</button>
              </div>
              <p class="calendar-status" id="voice-status">Sprachmodus bereit. Die Funktion nutzt die Sprachschnittstelle deines Browsers.</p>
              <p class="calendar-transcript" id="voice-transcript" hidden></p>
            </div>

            <div class="calendar-note">
              <strong>Hinweis</strong>
              <p>Die Spracheingabe laeuft direkt im Browser. Termine bleiben aktuell lokal auf diesem Geraet gespeichert. Adressen werden als direkter Karten-Link geoeffnet.</p>
            </div>
          </article>
        </div>

        <section class="calendar-visual-panel" id="calendar-visual-panel" hidden>
          <div class="calendar-grid-viewport" id="calendar-grid-viewport">
            <div class="calendar-weekdays" aria-hidden="true">
              <span>Mo</span>
              <span>Di</span>
              <span>Mi</span>
              <span>Do</span>
              <span>Fr</span>
              <span>Sa</span>
              <span>So</span>
            </div>
            <div class="calendar-grid" id="calendar-grid"></div>
          </div>

          <article class="calendar-subpanel" id="selected-panel">
            <span class="card-label">Auswahl</span>
            <h3 id="selected-date-label">Keine Auswahl</h3>
            <div class="calendar-subpanel-stack">
              <div class="calendar-agenda" id="selected-events">
                <p class="empty-state">Waehle einen Tag im Kalender oder lege direkt einen neuen Termin an.</p>
              </div>
              <div class="calendar-event-focus" id="selected-event-focus" hidden>
                <p class="empty-state">Waehle in der Terminliste einen Eintrag fuer die Detailansicht.</p>
              </div>
            </div>
          </article>
        </section>
      </article>

    </section>

    <script>
      (function () {
        var storageKey = 'webapp-central-calendar-events';
        var monthNames = ['Januar', 'Februar', 'Maerz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        var weekdayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        var query = new URLSearchParams(window.location.search);
        var serverApiUrl = '<?= app_h(app_url('calendar.php?api=events')) ?>';
        var recognition = null;
        var voiceMode = 'idle';
        var guidedVoiceState = null;
        var voiceCaptureActive = false;
        var queuedVoicePrompt = '';
        var persistTimer = null;
        var editingEventId = '';
        var activeView = ['list', 'month', 'week'].indexOf(String(query.get('view') || 'list')) !== -1
          ? String(query.get('view') || 'list')
          : 'list';
        var selectedEventId = query.get('event') || '';
        var detailArmed = selectedEventId !== '';
        var events = loadEvents();
        var now = new Date();
        var selectedDate = query.get('date') || toIsoDate(now);
        var viewYear = now.getFullYear();
        var viewMonth = now.getMonth();

        var monthLabel = document.getElementById('calendar-month');
        var grid = document.getElementById('calendar-grid');
        var gridViewport = document.getElementById('calendar-grid-viewport');
        var visualPanel = document.getElementById('calendar-visual-panel');
        var viewSummary = document.getElementById('calendar-view-summary');
        var selectedPanel = document.getElementById('selected-panel');
        var selectedLabel = document.getElementById('selected-date-label');
        var selectedEvents = document.getElementById('selected-events');
        var selectedEventFocus = document.getElementById('selected-event-focus');
        var overviewTitle = document.getElementById('overview-title');
        var overviewSummary = document.getElementById('overview-summary');
        var overviewMetrics = document.getElementById('overview-metrics');
        var overviewList = document.getElementById('overview-list');
        var overviewSearch = document.getElementById('overview-search');
        var formLabel = document.getElementById('event-form-label');
        var formTitle = document.getElementById('event-form-title');
        var editIdInput = document.getElementById('event-edit-id');
        var titleInput = document.getElementById('event-title');
        var dateInput = document.getElementById('event-date');
        var timeInput = document.getElementById('event-time');
        var addressInput = document.getElementById('event-address');
        var mapPreview = document.getElementById('event-map-preview');
        var mapFrame = document.getElementById('event-map-frame');
        var noteInput = document.getElementById('event-note');
        var quickVoiceButton = document.getElementById('voice-quick');
        var guidedVoiceButton = document.getElementById('voice-guided');
        var stopVoiceButton = document.getElementById('voice-stop');
        var saveButton = document.getElementById('event-save');
        var cancelButton = document.getElementById('event-cancel');
        var status = document.getElementById('voice-status');
        var transcript = document.getElementById('voice-transcript');
        var listViewButton = document.getElementById('view-list');
        var monthViewButton = document.getElementById('view-month');
        var weekViewButton = document.getElementById('view-week');
        var rangeActions = document.getElementById('calendar-range-actions');

        document.getElementById('overview-print').addEventListener('click', function () {
          printAgendaOverview('overview');
        });

        document.getElementById('overview-pdf').addEventListener('click', function () {
          exportAgendaPdf('overview');
        });

        overviewSearch.addEventListener('input', function () {
          renderOverview();
        });

        document.getElementById('calendar-prev').addEventListener('click', function () {
          if (activeView === 'list') {
            return;
          }

          if (activeView === 'week') {
            selectDate(shiftIsoDate(selectedDate, -7));
            return;
          }

          viewMonth -= 1;
          if (viewMonth < 0) {
            viewMonth = 11;
            viewYear -= 1;
          }
          renderCalendar();
        });

        document.getElementById('calendar-next').addEventListener('click', function () {
          if (activeView === 'list') {
            return;
          }

          if (activeView === 'week') {
            selectDate(shiftIsoDate(selectedDate, 7));
            return;
          }

          viewMonth += 1;
          if (viewMonth > 11) {
            viewMonth = 0;
            viewYear += 1;
          }
          renderCalendar();
        });

        document.getElementById('calendar-today').addEventListener('click', function () {
          var today = new Date();
          viewYear = today.getFullYear();
          viewMonth = today.getMonth();
          selectDate(toIsoDate(today));
          if (activeView === 'list') {
            renderOverview();
          }
        });

        saveButton.addEventListener('click', function () {
          saveEventFromForm();
        });

        cancelButton.addEventListener('click', function () {
          clearEditMode('Bearbeitung beendet.');
        });

        addressInput.addEventListener('input', function () {
          renderFormMapPreview(addressInput.value);
        });

        quickVoiceButton.addEventListener('click', function () {
          startQuickVoiceCapture();
        });

        guidedVoiceButton.addEventListener('click', function () {
          startGuidedVoiceCapture();
        });

        stopVoiceButton.addEventListener('click', function () {
          stopVoiceCapture('Sprachmodus beendet.');
        });
        listViewButton.addEventListener('click', function () {
          setActiveView('list');
        });
        monthViewButton.addEventListener('click', function () {
          setActiveView('month');
        });

        weekViewButton.addEventListener('click', function () {
          setActiveView('week');
        });

        dateInput.value = selectedDate;
        renderOverview();
        renderCalendar();
        renderSelectedDay();
        syncEventsFromServer();

        function loadEvents() {
          try {
            var raw = window.localStorage.getItem(storageKey);
            if (!raw) {
              return [];
            }

            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed.map(normalizeEventShape).filter(Boolean) : [];
          } catch (error) {
            return [];
          }
        }

        function persistEvents() {
          events = events.map(normalizeEventShape).filter(Boolean);
          window.localStorage.setItem(storageKey, JSON.stringify(events));
          scheduleServerPersist();
        }

        function normalizeEventShape(eventItem) {
          if (!eventItem || typeof eventItem !== 'object') {
            return null;
          }

          var id = String(eventItem.id || '').trim();
          var title = String(eventItem.title || '').trim();
          var date = String(eventItem.date || '').trim();
          var time = String(eventItem.time || '').trim();
          var address = String(eventItem.address || '').trim();
          var note = String(eventItem.note || '').trim();
          var updatedAt = String(eventItem.updatedAt || '').trim();

          if (!id || !title || !date) {
            return null;
          }

          return {
            id: id,
            title: title,
            date: date,
            time: time,
            address: address,
            note: note,
            updatedAt: updatedAt || new Date().toISOString()
          };
        }

        function mergeEvents(localEvents, remoteEvents) {
          var merged = {};

          localEvents.forEach(function (item) {
            var normalized = normalizeEventShape(item);
            if (normalized) {
              merged[normalized.id] = normalized;
            }
          });

          remoteEvents.forEach(function (item) {
            var normalized = normalizeEventShape(item);
            var known = normalized ? merged[normalized.id] : null;

            if (!normalized) {
              return;
            }

            if (!known) {
              merged[normalized.id] = normalized;
              return;
            }

            if (String(normalized.updatedAt || '') >= String(known.updatedAt || '')) {
              merged[normalized.id] = normalized;
            }
          });

          return Object.keys(merged).map(function (id) {
            return merged[id];
          });
        }

        function buildEventSignature(collection) {
          return getSortedEvents((Array.isArray(collection) ? collection : []).map(normalizeEventShape).filter(Boolean))
            .map(function (eventItem) {
              return [
                eventItem.id,
                eventItem.date,
                eventItem.time,
                eventItem.title,
                eventItem.address,
                eventItem.note,
                eventItem.updatedAt
              ].join('|');
            })
            .join('||');
        }

        function scheduleServerPersist() {
          if (persistTimer) {
            window.clearTimeout(persistTimer);
          }

          persistTimer = window.setTimeout(function () {
            persistTimer = null;
            persistEventsToServer();
          }, 240);
        }

        function persistEventsToServer() {
          fetch(serverApiUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ events: events })
          }).catch(function () {
            status.textContent = 'Hinweis: Termin lokal gespeichert, Serversync gerade nicht erreichbar.';
          });
        }

        function syncEventsFromServer() {
          fetch(serverApiUrl, { cache: 'no-store' })
            .then(function (response) {
              if (!response.ok) {
                throw new Error('server');
              }
              return response.json();
            })
            .then(function (payload) {
              var remoteEvents = Array.isArray(payload.events) ? payload.events : [];
              var mergedEvents = mergeEvents(events, remoteEvents);
              var remoteSignature = buildEventSignature(remoteEvents);
              var mergedSignature = buildEventSignature(mergedEvents);

              if (!mergedEvents.length && !events.length) {
                return;
              }

              events = mergedEvents;
              window.localStorage.setItem(storageKey, JSON.stringify(events));
              renderOverview();
              renderCalendar();
              renderSelectedDay();

              if (mergedSignature !== remoteSignature) {
                scheduleServerPersist();
              }
            })
            .catch(function () {
              // Offline/Server down: local data remains source of truth.
            });
        }

        function renderCalendar() {
          syncViewToggle();

          if (activeView === 'list') {
            monthLabel.textContent = 'Chronologische Terminliste';
            viewSummary.textContent = 'Bestehende Termine zuerst, Monats- und Wochenansicht nur bei Bedarf.';
            visualPanel.hidden = true;
            return;
          }

          visualPanel.hidden = false;

          if (activeView === 'week') {
            renderWeekCalendar();
            return;
          }

          monthLabel.textContent = monthNames[viewMonth] + ' ' + viewYear;
          viewSummary.textContent = 'Monatsansicht zur Orientierung. Die Verwaltung bleibt in der Terminliste.';
          grid.innerHTML = '';

          var firstDay = new Date(viewYear, viewMonth, 1);
          var startOffset = (firstDay.getDay() + 6) % 7;
          var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
          var daysInPreviousMonth = new Date(viewYear, viewMonth, 0).getDate();
          var totalCells = Math.ceil((startOffset + daysInMonth) / 7) * 7;

          for (var index = 0; index < totalCells; index += 1) {
            var dayNumber = index - startOffset + 1;
            var cellDate = new Date(viewYear, viewMonth, dayNumber);
            var isoDate = toIsoDate(cellDate);
            var isCurrentMonth = dayNumber >= 1 && dayNumber <= daysInMonth;
            var isToday = isoDate === toIsoDate(new Date());
            var dayEvents = getEventsForDate(isoDate);
            var button = document.createElement('button');
            var dayLabel = document.createElement('strong');
            var meta = document.createElement('span');
            var dots = document.createElement('div');

            button.type = 'button';
            button.className = 'calendar-day';
            if (!isCurrentMonth) {
              button.className += ' is-muted';
            }
            if (isoDate === selectedDate) {
              button.className += ' is-selected';
            }
            if (isToday) {
              button.className += ' is-today';
            }

            dayLabel.textContent = String(isCurrentMonth ? dayNumber : (dayNumber < 1 ? daysInPreviousMonth + dayNumber : dayNumber - daysInMonth));
            meta.textContent = dayEvents.length ? dayEvents.length + ' Termin' + (dayEvents.length === 1 ? '' : 'e') : 'frei';
            dots.className = 'calendar-dots';

            dayEvents.slice(0, 3).forEach(function () {
              var dot = document.createElement('span');
              dots.appendChild(dot);
            });

            button.appendChild(dayLabel);
            button.appendChild(meta);
            button.appendChild(dots);
            button.addEventListener('click', createDateHandler(isoDate));
            grid.appendChild(button);
          }
        }

        function renderWeekCalendar() {
          var weekDates = getWeekDates(selectedDate);

          monthLabel.textContent = buildWeekLabel(weekDates);
          viewSummary.textContent = 'Wochenansicht zur Orientierung. Die Verwaltung bleibt in der Terminliste.';
          grid.innerHTML = '';

          weekDates.forEach(function (isoDate) {
            var cellDate = new Date(isoDate + 'T12:00:00');
            var dayEvents = getEventsForDate(isoDate);
            var button = document.createElement('button');
            var dayLabel = document.createElement('strong');
            var meta = document.createElement('span');
            var summary = document.createElement('div');

            button.type = 'button';
            button.className = 'calendar-day calendar-day-week';
            if (isoDate === selectedDate) {
              button.className += ' is-selected';
            }
            if (isoDate === toIsoDate(new Date())) {
              button.className += ' is-today';
            }

            dayLabel.textContent = weekdayNames[cellDate.getDay()].slice(0, 2) + ' ' + pad(cellDate.getDate()) + '.' + pad(cellDate.getMonth() + 1);
            meta.textContent = dayEvents.length ? dayEvents.length + ' Termin' + (dayEvents.length === 1 ? '' : 'e') : 'frei';
            summary.className = 'calendar-week-summary';

            if (dayEvents.length) {
              dayEvents.sort(compareEvents).slice(0, 3).forEach(function (eventItem) {
                var item = document.createElement('span');
                item.className = 'calendar-week-summary-item';
                item.textContent = (eventItem.time || '--:--') + ' ' + eventItem.title;
                summary.appendChild(item);
              });
            } else {
              summary.innerHTML = '<span class="calendar-week-summary-empty">Keine Eintraege</span>';
            }

            button.appendChild(dayLabel);
            button.appendChild(meta);
            button.appendChild(summary);
            button.addEventListener('click', createDateHandler(isoDate));
            grid.appendChild(button);
          });
        }

        function createDateHandler(isoDate) {
          return function () {
            selectDate(isoDate);
          };
        }

        function selectDate(isoDate) {
          selectedDate = isoDate;
          detailArmed = false;
          dateInput.value = isoDate;
          var selected = new Date(isoDate + 'T12:00:00');
          viewYear = selected.getFullYear();
          viewMonth = selected.getMonth();
          renderCalendar();
          renderSelectedDay();
        }

        function renderSelectedDay() {
          var date = new Date(selectedDate + 'T12:00:00');
          var dayEvents = getEventsForDate(selectedDate);

          selectedLabel.textContent = weekdayNames[date.getDay()] + ', ' + pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear();
          selectedEvents.innerHTML = '';
          selectedEventFocus.innerHTML = '';

          if (!dayEvents.length) {
            selectedPanel.hidden = true;
            selectedEventId = '';
            return;
          }

          selectedPanel.hidden = false;

          dayEvents.sort(compareEvents).forEach(function (eventItem) {
            var article = document.createElement('article');
            var main = document.createElement('div');
            var heading = document.createElement('div');
            var title = document.createElement('strong');
            var meta = document.createElement('span');
            var address = document.createElement('p');
            var note = document.createElement('p');
            var actions = document.createElement('div');
            var detailButton = document.createElement('button');
            var editButton = document.createElement('button');
            var removeButton = document.createElement('button');
            var inlineMap;

            article.className = 'agenda-item';
            if (eventItem.id === selectedEventId) {
              article.className += ' is-active';
            }
            main.className = 'agenda-main';
            heading.className = 'agenda-heading';
            title.textContent = eventItem.title;
            meta.className = 'agenda-meta';
            meta.textContent = (eventItem.time || 'Ohne Uhrzeit') + ' | ' + eventItem.date;

            if (eventItem.address) {
              address.className = 'agenda-address';
              address.textContent = eventItem.address;
              inlineMap = document.createElement('iframe');
              inlineMap.className = 'agenda-inline-map';
              inlineMap.title = 'Minimap fuer ' + eventItem.title;
              inlineMap.loading = 'lazy';
              inlineMap.referrerPolicy = 'no-referrer-when-downgrade';
              inlineMap.src = buildMapEmbedUrl(eventItem.address);
              article.className += ' has-map';
            }

            note.className = 'agenda-note';
            note.textContent = eventItem.note || 'Keine Notiz';
            actions.className = 'agenda-actions';
            detailButton.className = 'btn btn-secondary btn-small';
            detailButton.type = 'button';
            detailButton.textContent = 'Weiterlesen';
            detailButton.addEventListener('click', function (clickEvent) {
              clickEvent.stopPropagation();
              openEventFocus(eventItem.id);
            });
            detailButton.addEventListener('mouseenter', function () {
              openEventFocus(eventItem.id);
            });

            editButton.className = 'btn btn-ghost btn-small';
            editButton.type = 'button';
            editButton.textContent = 'Bearbeiten';
            editButton.addEventListener('click', function (clickEvent) {
              clickEvent.stopPropagation();
              loadEventIntoForm(eventItem);
            });

            removeButton.className = 'btn btn-ghost btn-small';
            removeButton.type = 'button';
            removeButton.textContent = 'Loeschen';
            removeButton.addEventListener('click', function (clickEvent) {
              clickEvent.stopPropagation();
              removeEvent(eventItem.id);
            });

            actions.appendChild(detailButton);
            actions.appendChild(editButton);
            actions.appendChild(removeButton);

            heading.appendChild(title);
            heading.appendChild(meta);
            main.appendChild(heading);
            if (eventItem.address) {
              main.appendChild(address);
            }
            main.appendChild(note);
            main.appendChild(actions);
            article.appendChild(main);
            if (inlineMap) {
              article.appendChild(inlineMap);
            }
            selectedEvents.appendChild(article);
          });

          if (!findEventById(selectedEventId) || getEventDate(selectedEventId) !== selectedDate) {
            selectedEventId = dayEvents[0].id;
          }

          if (!detailArmed) {
            selectedEventFocus.hidden = true;
            selectedEventFocus.innerHTML = '';
            return;
          }

          selectedEventFocus.hidden = false;
          renderEventFocus(findEventById(selectedEventId));
        }

        function removeEvent(eventId) {
          events = events.filter(function (eventItem) {
            return eventItem.id !== eventId;
          });
          persistEvents();
          renderOverview();
          if (selectedEventId === eventId) {
            selectedEventId = '';
          }
          if (editingEventId === eventId) {
            clearEditMode('');
          }
          renderCalendar();
          renderSelectedDay();
          status.textContent = 'Termin geloescht.';
        }

        function getEventsForDate(isoDate) {
          return events.filter(function (eventItem) {
            return eventItem.date === isoDate;
          });
        }

        function compareEvents(left, right) {
          return String(left.time || '99:99').localeCompare(String(right.time || '99:99'));
        }

        function saveEventFromForm() {
          var title = titleInput.value.trim();
          var dateValue = dateInput.value;
          var timeValue = timeInput.value;
          var addressValue = addressInput.value.trim();
          var noteValue = noteInput.value.trim();
          var existingIndex = findEventIndex(editingEventId);
          var payload = {
            id: editingEventId || String(Date.now()) + '-' + Math.random().toString(16).slice(2),
            title: title,
            date: dateValue,
            time: timeValue,
            address: addressValue,
            note: noteValue,
            updatedAt: new Date().toISOString()
          };

          if (!title || !dateValue) {
            status.textContent = 'Bitte mindestens Titel und Datum fuer den Termin angeben.';
            return;
          }

          if (existingIndex >= 0) {
            events[existingIndex] = payload;
            status.textContent = 'Termin aktualisiert.';
          } else {
            events.push(payload);
            status.textContent = 'Termin gespeichert.';
          }

          persistEvents();
          renderOverview();
          selectDate(dateValue);
          selectedEventId = payload.id;
          clearEditMode('');
          transcript.hidden = true;
          detailArmed = true;
        }

        function loadEventIntoForm(eventItem) {
          editingEventId = eventItem.id;
          editIdInput.value = eventItem.id;
          titleInput.value = eventItem.title || '';
          dateInput.value = eventItem.date || selectedDate;
          timeInput.value = eventItem.time || '';
          addressInput.value = eventItem.address || '';
          renderFormMapPreview(eventItem.address || '');
          noteInput.value = eventItem.note || '';
          formLabel.textContent = 'Termin bearbeiten';
          formTitle.textContent = 'Bestehenden Termin anpassen';
          saveButton.textContent = 'Termin aktualisieren';
          cancelButton.hidden = false;
          status.textContent = 'Termin zur Bearbeitung geladen.';
        }

        function clearEditMode(message) {
          editingEventId = '';
          editIdInput.value = '';
          titleInput.value = '';
          timeInput.value = '';
          addressInput.value = '';
          renderFormMapPreview('');
          noteInput.value = '';
          dateInput.value = selectedDate;
          formLabel.textContent = 'Neuer Termin';
          formTitle.textContent = 'Per Sprache oder manuell eintragen';
          saveButton.textContent = 'Termin speichern';
          cancelButton.hidden = true;
          if (message) {
            status.textContent = message;
          }
        }

        function syncVoiceButtons() {
          var active = voiceMode !== 'idle';

          quickVoiceButton.hidden = active;
          guidedVoiceButton.hidden = active;
          stopVoiceButton.hidden = !active;
        }

        function queueOrStartVoiceCapture(promptText) {
          if (voiceCaptureActive) {
            queuedVoicePrompt = promptText || '';
            return;
          }

          startVoiceCapture(promptText);
        }

        function findEventIndex(eventId) {
          if (!eventId) {
            return -1;
          }

          return events.findIndex(function (candidate) {
            return candidate.id === eventId;
          });
        }

        function findEventById(eventId) {
          var index = findEventIndex(eventId);
          return index >= 0 ? events[index] : null;
        }

        function getEventDate(eventId) {
          var eventItem = findEventById(eventId);
          return eventItem ? eventItem.date : '';
        }

        function selectEvent(eventId) {
          selectedEventId = eventId;
          detailArmed = true;
          renderSelectedDay();
        }

        function openEventFocus(eventId) {
          selectedEventId = eventId;
          detailArmed = true;
          selectedEventFocus.hidden = false;
          renderEventFocus(findEventById(eventId));
          renderSelectedDay();
        }

        function renderEventFocus(eventItem) {
          var mapBlock;
          var mapLink;
          var heading;
          var meta;
          var note;
          var info;

          selectedEventFocus.innerHTML = '';

          if (!eventItem) {
            selectedEventFocus.innerHTML = '<p class="empty-state">Waehle in der Terminliste einen Eintrag fuer die Detailansicht.</p>';
            return;
          }

          heading = document.createElement('div');
          heading.className = 'event-focus-heading';
          heading.innerHTML = '<span class="card-label">Terminansicht</span><h4>' + escapeHtml(eventItem.title) + '</h4>';

          meta = document.createElement('p');
          meta.className = 'agenda-meta';
          meta.textContent = (eventItem.time || 'Ohne Uhrzeit') + ' | ' + eventItem.date;

          info = document.createElement('div');
          info.className = 'event-focus-meta';
          info.appendChild(meta);

          if (eventItem.address) {
            var address = document.createElement('p');
            address.className = 'agenda-address';
            address.textContent = eventItem.address;
            info.appendChild(address);

            mapBlock = document.createElement('iframe');
            mapBlock.className = 'event-focus-map';
            mapBlock.title = 'Kartenvorschau fuer ' + eventItem.title;
            mapBlock.loading = 'lazy';
            mapBlock.referrerPolicy = 'no-referrer-when-downgrade';
            mapBlock.src = buildMapEmbedUrl(eventItem.address);

            mapLink = document.createElement('a');
            mapLink.className = 'btn btn-secondary btn-small';
            mapLink.href = buildMapUrl(eventItem.address);
            mapLink.target = '_blank';
            mapLink.rel = 'noreferrer noopener';
            mapLink.textContent = 'Route oeffnen';
          }

          note = document.createElement('p');
          note.className = 'event-focus-note';
          note.textContent = eventItem.note || 'Keine Notiz hinterlegt.';

          selectedEventFocus.appendChild(heading);
          selectedEventFocus.appendChild(info);
          if (mapBlock) {
            selectedEventFocus.appendChild(mapBlock);
          }
          if (mapLink) {
            selectedEventFocus.appendChild(mapLink);
          }
          selectedEventFocus.appendChild(note);
        }

        function renderOverview() {
          var sortedEvents = getSortedEvents(events);
          var upcomingEvents = sortedEvents.filter(function (eventItem) {
            return buildEventTimestamp(eventItem) >= startOfDayTimestamp(new Date());
          });
          var searchTerm = normalizeSearch(overviewSearch.value);
          var displayEvents = upcomingEvents.length ? upcomingEvents : sortedEvents;

          if (searchTerm) {
            displayEvents = displayEvents.filter(function (eventItem) {
              return matchesSearch(eventItem, searchTerm);
            });
          }

          overviewTitle.textContent = upcomingEvents.length ? 'Anstehende Termine' : 'Alle Termine';
          overviewSummary.textContent = displayEvents.length
            ? displayEvents.length + ' Termin' + (displayEvents.length === 1 ? '' : 'e') + ' in der Uebersicht.'
            : 'Keine Termine fuer die aktuelle Suche gefunden.';

          renderOverviewMetrics(sortedEvents, upcomingEvents);
          renderOverviewList(displayEvents);
        }

        function renderOverviewMetrics(sortedEvents, upcomingEvents) {
          var filteredEvents = getOverviewEventsForExport();
          var nextEvent = upcomingEvents.length ? upcomingEvents[0] : null;
          var cards = [
            {
              label: 'Gesamt',
              value: String(sortedEvents.length)
            },
            {
              label: 'Anstehend',
              value: String(upcomingEvents.length)
            },
            {
              label: 'Naechster Termin',
              value: nextEvent ? formatEventStamp(nextEvent) : 'Keiner'
            },
            {
              label: 'Gefiltert',
              value: String(filteredEvents.length)
            }
          ];

          overviewMetrics.innerHTML = '';

          cards.forEach(function (card) {
            var item = document.createElement('article');
            var strong = document.createElement('strong');
            var span = document.createElement('span');

            item.className = 'metric-tile';
            strong.textContent = card.value;
            span.textContent = card.label;
            item.appendChild(strong);
            item.appendChild(span);
            overviewMetrics.appendChild(item);
          });
        }

        function renderOverviewList(displayEvents) {
          overviewList.innerHTML = '';

          if (!displayEvents.length) {
            overviewList.innerHTML = '<p class="empty-state">Noch keine passenden Termine vorhanden.</p>';
            return;
          }

          displayEvents.slice(0, 24).forEach(function (eventItem) {
            var article = document.createElement('article');
            var main = document.createElement('div');
            var heading = document.createElement('div');
            var title = document.createElement('strong');
            var stamp = document.createElement('div');
            var datePill = document.createElement('span');
            var timePill = document.createElement('span');
            var address = document.createElement('p');
            var note = document.createElement('p');
            var actions = document.createElement('div');
            var editButton = document.createElement('button');
            var visualizeButton = document.createElement('button');
            var miniMap;

            article.className = 'overview-item';
            main.className = 'overview-item-main';
            heading.className = 'overview-item-heading';
            stamp.className = 'overview-item-stamp';
            title.textContent = eventItem.title;
            datePill.className = 'overview-item-date';
            datePill.textContent = formatEventDate(eventItem);
            timePill.className = 'overview-item-time';
            timePill.textContent = eventItem.time || 'Ohne Uhrzeit';
            address.className = 'overview-item-location';
            address.textContent = eventItem.address || 'Ohne Adresse';
            note.className = 'overview-item-note';
            note.textContent = eventItem.note || 'Ohne weitere Details';
            actions.className = 'overview-item-actions';

            if (eventItem.address) {
              article.className += ' has-map';
              miniMap = document.createElement('iframe');
              miniMap.className = 'overview-item-map';
              miniMap.title = 'Mini-Map fuer ' + eventItem.title;
              miniMap.loading = 'lazy';
              miniMap.referrerPolicy = 'no-referrer-when-downgrade';
              miniMap.tabIndex = -1;
              miniMap.src = buildMapEmbedUrl(eventItem.address);
            }

            editButton.className = 'btn btn-secondary btn-small';
            editButton.type = 'button';
            editButton.textContent = 'Bearbeiten';
            editButton.addEventListener('click', function () {
              selectDate(eventItem.date);
              selectedEventId = eventItem.id;
              detailArmed = true;
              loadEventIntoForm(eventItem);
              renderSelectedDay();
            });

            visualizeButton.className = 'btn btn-ghost btn-small';
            visualizeButton.type = 'button';
            visualizeButton.textContent = 'Im Kalender zeigen';
            visualizeButton.addEventListener('click', function () {
              selectDate(eventItem.date);
              selectedEventId = eventItem.id;
              detailArmed = true;
              setActiveView('month');
              openEventFocus(eventItem.id);
            });

            heading.appendChild(title);
            stamp.appendChild(datePill);
            stamp.appendChild(timePill);
            main.appendChild(heading);
            main.appendChild(stamp);
            main.appendChild(address);
            if (eventItem.note) {
              main.appendChild(note);
            }
            actions.appendChild(editButton);
            actions.appendChild(visualizeButton);
            main.appendChild(actions);
            article.appendChild(main);
            if (miniMap) {
              article.appendChild(miniMap);
            }
            overviewList.appendChild(article);
          });
        }

        function setActiveView(nextView) {
          if (nextView === 'week') {
            activeView = 'week';
          } else if (nextView === 'month') {
            activeView = 'month';
          } else {
            activeView = 'list';
          }
          renderCalendar();
          if (activeView !== 'list') {
            renderSelectedDay();
          }
        }

        function syncViewToggle() {
          listViewButton.className = activeView === 'list' ? 'btn btn-secondary is-active' : 'btn btn-ghost';
          monthViewButton.className = activeView === 'month' ? 'btn btn-secondary is-active' : 'btn btn-ghost';
          weekViewButton.className = activeView === 'week' ? 'btn btn-secondary is-active' : 'btn btn-ghost';
          rangeActions.hidden = activeView === 'list';
          gridViewport.className = activeView === 'week'
            ? 'calendar-grid-viewport is-week-view'
            : 'calendar-grid-viewport';
        }

        function getWeekDates(isoDate) {
          var selected = new Date(isoDate + 'T12:00:00');
          var weekDay = (selected.getDay() + 6) % 7;
          var monday = new Date(selected);
          var dates = [];
          var index;

          monday.setDate(selected.getDate() - weekDay);

          for (index = 0; index < 7; index += 1) {
            dates.push(toIsoDate(new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + index)));
          }

          return dates;
        }

        function getSortedEvents(collection) {
          return collection.slice().sort(function (left, right) {
            return buildEventTimestamp(left) - buildEventTimestamp(right);
          });
        }

        function getOverviewEventsForExport() {
          var sortedEvents = getSortedEvents(events);
          var upcomingEvents = sortedEvents.filter(function (eventItem) {
            return buildEventTimestamp(eventItem) >= startOfDayTimestamp(new Date());
          });
          var displayEvents = upcomingEvents.length ? upcomingEvents : sortedEvents;
          var searchTerm = normalizeSearch(overviewSearch.value);

          if (!searchTerm) {
            return displayEvents;
          }

          return displayEvents.filter(function (eventItem) {
            return matchesSearch(eventItem, searchTerm);
          });
        }

        function buildEventTimestamp(eventItem) {
          var timeValue = eventItem.time || '23:59';
          return new Date(eventItem.date + 'T' + timeValue + ':00').getTime();
        }

        function startOfDayTimestamp(date) {
          return new Date(date.getFullYear(), date.getMonth(), date.getDate()).getTime();
        }

        function formatEventStamp(eventItem) {
          var date = new Date(eventItem.date + 'T12:00:00');
          return pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear() + ' | ' + (eventItem.time || 'Ohne Uhrzeit');
        }

        function formatEventDate(eventItem) {
          var date = new Date(eventItem.date + 'T12:00:00');
          return pad(date.getDate()) + '.' + pad(date.getMonth() + 1) + '.' + date.getFullYear();
        }

        function buildWeekLabel(weekDates) {
          var start = new Date(weekDates[0] + 'T12:00:00');
          var end = new Date(weekDates[6] + 'T12:00:00');

          if (start.getMonth() === end.getMonth()) {
            return pad(start.getDate()) + '.-' + pad(end.getDate()) + '. ' + monthNames[start.getMonth()] + ' ' + start.getFullYear();
          }

          return pad(start.getDate()) + '.' + pad(start.getMonth() + 1) + '.-' + pad(end.getDate()) + '.' + pad(end.getMonth() + 1) + '. ' + end.getFullYear();
        }

        function shiftIsoDate(isoDate, days) {
          var shifted = new Date(isoDate + 'T12:00:00');
          shifted.setDate(shifted.getDate() + days);
          return toIsoDate(shifted);
        }

        function printAgendaOverview(mode) {
          var printableEvents = mode === 'day'
            ? getEventsForDate(selectedDate).slice().sort(compareEvents)
            : getOverviewEventsForExport();
          var title = mode === 'day' ? 'Tagesansicht ' + selectedLabel.textContent : 'Terminuebersicht';
          var summary = mode === 'day'
            ? 'Ausdruck fuer den ausgewaehlten Tag.'
            : 'Gesamte Terminliste aus Webapp Central.';
          var popup = window.open('', '_blank', 'width=980,height=720');

          if (!popup) {
            status.textContent = 'Druckansicht konnte nicht geoeffnet werden. Bitte Popups erlauben.';
            return;
          }

          popup.document.open();
          popup.document.write(buildPrintDocument(title, summary, printableEvents));
          popup.document.close();
          popup.focus();
          popup.print();
        }

        function buildPrintDocument(title, summary, printableEvents) {
          var items = printableEvents.length ? printableEvents.map(function (eventItem) {
            return '<article class="print-item">'
              + '<h2>' + escapeHtml(eventItem.title) + '</h2>'
              + '<p class="print-meta">' + escapeHtml(formatEventStamp(eventItem)) + '</p>'
              + (eventItem.address ? '<p><strong>Adresse:</strong> ' + escapeHtml(eventItem.address) + '</p>' : '')
              + '<p>' + escapeHtml(eventItem.note || 'Keine Notiz hinterlegt.') + '</p>'
              + '</article>';
          }).join('') : '<p>Keine Termine vorhanden.</p>';

          return '<!doctype html><html lang="de"><head><meta charset="utf-8"><title>' + escapeHtml(title) + '</title>'
            + '<style>'
            + 'body{font-family:Georgia,serif;margin:32px;color:#172126;}'
            + 'h1{margin:0 0 8px;font-size:28px;}'
            + '.print-summary{margin:0 0 24px;color:#5b676d;}'
            + '.print-item{padding:16px 0;border-top:1px solid #d9d3ca;}'
            + '.print-item:first-of-type{border-top:0;padding-top:0;}'
            + '.print-item h2{margin:0 0 6px;font-size:20px;}'
            + '.print-item p{margin:6px 0;}'
            + '.print-meta{color:#5b676d;font-size:14px;}'
            + '</style></head><body>'
            + '<h1>' + escapeHtml(title) + '</h1>'
            + '<p class="print-summary">' + escapeHtml(summary) + '</p>'
            + items
            + '</body></html>';
        }

        function exportAgendaPdf(mode) {
          var pdfEvents = mode === 'day'
            ? getEventsForDate(selectedDate).slice().sort(compareEvents)
            : getOverviewEventsForExport();
          var title = mode === 'day' ? 'Tagesansicht ' + selectedLabel.textContent : 'Terminuebersicht';
          var fileName = mode === 'day'
            ? 'termine-' + selectedDate + '.pdf'
            : 'termine-uebersicht.pdf';
          var pdfContent = buildPdfDocument(title, pdfEvents);
          var pdfBytes = stringToByteArray(pdfContent);
          var blob = new Blob([pdfBytes], { type: 'application/pdf' });
          var link = document.createElement('a');

          link.href = URL.createObjectURL(blob);
          link.download = fileName;
          link.click();

          window.setTimeout(function () {
            URL.revokeObjectURL(link.href);
          }, 1000);

          status.textContent = 'PDF wurde erstellt.';
        }

        function buildPdfDocument(title, pdfEvents) {
          var lines = [title, ''];
          var body = [];
          var offsets = [];
          var objects = [];
          var pdf = '%PDF-1.4\n';
          var index;
          var xrefStart;

          if (!pdfEvents.length) {
            lines.push('Keine Termine vorhanden.');
          } else {
            pdfEvents.forEach(function (eventItem, eventIndex) {
              lines.push((eventIndex + 1) + '. ' + eventItem.title);
              lines.push('   ' + formatEventStamp(eventItem));
              if (eventItem.address) {
                lines.push('   Adresse: ' + eventItem.address);
              }
              lines.push('   ' + (eventItem.note || 'Keine Notiz hinterlegt.'));
              lines.push('');
            });
          }

          lines.forEach(function (line, lineIndex) {
            var y = 800 - (lineIndex * 18);
            if (y < 40) {
              return;
            }

            body.push('BT /F1 ' + (lineIndex === 0 ? '18' : '11') + ' Tf 50 ' + y + ' Td (' + escapePdfText(line) + ') Tj ET');
          });

          objects.push('1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n');
          objects.push('2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n');
          objects.push('3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj\n');
          objects.push('4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n');
          objects.push('5 0 obj << /Length ' + body.join('\n').length + ' >> stream\n' + body.join('\n') + '\nendstream endobj\n');

          for (index = 0; index < objects.length; index += 1) {
            offsets.push(pdf.length);
            pdf += objects[index];
          }

          xrefStart = pdf.length;
          pdf += 'xref\n0 ' + (objects.length + 1) + '\n';
          pdf += '0000000000 65535 f \n';
          offsets.forEach(function (offset) {
            pdf += String(offset).padStart(10, '0') + ' 00000 n \n';
          });
          pdf += 'trailer << /Size ' + (objects.length + 1) + ' /Root 1 0 R >>\n';
          pdf += 'startxref\n' + xrefStart + '\n%%EOF';

          return pdf;
        }

        function stringToByteArray(value) {
          var bytes = new Uint8Array(value.length);
          var index;

          for (index = 0; index < value.length; index += 1) {
            bytes[index] = value.charCodeAt(index) & 255;
          }

          return bytes;
        }

        function escapePdfText(value) {
          return sanitizePdfText(value)
            .replace(/\\/g, '\\\\')
            .replace(/\(/g, '\\(')
            .replace(/\)/g, '\\)');
        }

        function sanitizePdfText(value) {
          return String(value || '')
            .replace(/\r/g, ' ')
            .replace(/\n/g, ' ')
            .replace(/[\u2013\u2014]/g, '-')
            .replace(/\u20ac/g, 'EUR')
            .replace(/[^\x20-\x7e\xa0-\xff]/g, '?');
        }

        function normalizeSearch(value) {
          return normalizeText(String(value || '')).trim();
        }

        function matchesSearch(eventItem, searchTerm) {
          var haystack = normalizeSearch([
            eventItem.title,
            eventItem.address,
            eventItem.note,
            eventItem.date,
            formatEventStamp(eventItem)
          ].join(' '));

          return haystack.indexOf(searchTerm) !== -1;
        }

        function buildMapUrl(addressValue) {
          return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(addressValue);
        }

        function buildMapEmbedUrl(addressValue) {
          return 'https://www.google.com/maps?q=' + encodeURIComponent(addressValue) + '&output=embed';
        }

        function renderFormMapPreview(addressValue) {
          var trimmed = String(addressValue || '').trim();

          if (!trimmed) {
            mapPreview.hidden = true;
            mapFrame.removeAttribute('src');
            return;
          }

          mapFrame.src = buildMapEmbedUrl(trimmed);
          mapPreview.hidden = false;
        }

        function startQuickVoiceCapture() {
          voiceMode = 'quick';
          guidedVoiceState = null;
          queuedVoicePrompt = '';
          syncVoiceButtons();
          queueOrStartVoiceCapture('Sage den Termin in einem Satz, zum Beispiel: Morgen 14 Uhr Zahnarzt in Winterberg.');
        }

        function startGuidedVoiceCapture() {
          voiceMode = 'guided';
          guidedVoiceState = {
            index: 0,
            steps: [
              { key: 'title', label: 'Titel', prompt: 'Wie heisst der Termin?' },
              { key: 'date', label: 'Datum', prompt: 'Welches Datum hat der Termin? Du kannst zum Beispiel morgen oder den zwanzigsten Mai sagen.' },
              { key: 'time', label: 'Uhrzeit', prompt: 'Welche Uhrzeit hat der Termin?' },
              { key: 'address', label: 'Adresse', prompt: 'Welche Adresse oder welcher Ort gehoert dazu? Du kannst auch ueberspringen sagen.' },
              { key: 'note', label: 'Notiz', prompt: 'Gibt es noch eine Notiz oder Beschreibung? Du kannst auch ueberspringen sagen.' }
            ]
          };
          transcript.hidden = false;
          transcript.textContent = 'Gefuehrte Spracheingabe gestartet.';
          syncVoiceButtons();
          continueGuidedVoiceCapture();
        }

        function continueGuidedVoiceCapture() {
          var step;

          if (!guidedVoiceState || !guidedVoiceState.steps || guidedVoiceState.index >= guidedVoiceState.steps.length) {
            voiceMode = 'idle';
            guidedVoiceState = null;
            syncVoiceButtons();
            status.textContent = 'Gefuehrte Spracheingabe abgeschlossen. Bitte kurz pruefen und speichern.';
            return;
          }

          step = guidedVoiceState.steps[guidedVoiceState.index];
          queueOrStartVoiceCapture(step.prompt);
        }

        function stopVoiceCapture(message) {
          voiceMode = 'idle';
          guidedVoiceState = null;
          voiceCaptureActive = false;
          queuedVoicePrompt = '';
          syncVoiceButtons();

          if (recognition) {
            try {
              recognition.abort();
            } catch (error) {
              // no-op
            }
          }

          if (window.speechSynthesis && typeof window.speechSynthesis.cancel === 'function') {
            window.speechSynthesis.cancel();
          }

          status.textContent = message || 'Sprachmodus beendet.';
        }

        function startVoiceCapture(promptText) {
          var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

          if (!SpeechRecognition) {
            status.textContent = 'Dieser Browser unterstuetzt die Sprachaufnahme aktuell nicht.';
            voiceMode = 'idle';
            syncVoiceButtons();
            return;
          }

          if (!recognition) {
            recognition = new SpeechRecognition();
            recognition.lang = 'de-DE';
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            recognition.addEventListener('result', function (event) {
              var text = '';
              if (event.results && event.results[0] && event.results[0][0]) {
                text = String(event.results[0][0].transcript || '').trim();
              }

              handleVoiceResult(text);
            });

            recognition.addEventListener('error', function (event) {
              voiceCaptureActive = false;
              queuedVoicePrompt = '';
              status.textContent = 'Sprachaufnahme fehlgeschlagen: ' + event.error;
              voiceMode = 'idle';
              guidedVoiceState = null;
              syncVoiceButtons();
            });

            recognition.addEventListener('end', function () {
              var nextPrompt = '';

              voiceCaptureActive = false;

              if (queuedVoicePrompt && voiceMode !== 'idle') {
                nextPrompt = queuedVoicePrompt;
                queuedVoicePrompt = '';
                window.setTimeout(function () {
                  queueOrStartVoiceCapture(nextPrompt);
                }, 180);
                return;
              }

              if (status.textContent === 'Aufnahme laeuft ...') {
                status.textContent = 'Sprachaufnahme beendet.';
              }
            });
          }

          status.textContent = promptText || 'Aufnahme laeuft ...';
          speakPrompt(promptText, function () {
            try {
              status.textContent = 'Aufnahme laeuft ...';
              voiceCaptureActive = true;
              recognition.start();
            } catch (error) {
              voiceCaptureActive = false;
              status.textContent = 'Sprachaufnahme konnte nicht gestartet werden.';
              voiceMode = 'idle';
              guidedVoiceState = null;
              queuedVoicePrompt = '';
              syncVoiceButtons();
            }
          });
        }

        function handleVoiceResult(text) {
          if (voiceMode === 'guided') {
            applyGuidedTranscript(text);
            return;
          }

          applyQuickTranscript(text);
          voiceMode = 'idle';
          syncVoiceButtons();
        }

        function applyQuickTranscript(text) {
          var parsed = parseGermanTranscript(text);

          transcript.hidden = false;
          transcript.textContent = 'Erkannt: ' + text;

          if (parsed.title) {
            titleInput.value = parsed.title;
          }
          if (parsed.date) {
            dateInput.value = parsed.date;
            selectedDate = parsed.date;
          }
          if (parsed.time) {
            timeInput.value = parsed.time;
          }
          if (parsed.address) {
            addressInput.value = parsed.address;
          }
          if (parsed.note) {
            noteInput.value = parsed.note;
          }

          renderFormMapPreview(addressInput.value);

          renderCalendar();
          renderSelectedDay();
          status.textContent = parsed.missing.length
            ? 'Sprachtext uebernommen. Bitte noch pruefen: ' + parsed.missing.join(', ') + '.'
            : 'Sprachtext uebernommen. Bitte kurz pruefen und dann speichern.';
        }

        function applyGuidedTranscript(text) {
          var step;
          var value;

          if (!guidedVoiceState || !guidedVoiceState.steps || guidedVoiceState.index >= guidedVoiceState.steps.length) {
            stopVoiceCapture('Sprachmodus beendet.');
            return;
          }

          step = guidedVoiceState.steps[guidedVoiceState.index];
          value = readGuidedFieldValue(step.key, text);

          transcript.hidden = false;
          transcript.textContent = 'Erkannt fuer ' + step.label + ': ' + text;

          if (step.key === 'title' && value) {
            titleInput.value = value;
          }
          if (step.key === 'date' && value) {
            dateInput.value = value;
            selectedDate = value;
          }
          if (step.key === 'time' && value) {
            timeInput.value = value;
          }
          if (step.key === 'address') {
            addressInput.value = value;
            renderFormMapPreview(value);
          }
          if (step.key === 'note') {
            noteInput.value = value;
          }

          guidedVoiceState.index += 1;
          renderCalendar();
          renderSelectedDay();
          continueGuidedVoiceCapture();
        }

        function parseGermanTranscript(text) {
          var result = {
            title: '',
            date: dateInput.value || selectedDate,
            time: '',
            address: '',
            note: '',
            missing: []
          };
          var original = compactText(text);
          var dateInfo = parseVoiceDateInfo(original);
          var timeInfo = parseVoiceTimeInfo(original);
          var addressMatch;
          var noteMatch;
          var cleanedTitle = original;
          var detailParts = [];

          if (dateInfo.value) {
            result.date = dateInfo.value;
            cleanedTitle = removeMatchSegment(cleanedTitle, dateInfo.matchedText);
          }

          if (timeInfo.value) {
            result.time = timeInfo.value;
            cleanedTitle = removeMatchSegment(cleanedTitle, timeInfo.matchedText);
          }

          noteMatch = original.match(/\b(?:notiz|hinweis|bemerkung|beschreibung|text)\s+(.+)$/i);
          if (noteMatch) {
            result.note = compactText(noteMatch[1]);
            cleanedTitle = cleanedTitle.replace(noteMatch[0], ' ');
          }

          addressMatch = cleanedTitle.match(/\b(?:in|im|bei|an der|am|an|auf)\s+(.+)$/i);
          if (addressMatch) {
            result.address = compactText(addressMatch[1]);
            cleanedTitle = cleanedTitle.slice(0, addressMatch.index);
          }

          detailParts = extractDetailParts(cleanedTitle);
          result.title = cleanTitle(detailParts[0] || cleanedTitle);

          if (detailParts.length > 1 && !result.address) {
            result.address = compactText(detailParts.slice(1).join(' '));
          }

          if (!result.title || result.title === 'Neuer Termin') {
            result.missing.push('Titel');
          }
          if (!dateInfo.value && !dateInput.value) {
            result.missing.push('Datum');
          }
          if (!result.time) {
            result.missing.push('Uhrzeit');
          }
          if (!result.address && /\b(?:in|im|bei|am|an|auf)\b/i.test(original)) {
            result.missing.push('Ort/Adresse');
          }

          return result;
        }

        function cleanTitle(text) {
          return compactText(String(text || '')
            .replace(/uebermorgen|morgen|heute/gi, ' ')
            .replace(/\b(?:naechsten|kommenden)\b/gi, ' ')
            .replace(/\d{1,2}[.\-/]\d{1,2}(?:[.\-/]\d{2,4})?/g, ' ')
            .replace(/\b(?:um\s+)?\d{1,2}(?::\d{1,2})?(?:\s*uhr)?\b/gi, ' ')
            .replace(/\b(?:in|im|bei|an der|am|an|auf)\b.+$/i, ' ')
            .replace(/\b(?:notiz|hinweis|bemerkung|beschreibung|text)\b.+$/i, ' ')) || 'Neuer Termin';
        }

        function normalizeText(text) {
          return text
            .toLowerCase()
            .replace(/[.,]/g, ' ')
            .replace(/\u00e4/g, 'ae')
            .replace(/\u00f6/g, 'oe')
            .replace(/\u00fc/g, 'ue')
            .replace(/\u00df/g, 'ss');
        }

        function compactText(text) {
          return String(text || '')
            .replace(/\s+/g, ' ')
            .trim();
        }
        function removeMatchSegment(text, matchText) {
          if (!matchText) {
            return compactText(text);
          }
          return compactText(String(text || '').replace(matchText, ' '));
        }

        function extractDetailParts(text) {
          return compactText(text)
            .split(/\s+-\s+|\s+bei\s+/i)
            .map(function (part) {
              return compactText(part);
            })
            .filter(Boolean);
        }
        function parseVoiceDateInfo(text) {
          var normalized = normalizeText(text);
          var today = new Date();
          var numericMatch = normalized.match(/(\d{1,2})[.\-/](\d{1,2})(?:[.\-/](\d{2,4}))?/);
          var monthMatch = normalized.match(/\b(?:am\s+|den\s+)?(\d{1,2})\.?\s+(januar|februar|maerz|april|mai|juni|juli|august|september|oktober|november|dezember)(?:\s+(\d{2,4}))?\b/);
          var weekdayInfo = parseWeekdayDate(normalized, today);
          var year;
          var month;
          if (normalized.indexOf('uebermorgen') !== -1) {
            return { value: shiftDate(today, 2), matchedText: findMatchedToken(text, /uebermorgen/i) };
          }
          if (normalized.indexOf('morgen') !== -1) {
            return { value: shiftDate(today, 1), matchedText: findMatchedToken(text, /morgen/i) };
          }
          if (normalized.indexOf('heute') !== -1) {
            return { value: shiftDate(today, 0), matchedText: findMatchedToken(text, /heute/i) };
          }
          if (numericMatch) {
            year = numericMatch[3] ? Number(numericMatch[3]) : today.getFullYear();
            if (year < 100) {
              year += 2000;
            }
            return {
              value: [String(year), pad(Number(numericMatch[2])), pad(Number(numericMatch[1]))].join('-'),
              matchedText: numericMatch[0]
            };
          }
          if (monthMatch) {
            month = monthNameToNumber(monthMatch[2]);
            year = monthMatch[3] ? Number(monthMatch[3]) : today.getFullYear();
            if (year < 100) {
              year += 2000;
            }
            return {
              value: [String(year), pad(month), pad(Number(monthMatch[1]))].join('-'),
              matchedText: monthMatch[0]
            };
          }
          if (weekdayInfo) {
            return weekdayInfo;
          }
          return { value: '', matchedText: '' };
        }
        function parseWeekdayDate(normalized, baseDate) {
          var weekdayMap = {
            montag: 1,
            dienstag: 2,
            mittwoch: 3,
            donnerstag: 4,
            freitag: 5,
            samstag: 6,
            sonntag: 0
          };
          var weekday = '';
          var targetWeekday = 0;
          var dayDiff = 0;
          var shiftedDate = new Date(baseDate);
          Object.keys(weekdayMap).some(function (name) {
            if (normalized.indexOf(name) !== -1) {
              weekday = name;
              targetWeekday = weekdayMap[name];
              return true;
            }
            return false;
          });
          if (!weekday) {
            return null;
          }
          dayDiff = targetWeekday - baseDate.getDay();
          if (dayDiff <= 0 || /\b(?:naechsten|kommenden)\b/.test(normalized)) {
            dayDiff += 7;
          }
          shiftedDate.setDate(shiftedDate.getDate() + dayDiff);
          return {
            value: toIsoDate(shiftedDate),
            matchedText: weekday
          };
        }
        function monthNameToNumber(monthName) {
          var monthMap = {
            januar: 1,
            februar: 2,
            maerz: 3,
            april: 4,
            mai: 5,
            juni: 6,
            juli: 7,
            august: 8,
            september: 9,
            oktober: 10,
            november: 11,
            dezember: 12
          };
          return monthMap[normalizeText(monthName)] || 1;
        }
        function findMatchedToken(text, regex) {
          var match = String(text || '').match(regex);
          return match ? match[0] : '';
        }
        function parseVoiceTimeInfo(text) {
          var normalized = normalizeText(text);
          var fullMatch = normalized.match(/\b(?:um\s+)?(\d{1,2})(?::(\d{1,2}))?(?:\s*uhr)?\b/);
          var halfMatch = normalized.match(/\bhalb\s+(\d{1,2})\b/);
          var quarterAfterMatch = normalized.match(/\bviertel\s+nach\s+(\d{1,2})\b/);
          var quarterBeforeMatch = normalized.match(/\bviertel\s+vor\s+(\d{1,2})\b/);
          var hour = 0;
          if (quarterAfterMatch) {
            hour = Number(quarterAfterMatch[1]);
            return {
              value: pad(hour) + ':15',
              matchedText: quarterAfterMatch[0]
            };
          }
          if (quarterBeforeMatch) {
            hour = Number(quarterBeforeMatch[1]) - 1;
            if (hour < 0) {
              hour = 23;
            }
            return {
              value: pad(hour) + ':45',
              matchedText: quarterBeforeMatch[0]
            };
          }
          if (halfMatch) {
            hour = Number(halfMatch[1]) - 1;
            if (hour < 0) {
              hour = 23;
            }
            return {
              value: pad(hour) + ':30',
              matchedText: halfMatch[0]
            };
          }
          if (fullMatch) {
            return {
              value: pad(Number(fullMatch[1])) + ':' + pad(Number(fullMatch[2] || 0)),
              matchedText: fullMatch[0]
            };
          }
          return { value: '', matchedText: '' };
        }
        function readGuidedFieldValue(fieldKey, text) {
          var compact = compactText(text);
          var normalized = normalizeText(compact);

          if (isSkippedVoiceAnswer(normalized)) {
            return '';
          }

          if (fieldKey === 'title') {
            return compact || titleInput.value.trim();
          }

          if (fieldKey === 'date') {
            return parseVoiceDate(compact) || dateInput.value || selectedDate;
          }

          if (fieldKey === 'time') {
            return parseVoiceTime(compact) || timeInput.value;
          }

          if (fieldKey === 'address') {
            return compact;
          }

          if (fieldKey === 'note') {
            return compact;
          }

          return compact;
        }

        function parseVoiceDate(text) {
          return parseVoiceDateInfo(text).value || '';
        }

        function parseVoiceTime(text) {
          return parseVoiceTimeInfo(text).value || '';
        }

        function isSkippedVoiceAnswer(normalizedText) {
          return ['ueberspringen', 'weiter', 'keine', 'nichts', 'leer'].some(function (keyword) {
            return normalizedText === normalizeText(keyword) || normalizedText.indexOf(normalizeText(keyword)) === 0;
          });
        }

        function speakPrompt(promptText, callback) {
          if (!promptText) {
            window.setTimeout(callback, 120);
            return;
          }

          if (!window.speechSynthesis || typeof window.SpeechSynthesisUtterance === 'undefined') {
            window.setTimeout(callback, 280);
            return;
          }

          try {
            window.speechSynthesis.cancel();
            var utterance = new window.SpeechSynthesisUtterance(promptText);
            utterance.lang = 'de-DE';
            utterance.rate = 1;
            utterance.onend = function () {
              window.setTimeout(callback, 120);
            };
            utterance.onerror = function () {
              window.setTimeout(callback, 120);
            };
            window.speechSynthesis.speak(utterance);
          } catch (error) {
            window.setTimeout(callback, 280);
          }
        }

        function shiftDate(baseDate, days) {
          var shifted = new Date(baseDate);
          shifted.setDate(shifted.getDate() + days);
          return toIsoDate(shifted);
        }

        function toIsoDate(date) {
          return [
            String(date.getFullYear()),
            pad(date.getMonth() + 1),
            pad(date.getDate())
          ].join('-');
        }

        function pad(value) {
          return String(value).padStart(2, '0');
        }

        function escapeHtml(value) {
          return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
        }
      }());
    </script>
    <?php
});
