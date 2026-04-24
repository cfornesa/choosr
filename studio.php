<?php
/**
 * Data-to-Art Studio — Protected Studio View
 *
 * Route: /studio.php
 * 
 * - If user is NOT authenticated, redirect to / (index.php)
 * - If user IS authenticated, render the full studio UI
 */

require_once __DIR__ . '/config/bootstrap.php';

// ── Redirect unauthenticated users to home ──────────────────────────────
if (!is_authenticated()) {
    header('Location: /index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data-to-Art Studio</title>
  <link rel="stylesheet" href="css/app.css">
</head>
<body>

  <!-- Header -->
  <header id="dta-header">
    <h1>Data-to-Art Studio</h1>
  </header>

  <!-- Error / Status Display -->
  <div id="dta-error-display"></div>

  <!-- Main Layout: Canvas + Sidebar -->
  <main id="dta-main">

    <!-- Canvas Region (hero) -->
    <section id="dta-canvas-region">
      <canvas id="dta-canvas"></canvas>
      <div id="dta-empty-state">
        <p>Upload data or pick a dataset to begin</p>
      </div>
    </section>

    <!-- Controls Sidebar -->
    <aside id="dta-sidebar" data-username="<?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-authenticated="1">

      <!-- Auth Section (collapsible) — top for visibility on authenticated-only page -->
      <details id="dta-auth-section" data-username="<?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-authenticated="<?php echo isset($_SESSION['user_id']) ? '1' : '0'; ?>">
        <summary><span id="dta-auth-summary">Account</span></summary>

        <!-- Auth forms container (hidden via CSS when logged in) -->
        <div id="dta-auth-forms-container">
          <!-- Login form (registration is disabled) -->
          <form id="dta-login-form" class="dta-auth-form">
            <label for="dta-login-email">Email</label>
            <input type="email" id="dta-login-email" name="email" required autocomplete="email">

            <label for="dta-login-password">Password</label>
            <input type="password" id="dta-login-password" name="password" required autocomplete="current-password">

            <button type="submit">Log In</button>
          </form>
          <p id="dta-registration-notice" class="dta-auth-notice">Registration is disabled. Owner access only.</p>
        </div><!-- /#dta-auth-forms-container -->

        <div id="dta-auth-status"></div>
        <button type="button" id="dta-logout-btn" class="dta-auth-logout-btn">Log Out</button>
      </details>

      <!-- Top Control Bar -->
      <div class="dta-control-group">
        <label for="dta-file-upload">Upload Data</label>
        <input type="file" id="dta-file-upload" accept=".csv,.tsv,.xlsx">

        <label for="dta-dataset-select">Dataset</label>
        <select id="dta-dataset-select">
          <option value="">— No datasets yet — Upload data to begin</option>
        </select>

        <label for="dta-style-select">Art Style</label>
        <select id="dta-style-select">
          <option value="">— Select art style —</option>
        </select>

        <div class="dta-button-row">
          <button id="dta-render-btn">Render</button>
          <button id="dta-export-btn">Export PNG</button>
        </div>
        <div class="dta-button-row">
          <button id="dta-save-artwork-btn">Save Artwork</button>
          <button id="dta-load-artwork-btn">Load Artwork</button>
        </div>
      </div>

      <!-- Artwork Metadata Panel -->
      <details id="dta-metadata-section" class="dta-control-group">
        <summary>Artwork Metadata</summary>
        <div id="dta-metadata-panel">
          <input type="hidden" id="dta-current-artwork-id" value="">
          <label for="dta-artwork-title">Title</label>
          <input type="text" id="dta-artwork-title" placeholder="Give your artwork a title">

          <label for="dta-artwork-description">Description</label>
          <textarea id="dta-artwork-description" placeholder="Describe your artwork (optional)" rows="3"></textarea>

          <label for="dta-artwork-tags">Tags</label>
          <input type="text" id="dta-artwork-tags" placeholder="Comma-separated tags (optional)">

          <div class="dta-visibility-controls">
            <label>
              <input type="checkbox" id="dta-artwork-is-public">
              <span>Public</span>
            </label>
            <label>
              <input type="checkbox" id="dta-artwork-is-featured">
              <span>Featured</span>
            </label>
          </div>

          <button id="dta-save-metadata-btn" class="dta-metadata-save-btn">Save Metadata</button>
          <div id="dta-save-status"></div>
        </div>
      </details>

      <!-- Column Mapper & Palette Picker are auto-rendered here by Controls -->
      <div id="dta-controls"></div>

    </aside>
  </main>

  <!-- Scripts (load order is critical) -->
  <script src="src/canvas/styles/particleField.js"></script>
  <script src="src/canvas/styles/geometricGrid.js"></script>
  <script src="src/canvas/styles/flowingCurves.js"></script>
  <script src="src/canvas/artStyles.js"></script>
  <script src="src/canvas/renderer.js"></script>
  <script src="src/data/normalizer.js"></script>
  <script src="src/data/dataMapper.js"></script>
  <script src="src/controls/columnMapper.js"></script>
  <script src="src/controls/palettePicker.js"></script>
  <script src="src/controls/controls.js"></script>
  <script src="src/app.js"></script>

</body>
</html>
