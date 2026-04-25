/**
 * Heat Map Art Style Module - Density-based color gradient
 */
(function() {
  'use strict';
  console.log('[HeatMap] Module loading');
  var MAX_SIZE = 600;

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.HeatMapStyle = {
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
        this._drawGradient(ctx, cx + p.x * width/2, cy + p.y * height/2, 
            (p.size || MAX_SIZE) * 0.5, p.opacity || 1, p.rotation || 0, p.color || colors[0], colors);
      } else if (dataPoints && dataPoints.length > 0) {
        // Create a density grid
        var gridSize = 20;
        var densityGrid = [];
        for (var i = 0; i < gridSize; i++) {
          densityGrid[i] = [];
          for (var j = 0; j < gridSize; j++) {
            densityGrid[i][j] = 0;
          }
        }

        // Count points in each grid cell
        for (var i = 0; i < dataPoints.length; i++) {
          var p = dataPoints[i];
          if (p.x === null || p.y === null) continue;
          var gx = Math.floor(((p.x + 1) / 2) * gridSize);
          var gy = Math.floor(((p.y + 1) / 2) * gridSize);
          if (gx >= 0 && gx < gridSize && gy >= 0 && gy < gridSize) {
            densityGrid[gx][gy]++;
          }
        }

        // Find max density for normalization
        var maxDensity = 1;
        for (var i = 0; i < gridSize; i++) {
          for (var j = 0; j < gridSize; j++) {
            if (densityGrid[i][j] > maxDensity) maxDensity = densityGrid[i][j];
          }
        }

        // Draw heat map
        for (var i = 0; i < gridSize; i++) {
          for (var j = 0; j < gridSize; j++) {
            if (densityGrid[i][j] > 0) {
              var intensity = densityGrid[i][j] / maxDensity;
              var colorIdx = Math.min(Math.floor(intensity * colors.length), colors.length - 1);
              ctx.fillStyle = colors[colorIdx];
              ctx.globalAlpha = intensity * 0.8 + 0.2;
              ctx.fillRect(
                (i / gridSize) * width,
                (j / gridSize) * height,
                width / gridSize,
                height / gridSize
              );
            }
          }
        }
        ctx.globalAlpha = 1;
      }
    },
    _drawGradient: function(ctx, x, y, radius, opacity, rotation, baseColor, colors) {
      ctx.save();
      ctx.globalAlpha = opacity;
      ctx.translate(x, y);
      ctx.rotate(rotation * Math.PI / 180);

      var gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, radius);
      gradient.addColorStop(0, baseColor || colors[0]);
      for (var i = 1; i < colors.length; i++) {
        gradient.addColorStop(i / (colors.length - 1), colors[i]);
      }
      gradient.addColorStop(1, '#0d0d0d');

      ctx.fillStyle = gradient;
      ctx.beginPath();
      ctx.arc(0, 0, radius, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    },
    cleanup: function() {}
  };
})();
