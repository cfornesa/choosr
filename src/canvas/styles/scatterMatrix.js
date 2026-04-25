/**
 * Scatter Matrix Art Style Module - Small multiples showing pairwise relationships
 */
(function() {
  'use strict';
  console.log('[ScatterMatrix] Module loading');
  var MAX_SIZE = 200;

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.ScatterMatrixStyle = {
    maxSize: MAX_SIZE,
    init: function(ctx, w, h, rc) {},
    render: function(ctx, width, height, dataPoints, palette, renderingConfig) {
      var colors = (palette && palette.colors) || ['#c9922a', '#f0ece4', '#8a8580', '#444444'];
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
        // Draw matrix for each data point (like particleField iterates all points)
        for (var i = 0; i < dataPoints.length; i++) {
          var p = dataPoints[i];
          var cellSize = ((p.size || 0.5) * MAX_SIZE) * 0.08;
          var rows = Math.max(2, Math.floor(((p.size || 0.5) * MAX_SIZE) * 0.02));
          var manualOpacity = (renderingConfig && renderingConfig.opacity !== undefined) ? renderingConfig.opacity : (p.opacity !== null ? p.opacity : 1);
          this._drawMatrix(ctx, width, height, rows, cellSize, manualOpacity, p.color || colors[i % colors.length], colors);
        }
      } else if (dataPoints && dataPoints.length > 0) {
        // In data-driven mode, create a matrix based on pairwise relationships
        var cellSize = Math.min(40, width / Math.ceil(Math.sqrt(dataPoints.length)));
        var rows = Math.ceil(Math.sqrt(dataPoints.length));
        this._drawDataMatrix(ctx, width, height, dataPoints, cellSize, rows, colors, palette);
      }
    },
    _drawMatrix: function(ctx, w, h, rows, cellSize, opacity, color, colors) {
      ctx.save();
      ctx.globalAlpha = opacity;
      var cols = rows;
      var startX = (w - cols * cellSize) / 2;
      var startY = (h - rows * cellSize) / 2;

      for (var r = 0; r < rows; r++) {
        for (var c = 0; c < cols; c++) {
          var x = startX + c * cellSize;
          var y = startY + r * cellSize;
          var currentColor = colors[(r + c) % colors.length];
          
          // Draw cell with pattern
          ctx.fillStyle = currentColor;
          ctx.fillRect(x, y, cellSize, cellSize);

          // Add diagonal line for symmetry
          ctx.strokeStyle = color;
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(x, y);
          ctx.lineTo(x + cellSize, y + cellSize);
          ctx.stroke();
        }
      }
      ctx.restore();
    },
    _drawDataMatrix: function(ctx, w, h, dataPoints, cellSize, rows, colors, palette) {
      ctx.save();
      var cols = rows;
      var startX = (w - cols * cellSize) / 2;
      var startY = (h - rows * cellSize) / 2;

      for (var r = 0; r < rows; r++) {
        for (var c = 0; c < cols; c++) {
          var idx = r * cols + c;
          if (idx >= dataPoints.length) continue;
          
          var p = dataPoints[idx];
          if (p.x === null || p.y === null) continue;

          var x = startX + c * cellSize;
          var y = startY + r * cellSize;
          var sizeVal = (p.size || 0.5) * cellSize * 0.8;
          var opacity = p.opacity || 1;
          var rotation = (p.rotation || 0) * Math.PI / 180;
          var color = colors[idx % colors.length];

          ctx.save();
          ctx.translate(x + cellSize/2, y + cellSize/2);
          ctx.rotate(rotation);
          ctx.globalAlpha = opacity;
          
          ctx.fillStyle = color;
          ctx.fillRect(-sizeVal/2, -sizeVal/2, sizeVal, sizeVal);
          
          // Add point marker
          ctx.fillStyle = '#ffffff';
          ctx.globalAlpha = 0.6;
          ctx.beginPath();
          ctx.arc(0, 0, 2, 0, Math.PI * 2);
          ctx.fill();
          
          ctx.restore();
        }
      }
      ctx.restore();
    },
    cleanup: function() {}
  };
})();
