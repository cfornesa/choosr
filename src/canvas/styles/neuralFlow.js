/**
 * Neural Flow Art Style Module - Perlin noise-driven organic curves
 */
(function() {
  'use strict';
  console.log('[NeuralFlow] Module loading');
  var MAX_SIZE = 600;

  // Simple pseudo-random for Perlin noise
  var seed = Math.random();
  function noise(x, y) {
    var n = x + y * 57;
    n = (n << 13) ^ n;
    return (1.0 - ((n * (n * n * 15731 + 789221) + 1376312589) & 0x7fffffff) / 1073741824.0);
  }

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.NeuralFlowStyle = {
    maxSize: MAX_SIZE,
    init: function(ctx, width, height, rc) {},
    render: function(ctx, width, height, dataPoints, palette, rc) {
      var colors = (palette && palette.colors) || ['#c9922a', '#f0ece4', '#8a8580'];
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
        px = cx + p.x * width/2, py = cy + p.y * height/2;
        var scale = (p.size || MAX_SIZE) * 0.01;
        this._drawFlow(ctx, px, py, width, height, scale, p.opacity || 1, p.rotation || 0, p.color || colors[0], colors);
      } else {
        for (var i = 0; i < Math.min(dataPoints.length, 50); i++) {
          var p = dataPoints[i];
          if (p.x === null || p.y === null) continue;
          var px = cx + p.x * width/2, py = cy + p.y * height/2;
          var scale = ((p.size || 0.5) * MAX_SIZE) * 0.01 * (p.size || 0.5);
          this._drawFlow(ctx, px, py, width, height, scale, p.opacity || 1, (p.rotation || 0), colors[i % colors.length], colors);
        }
      }
    },
    _drawFlow: function(ctx, x, y, w, h, scale, opacity, rotation, color, colors) {
      ctx.save();
      ctx.globalAlpha = opacity;
      ctx.strokeStyle = color;
      ctx.lineWidth = 2 * scale;
      ctx.translate(x, y);
      ctx.rotate(rotation * Math.PI / 180);

      var segments = 50;
      var px = 0, py = 0;
      ctx.beginPath();
      ctx.moveTo(0, 0);
      for (var i = 1; i <= segments; i++) {
        var t = i / segments;
        var nx = noise(t * 10 + seed, 0) * scale * w * 0.3;
        var ny = noise(0, t * 10 + seed) * scale * h * 0.3;
        ctx.lineTo(nx, ny);
        px = nx; py = ny;
      }
      ctx.stroke();
      ctx.restore();
    },
    cleanup: function() {}
  };
})();
