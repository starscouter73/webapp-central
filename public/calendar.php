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
          <div class="button-row">
            <button class="btn btn-ghost" id="calendar-prev" type="button">Zurueck</button>
            <button class="btn btn-secondary" id="calendar-today" type="button">Heute</button>
            <button class="btn btn-ghost" id="calendar-next" type="button">Weiter</button>
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
            <div class="calendar-agenda" id="selected-events">
              <p class="empty-state">Waehle einen Tag im Kalender oder lege direkt einen neuen Termin an.</p>
            </div>
          </article>

          <article class="calendar-subpanel calendar-panel">
            <span class="card-label">Neuer Termin</span>
            <h3>Per Sprache oder manuell eintragen</h3>
            <p>Sage zum Beispiel: "Morgen 14 Uhr Zahnarzt" oder "20.05. 09:30 Teammeeting". Danach kannst du den Eintrag direkt speichern.</p>

            <div class="calendar-form">
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
                <span>Notiz</span>
                <textarea id="event-note" name="note" rows="3" placeholder="Optionale Details"></textarea>
              </label>
              <div class="button-row">
                <button class="btn btn-primary" id="voice-start" type="button">Voice to Text starten</button>
                <button class="btn btn-secondary" id="event-save" type="button">Termin speichern</button>
              </div>
              <p class="calendar-status" id="voice-status">Sprachmodus bereit. Die Funktion nutzt die Sprachschnittstelle deines Browsers.</p>
              <p class="calendar-transcript" id="voice-transcript" hidden></p>
            </div>

            <div class="calendar-note">
              <strong>Hinweis</strong>
              <p>Die Spracheingabe laeuft direkt im Browser. Termine bleiben aktuell lokal auf diesem Geraet gespeichert.</p>
            </div>
          </article>
        </div>
      </article>
    </section>

    <script>
      (function () {
        var storageKey = 'webapp-central-calendar-events';
        var monthNames = ['Januar', 'Februar', 'Maerz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
        var weekdayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        var recognition = null;
        var events = loadEvents();
        var now = new Date();
        var selectedDate = toIsoDate(now);
        var viewYear = now.getFullYear();
        var viewMonth = now.getMonth();

        var monthLabel = document.getElementById('calendar-month');
        var grid = document.getElementById('calendar-grid');
        var selectedLabel = document.getElementById('selected-date-label');
        var selectedEvents = document.getElementById('selected-events');
        var titleInput = document.getElementById('event-title');
        var dateInput = document.getElementById('event-date');
        var timeInput = document.getElementById('event-time');
        var noteInput = document.getElementById('event-note');
        var status = document.getElementById('voice-status');
        var transcript = document.getElementById('voice-transcript');

        document.getElementById('calendar-prev').addEventListener('click', function () {
          viewMonth -= 1;
          if (viewMonth < 0) {
            viewMonth = 11;
            viewYear -= 1;
          }
          renderCalendar();
        });

        document.getElementById('calendar-next').addEventListener('click', function () {
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

        document.getElementById('event-save').addEventListener('click', function () {
          saveEventFromForm();
        });

        document.getElementById('voice-start').addEventListener('click', function () {
          startVoiceCapture();
        });

        dateInput.value = selectedDate;
        renderCalendar();
        renderSelectedDay();

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

          if (!dayEvents.length) {
            selectedEvents.innerHTML = '<p class="empty-state">Fuer diesen Tag sind noch keine Termine gespeichert.</p>';
            return;
          }

          dayEvents.sort(compareEvents).forEach(function (eventItem) {
            var article = document.createElement('article');
            var title = document.createElement('strong');
            var meta = document.createElement('span');
            var note = document.createElement('p');
            var removeButton = document.createElement('button');

            article.className = 'agenda-item';
            title.textContent = eventItem.title;
            meta.className = 'agenda-meta';
            meta.textContent = (eventItem.time || 'Ohne Uhrzeit') + ' | ' + eventItem.date;
            note.textContent = eventItem.note || 'Keine Notiz';
            removeButton.className = 'btn btn-ghost btn-small';
            removeButton.type = 'button';
            removeButton.textContent = 'Loeschen';
            removeButton.addEventListener('click', function () {
              removeEvent(eventItem.id);
            });

            article.appendChild(title);
            article.appendChild(meta);
            article.appendChild(note);
            article.appendChild(removeButton);
            selectedEvents.appendChild(article);
          });
        }

        function removeEvent(eventId) {
          events = events.filter(function (eventItem) {
            return eventItem.id !== eventId;
          });
          persistEvents();
          renderCalendar();
          renderSelectedDay();
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
          var noteValue = noteInput.value.trim();

          if (!title || !dateValue) {
            status.textContent = 'Bitte mindestens Titel und Datum fuer den Termin angeben.';
            return;
          }

          events.push({
            id: String(Date.now()) + '-' + Math.random().toString(16).slice(2),
            title: title,
            date: dateValue,
            time: timeValue,
            note: noteValue
          });

          persistEvents();
          selectDate(dateValue);
          titleInput.value = '';
          timeInput.value = '';
          noteInput.value = '';
          transcript.hidden = true;
          status.textContent = 'Termin gespeichert.';
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
            .replace(/ä/g, 'ae')
            .replace(/ö/g, 'oe')
            .replace(/ü/g, 'ue')
            .replace(/ß/g, 'ss');
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
      }());
    </script>
    <?php
});
