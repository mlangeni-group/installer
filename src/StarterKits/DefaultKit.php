<?php

namespace Machinjiri\Installer\StarterKits;

class DefaultKit
{
  public static $options = [];

  public static function getOptions(array $options): void 
  {
    self::$options = $options;
  }

  public static function files(): array 
  {
    return [
      'routes' => [
          'file' => '/routes/web.php',
          'template' => self::RoutesTemplate(),
        ],
      'welcome' => [
          'file' => '/resources/views/welcome.view.php',
          'template' => self::WelcomeTemplate(),
        ],
      'home-controller' => [
          'file' => '/app/Controllers/HomeController.php',
          'template' => self::HomeControllerTemplate(),
        ],
      'style' => [
          'file' => '/public/src/css/main.css',
          'template' => self::style(),
      ],
      'layout' => [
        'file' => '/resources/views/layouts/main.layout.php',
        'template' => self::layout(),
      ]
    ];
  }
  
  private static function RoutesTemplate(): string { return <<<'PHP'
<?php

use Mlangeni\Machinjiri\Core\Routing\Router;
use App\Controllers\HomeController;

/**
 * Web Routes
 * Define your web routes here.
 * You can create additional route files as needed.
 * Remember to keep your routes organized and manageable.
 */

/* Welcome Route */
Router::get('/', [HomeController::class, 'index'], 'welcome');




/* Dispatch the router to handle the incoming request */
Router::dispatch();

PHP;
    }

  private static function HomeControllerTemplate(): string { return <<<PHP
<?php

namespace App\Controllers;

use Mlangeni\Machinjiri\Core\Artisans\Base\AbstractController;
use Mlangeni\Machinjiri\Core\Http\HttpRequest;
use Mlangeni\Machinjiri\Core\Http\HttpResponse;

class HomeController extends AbstractController
{
    /**
     * Display the welcome page.
     *
     * @param HttpRequest  \$request
     * @param HttpResponse \$response
     * @return string|HttpResponse
     */
    public function index(HttpRequest \$request, HttpResponse \$response)
    {
        // Example: render a view
        return \$this->view('welcome');
    }
    
    // Add your custom methods below
}
PHP;
  }

