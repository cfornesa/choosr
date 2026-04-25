/**
 * Radial Symmetry Art Style Module - Kaleidoscope/mirrored patterns
 */
(function() {
  'use strict';
  console.log('[RadialSymmetry] Module loading');
  var MAX_SIZE = 400;

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.RadialSymmetryStyle = {
    maxSize: MAX_SIZE,
    init: function(ctx, w, h, rc) {},
    render: function(ctx, width, height, dataPoints, palette, rc) {
      var colors = (palette && palette.colors) || ['#c9922a', '#f0ece4', '#8a8580', '#444444'];
      var bg = (palette && palette.background) || '#0d0d0d';
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
        this._drawSymmetry(ctx, cx, cy, (p.size || MAX_SIZE) * 0.3, 
            p.rotation || 0, Math.max(3, Math.floor(p.size * 0.02)) || 6, 
            manualOpacity, p.color || colors[0], colors);
      } else {
        for (var i = 0; i < Math.min(dataPoints.length, 10); i++) {
          var p = dataPoints[i];
          if (p.x === null || p.y === null) continue;
          var segments = Math.max(3, Math.floor(((p.size || 0.5) * MAX_SIZE) * 0.02)) || 6;
          this._drawSymmetry(ctx, cx + p.x * width/2, cy + p.y * height/2, 
              ((p.size || 0.5) * MAX_SIZE) * 0.3, p.rotation || (i * 30), segments,
              p.opacity || 1, colors[i % colors.length], colors);
        }
      }
    },
    _drawSymmetry: function(ctx, x, y, radius, baseRotation, segments, opacity, baseColor, colors) {
      ctx.save();
      ctx.globalAlpha = opacity;
      ctx.translate(x, y);
      ctx.rotate(baseRotation * Math.PI / 180);

      for (var s = 0; s < segments; s++) {
        ctx.save();
        ctx.rotate((s / segments) * Math.PI * 2);
        this._drawPattern(ctx, radius, colors[s % colors.length]);
        ctx.restore();
      }
      ctx.restore();
    },
    _drawPattern: function(ctx, radius, color) {
      ctx.fillStyle = color;
      ctx.beginPath();
      ctx.moveTo(0, 0);
      ctx.lineTo(radius, -radius * 0.3);
      ctx.lineTo(radius * 0.8, radius * 0.2);
      ctx.lineTo(radius * 0.3, radius * 0.8);
      ctx.lineTo(-radius * 0.2, radius * 0.6);
      ctx.closePath();
      ctx.fill();
    },
    cleanup: function() {}
  };
})();
