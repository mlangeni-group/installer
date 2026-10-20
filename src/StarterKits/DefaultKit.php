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
    $version = (self::$options['version'] === '*') ? 'Latest' : self::$options['version'];
    $date = self::$options['date'];
    $appName = self::$options['project_name'];
    $current = date('Y');

    return <<<HTML
<?php use Mlangeni\Machinjiri\Core\Views\View; ?>

@extends('layouts.main')

@section('content')
<div class="success-card">
    <!-- Header -->
    <div class="success-header">
      <div class="check-icon">
        <i class="fas fa-check"></i>
      </div>
      <h1>
        Machinjiri - Installation successful!
        <span>Your app is ready to go.</span>
      </h1>
    </div>

    <div class="grid-layout">
      <div class="details-panel">
        <div class="panel-title">
          <i class="fas fa-cube"></i>
          Installation details
        </div>

        <div class="detail-row">
          <span class="detail-label"><i class="fas fa-tag"></i> App name</span>
          <span class="detail-value">{$appName}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="fas fa-code-branch"></i> Version</span>
          <span class="detail-value">{$version} (stable)</span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="fas fa-calendar"></i> Created at:</span>
          <span class="detail-value">{$date}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="fas fa-shield-alt"></i> Status</span>
          <span class="badge-success"><i class="fas fa-check-circle"></i> Verified · Ready</span>
        </div>
      </div>

      <div class="tutorial-panel">
        <div class="panel-title">
          <i class="fas fa-rocket"></i>
          Quick Start
        </div>

        <div class="tutorial-steps">
          <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-content">
              <p>Define a Route:</p>
              <p><code>Router::get('/', 'YourController@index');</code></p>
              <div class="small-hint"><i class="fas fa-arrow-right"></i> in routes/web.php</div>
            </div>
          </div>
          <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-content">
              <p>Create a Controller:</p>
              <p><code>php artisan make:controller ControllerName</code></p>
              <div class="small-hint"><i class="fas fa-arrow-right"></i> in terminal</div>
            </div>
          </div>
          <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-content">
              <p>Create a View:</p>
              <p><code>php artisan view:make ViewName</code></p>
              <div class="small-hint"><i class="fas fa-arrow-right"></i> in terminal</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="next-steps">
      <div class="next-steps-left">
        <i class="fas fa-forward-step"></i>
        <div class="next-steps-text">
          <h3>Next steps</h3>
          <p>Dive into documentation or scaffold your first module.</p>
        </div>
      </div>
      <div class="next-buttons">
        <a href="https://github.com/mlangeni-group/machinjiri" class="btn btn-primary" style="cursor: default;" target="_blank">
          <i class="fab fa-github"></i> Read docs
        </a>
      </div>
    </div>

    <div class="footer-meta">
      <span><i class="fas fa-check-circle" style="color: #10b981;"></i> &copy; 2025 - {$current} Mlangeni QuarterMaster</span>
      <span><i class="fas fa-headset"></i> Need help? <a href="mailto:precious.lyson@gmail.com" style="color: var(--color);">precious.lyson@gmail.com</a></span>
    </div>
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
    <title>Machinjiri - Your Cozy Dev Space</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{ style('css/main.css') }}
</head>
<body>
    @yield('content')
</body>
</html>
HTML;
    }
}