  private static function WelcomeTemplate(): string { 
    $version = htmlspecialchars((self::$options['version'] === '*') ? 'Latest' : self::$options['version'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $date = htmlspecialchars(self::$options['date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $appName = htmlspecialchars(self::$options['project_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $current = date('Y');

    return <<<HTML
<?php use Mlangeni\Machinjiri\Core\Views\View; ?>

@extends('layouts.main')

@section('content')
<div class="page-shell">
  <header class="topbar">
    <a class="brand" href="/" aria-label="Machinjiri home">
      <span>machinjiri<span class="brand-period">.</span></span>
    </a>
    <div class="topbar-actions">
      <span class="workspace-label"><span class="status-dot"></span> Your workspace</span>
      <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch to dark theme" aria-pressed="false">
        <i class="fas fa-moon" aria-hidden="true"></i>
        <span>Dark mode</span>
      </button>
    </div>
  </header>

  <main>
    <section class="welcome-hero" aria-labelledby="welcome-title">
      <div class="hero-copy">
        <div class="eyebrow"><span class="eyebrow-line"></span> INSTALLATION COMPLETE</div>
        <h1 id="welcome-title">A fresh start.<br><span>Built for what's next.</span></h1>
        <p>Your Machinjiri project is installed and ready. The essentials are in place - now make this space your own.</p>
        <div class="hero-status"><span class="status-check"><i class="fas fa-check" aria-hidden="true"></i></span> All systems ready to build</div>
      </div>
    </section>

    <div class="content-grid">
      <section class="panel details-panel" aria-labelledby="details-title">
        <div class="section-heading">
          <span class="section-icon"><i class="fas fa-sliders" aria-hidden="true"></i></span>
          <div>
            <h2 id="details-title">Project overview</h2>
            <p>Your installation at a glance</p>
          </div>
        </div>
        <dl class="details-list">
          <div class="detail-row">
            <dt><i class="fas fa-folder" aria-hidden="true"></i> Project name</dt>
            <dd>{$appName}</dd>
          </div>
          <div class="detail-row">
            <dt><i class="fas fa-code-branch" aria-hidden="true"></i> Framework version</dt>
            <dd>{$version} <span class="version-tag">stable</span></dd>
          </div>
          <div class="detail-row">
            <dt><i class="fas fa-calendar-days" aria-hidden="true"></i> Installed</dt>
            <dd>{$date}</dd>
          </div>
          <div class="detail-row">
            <dt><i class="fas fa-shield-halved" aria-hidden="true"></i> Project status</dt>
            <dd><span class="ready-badge"><span class="status-dot"></span> Ready</span></dd>
          </div>
        </dl>
      </section>

      <section class="panel quickstart-panel" aria-labelledby="quickstart-title">
        <div class="section-heading">
          <span class="section-icon section-icon-accent"><i class="fas fa-terminal" aria-hidden="true"></i></span>
          <div>
            <h2 id="quickstart-title">Your first steps</h2>
            <p>A few building blocks to get moving</p>
          </div>
        </div>
        <ol class="steps-list">
          <li class="step-item">
            <span class="step-number">01</span>
            <div class="step-copy">
              <h3>Define a route</h3>
              <code>routes/web.php</code>
            </div>
            <i class="fas fa-arrow-right step-arrow" aria-hidden="true"></i>
          </li>
          <li class="step-item">
            <span class="step-number">02</span>
            <div class="step-copy">
              <h3>Create a controller</h3>
              <code>php artisan make:controller HomeController</code>
            </div>
            <i class="fas fa-arrow-right step-arrow" aria-hidden="true"></i>
          </li>
          <li class="step-item">
            <span class="step-number">03</span>
            <div class="step-copy">
              <h3>Build your first view</h3>
              <code>php artisan view:make</code>
            </div>
            <i class="fas fa-arrow-right step-arrow" aria-hidden="true"></i>
          </li>
        </ol>
      </section>
    </div>

    <section class="next-steps" aria-label="Continue building">
      <div class="next-steps-copy">
        <span class="next-steps-icon"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i></span>
        <div>
          <h2>Ready when you are.</h2>
          <p>Explore the framework and start shaping your application.</p>
        </div>
      </div>
      <a href="https://github.com/mlangeni-group/machinjiri" class="docs-link" target="_blank" rel="noopener noreferrer">
        Explore the docs <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
      </a>
    </section>
  </main>

  <footer class="footer-meta">
    <span>&copy; {$current} Mlangeni QuarterMaster</span>
    <a href="mailto:precious.lyson@gmail.com"><i class="fas fa-life-ring" aria-hidden="true"></i> Need a hand?</a>
  </footer>
</div>
@endsection
HTML;
    }

    private static function style(): string
    {
        return <<<'CSS'
:root {
    --bg: #f6f9fc;
    --success-bg: #ffffff;
    --color: #1a2b3e;
    --primary: rgba(109, 91, 208, .10);
    --secondary: rgb(109, 91, 208);
    --radius: 1rem;
    --shadow: 0 25px 50px -12px rgba(0, 20, 30, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.02);
}

@media (prefers-color-scheme: dark) {
    :root {
        --bg: #0f1116;
        --success-bg: #161922;
        --color: #c8cdd8;
    }
    
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: var(--bg);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1.5rem;
    color: var(--color);
}

.success-card {
    max-width: 1000px;
    width: 100%;
    background: var(--success-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 2.5rem;
    transition: all 0.2s ease;
}

/* header with success mark */
.success-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.check-icon {
    background: var(--primary);
    color: var(--color);
    width: 3.5rem;
    height: 3.5rem;
    border-radius: var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    box-shadow: 0 8px 18px -4px rgba(16, 185, 129, 0.35);
}

.success-header h1 {
    font-size: 1.9rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--color);
    line-height: 1.2;
}

.success-header h1 span {
    font-weight: 400;
    color: var(--secondary);
    font-size: 1.1rem;
    display: block;
    margin-top: 0.2rem;
    letter-spacing: 0;
}

/* main grid: left details, right tutorial */
.grid-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

/* installation details panel */
.details-panel {
    border-radius: var(--radius);
    padding: 1.8rem 1.6rem;
    border: 1px solid rgba(255,255,255,0.3);
}

.panel-title {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--color);
    margin-bottom: 1.3rem;
    letter-spacing: -0.01em;
}

.panel-title i {
    color: var(--color);
    font-size: 1.2rem;
    background: rgba(45, 122, 155, 0.1);
    padding: 0.4rem;
    border-radius: 0.7rem;
    width: 2rem;
    text-align: center;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding: 0.9rem 0;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 500;
    color: var(--secondary);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-label i {
    width: 1.1rem;
    color: var(--secondary);
    font-size: 0.85rem;
    text-align: center;
}

.detail-value {
    font-weight: 600;
    color: var(--color);
    background: var(--primary);
    padding: 0.3rem 0.8rem;
    border-radius: var(--radius);
    font-size: 0.9rem;
    letter-spacing: 0.01em;
    word-break: break-word;
    text-align: right;
    max-width: 60%;
}

.badge-success {
    background: #e3f9ef;
    color: #0b6e4f;
    padding: 0.25rem 0.9rem;
    border-radius: var(--radius);
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    border: 1px solid #b6e6d3;
}

/* tutorial panel */
.tutorial-panel {
    background: var(--success-bg);
    border-radius: var(--radius);
    padding: 1.8rem 1.6rem;
    border: 1px solid rgba(255,255,255,0.3);
}

.tutorial-steps {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
    margin-top: 0.8rem;
}

.step-item {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.step-num {
    background: #1e3b5c;
    color: white;
    font-weight: 700;
    font-size: 0.85rem;
    width: 1.9rem;
    height: 1.9rem;
    border-radius: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 0.1rem;
    box-shadow: 0 4px 8px -2px rgba(0, 30, 50, 0.2);
}

.step-content {
    flex: 1;
}

.step-content p {
    font-size: 0.95rem;
    line-height: 1.5;
    color: #1f3a4e;
    font-weight: 500;
}

.step-content code {
    background: #e5edf4;
    padding: 0.15rem 0.5rem;
    border-radius: 0.6rem;
    font-family: 'SF Mono', 'Fira Code', monospace;
    font-size: 0.85rem;
    color: #0b2b42;
    font-weight: 600;
    border: 1px solid #cedce7;
    margin: 0 0.1rem;
}

.step-content .small-hint {
    font-size: 0.8rem;
    color: #5f7d96;
    margin-top: 0.2rem;
    font-weight: 400;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.step-content .small-hint i {
    font-size: 0.65rem;
    color: #5f7d96;
}

/* next steps section */
.next-steps {
    margin-top: 1.8rem;
    background: linear-gradient(105deg, #eef7fb 0%, #e4f0f7 100%);
    border-radius: var(--radius);
    padding: 1.6rem 1.9rem;
    border: 1px solid #cfe1ed;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.next-steps-left {
    display: flex;
    align-items: center;
    gap: 0.9rem;
}

.next-steps-left i {
    font-size: 1.8rem;
    color: #1f5e7a;
    background: #ffffffc0;
    padding: 0.5rem;
    border-radius: 1.2rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.next-steps-text h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0d2b3f;
    letter-spacing: -0.01em;
    margin-bottom: 0.2rem;
}

.next-steps-text p {
    font-size: 0.9rem;
    color: #3b5f7a;
    font-weight: 400;
}

.next-buttons {
    display: flex;
    gap: 0.8rem;
    flex-wrap: wrap;
}

.btn {
    border: none;
    padding: 0.8rem 1.5rem;
    border-radius: 2rem;
    font-weight: 600;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: default;
    transition: all 0.15s;
    background: white;
    color: #1e3b5c;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.02);
    text-decoration: none;
}

.btn-primary {
    background: #1e3b5c;
    color: white;
    box-shadow: 0 8px 18px -6px rgba(20, 60, 90, 0.3);
}

.btn i {
    font-size: 0.9rem;
}

.btn-primary i {
    color: #b3d9f0;
}

/* small footnote / meta */
.footer-meta {
    margin-top: 1.5rem;
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #7f99b0;
    border-top: 1px solid #deecf3;
    padding-top: 1.2rem;
    flex-wrap: wrap;
    gap: 0.8rem;
}

.footer-meta i {
    margin-right: 0.3rem;
}

/* responsive */
@media (max-width: 760px) {
    .success-card {
    padding: 1.8rem;
    }

    .grid-layout {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    }

    .success-header h1 {
    font-size: 1.6rem;
    }

    .next-steps {
    flex-direction: column;
    align-items: flex-start;
    }

    .next-buttons {
    width: 100%;
    }

    .btn {
    flex: 1;
    justify-content: center;
    }

    .detail-value {
    max-width: 55%;
    }
}

@media (max-width: 480px) {
    .success-card {
    padding: 1.3rem;
    border-radius: 2rem;
    }

    .check-icon {
    width: 2.8rem;
    height: 2.8rem;
    font-size: 1.5rem;
    }

    .success-header h1 {
    font-size: 1.4rem;
    }

    .details-panel,
    .tutorial-panel {
    padding: 1.4rem;
    }

    .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.4rem;
    }

    .detail-value {
    max-width: 100%;
    text-align: left;
    width: auto;
    background: #eef4f9;
    }

    .next-steps {
    padding: 1.3rem;
    }

    .next-buttons {
    flex-direction: column;
    }
}

:root {
    color-scheme: light;
    --page: #f5f6fa;
    --surface: #ffffff;
    --surface-soft: #f8f9fc;
    --text: #20243a;
    --muted: #777d91;
    --line: #e9eaf1;
    --accent: #6958d6;
    --accent-soft: #f0edff;
    --green: #168a64;
    --green-soft: #e9f7f1;
    --shadow: 0 22px 65px rgba(34, 39, 72, .08), 0 2px 8px rgba(34, 39, 72, .03);
    --radius: 1.25rem;
}

:root[data-theme="light"] {
    color-scheme: light;
}

:root[data-theme="dark"] {
    color-scheme: dark;
    --page: #11131b;
    --surface: #1a1d28;
    --surface-soft: #202431;
    --text: #f0f1f8;
    --muted: #a0a5b8;
    --line: #303443;
    --accent: #a99bff;
    --accent-soft: #302b4c;
    --green: #74d6b1;
    --green-soft: #20382f;
    --shadow: 0 22px 65px rgba(0, 0, 0, .24), 0 2px 8px rgba(0, 0, 0, .14);
}

@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]):not([data-theme="dark"]) {
        color-scheme: dark;
        --page: #11131b;
        --surface: #1a1d28;
        --surface-soft: #202431;
        --text: #f0f1f8;
        --muted: #a0a5b8;
        --line: #303443;
        --accent: #a99bff;
        --accent-soft: #302b4c;
        --green: #74d6b1;
        --green-soft: #20382f;
        --shadow: 0 22px 65px rgba(0, 0, 0, .24), 0 2px 8px rgba(0, 0, 0, .14);
    }
}

html {
    min-width: 320px;
    background: var(--page);
}

body {
    display: block;
    min-height: 100vh;
    padding: 0 2rem;
    color: var(--text);
    background:
        radial-gradient(ellipse at 8% 0%, rgba(116, 98, 222, .09), transparent 34rem),
        var(--page);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    -webkit-font-smoothing: antialiased;
    transition: background-color .2s ease, color .2s ease;
}

a {
    color: inherit;
}

:focus-visible {
    outline: 3px solid var(--accent);
    outline-offset: 3px;
}

.page-shell {
    width: min(100%, 1080px);
    min-height: 100vh;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
}

.topbar {
    min-height: 82px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.brand {
    display: inline-flex;
    align-items: center;
    gap: .65rem;
    color: var(--text);
    font-size: 1.16rem;
    font-weight: 750;
    letter-spacing: -.055em;
    text-decoration: none;
}

.brand-mark {
    width: 2rem;
    height: 2rem;
    display: grid;
    place-items: center;
    border-radius: .7rem;
    color: #fff;
    background: linear-gradient(145deg, #8474ee, #5c4bc7);
    font-size: .9rem;
    box-shadow: 0 5px 12px rgba(105, 88, 214, .23);
}

.brand-period {
    color: var(--accent);
}

.topbar-actions {
    display: flex;
    align-items: center;
    gap: 1.1rem;
}

.workspace-label {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    color: var(--muted);
    font-size: .8rem;
    font-weight: 550;
}

.status-dot {
    width: .48rem;
    height: .48rem;
    border-radius: 50%;
    background: #35b585;
    box-shadow: 0 0 0 3px rgba(53, 181, 133, .12);
}

.theme-toggle {
    min-height: 2.45rem;
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    padding: 0 .85rem;
    border: 1px solid var(--line);
    border-radius: .75rem;
    color: var(--text);
    background: var(--surface);
    font: inherit;
    font-size: .78rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color .2s ease, background .2s ease, transform .2s ease;
}

.theme-toggle:hover {
    border-color: var(--accent);
    transform: translateY(-1px);
}

.theme-toggle i {
    color: var(--accent);
}

.welcome-hero {
    position: relative;
    min-height: 286px;
    display: flex;
    align-items: center;
    overflow: hidden;
    padding: 3.1rem 3.25rem;
    border: 1px solid rgba(105, 88, 214, .16);
    border-radius: 1.5rem;
    background: linear-gradient(115deg, var(--surface) 0%, var(--surface) 59%, var(--accent-soft) 100%);
    box-shadow: var(--shadow);
}

.hero-copy {
    position: relative;
    z-index: 1;
    width: min(66%, 590px);
}

.eyebrow {
    display: flex;
    align-items: center;
    gap: .55rem;
    color: var(--accent);
    font-size: .66rem;
    font-weight: 750;
    letter-spacing: .14em;
}

.eyebrow-line {
    width: 1.2rem;
    height: 2px;
    border-radius: 2px;
    background: var(--accent);
}

.welcome-hero h1 {
    margin-top: 1rem;
    color: var(--text);
    font-size: clamp(2.15rem, 4vw, 3.25rem);
    font-weight: 740;
    letter-spacing: -.065em;
    line-height: 1.08;
}

.welcome-hero h1 span {
    color: var(--accent);
}

.hero-copy > p {
    max-width: 460px;
    margin-top: .9rem;
    color: var(--muted);
    font-size: .94rem;
    line-height: 1.65;
}

.hero-status {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    margin-top: 1.15rem;
    color: var(--green);
    font-size: .78rem;
    font-weight: 650;
}

.status-check {
    width: 1.2rem;
    height: 1.2rem;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: var(--green-soft);
    font-size: .62rem;
}

.orbit {
    position: absolute;
    inset: 12%;
    border: 1px solid rgba(105, 88, 214, .16);
    border-radius: 50%;
    transform: rotate(-28deg) scaleY(.47);
}

.orbit-two {
    inset: 5%;
    transform: rotate(35deg) scaleY(.47);
}

.hero-cube {
    position: absolute;
    inset: 0;
    width: 112px;
    height: 112px;
    display: grid;
    place-items: center;
    margin: auto;
    border: 1px solid rgba(255, 255, 255, .36);
    border-radius: 2rem;
    color: #fff;
    background: linear-gradient(145deg, #998bfa, #6655d4 72%);
    box-shadow: 0 22px 44px rgba(90, 72, 196, .32), inset 0 1px rgba(255, 255, 255, .28);
    font-size: 2.7rem;
    transform: rotate(-8deg);
}

.art-spark {
    position: absolute;
    color: var(--accent);
}

.spark-one {
    top: 27%;
    left: 21%;
    font-size: 1.1rem;
}

.spark-two {
    right: 18%;
    bottom: 25%;
    font-size: 1.7rem;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.15rem;
    margin-top: 1.2rem;
}

.panel {
    min-width: 0;
    padding: 1.65rem 1.75rem;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    background: var(--surface);
    box-shadow: 0 6px 24px rgba(34, 39, 72, .035);
}

.section-heading {
    display: flex;
    align-items: center;
    gap: .85rem;
    margin-bottom: 1.35rem;
}

.section-icon {
    width: 2.45rem;
    height: 2.45rem;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    border-radius: .8rem;
    color: var(--accent);
    background: var(--accent-soft);
    font-size: .95rem;
}

.section-icon-accent {
    color: var(--green);
    background: var(--green-soft);
}

.section-heading h2 {
    color: var(--text);
    font-size: .98rem;
    font-weight: 700;
    letter-spacing: -.025em;
}

.section-heading p {
    margin-top: .18rem;
    color: var(--muted);
    font-size: .75rem;
}

.details-list,
.steps-list {
    display: grid;
    list-style: none;
}

.detail-row {
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .8rem;
    padding: .83rem 0;
    border-top: 1px solid var(--line);
}

.detail-row dt {
    display: inline-flex;
    align-items: center;
    gap: .55rem;
    color: var(--muted);
    font-size: .76rem;
}

.detail-row dt i {
    width: 1rem;
    color: var(--accent);
    font-size: .75rem;
    text-align: center;
}

.detail-row dd {
    min-width: 0;
    color: var(--text);
    font-size: .77rem;
    font-weight: 650;
    text-align: right;
    overflow-wrap: anywhere;
}

.version-tag {
    margin-left: .25rem;
    padding: .16rem .4rem;
    border-radius: .35rem;
    color: var(--muted);
    background: var(--surface-soft);
    font-size: .62rem;
    font-weight: 600;
    text-transform: uppercase;
}

.ready-badge {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .3rem .6rem;
    border-radius: 99px;
    color: var(--green);
    background: var(--green-soft);
    font-size: .68rem;
    font-weight: 700;
}

.ready-badge .status-dot {
    width: .4rem;
    height: .4rem;
    box-shadow: none;
}

.step-item {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: .85rem;
    padding: .78rem 0;
    border-top: 1px solid var(--line);
}

.step-number {
    width: 2rem;
    height: 2rem;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    border: 1px solid var(--line);
    border-radius: .65rem;
    color: var(--accent);
    background: var(--surface-soft);
    font-size: .65rem;
    font-weight: 750;
}

.step-copy {
    min-width: 0;
    flex: 1;
}

.step-copy h3 {
    margin-bottom: .28rem;
    color: var(--text);
    font-size: .77rem;
    font-weight: 650;
}

.step-copy code {
    display: inline-block;
    max-width: 100%;
    padding: .2rem .4rem;
    overflow: hidden;
    border-radius: .35rem;
    color: var(--muted);
    background: var(--surface-soft);
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: .64rem;
    text-overflow: ellipsis;
    vertical-align: middle;
    white-space: nowrap;
}

.step-arrow {
    color: var(--muted);
    font-size: .7rem;
}

.next-steps {
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.2rem;
    padding: 1.3rem 1.65rem;
    border: 1px solid var(--line);
    border-radius: var(--radius);
    background: var(--surface);
}

.next-steps-copy {
    display: flex;
    align-items: center;
    gap: .9rem;
}

.next-steps-icon {
    width: 2.65rem;
    height: 2.65rem;
    flex: 0 0 auto;
    display: grid;
    place-items: center;
    border-radius: .9rem;
    color: var(--accent);
    background: var(--accent-soft);
}

.next-steps h2 {
    color: var(--text);
    font-size: .9rem;
    font-weight: 700;
}

.next-steps p {
    margin-top: .25rem;
    color: var(--muted);
    font-size: .74rem;
    line-height: 1.5;
}

.docs-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .65rem;
    min-height: 2.65rem;
    padding: 0 1rem;
    border: 1px solid #6958d6;
    border-radius: .75rem;
    color: #fff;
    background: #6958d6;
    font-size: .75rem;
    font-weight: 650;
    text-decoration: none;
    transition: background .2s ease, transform .2s ease;
}

.docs-link:hover {
    background: #5543c3;
    transform: translateY(-1px);
}

.docs-link i {
    font-size: .68rem;
}

.footer-meta {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.2rem 0 1.4rem;
    border-top: 1px solid var(--line);
    color: var(--muted);
    font-size: .7rem;
}

.footer-meta a {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    color: var(--muted);
    text-decoration: none;
    transition: color .2s ease;
}

.footer-meta a:hover {
    color: var(--accent);
}

@media (max-width: 760px) {
    body {
        padding: 0 1.25rem;
    }

    .topbar {
        min-height: 72px;
    }

    .welcome-hero {
        min-height: 270px;
        padding: 2.4rem;
    }

    .hero-copy {
        width: 75%;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }

    .panel {
        padding: 1.5rem;
    }
}

@media (max-width: 520px) {
    body {
        padding: 0 1rem;
    }

    .workspace-label {
        display: none;
    }

    .theme-toggle {
        width: 2.55rem;
        height: 2.55rem;
        justify-content: center;
        padding: 0;
        font-size: 0;
    }

    .theme-toggle i {
        font-size: .9rem;
    }

    .welcome-hero {
        min-height: 300px;
        padding: 2rem 1.45rem;
        border-radius: 1.2rem;
    }

    .hero-copy {
        width: 100%;
    }

    .welcome-hero h1 {
        max-width: 14rem;
        font-size: 2.25rem;
    }

    .hero-copy > p {
        max-width: 18rem;
        font-size: .86rem;
    }

    .hero-cube {
        width: 82px;
        height: 82px;
        border-radius: 1.4rem;
        font-size: 2rem;
    }

    .panel {
        padding: 1.25rem;
    }

    .detail-row {
        align-items: flex-start;
        gap: .5rem;
    }

    .detail-row dt {
        max-width: 52%;
    }

    .detail-row dd {
        max-width: 48%;
    }

    .next-steps {
        align-items: stretch;
        flex-direction: column;
        padding: 1.25rem;
    }

    .docs-link {
        width: 100%;
    }

    .footer-meta {
        padding-bottom: 1.2rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
CSS;
    }

    private static function layout(): string
    {
        return <<<'HTML'
<?php use Mlangeni\Machinjiri\Core\Views\View; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#f5f6fa">
    <title>Machinjiri - Installation complete</title>
    <script>
        try {
            const theme = localStorage.getItem('machinjiri-theme');
            if (theme === 'light' || theme === 'dark') {
                document.documentElement.dataset.theme = theme;
            }
        } catch (error) {
            console.warn('Unable to load the saved theme preference.', error);
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{ style('css/main.css') }}
</head>
<body>
    @yield('content')
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const root = document.documentElement;
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)');

        function updateThemeToggle() {
            const isDark = root.dataset.theme
                ? root.dataset.theme === 'dark'
                : systemPrefersDark.matches;
            themeToggle.setAttribute('aria-pressed', String(isDark));
            themeToggle.setAttribute('aria-label', 'Switch to ' + (isDark ? 'light' : 'dark') + ' theme');
            themeToggle.querySelector('i').className = 'fas fa-' + (isDark ? 'sun' : 'moon');
            themeToggle.querySelector('span').textContent = isDark ? 'Light mode' : 'Dark mode';
        }

        systemPrefersDark.addEventListener('change', updateThemeToggle);

        themeToggle.addEventListener('click', function () {
            const currentTheme = root.dataset.theme
                || (systemPrefersDark.matches ? 'dark' : 'light');
            const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';
            root.dataset.theme = nextTheme;
            updateThemeToggle();

            try {
                localStorage.setItem('machinjiri-theme', nextTheme);
            } catch (error) {
                console.warn('Unable to save the theme preference.', error);
            }
        });

        updateThemeToggle();
    </script>
</body>
</html>
HTML;
    }
}