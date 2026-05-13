<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Kalender', 'Termine', static function (): void {
    ?>
    <section class="calendar-page">
      <article class="card calendar-board calendar-board-full">
        <div class="calendar-toolbar">
          <div>
            <span class="card-label">Kalender</span>
            <h3 id="calendar-month">Monat wird geladen</h3>
          </div>
          <div class="calendar-toolbar-actions">
            <div class="calendar-view-toggle" role="tablist" aria-label="Ansicht wechseln">
              <button class="btn btn-secondary is-active" id="view-month" type="button">Monat</button>
              <button class="btn btn-ghost" id="view-week" type="button">Woche</button>
            </div>
            <div class="button-row">
            <button class="btn btn-ghost" id="calendar-prev" type="button">Zurueck</button>
            <button class="btn btn-secondary" id="calendar-today" type="button">Heute</button>
            <button class="btn btn-ghost" id="calendar-next" type="button">Weiter</button>
            </div>
          </div>
        </div>
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

        <div class="calendar-detail-layout">
          <article class="calendar-subpanel">
            <span class="card-label">Auswahl</span>
            <h3 id="selected-date-label">Keine Auswahl</h3>
            <div class="calendar-subpanel-stack">
              <div class="calendar-agenda" id="selected-events">
                <p class="empty-state">Waehle einen Tag im Kalender oder lege direkt einen neuen Termin an.</p>
              </div>
              <div class="calendar-event-focus" id="selected-event-focus">
                <p class="empty-state">Waehle in der Terminliste einen Eintrag fuer die Detailansicht.</p>
              </div>
            </div>
          </article>

          <article class="calendar-subpanel calendar-panel">
            <span class="card-label" id="event-form-label">Neuer Termin</span>
            <h3 id="event-form-title">Per Sprache oder manuell eintragen</h3>
            <p>Sage zum Beispiel: "Morgen 14 Uhr Zahnarzt" oder "20.05. 09:30 Teammeeting". Danach kannst du den Eintrag direkt speichern.</p>

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
                <button class="btn btn-primary" id="voice-start" type="button">Voice to Text starten</button>
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
      </article>

      <article class="card calendar-overview-panel">
        <div class="calendar-overview-toolbar">
          <div>
            <span class="card-label">Uebersicht</span>
            <h3 id="overview-title">Anstehende Termine</h3>
            <p id="overview-summary">Die naechsten Termine werden geladen.</p>
          </div>
          <div class="button-row">
            <button class="btn btn-secondary" id="overview-print" type="button">Uebersicht drucken</button>
            <button class="btn btn-ghost" id="day-print" type="button">Tag drucken</button>
          </div>
        </div>
        <div class="calendar-overview-metrics" id="overview-metrics"></div>
        <div class="calendar-overview-list" id="overview-list">
          <p class="empty-state">Noch keine Termine vorhanden.</p>
        </div>
      </article>
    </section>

    <script>
      (function () {
        var storageKey = 'webapp-central-calendar-events';
        var monthNames = ['Januar', 'Februar', 'Maerz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        var weekdayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        var recognition = null;
        var editingEventId = '';
        var activeView = 'month';
        var selectedEventId = '';
        var events = loadEvents();
        var now = new Date();
        var selectedDate = toIsoDate(now);
        var viewYear = now.getFullYear();
        var viewMonth = now.getMonth();

        var monthLabel = document.getElementById('calendar-month');
        var grid = document.getElementById('calendar-grid');
        var selectedLabel = document.getElementById('selected-date-label');
        var selectedEvents = document.getElementById('selected-events');
        var selectedEventFocus = document.getElementById('selected-event-focus');
        var overviewTitle = document.getElementById('overview-title');
        var overviewSummary = document.getElementById('overview-summary');
        var overviewMetrics = document.getElementById('overview-metrics');
        var overviewList = document.getElementById('overview-list');
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
        var saveButton = document.getElementById('event-save');
        var cancelButton = document.getElementById('event-cancel');
        var status = document.getElementById('voice-status');
        var transcript = document.getElementById('voice-transcript');
        var monthViewButton = document.getElementById('view-month');
        var weekViewButton = document.getElementById('view-week');

        document.getElementById('calendar-prev').addEventListener('click', function () {
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

        document.getElementById('voice-start').addEventListener('click', function () {
          startVoiceCapture();
        });
        document.getElementById('overview-print').addEventListener('click', function () {
          printAgendaOverview('all');
        });
        document.getElementById('day-print').addEventListener('click', function () {
          printAgendaOverview('day');
        });

        monthViewButton.addEventListener('click', function () {
          setActiveView('month');
        });

        weekViewButton.addEventListener('click', function () {
          setActiveView('week');
        });

        dateInput.value = selectedDate;
        renderCalendar();
        renderSelectedDay();
        renderOverview();

        function loadEvents() {
          try {
            var raw = window.localStorage.getItem(storageKey);
            if (!raw) {
              return [];
            }

            var parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
          } catch (error) {
            return [];
          }
        }

        function persistEvents() {
          window.localStorage.setItem(storageKey, JSON.stringify(events));
        }

        function renderCalendar() {
          syncViewToggle();

          if (activeView === 'week') {
            renderWeekCalendar();
            return;
          }

          monthLabel.textContent = monthNames[viewMonth] + ' ' + viewYear;
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
                item.textContent = (eventItem.time || '--:--') + ' ' + eventItem.title;
                summary.appendChild(item);
              });
            } else {
              summary.innerHTML = '<span>Keine Eintraege</span>';
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
            selectedEvents.innerHTML = '<p class="empty-state">Fuer diesen Tag sind noch keine Termine gespeichert.</p>';
            selectedEventFocus.innerHTML = '<p class="empty-state">Sobald Termine vorhanden sind, erscheint hier die Detailansicht.</p>';
            selectedEventId = '';
            return;
          }

          dayEvents.sort(compareEvents).forEach(function (eventItem) {
            var article = document.createElement('article');
            var title = document.createElement('strong');
            var meta = document.createElement('span');
            var address = document.createElement('p');
            var note = document.createElement('p');
            var actions = document.createElement('div');
            var mapLink = document.createElement('a');
            var editButton = document.createElement('button');
            var removeButton = document.createElement('button');

            article.className = 'agenda-item';
            if (eventItem.id === selectedEventId) {
              article.className += ' is-active';
            }
            title.textContent = eventItem.title;
            meta.className = 'agenda-meta';
            meta.textContent = (eventItem.time || 'Ohne Uhrzeit') + ' | ' + eventItem.date;

            if (eventItem.address) {
              address.className = 'agenda-address';
              address.textContent = eventItem.address;
            }

            note.textContent = eventItem.note || 'Keine Notiz';
            actions.className = 'agenda-actions';

            if (eventItem.address) {
              var preview = document.createElement('iframe');
              mapLink.className = 'btn btn-secondary btn-small';
              mapLink.href = buildMapUrl(eventItem.address);
              mapLink.target = '_blank';
              mapLink.rel = 'noreferrer noopener';
              mapLink.textContent = 'Karte';
              preview.className = 'agenda-map-preview';
              preview.title = 'Kartenvorschau fuer ' + eventItem.title;
              preview.loading = 'lazy';
              preview.referrerPolicy = 'no-referrer-when-downgrade';
              preview.src = buildMapEmbedUrl(eventItem.address);
              actions.appendChild(mapLink);
              article.appendChild(preview);
            }

            editButton.className = 'btn btn-ghost btn-small';
            editButton.type = 'button';
            editButton.textContent = 'Bearbeiten';
            editButton.addEventListener('click', function () {
              loadEventIntoForm(eventItem);
            });

            removeButton.className = 'btn btn-ghost btn-small';
            removeButton.type = 'button';
            removeButton.textContent = 'Loeschen';
            removeButton.addEventListener('click', function () {
              removeEvent(eventItem.id);
            });

            actions.appendChild(editButton);
            actions.appendChild(removeButton);

            article.addEventListener('click', function () {
              selectEvent(eventItem.id);
            });

            article.appendChild(title);
            article.appendChild(meta);
            if (eventItem.address) {
              article.appendChild(address);
            }
            article.appendChild(note);
            article.appendChild(actions);
            selectedEvents.appendChild(article);
          });

          if (!findEventById(selectedEventId) || getEventDate(selectedEventId) !== selectedDate) {
            selectedEventId = dayEvents[0].id;
          }

          renderEventFocus(findEventById(selectedEventId));
        }

        function removeEvent(eventId) {
          events = events.filter(function (eventItem) {
            return eventItem.id !== eventId;
          });
          persistEvents();
          if (selectedEventId === eventId) {
            selectedEventId = '';
          }
          if (editingEventId === eventId) {
            clearEditMode('');
          }
          renderCalendar();
          renderSelectedDay();
          renderOverview();
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
            note: noteValue
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
          selectDate(dateValue);
          selectedEventId = payload.id;
          clearEditMode('');
          transcript.hidden = true;
          renderOverview();
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
          var displayEvents = upcomingEvents.length ? upcomingEvents : sortedEvents;

          overviewTitle.textContent = upcomingEvents.length ? 'Anstehende Termine' : 'Alle Termine';
          overviewSummary.textContent = displayEvents.length
            ? displayEvents.length + ' Termin' + (displayEvents.length === 1 ? '' : 'e') + ' in der Uebersicht.'
            : 'Noch keine Termine vorhanden.';

          renderOverviewMetrics(sortedEvents, upcomingEvents);
          renderOverviewList(displayEvents);
        }

        function renderOverviewMetrics(sortedEvents, upcomingEvents) {
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
            overviewList.innerHTML = '<p class="empty-state">Noch keine Termine vorhanden.</p>';
            return;
          }

          displayEvents.slice(0, 24).forEach(function (eventItem) {
            var article = document.createElement('article');
            var heading = document.createElement('div');
            var title = document.createElement('strong');
            var meta = document.createElement('span');
            var note = document.createElement('p');

            article.className = 'overview-item';
            heading.className = 'overview-item-heading';
            title.textContent = eventItem.title;
            meta.className = 'agenda-meta';
            meta.textContent = formatEventStamp(eventItem);
            note.textContent = eventItem.address || eventItem.note || 'Ohne weitere Details';

            article.addEventListener('click', function () {
              selectDate(eventItem.date);
              selectedEventId = eventItem.id;
              renderSelectedDay();
            });

            heading.appendChild(title);
            heading.appendChild(meta);
            article.appendChild(heading);
            article.appendChild(note);
            overviewList.appendChild(article);
          });
        }

        function setActiveView(nextView) {
          activeView = nextView === 'week' ? 'week' : 'month';
          renderCalendar();
        }

        function syncViewToggle() {
          monthViewButton.className = activeView === 'month' ? 'btn btn-secondary is-active' : 'btn btn-ghost';
          weekViewButton.className = activeView === 'week' ? 'btn btn-secondary is-active' : 'btn btn-ghost';
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
            : getSortedEvents(events);
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

        function startVoiceCapture() {
          var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

          if (!SpeechRecognition) {
            status.textContent = 'Dieser Browser unterstuetzt die Sprachaufnahme aktuell nicht.';
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

              applyTranscript(text);
            });

            recognition.addEventListener('error', function (event) {
              status.textContent = 'Sprachaufnahme fehlgeschlagen: ' + event.error;
            });

            recognition.addEventListener('end', function () {
              if (status.textContent === 'Aufnahme laeuft ...') {
                status.textContent = 'Sprachaufnahme beendet.';
              }
            });
          }

          status.textContent = 'Aufnahme laeuft ...';
          recognition.start();
        }

        function applyTranscript(text) {
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

          renderCalendar();
          renderSelectedDay();
          status.textContent = 'Sprachtext uebernommen. Bitte kurz pruefen und dann speichern.';
        }

        function parseGermanTranscript(text) {
          var result = {
            title: text,
            date: dateInput.value || selectedDate,
            time: ''
          };
          var working = normalizeText(text);
          var today = new Date();
          var dateMatch = working.match(/(\d{1,2})[.\-/](\d{1,2})(?:[.\-/](\d{2,4}))?/);
          var timeMatch = working.match(/(\d{1,2})(?::| uhr ?)(\d{0,2})/);

          if (working.indexOf('uebermorgen') !== -1) {
            result.date = shiftDate(today, 2);
          } else if (working.indexOf('morgen') !== -1) {
            result.date = shiftDate(today, 1);
          } else if (working.indexOf('heute') !== -1) {
            result.date = shiftDate(today, 0);
          } else if (dateMatch) {
            var year = dateMatch[3] ? Number(dateMatch[3]) : today.getFullYear();
            if (year < 100) {
              year += 2000;
            }
            result.date = [
              String(year),
              pad(Number(dateMatch[2])),
              pad(Number(dateMatch[1]))
            ].join('-');
          }

          if (timeMatch) {
            result.time = pad(Number(timeMatch[1])) + ':' + pad(Number(timeMatch[2] || 0));
          }

          result.title = cleanTitle(working);
          return result;
        }

        function cleanTitle(text) {
          return text
            .replace(/uebermorgen|morgen|heute/gi, ' ')
            .replace(/\d{1,2}[.\-/]\d{1,2}(?:[.\-/]\d{2,4})?/g, ' ')
            .replace(/\d{1,2}(?::| uhr ?)\d{0,2}/g, ' ')
            .replace(/\s+/g, ' ')
            .trim() || 'Neuer Termin';
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
