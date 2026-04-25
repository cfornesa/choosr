/**
 * Voronoi Cells Art Style Module - Polygonal Voronoi diagram
 */
(function() {
  'use strict';
  console.log('[VoronoiCells] Module loading');
  var MAX_SIZE = 400;

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.VoronoiCellsStyle = {
    maxSize: MAX_SIZE,
    init: function(ctx, w, h, rc) {},
    render: function(ctx, width, height, dataPoints, palette, renderingConfig) {
      var colors = (palette && palette.colors) || ['#c9922a', '#f0ece4', '#8a8580', '#444444', '#1c1814'];
      var bg = (palette && palette.background) || '#0d0d0d';
      ctx.save();
      ctx.fillStyle = bg;
      ctx.fillRect(0, 0, width, height);
      ctx.restore();

      var cx = width / 2, cy = height / 2;

      // Manual mode: check renderingConfig.manualMode flag set by renderer
      // Data-driven mode: use cx + p.x * width/2 positioning
      var isManualMode = renderingConfig && renderingConfig.manualMode;

      if (isManualMode) {
        // Draw voronoi cell for each data point (like particleField iterates all points)
        // Note: Using simpler fillRect approach instead of getImageData to avoid
        // coordinate transform conflicts with canvas transforms
        for (var i = 0; i < dataPoints.length; i++) {
          var p = dataPoints[i];
          var px = (p.x - 0.5) * width;
          var py = (p.y - 0.5) * height;
          var radius = ((p.size || 0.5) * MAX_SIZE) * 0.08;
          var count = Math.floor(((p.size || 0.5) * MAX_SIZE) * 0.02) + 4;
          var manualOpacity = (renderingConfig && renderingConfig.opacity !== undefined) ? renderingConfig.opacity : (p.opacity !== null ? p.opacity : 1);
          this._drawVoronoi(ctx, width, height, px, py, radius, count, manualOpacity, p.color || colors[i % colors.length], colors);
        }
      } else if (dataPoints && dataPoints.length > 0) {
        // Data-driven: draw voronoi at each data point position
        var points = [];
        for (var i = 0; i < Math.min(dataPoints.length, 20); i++) {
          var p = dataPoints[i];
          if (p.x === null || p.y === null) continue;
          var ptX = cx + p.x * width/2;
          var ptY = cy + p.y * height/2;
          points.push({
            x: ptX,
            y: ptY,
            color: colors[i % colors.length]
          });
        }
        this._drawVoronoiFromPoints(ctx, width, height, points, dataPoints, palette, renderingConfig);
      }
    },
    _drawVoronoi: function(ctx, w, h, cx, cy, radius, count, opacity, baseColor, colors) {
      var points = [];
      for (var i = 0; i < count; i++) {
        var angle = (i / count) * Math.PI * 2;
        var r = radius * (0.7 + Math.random() * 0.3);
        points.push({
          x: cx + Math.cos(angle) * r,
          y: cy + Math.sin(angle) * r,
          color: colors[i % colors.length]
        });
      }
      this._drawVoronoiFromPoints(ctx, w, h, points, {}, {colors: colors}, {});
      ctx.globalAlpha = opacity;
    },
    _drawVoronoiFromPoints: function(ctx, w, h, points, dataPoints, palette, rc) {
      var colors = (palette && palette.colors) || ['#c9922a', '#f0ece4', '#8a8580'];
      var bg = (palette && palette.background) || '#0d0d0d';

      // Simple voronoi approximation: draw cells by finding nearest points
      var img = ctx.getImageData(0, 0, w, h);
      var data = img.data;

      for (var y = 0; y < h; y += 4) {
        for (var x = 0; x < w; x += 4) {
          var nearestDist = Infinity;
          var nearestColor = colors[0];
          for (var i = 0; i < points.length; i++) {
            var dx = x - points[i].x;
            var dy = y - points[i].y;
            var dist = dx * dx + dy * dy;
            if (dist < nearestDist) {
              nearestDist = dist;
              nearestColor = points[i].color || colors[i % colors.length];
            }
          }
          // Draw a small rect at this position
          ctx.fillStyle = nearestColor;
          ctx.fillRect(x, y, 4, 4);
        }
      }
    },
    cleanup: function() {}
  };
})();
