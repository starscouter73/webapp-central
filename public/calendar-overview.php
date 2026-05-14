<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Terminuebersicht', 'Termine', static function (): void {
    ?>
    <section class="calendar-page">
      <article class="card calendar-overview-panel">
        <div class="calendar-overview-toolbar">
          <div>
            <span class="card-label">Uebersicht</span>
            <h3 id="overview-title">Anstehende Termine</h3>
            <p id="overview-summary">Die naechsten Termine werden geladen.</p>
          </div>
          <div class="button-row">
            <a class="btn btn-ghost" href="<?= app_h(app_url('calendar.php')) ?>">Zum Kalender</a>
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
    </section>

    <script>
      (function () {
        var storageKey = 'webapp-central-calendar-events';
        var overviewTitle = document.getElementById('overview-title');
        var overviewSummary = document.getElementById('overview-summary');
        var overviewMetrics = document.getElementById('overview-metrics');
        var overviewList = document.getElementById('overview-list');
        var overviewSearch = document.getElementById('overview-search');
        var events = loadEvents();

        document.getElementById('overview-print').addEventListener('click', function () {
          printOverview();
        });

        document.getElementById('overview-pdf').addEventListener('click', function () {
          exportOverviewPdf();
        });

        overviewSearch.addEventListener('input', function () {
          renderOverview();
        });

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

          renderOverviewMetrics(sortedEvents, upcomingEvents, displayEvents);
          renderOverviewList(displayEvents);
        }

        function renderOverviewMetrics(sortedEvents, upcomingEvents, displayEvents) {
          var nextEvent = upcomingEvents.length ? upcomingEvents[0] : null;
          var cards = [
            { label: 'Gesamt', value: String(sortedEvents.length) },
            { label: 'Anstehend', value: String(upcomingEvents.length) },
            { label: 'Naechster Termin', value: nextEvent ? formatEventStamp(nextEvent) : 'Keiner' },
            { label: 'Gefiltert', value: String(displayEvents.length) }
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
              window.location.href = '<?= app_h(app_url('calendar.php')) ?>?date='
                + encodeURIComponent(eventItem.date)
                + '&event=' + encodeURIComponent(eventItem.id);
            });

            heading.appendChild(title);
            heading.appendChild(meta);
            article.appendChild(heading);
            article.appendChild(note);
            overviewList.appendChild(article);
          });
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

        function printOverview() {
          var printableEvents = getOverviewEventsForExport();
          var popup = window.open('', '_blank', 'width=980,height=720');

          if (!popup) {
            return;
          }

          popup.document.open();
          popup.document.write(buildPrintDocument('Terminuebersicht', 'Gesamte Terminliste aus Webapp Central.', printableEvents));
          popup.document.close();
          popup.focus();
          popup.print();
        }

        function exportOverviewPdf() {
          var pdfEvents = getOverviewEventsForExport();
          var pdfContent = buildPdfDocument('Terminuebersicht', pdfEvents);
          var pdfBytes = stringToByteArray(pdfContent);
          var blob = new Blob([pdfBytes], { type: 'application/pdf' });
          var link = document.createElement('a');

          link.href = URL.createObjectURL(blob);
          link.download = 'termine-uebersicht.pdf';
          link.click();

          window.setTimeout(function () {
            URL.revokeObjectURL(link.href);
          }, 1000);
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

        function normalizeText(value) {
          return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
        }

        function escapeHtml(value) {
          return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
        }

        function pad(value) {
          return String(value).padStart(2, '0');
        }
      }());
    </script>
    <?php
});
