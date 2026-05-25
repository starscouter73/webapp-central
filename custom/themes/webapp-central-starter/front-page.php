<?php
declare(strict_types=1);

get_header();

$platform_modules = webapp_central_starter_portal_sections();
$workflow_cards = [
    [
        'title' => __('Dokumentation als Plattformgedaechtnis', 'webapp-central-starter'),
        'description' => __('Strukturierte Referenztexte, nachvollziehbare Arbeitswege und Git-gestuetzte Dokumentationsspuren fuer Entwicklung und Betrieb.', 'webapp-central-starter'),
        'link' => webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/'),
        'label' => __('Dokumentation ansehen', 'webapp-central-starter'),
    ],
    [
        'title' => __('GitHub als Steuerzentrale', 'webapp-central-starter'),
        'description' => __('Repository, Versionierung und technische Governance bleiben die verbindliche Quelle fuer Aenderungen, Reviews und Langzeitpflege.', 'webapp-central-starter'),
        'link' => 'https://github.com/starscouter73/webapp-central',
        'label' => __('Repository oeffnen', 'webapp-central-starter'),
    ],
    [
        'title' => __('Codex, Agents und Automatisierung', 'webapp-central-starter'),
        'description' => __('KI-gestuetzte Workflows werden kontrolliert in Dokumentation, Strukturarbeit und operative Umsetzung eingebunden.', 'webapp-central-starter'),
        'link' => webapp_central_starter_portal_page_url('system', '/system/'),
        'label' => __('Systemkontext lesen', 'webapp-central-starter'),
    ],
];

