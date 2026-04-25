/**
 * Pixel Mosaic Art Style Module - Grid of colored blocks/tiles
 */
(function() {
  'use strict';
  console.log('[PixelMosaic] Module loading');
  var MAX_SIZE = 200;

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.PixelMosaicStyle = {
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
        var px;
        var py;
        // Manual mode: canvas origin is at center after renderer transform
        if (renderingConfig && renderingConfig.manualMode) {
          px = (p.x - 0.5) * width;
          py = (p.y - 0.5) * height;
        } else {
          px = cx + p.x * width/2;
          py = cy + p.y * height/2;
        }
        var tileSize = (p.size || MAX_SIZE) * 0.1;
        var count = Math.floor((p.size || MAX_SIZE) * 0.5);
        // Use renderingConfig.opacity if provided (from canvas-level visual dimensions), else fall back to point opacity
        var manualOpacity = (renderingConfig && renderingConfig.opacity !== undefined) ? renderingConfig.opacity : (p.opacity || 1);
        this._drawMosaic(ctx, px, py, tileSize, count, manualOpacity, p.rotation || 0, p.color || colors[0], colors);
      } else {
        for (var i = 0; i < Math.min(dataPoints.length, 30); i++) {
          var p = dataPoints[i];
          if (p.x === null || p.y === null) continue;
          var px = cx + p.x * width/2, py = cy + p.y * height/2;
          var tileSize = ((p.size || 0.5) * MAX_SIZE) * 0.1;
          var count = Math.floor(((p.size || 0.5) * MAX_SIZE) * 0.5);
          this._drawMosaic(ctx, px, py, tileSize, count, p.opacity || 1, p.rotation || 0, colors[i % colors.length], colors);
        }
      }
    },
    _drawMosaic: function(ctx, x, y, tileSize, count, opacity, rotation, baseColor, colors) {
      ctx.save();
      ctx.globalAlpha = opacity;
      ctx.translate(x, y);
      ctx.rotate(rotation * Math.PI / 180);

      var perdue = count;
      for (var i = 0; i < perdue; i++) {
        var row = Math.floor(i / Math.sqrt(perdue));
        var col = i % Math.sqrt(perdue);
        var color = colors[(row + col) % colors.length];
        ctx.fillStyle = color;
        ctx.fillRect(
          (col - Math.sqrt(perdue)/2) * tileSize,
          (row - Math.sqrt(perdue)/2) * tileSize,
          tileSize * 0.9,
          tileSize * 0.9
        );
      }
      ctx.restore();
    },
    cleanup: function() {}
  };
})();
