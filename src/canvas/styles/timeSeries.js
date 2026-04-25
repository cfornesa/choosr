/**
 * Time Series Art Style Module - Animated flowing data particles
 */
(function() {
  'use strict';
  console.log('[TimeSeries] Module loading');
  var MAX_SIZE = 500;

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.TimeSeriesStyle = {
    maxSize: MAX_SIZE,
    init: function(ctx, w, h, rc) {
      this._lastTime = Date.now();
    },
    render: function(ctx, width, height, dataPoints, palette, rc) {
      var colors = (palette && palette.colors) || ['#c9922a', '#f0ece4', '#8a8580'];
      var bg = (palette && palette.background) || '#0d0d0d';
      var now = Date.now();
      var elapsed = (now - (this._lastTime || now)) / 1000;
      this._lastTime = now;

      ctx.save();
      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.fillStyle = bg;
      ctx.fillRect(0, 0, width * window.devicePixelRatio, height * window.devicePixelRatio);
      ctx.restore();

      var cx = width / 2, cy = height / 2;
      var isManual = dataPoints && dataPoints.length === 1 && dataPoints[0].x !== null;

      if (isManual && dataPoints[0].x !== undefined) {
        var p = dataPoints[0];
        // Use renderingConfig.opacity if provided (from canvas-level visual dimensions), else fall back to point opacity
        var manualOpacity = (renderingConfig && renderingConfig.opacity !== undefined) ? renderingConfig.opacity : (p.opacity || 1);
        this._drawParticleFlow(ctx, width, height, cx + p.x * width/2, cy + p.y * height/2,
            (p.size || MAX_SIZE) * 0.05, elapsed, manualOpacity, p.rotation || 0, p.color || colors[0], colors);
      } else {
        for (var i = 0; i < Math.min(dataPoints.length, 40); i++) {
          var p = dataPoints[i];
          if (p.x === null || p.y === null) continue;
          var size = ((p.size || 0.5) * MAX_SIZE) * 0.05;
          this._drawParticleFlow(ctx, width, height, cx + p.x * width/2, cy + p.y * height/2,
              size, elapsed, p.opacity || 1, (p.rotation || 0) + (i * 15), colors[i % colors.length], colors);
        }
      }
    },
    _drawParticleFlow: function(ctx, w, h, x, y, size, elapsed, opacity, baseRotation, baseColor, colors) {
      ctx.save();
      ctx.globalAlpha = opacity;
      ctx.translate(x, y);
      ctx.rotate(baseRotation * Math.PI / 180);

      var particleCount = Math.floor(size * 50) + 10;
      var angleOffset = elapsed * 0.5;

      for (var i = 0; i < particleCount; i++) {
        var angle = (i / particleCount) * Math.PI * 2 + angleOffset;
        var distance = size * (0.5 + Math.sin(elapsed + i * 0.2) * 0.5);
        var px = Math.cos(angle) * distance;
        var py = Math.sin(angle) * distance;
        var particleSize = size * 0.2 * (0.5 + Math.random() * 0.5);
        var trailLength = i / particleCount;

        ctx.fillStyle = colors[Math.floor(i * colors.length / particleCount) % colors.length];
        ctx.beginPath();
        ctx.arc(px, py, particleSize, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();
    },
    cleanup: function() {}
  };
})();
