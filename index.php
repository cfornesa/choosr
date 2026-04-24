<?php
/**
 * Data-to-Art Studio — Public Landing Page
 *
 * Route: /
 * 
 * - If user is authenticated, redirect to /studio.php
 * - If not authenticated, show landing page with login/register CTAs
 */

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/env.php';

// ── Redirect authenticated users to studio ────────────────────────────
if (is_authenticated()) {
    header('Location: /studio.php');
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
  <script>
    var DTA_CONFIG = {
      thumbnailUrl: '<?php echo ARTWORK_THUMBNAIL_URL; ?>'
    };
  </script>
  <style>
    /* Landing page specific styles */
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    #dta-landing-main {
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 24px;
      text-align: center;
    }
    
    #dta-landing-header {
      margin-bottom: 48px;
    }
    
    #dta-landing-header h1 {
      font-size: 32px;
      font-weight: 700;
      letter-spacing: 0.05em;
      text-transform: uppercase;
      color: #f0ece4;
      border-bottom: 2px solid #c9922a;
      padding-bottom: 12px;
      display: inline-block;
    }
    
    #dta-landing-description {
      max-width: 600px;
      margin: 0 auto 48px;
      font-size: 18px;
      line-height: 1.8;
      color: #a0a0a0;
    }
    
    #dta-landing-actions {
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
      justify-content: center;
    }
    
    .dta-cta-btn {
      display: inline-block;
      padding: 16px 32px;
      font-size: 16px;
      font-weight: 600;
      text-decoration: none;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border: none;
      border-radius: 0;
      cursor: pointer;
      transition: background-color 0.2s ease;
    }
    
    .dta-cta-btn-primary {
      background: #c9922a;
      color: #1c1814;
    }
    
    .dta-cta-btn-primary:hover {
      background: #a67a22;
    }
    
    .dta-cta-btn-secondary {
      background: #242018;
      color: #f0ece4;
      border: 2px solid #c9922a;
    }
    
    .dta-cta-btn-secondary:hover {
      background: #322c24;
    }
    
    #dta-landing-features {
      max-width: 600px;
      margin: 48px auto 0;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 24px;
      text-align: left;
    }
    
    .dta-feature-card {
      background: #242018;
      padding: 24px;
      border: 1px solid #2a2a2a;
      box-shadow: 4px 4px 0px #000000;
    }
    
    .dta-feature-card h3 {
      font-size: 16px;
      font-weight: 700;
      color: #c9922a;
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .dta-feature-card p {
      font-size: 14px;
      color: #a0a0a0;
      line-height: 1.6;
    }
    
    #dta-featured-section {
      max-width: 800px;
      margin: 64px auto 48px;
      text-align: center;
    }
    
    #dta-featured-section h2 {
      font-size: 24px;
      font-weight: 700;
      color: #c9922a;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }
    
    #dta-featured-section p {
      color: #8a8580;
      margin-bottom: 24px;
      font-size: 15px;
    }
    
    #dta-featured-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 24px;
    }
    
    .dta-featured-card {
      background: #242018;
      border: 1px solid #2a2a2a;
      box-shadow: 4px 4px 0px #000000;
      overflow: hidden;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .dta-featured-card:hover {
      transform: translate(-2px, -2px);
      box-shadow: 6px 6px 0px #000000;
    }
    
    .dta-featured-thumbnail {
      width: 100%;
      height: 180px;
      background: #0d0d0d;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    
    .dta-featured-thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .dta-featured-thumbnail .dta-placeholder {
      color: #555;
      font-family: 'Courier New', monospace;
      font-size: 12px;
      text-align: center;
      padding: 16px;
    }
    
    .dta-featured-info {
      padding: 16px;
    }
    
    .dta-featured-info h3 {
      font-size: 15px;
      font-weight: 600;
      color: #f0ece4;
      margin-bottom: 8px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .dta-featured-info p {
      font-size: 13px;
      color: #8a8580;
      line-height: 1.5;
      margin: 0;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .dta-featured-empty {
      background: #242018;
      border: 1px solid #2a2a2a;
      box-shadow: 4px 4px 0px #000000;
      padding: 48px 24px;
      grid-column: 1 / -1;
    }
    
    .dta-featured-empty p {
      color: #606060;
      font-style: italic;
      margin: 0;
    }
    
    #dta-landing-footer {
      padding: 24px;
      font-size: 12px;
      color: #606060;
      text-align: center;
      border-top: 1px solid #2a2a2a;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header id="dta-header">
    <h1>Data-to-Art Studio</h1>
  </header>

  <!-- Landing Page Content -->
  <main id="dta-landing-main">
    <div id="dta-landing-header">
      <h1>Data-to-Art Studio</h1>
    </div>

    <p id="dta-landing-description">
      Transform your data into generative artwork. Upload CSV, TSV, or Excel files,
      map columns to visual dimensions, and compose unique pieces using our
      collection of art styles and palettes.
    </p>

    <div id="dta-landing-actions">
      <a href="studio.php" class="dta-cta-btn dta-cta-btn-primary">Enter Studio</a>
      <a href="#" onclick="showAuthModal()" class="dta-cta-btn dta-cta-btn-secondary">Login (owner only)</a>
    </div>

    <div id="dta-landing-features">
      <div class="dta-feature-card">
        <h3>Upload Data</h3>
        <p>Bring your own CSV, TSV, or Excel files and see them transformed into art.</p>
      </div>
      <div class="dta-feature-card">
        <h3>Map & Control</h3>
        <p>Assign data columns to visual properties: color, size, position, and more.</p>
      </div>
      <div class="dta-feature-card">
        <h3>Export & Share</h3>
        <p>Save your creations as PNG images and share them with the world.</p>
      </div>
    </div>

    <!-- Featured Pieces Section -->
    <section id="dta-featured-section">
      <h2>Featured Pieces</h2>
      <p>Selected works from the collection</p>
      <div id="dta-featured-grid">
        <!-- Featured artworks will be loaded dynamically -->
      </div>
    </section>
  </main>

  <footer id="dta-landing-footer">
    <p>Data-to-Art Studio — A generative art workstation</p>
    <p><a href="portfolio.php" style="color:#606060;">View all public artworks</a></p>
  </footer>

  <!-- Simple inline auth modal that redirects to studio after login -->
  <div id="dta-auth-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000;" onclick="closeAuthModal(event)">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#242018; padding:32px; border:2px solid #c9922a; box-shadow:4px 4px 0px #000000; width:320px; max-width:90%;" onclick="event.stopPropagation()">
      <h2 style="color:#c9922a; margin-bottom:24px; font-size:18px; text-transform:uppercase;">Log In</h2>
      <form id="dta-quick-login" onsubmit="quickLogin(event)">
        <div style="margin-bottom:16px;">
          <label for="modal-email" style="display:block; margin-bottom:4px; color:#a0a0a0; font-size:14px;">Email</label>
          <input type="email" id="modal-email" name="email" required style="width:100%; padding:8px; background:#1c1814; border:1px solid #333; color:#f0ece4; font-family:system-ui, sans-serif; font-size:14px;" autocomplete="email">
        </div>
        <div style="margin-bottom:24px;">
          <label for="modal-password" style="display:block; margin-bottom:4px; color:#a0a0a0; font-size:14px;">Password</label>
          <input type="password" id="modal-password" name="password" required style="width:100%; padding:8px; background:#1c1814; border:1px solid #333; color:#f0ece4; font-family:system-ui, sans-serif; font-size:14px;" autocomplete="current-password">
        </div>
        <button type="submit" style="width:100%; padding:12px; background:#c9922a; color:#1c1814; border:none; font-size:14px; font-weight:600; text-transform:uppercase; cursor:pointer;">Log In</button>
      </form>
      <p style="margin-top:16px; font-size:12px; color:#606060;">
        Owner access only
      </p>
    </div>
  </div>

  <script>
    function showAuthModal() {
      document.getElementById('dta-auth-modal').style.display = 'block';
      document.getElementById('modal-email').focus();
    }
    
    function closeAuthModal(event) {
      if (event && event.target.id === 'dta-auth-modal') {
        document.getElementById('dta-auth-modal').style.display = 'none';
      }
    }
    
    function quickLogin(event) {
      event.preventDefault();
      var email = document.getElementById('modal-email').value;
      var password = document.getElementById('modal-password').value;
      
      fetch('api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email, password: password })
      })
      .then(function(response) {
        if (!response.ok) {
          throw new Error('Login failed');
        }
        return response.json();
      })
      .then(function(data) {
        if (data.success) {
          window.location.href = 'studio.php';
        } else {
          alert(data.error || 'Login failed');
        }
      })
      .catch(function(err) {
        alert('Login failed: ' + err.message);
      });
    }
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        document.getElementById('dta-auth-modal').style.display = 'none';
      }
    });

    // Load featured artworks on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadFeaturedArtworks();
    });

    function loadFeaturedArtworks() {
      var grid = document.getElementById('dta-featured-grid');
      if (!grid) return;

      fetch('api/artworks.php?filter=featured')
        .then(function(response) {
          if (!response.ok) {
            throw new Error('Failed to load featured artworks');
          }
          return response.json();
        })
        .then(function(data) {
          if (data.success && data.artworks && data.artworks.length > 0) {
            displayFeaturedArtworks(data.artworks);
          } else {
            showFeaturedEmptyState();
          }
        })
        .catch(function(err) {
          console.error('Error loading featured artworks:', err);
          showFeaturedEmptyState();
        });
    }

    function displayFeaturedArtworks(artworks) {
      var grid = document.getElementById('dta-featured-grid');
      if (!grid) return;

      grid.innerHTML = '';

      artworks.forEach(function(artwork) {
        var card = document.createElement('div');
        card.className = 'dta-featured-card';

        // Thumbnail section
        var thumbnailDiv = document.createElement('div');
        thumbnailDiv.className = 'dta-featured-thumbnail';

        if (artwork.thumbnail_path) {
          var img = document.createElement('img');
          img.src = DTA_CONFIG.thumbnailUrl + artwork.thumbnail_path.split('/').pop();
          img.alt = artwork.title || 'Artwork thumbnail';
          thumbnailDiv.appendChild(img);
        } else {
          var placeholder = document.createElement('div');
          placeholder.className = 'dta-placeholder';
          placeholder.textContent = 'No thumbnail available';
          thumbnailDiv.appendChild(placeholder);
        }

        card.appendChild(thumbnailDiv);

        // Info section
        var infoDiv = document.createElement('div');
        infoDiv.className = 'dta-featured-info';

        var titleEl = document.createElement('h3');
        titleEl.textContent = artwork.title || 'Untitled';
        infoDiv.appendChild(titleEl);

        var descEl = document.createElement('p');
        descEl.textContent = (artwork.description && artwork.description.length > 100) 
          ? artwork.description.substring(0, 100) + '…' 
          : (artwork.description || 'No description') ;
        infoDiv.appendChild(descEl);

        card.appendChild(infoDiv);

        // Link to exhibit page
        var link = document.createElement('a');
        link.href = 'exhibit.php?id=' + artwork.id;
        link.style.cssText = 'display:block; text-decoration:none; color:inherit;';
        link.appendChild(card);

        grid.appendChild(link);
      });
    }

    function showFeaturedEmptyState() {
      var grid = document.getElementById('dta-featured-grid');
      if (!grid) return;

      grid.innerHTML = '<div class="dta-featured-empty"><p>No featured pieces yet</p></div>';
    }
  </script>

</body>
</html>
