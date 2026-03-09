<?php

declare(strict_types=1);

use yii\helpers\Html;
use yii\helpers\Json;

/** @var string $specUrl */
/** @var string $apiHomeUrl */
/** @var string $loginUrl */
/** @var array<string, mixed> $specSummary */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= Html::encode((string) ($specSummary['title'] ?? 'Library API')) ?> Docs</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
  <style>
    :root {
      --page-bg: #f5f7fb;
      --panel: rgba(255, 255, 255, 0.88);
      --panel-strong: #ffffff;
      --line: rgba(15, 23, 42, 0.09);
      --ink: #0f172a;
      --muted: #5f6f85;
      --teal: #146c67;
      --teal-deep: #0f4f4b;
      --amber: #9e652e;
      --shadow: 0 22px 50px rgba(15, 23, 42, 0.08);
      --radius-xl: 28px;
      --radius-lg: 20px;
      --radius-md: 14px;
    }

    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      margin: 0;
      min-height: 100vh;
      color: var(--ink);
      font-family: "Inter", sans-serif;
      background:
        radial-gradient(circle at top left, rgba(20, 108, 103, 0.08), transparent 24%),
        radial-gradient(circle at top right, rgba(158, 101, 46, 0.06), transparent 20%),
        linear-gradient(180deg, #f7f9fc 0%, var(--page-bg) 100%);
      position: relative;
    }

    body::before,
    body::after {
      content: "";
      position: fixed;
      border-radius: 50%;
      pointer-events: none;
      filter: blur(18px);
      opacity: 0.9;
      z-index: 0;
      animation: drift 20s ease-in-out infinite;
    }

    body::before {
      width: 320px;
      height: 320px;
      top: -110px;
      right: -110px;
      background: radial-gradient(circle, rgba(20, 108, 103, 0.12), rgba(20, 108, 103, 0) 70%);
    }

    body::after {
      width: 340px;
      height: 340px;
      left: -120px;
      bottom: -120px;
      background: radial-gradient(circle, rgba(158, 101, 46, 0.1), rgba(158, 101, 46, 0) 72%);
      animation-direction: reverse;
    }

    a { color: inherit; text-decoration: none; }
    p, h1, h2, h3, strong, small, span, div { max-width: 100%; }

    .page {
      position: relative;
      z-index: 1;
      max-width: 1360px;
      margin: 0 auto;
      padding: 24px 20px 40px;
    }

    .glass {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow);
      backdrop-filter: blur(14px);
      overflow: hidden;
    }

    .header,
    .hero,
    .side,
    .docs-shell { animation: rise 0.75s ease both; }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 18px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .brand-mark {
      width: 58px;
      height: 58px;
      border-radius: 20px;
      background: linear-gradient(145deg, var(--teal), var(--teal-deep));
      box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 16px 28px rgba(19, 72, 68, 0.22);
      position: relative;
    }

    .brand-mark::before,
    .brand-mark::after {
      content: "";
      position: absolute;
      top: 12px;
      bottom: 12px;
      border-radius: 999px;
      background: rgba(255, 249, 241, 0.9);
    }

    .brand-mark::before {
      left: 15px;
      width: 10px;
      box-shadow: 14px 0 0 rgba(255, 249, 241, 0.75);
    }

    .brand-mark::after {
      right: 11px;
      width: 8px;
      background: rgba(255, 249, 241, 0.62);
    }

    .brand-copy strong {
      display: block;
      font-size: 1.125rem;
      font-weight: 600;
      line-height: 1.35;
      overflow-wrap: anywhere;
    }

    .brand-copy span {
      color: var(--muted);
      font-size: 0.875rem;
      line-height: 1.5;
      overflow-wrap: anywhere;
    }

    .status {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 10px;
    }

    .status-pill,
    .tag,
    .chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 999px;
      border: 1px solid rgba(23, 49, 44, 0.08);
      background: rgba(255, 255, 255, 0.74);
      color: var(--muted);
      font-size: 0.8125rem;
      font-weight: 600;
      overflow: hidden;
      text-wrap: balance;
      overflow-wrap: anywhere;
    }

    .status-pill strong { color: var(--ink); }

    .hero-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.5fr) minmax(320px, 0.92fr);
      gap: 20px;
      align-items: stretch;
      margin-bottom: 28px;
    }

    .hero,
    .side,
    .docs-shell {
      min-width: 0;
    }

    .hero {
      padding: 36px clamp(24px, 4vw, 40px);
    }

    .kicker {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(20, 108, 103, 0.08);
      color: var(--teal-deep);
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      max-width: max-content;
      overflow-wrap: anywhere;
    }

    .kicker::before {
      content: "";
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--teal), var(--amber));
      box-shadow: 0 0 0 6px rgba(20, 108, 103, 0.06);
    }

    h1, h2, h3 {
      margin: 0;
      font-family: "Inter", sans-serif;
      letter-spacing: -0.02em;
      overflow-wrap: anywhere;
    }

    .hero h1 {
      margin-top: 18px;
      font-size: clamp(2.25rem, 4vw, 2.875rem);
      font-weight: 600;
      line-height: 1.08;
      text-wrap: balance;
    }

    .hero p,
    .side p,
    .docs-head p {
      margin: 14px 0 0;
      color: var(--muted);
      line-height: 1.65;
      font-size: 0.875rem;
      overflow: hidden;
      text-wrap: pretty;
      overflow-wrap: break-word;
      word-break: break-word;
    }

    .actions,
    .tags,
    .docs-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      min-width: 0;
    }

    .actions {
      margin-top: 28px;
    }

    .button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 46px;
      padding: 12px 18px;
      border-radius: 999px;
      border: 1px solid transparent;
      font-size: 0.875rem;
      font-weight: 600;
      line-height: 1.4;
      max-width: 100%;
      text-align: center;
      overflow-wrap: anywhere;
      transition: transform 0.22s ease, box-shadow 0.22s ease;
    }

    .button:hover,
    .swagger-ui .btn:hover { transform: translateY(-2px); }

    .button-primary {
      background: linear-gradient(135deg, var(--teal), var(--teal-deep));
      color: #fffdf8;
      box-shadow: 0 16px 28px rgba(19, 72, 68, 0.22);
    }

    .button-secondary {
      background: rgba(255, 255, 255, 0.9);
      border-color: rgba(15, 23, 42, 0.1);
    }

    .button code,
    .step code,
    .chip code {
      padding: 0.14rem 0.4rem;
      border-radius: 8px;
      background: rgba(20, 108, 103, 0.08);
      color: var(--teal-deep);
      font-family: "Inter", sans-serif;
      font-size: 0.88em;
      font-weight: 700;
      overflow-wrap: anywhere;
    }

    .stats {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 16px;
      margin-top: 32px;
      min-width: 0;
    }

    .stat {
      min-width: 0;
      padding: 24px;
      border-radius: var(--radius-lg);
      border: 1px solid rgba(15, 23, 42, 0.08);
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 250, 252, 0.86));
      overflow: hidden;
    }

    .stat span {
      display: block;
      color: var(--muted);
      font-size: 1.125rem;
      font-weight: 500;
      line-height: 1.4;
      text-wrap: balance;
      overflow-wrap: anywhere;
    }

    .stat strong {
      display: block;
      margin-top: 14px;
      font-size: clamp(1.75rem, 2.7vw, 2.1rem);
      font-weight: 600;
      line-height: 1.15;
      overflow-wrap: anywhere;
    }

    .stat small {
      display: block;
      margin-top: 12px;
      color: var(--muted);
      font-size: 0.875rem;
      line-height: 1.65;
      overflow: hidden;
      text-wrap: pretty;
      overflow-wrap: break-word;
      word-break: break-word;
    }

    .side {
      padding: 24px;
      display: grid;
      grid-template-rows: 1fr 1fr;
      gap: 16px;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(248, 250, 252, 0.84));
    }

    .card {
      min-width: 0;
      max-width: 100%;
      padding: 24px;
      border-radius: var(--radius-lg);
      border: 1px solid rgba(15, 23, 42, 0.08);
      background: rgba(255, 255, 255, 0.94);
      overflow: hidden;
    }

    .card h3,
    .docs-head h2 {
      font-size: 1.125rem;
      font-weight: 500;
      line-height: 1.4;
      text-wrap: balance;
    }

    .tags {
      margin-top: 18px;
    }

    .tag {
      padding: 8px 12px;
      color: var(--teal-deep);
      background: rgba(20, 108, 103, 0.08);
      overflow-wrap: anywhere;
    }

    .steps {
      display: grid;
      gap: 14px;
      margin-top: 18px;
      min-width: 0;
    }

    .step {
      display: grid;
      grid-template-columns: 34px 1fr;
      gap: 12px;
      align-items: start;
      min-width: 0;
    }

    .step b {
      width: 34px;
      height: 34px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: linear-gradient(145deg, var(--amber), #d48e51);
      color: #fff9f1;
      box-shadow: 0 10px 18px rgba(179, 112, 50, 0.18);
      flex-shrink: 0;
    }

    .step div {
      min-width: 0;
      color: var(--muted);
      font-size: 0.875rem;
      line-height: 1.65;
      overflow: hidden;
      text-wrap: pretty;
      overflow-wrap: break-word;
      word-break: break-word;
    }

    .docs-shell {
      padding: 20px;
    }

    .docs-head {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 18px;
      padding: 4px 4px 20px;
      margin-bottom: 20px;
      border-bottom: 1px solid rgba(15, 23, 42, 0.08);
      min-width: 0;
    }

    .docs-head > div:first-child,
    .docs-actions {
      min-width: 0;
    }

    #swagger-ui {
      margin: 0;
      padding: 0;
      max-width: 100%;
      min-width: 0;
      overflow: hidden;
    }

    .swagger-ui {
      color: var(--ink);
      font-family: "Inter", sans-serif;
      min-width: 0;
      overflow-wrap: break-word;
      word-break: break-word;
    }
    .swagger-ui .topbar { display: none; }
    .swagger-ui .wrapper { max-width: 100%; padding: 0; }
    .swagger-ui .info .title,
    .swagger-ui .opblock-tag,
    .swagger-ui .opblock .opblock-summary-path,
    .swagger-ui .responses-inner h4,
    .swagger-ui .responses-inner h5,
    .swagger-ui .opblock-section-header h4 {
      color: var(--ink);
      font-family: "Inter", sans-serif;
    }

    .swagger-ui .info .title {
      font-size: clamp(1.625rem, 3vw, 2.125rem);
      font-weight: 600;
      line-height: 1.3;
      text-wrap: balance;
    }
    .swagger-ui .info .title small {
      padding: 6px 10px;
      border-radius: 999px;
      background: rgba(20, 108, 103, 0.12);
      color: var(--teal-deep);
      font-family: "Inter", sans-serif;
      font-weight: 700;
    }

    .swagger-ui .info p,
    .swagger-ui .info li,
    .swagger-ui .renderedMarkdown p,
    .swagger-ui .opblock .opblock-summary-description,
    .swagger-ui .responses-inner,
    .swagger-ui .model-box {
      color: var(--muted);
      font-family: "Inter", sans-serif;
    }

    .swagger-ui .info a,
    .swagger-ui .renderedMarkdown a { color: var(--teal); font-weight: 700; }
    .swagger-ui .scheme-container,
    .swagger-ui .model-box,
    .swagger-ui .opblock-body pre,
    .swagger-ui .highlight-code,
    .swagger-ui .microlight {
      border-radius: 18px;
      border: 1px solid rgba(23, 49, 44, 0.08);
      background: #fffaf3;
      box-shadow: none;
    }

    .swagger-ui .scheme-container { padding: 18px 20px; margin-bottom: 20px; }
    .swagger-ui input[type=text],
    .swagger-ui input[type=password],
    .swagger-ui input[type=search],
    .swagger-ui textarea,
    .swagger-ui select {
      border: 1px solid rgba(15, 23, 42, 0.12);
      border-radius: 16px;
      background: #ffffff;
      color: var(--ink);
      font-family: "Inter", sans-serif;
      box-shadow: none;
      max-width: 100%;
    }

    .swagger-ui .btn,
    .swagger-ui .tab li button.tablinks {
      border-radius: 999px;
      font-family: "Inter", sans-serif;
      font-weight: 700;
      transition: transform 0.22s ease;
    }

    .swagger-ui .btn.authorize,
    .swagger-ui .tab li button.tablinks.active {
      background: linear-gradient(135deg, var(--teal), var(--teal-deep));
      color: #fff;
      border-color: transparent;
    }

    .swagger-ui .btn.execute {
      background: linear-gradient(135deg, var(--amber), #8b5925);
      color: #fff;
      border-color: transparent;
    }

    .swagger-ui .btn.try-out__btn,
    .swagger-ui .btn.cancel,
    .swagger-ui .authorization__btn {
      background: rgba(255, 255, 255, 0.9);
      border-color: rgba(15, 23, 42, 0.12);
      color: var(--ink);
    }

    .swagger-ui .opblock-tag {
      padding: 14px 6px 16px;
      border-bottom: none;
      font-size: 1.125rem;
      font-weight: 500;
      line-height: 1.4;
    }
    .swagger-ui .opblock {
      overflow: hidden;
      margin-bottom: 16px;
      border-width: 1px;
      border-radius: 24px;
      box-shadow: 0 18px 38px rgba(66, 53, 35, 0.08);
    }

    .swagger-ui .opblock .opblock-summary { padding: 16px 18px; }
    .swagger-ui .opblock .opblock-summary-method {
      min-width: 84px;
      border: none;
      border-radius: 999px;
      font-weight: 700;
      letter-spacing: 0.06em;
      box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.08);
    }

    .swagger-ui .opblock .opblock-section-header {
      background: rgba(255, 251, 245, 0.72);
      border-top: 1px solid rgba(23, 49, 44, 0.08);
      box-shadow: none;
    }

    .swagger-ui .opblock.opblock-get { background: linear-gradient(180deg, rgba(31, 107, 101, 0.09), rgba(31, 107, 101, 0.02)); border-color: rgba(31, 107, 101, 0.25); }
    .swagger-ui .opblock.opblock-post { background: linear-gradient(180deg, rgba(179, 112, 50, 0.1), rgba(179, 112, 50, 0.02)); border-color: rgba(179, 112, 50, 0.25); }
    .swagger-ui .opblock.opblock-put,
    .swagger-ui .opblock.opblock-patch { background: linear-gradient(180deg, rgba(99, 89, 157, 0.08), rgba(99, 89, 157, 0.02)); border-color: rgba(99, 89, 157, 0.24); }
    .swagger-ui .opblock.opblock-delete { background: linear-gradient(180deg, rgba(143, 83, 77, 0.1), rgba(143, 83, 77, 0.02)); border-color: rgba(143, 83, 77, 0.24); }

    .swagger-ui .opblock-summary-path,
    .swagger-ui .opblock-summary-description,
    .swagger-ui .parameter__name,
    .swagger-ui td,
    .swagger-ui th,
    .swagger-ui pre,
    .swagger-ui code {
      overflow-wrap: break-word;
      word-break: break-word;
    }

    @keyframes rise {
      from { opacity: 0; transform: translateY(18px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes drift {
      0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
      50% { transform: translate3d(-12px, 18px, 0) scale(1.04); }
    }

    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      body::before,
      body::after,
      .header,
      .hero,
      .side,
      .docs-shell { animation: none; }
    }

    @media (max-width: 1100px) {
      .hero-grid { grid-template-columns: 1fr; }
      .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .docs-head { align-items: flex-start; flex-direction: column; }
      .side { grid-template-rows: auto; }
    }

    @media (max-width: 720px) {
      .page { padding: 18px 14px 32px; }
      .header { align-items: flex-start; flex-direction: column; }
      .status { justify-content: flex-start; }
      .hero { padding: 28px 22px; }
      .hero h1 { font-size: clamp(2rem, 10vw, 2.5rem); }
      .stats { grid-template-columns: 1fr; }
      .docs-shell { padding: 14px; }
      .card,
      .stat { padding: 20px; }
    }
  </style>
</head>
<body>
  <main class="page">
    <header class="header">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true"></div>
        <div class="brand-copy">
          <strong><?= Html::encode((string) ($specSummary['title'] ?? 'Library API')) ?></strong>
          <span>Swagger UI editorial para explorar y probar la API</span>
        </div>
      </div>

      <div class="status">
        <span class="status-pill">OpenAPI <strong><?= Html::encode((string) ($specSummary['openapiVersion'] ?? '3.0.3')) ?></strong></span>
        <span class="status-pill">API <strong>v<?= Html::encode((string) ($specSummary['version'] ?? '2.0.0')) ?></strong></span>
        <span class="status-pill">Servidor <strong><?= Html::encode((string) ($specSummary['serverLabel'] ?? 'local')) ?></strong></span>
        <span class="status-pill">Spec <strong><?= Html::encode((string) ($specSummary['generatedAt'] ?? 'actual')) ?></strong></span>
      </div>
    </header>

    <section class="hero-grid">
      <article class="hero glass">
        <span class="kicker">Interactive API Documentation</span>
        <h1>Library API Documentation</h1>
        <p>Interactive documentation to explore and test the Library API.</p>
        <p>Authenticate, inspect schemas and execute real API requests directly from this interface.</p>

        <div class="actions">
          <a class="button button-primary" href="#swagger-ui">Explore Endpoints</a>
          <a class="button button-secondary" href="<?= Html::encode($apiHomeUrl) ?>">Open API Docs</a>
          <a class="button button-secondary" href="<?= Html::encode($specUrl) ?>" target="_blank" rel="noreferrer">Download OpenAPI YAML</a>
        </div>

        <div class="stats">
          <div class="stat">
            <span>Operations</span>
            <strong><?= Html::encode((string) ($specSummary['operationCount'] ?? 0)) ?></strong>
            <small>Available API operations ready for interactive testing.</small>
          </div>
          <div class="stat">
            <span>Routes</span>
            <strong><?= Html::encode((string) ($specSummary['pathCount'] ?? 0)) ?></strong>
            <small>Defined endpoints exposed by the OpenAPI specification.</small>
          </div>
          <div class="stat">
            <span>Domains</span>
            <strong><?= Html::encode((string) ($specSummary['tagCount'] ?? 0)) ?></strong>
            <small>Logical groups for authentication, books and authors.</small>
          </div>
          <div class="stat">
            <span>Base URL</span>
            <strong style="font-size: clamp(1.35rem, 2.4vw, 1.9rem);"><?= Html::encode((string) ($specSummary['serverLabel'] ?? 'local')) ?></strong>
            <small>Primary API server used to execute requests.</small>
          </div>
        </div>
      </article>

      <aside class="side glass">
        <section class="card">
          <h3>API Overview</h3>
          <p>Browse the API structure organized by domains to quickly understand available resources.</p>
          <div class="tags">
            <?php foreach ((array) ($specSummary['tags'] ?? []) as $tag): ?>
              <span class="tag"><?= Html::encode((string) $tag) ?></span>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="card">
          <h3>Quick Start</h3>
          <div class="steps">
            <div class="step">
              <b>1</b>
              <div>Request a token using <code>POST /api/login</code>.</div>
            </div>
            <div class="step">
              <b>2</b>
              <div>Click <code>Authorize</code> and paste your Bearer token.</div>
            </div>
            <div class="step">
              <b>3</b>
              <div>Start exploring endpoints like <code>GET /api/books</code> and <code>GET /api/authors</code>.</div>
            </div>
          </div>
        </section>
      </aside>
    </section>

    <section class="docs-shell glass">
      <div class="docs-head">
        <div>
          <h2>API Explorer</h2>
          <p>Review schemas, authorize requests and validate responses directly against the bundled OpenAPI document.</p>
        </div>

        <div class="docs-actions">
          <span class="chip">Spec <code><?= Html::encode($specUrl) ?></code></span>
          <span class="chip">API <code><?= Html::encode($apiHomeUrl) ?></code></span>
        </div>
      </div>

      <div id="swagger-ui"></div>
    </section>
  </main>

  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
  <script>
    window.addEventListener('load', function () {
      SwaggerUIBundle({
        url: <?= Json::htmlEncode($specUrl) ?>,
        dom_id: '#swagger-ui',
        deepLinking: true,
        displayRequestDuration: true,
        filter: true,
        tryItOutEnabled: true,
        docExpansion: 'list',
        defaultModelsExpandDepth: 1,
        persistAuthorization: true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        layout: 'StandaloneLayout'
      });
    });
  </script>
</body>
</html>
