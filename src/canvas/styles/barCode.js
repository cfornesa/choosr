/**
 * Bar Code Art Style Module - Linear bar representation
 */
(function() {
  'use strict';
  console.log('[BarCode] Module loading');
  var MAX_SIZE = 300;

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.BarCodeStyle = {
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
        var barCount = Math.max(3, Math.floor((p.size || MAX_SIZE) * 0.05));
        this._drawBars(ctx, cx + p.x * width/2, cy + p.y * height/2, barCount, 
            (p.size || MAX_SIZE) * 0.2, p.opacity || 1, (p.rotation || 0) % 180 === 0 ? 'vertical' : 'horizontal',
            p.color || colors[0], colors);
      } else if (dataPoints && dataPoints.length > 0) {
        // Data-driven: draw bars for each data point
        var barWidth = width / Math.min(dataPoints.length + 2, 20) * 0.8;
        var spacing = barWidth * 0.2;
        for (var i = 0; i < Math.min(dataPoints.length, 20); i++) {
          var p = dataPoints[i];
          if (p.x === null) continue;
          var x = (i + 0.5) * (barWidth + spacing);
          var barHeight = ((p.y || 0) + 1) / 2 * height * 0.8;
          var barColor = colors[i % colors.length];
          ctx.fillStyle = barColor;
          ctx.fillRect(x, height - barHeight, barWidth, barHeight);
        }
      }
    },
    _drawBars: function(ctx, x, y, count, maxHeight, opacity, direction, baseColor, colors) {
      ctx.save();
      ctx.globalAlpha = opacity;
      ctx.translate(x, y);
      
      var width = maxHeight * 0.3 * count;
      var height = maxHeight;

      if (direction === 'horizontal') {
        // Swap dimensions for horizontal drawing
        var temp = width;
        width = height;
        height = temp;
        ctx.rotate(Math.PI / 2);
      }

      var barWidth = width / count;
      for (var i = 0; i < count; i++) {
        var barHeight = height * (0.3 + (i / count) * 0.7);
        var color = colors[i % colors.length] || baseColor;
        ctx.fillStyle = color;
        ctx.fillRect(i * barWidth, -barHeight/2, barWidth * 0.8, barHeight);
      }
      ctx.restore();
    },
    cleanup: function() {}
  };
})();