$roadmap_cards = [
    [
        'phase' => __('Phase 01', 'webapp-central-starter'),
        'title' => __('Struktur sichern', 'webapp-central-starter'),
        'description' => __('Theme-, Repo- und Dokumentationsbasis stehen, damit Inhalte und Module kontrolliert wachsen koennen.', 'webapp-central-starter'),
    ],
    [
        'phase' => __('Phase 02', 'webapp-central-starter'),
        'title' => __('Module ausbauen', 'webapp-central-starter'),
        'description' => __('Projekt-Hubs, Dokumentationen, Medienbereiche und spezielle Arbeitsflaechen werden schrittweise konkretisiert.', 'webapp-central-starter'),
    ],
    [
        'phase' => __('Phase 03', 'webapp-central-starter'),
        'title' => __('Workflows automatisieren', 'webapp-central-starter'),
        'description' => __('Codex-, Agenten- und spaetere Serverprozesse werden erst nach klarer Governance in den Betrieb uebernommen.', 'webapp-central-starter'),
    ],
];
?>
<main class="platform-home">
    <?php while (have_posts()) : the_post(); ?>
        <section class="platform-hero">
            <div class="platform-hero__content">
                <span class="portal-badge"><?php esc_html_e('Steuerzentrale', 'webapp-central-starter'); ?></span>
                <h1><?php esc_html_e('Webapp Central', 'webapp-central-starter'); ?></h1>
                <p class="platform-hero__subheadline"><?php esc_html_e('Digitale Steuerzentrale fuer Projekte, Dokumentation und intelligente Workflows.', 'webapp-central-starter'); ?></p>
                <p class="platform-hero__claim"><?php esc_html_e('Struktur. Dokumentation. Automatisierung.', 'webapp-central-starter'); ?></p>
                <div class="hero__actions">
                    <a class="button-link" href="<?php echo esc_url(webapp_central_starter_portal_page_url('projekte', '/projekte/')); ?>">
                        <?php esc_html_e('Plattform ansehen', 'webapp-central-starter'); ?>
                    </a>
                    <a class="button-link button-link--ghost" href="<?php echo esc_url(webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/')); ?>">
                        <?php esc_html_e('Dokumentation', 'webapp-central-starter'); ?>
                    </a>
                </div>
                <div class="hero__stats platform-hero__stats">
                    <article class="hero__stat">
                        <strong><?php esc_html_e('Modular', 'webapp-central-starter'); ?></strong>
                        <span><?php esc_html_e('Projekt-Hubs, Inhalte und Arbeitsbereiche mit klaren Grenzen.', 'webapp-central-starter'); ?></span>
                    </article>
                    <article class="hero__stat">
                        <strong><?php esc_html_e('Nachvollziehbar', 'webapp-central-starter'); ?></strong>
                        <span><?php esc_html_e('Dokumentation, Git-Historie und Governance bleiben gekoppelt.', 'webapp-central-starter'); ?></span>
                    </article>
                    <article class="hero__stat">
                        <strong><?php esc_html_e('KI-kooperativ', 'webapp-central-starter'); ?></strong>
                        <span><?php esc_html_e('Codex und Agents unterstuetzen Strukturarbeit statt unkontrollierter Automation.', 'webapp-central-starter'); ?></span>
                    </article>
                </div>
            </div>
            <aside class="platform-hero__aside">
                <div class="platform-hero-card">
                    <span class="portal-badge portal-badge--soft"><?php esc_html_e('Live-Ausrichtung', 'webapp-central-starter'); ?></span>
                    <h2><?php esc_html_e('Eine Plattform statt einer klassischen Agentur-Website', 'webapp-central-starter'); ?></h2>
                    <ul class="platform-list">
                        <li><?php esc_html_e('ruhige, dunkle Oberflaeche mit hohem Kontrast', 'webapp-central-starter'); ?></li>
                        <li><?php esc_html_e('klare Module fuer Projekte, Dokumentation, Medien und Systemlogik', 'webapp-central-starter'); ?></li>
                        <li><?php esc_html_e('Startseite als Uebersicht, nicht als Blog oder Marketing-Sammelflaeche', 'webapp-central-starter'); ?></li>
                    </ul>
                </div>
            </aside>
        </section>

        <section class="platform-section platform-section--split" id="was-ist-webapp-central">
            <div class="platform-panel">
                <span class="portal-badge portal-badge--soft"><?php esc_html_e('Was ist Webapp Central?', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Kontrollierte Projektumgebung mit Plattformlogik', 'webapp-central-starter'); ?></h2>
                <p><?php esc_html_e('Webapp Central ist als digitale Steuerzentrale gedacht: nicht nur fuer eine Website, sondern fuer Projektorganisation, technische Dokumentation, Referenzsysteme und spaetere intelligente Arbeitsablaeufe.', 'webapp-central-starter'); ?></p>
                <p><?php esc_html_e('Der Auftritt soll nach aussen reduziert und praezise wirken, waehrend intern Module, Workspaces und Entwicklungsprozesse sauber organisiert bleiben.', 'webapp-central-starter'); ?></p>
            </div>
            <div class="platform-panel">
                <span class="portal-badge portal-badge--soft"><?php esc_html_e('Plattformprinzip', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Von Inhalten bis Governance in einem System', 'webapp-central-starter'); ?></h2>
                <ul class="platform-list">
                    <li><?php esc_html_e('oeffentliche Klarheit bei gleichzeitig interner technischer Tiefe', 'webapp-central-starter'); ?></li>
                    <li><?php esc_html_e('dokumentierte Entwicklung statt chaotischer Einzelaktionen', 'webapp-central-starter'); ?></li>
                    <li><?php esc_html_e('Workspace-orientierte Pflege mit Git, VSCode und Codex', 'webapp-central-starter'); ?></li>
                </ul>
            </div>
        </section>

        <section class="platform-section" id="plattformmodule">
            <div class="platform-section__heading">
                <span class="portal-badge"><?php esc_html_e('Plattformmodule', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Bereiche fuer Projekte, Dokumentation und Systemarbeit', 'webapp-central-starter'); ?></h2>
                <p><?php esc_html_e('Die Kernbereiche sind als navigierbare Module aufgebaut und koennen spaeter eigenstaendig wachsen, ohne die Gesamtstruktur zu zerfasern.', 'webapp-central-starter'); ?></p>
            </div>
            <div class="platform-grid platform-grid--modules">
                <?php foreach ($platform_modules as $module) : ?>
                    <article class="platform-card">
                        <div class="platform-card__meta">
                            <span class="platform-card__status"><?php echo esc_html((string) $module['badge']); ?></span>
                        </div>
                        <h3><?php echo esc_html((string) $module['title']); ?></h3>
                        <p><?php echo esc_html((string) $module['description']); ?></p>
                        <a class="platform-card__link" href="<?php echo esc_url((string) $module['url']); ?>"><?php esc_html_e('Modul oeffnen', 'webapp-central-starter'); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="platform-section" id="dokumentation-github">
            <div class="platform-section__heading">
                <span class="portal-badge"><?php esc_html_e('Dokumentation & GitHub', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Repository und Dokumentation bleiben die verbindliche Arbeitsbasis', 'webapp-central-starter'); ?></h2>
            </div>
            <div class="platform-grid platform-grid--workflows">
                <?php foreach ($workflow_cards as $card) : ?>
                    <article class="platform-card platform-card--workflow">
                        <h3><?php echo esc_html((string) $card['title']); ?></h3>
                        <p><?php echo esc_html((string) $card['description']); ?></p>
                        <a class="platform-card__link" href="<?php echo esc_url((string) $card['link']); ?>"><?php echo esc_html((string) $card['label']); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="platform-section" id="automatisierung-codex-agents">
            <div class="platform-section__heading">
                <span class="portal-badge"><?php esc_html_e('Automatisierung', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Codex, Agents und spaetere Workflows mit Governance-Rahmen', 'webapp-central-starter'); ?></h2>
                <p><?php esc_html_e('Automatisierung wird nicht als Show-Effekt verstanden, sondern als kontrollierte Erweiterung fuer Analyse, Strukturpflege und technische Umsetzung.', 'webapp-central-starter'); ?></p>
            </div>
            <div class="platform-grid platform-grid--triad">
                <article class="platform-card">
                    <h3><?php esc_html_e('Codex-Workspace', 'webapp-central-starter'); ?></h3>
                    <p><?php esc_html_e('Lokale Arbeit im Repository mit klaren Diff-Spuren, Dokumentationspflege und nachvollziehbaren Theme-Anpassungen.', 'webapp-central-starter'); ?></p>
                </article>
                <article class="platform-card">
                    <h3><?php esc_html_e('Agentenregeln', 'webapp-central-starter'); ?></h3>
                    <p><?php esc_html_e('AGENTS.md definiert Verhalten, Governance und sichere Arbeitsgrenzen fuer KI-gestuetzte Entwicklungsschritte.', 'webapp-central-starter'); ?></p>
                </article>
                <article class="platform-card">
                    <h3><?php esc_html_e('Spaetere Serverprozesse', 'webapp-central-starter'); ?></h3>
                    <p><?php esc_html_e('Deployment und produktive Automationen werden erst nach Freigaben, Rollback-Logik und Ownership-Regeln aktiviert.', 'webapp-central-starter'); ?></p>
                </article>
            </div>
        </section>

        <section class="platform-section" id="projektstatus-roadmap">
            <div class="platform-section__heading">
                <span class="portal-badge"><?php esc_html_e('Projektstatus', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Roadmap fuer den kontrollierten Ausbau', 'webapp-central-starter'); ?></h2>
            </div>
            <div class="platform-grid platform-grid--roadmap">
                <?php foreach ($roadmap_cards as $card) : ?>
                    <article class="platform-card platform-card--roadmap">
                        <span class="platform-card__phase"><?php echo esc_html((string) $card['phase']); ?></span>
                        <h3><?php echo esc_html((string) $card['title']); ?></h3>
                        <p><?php echo esc_html((string) $card['description']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="platform-section platform-section--cta">
            <div class="platform-cta">
                <span class="portal-badge"><?php esc_html_e('Naechster Schritt', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Die Plattform ist bereit fuer den weiteren Ausbau.', 'webapp-central-starter'); ?></h2>
                <p><?php esc_html_e('Projektbereiche, Dokumentationen und Workflows koennen nun innerhalb derselben visuellen und strukturellen Logik weiterentwickelt werden.', 'webapp-central-starter'); ?></p>
                <div class="hero__actions">
                    <a class="button-link" href="<?php echo esc_url(webapp_central_starter_portal_page_url('projekte', '/projekte/')); ?>">
                        <?php esc_html_e('Module erkunden', 'webapp-central-starter'); ?>
                    </a>
                    <a class="button-link button-link--ghost" href="<?php echo esc_url(webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/')); ?>">
                        <?php esc_html_e('Dokumentation lesen', 'webapp-central-starter'); ?>
                    </a>
                </div>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php
get_footer();
