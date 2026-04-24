/**
 * Art Styles Registry
 * Maintains an internal registry of available art style modules.
 * Three built-in styles are auto-registered on load if available.
 *
 * Style Module Interface:
 *   {
 *     init: function(ctx, width, height, renderingConfig) {},
 *     render: function(ctx, width, height, dataPoints, paletteConfig, renderingConfig) {},
 *     cleanup: function() {} // optional
 *   }
 *
 * dataPoints structure (per point):
 *   { x: 0-1|null, y: 0-1|null, size: 0-1|null, color: 0-1|null, opacity: 0-1|null, rotation: 0-1|null }
 */
(function() {
  'use strict';

  var DEBUG = false;
  function log() { if (DEBUG) console.log.apply(console, arguments); }

  var registry = {};

  /**
   * Explicitly register the three built-in styles.
   * Safe to call multiple times — idempotent.
   * Exposed as public API for delayed/batched registration scenarios.
   */
  function registerBuiltinStyles() {
    if (window.DataToArt && window.DataToArt.ParticleFieldStyle) {
      registry['particleField'] = window.DataToArt.ParticleFieldStyle;
      log('ArtStyles: registered particleField');
    }
    if (window.DataToArt && window.DataToArt.GeometricGridStyle) {
      registry['geometricGrid'] = window.DataToArt.GeometricGridStyle;
      log('ArtStyles: registered geometricGrid');
    }
    if (window.DataToArt && window.DataToArt.FlowingCurvesStyle) {
      registry['flowingCurves'] = window.DataToArt.FlowingCurvesStyle;
      log('ArtStyles: registered flowingCurves');
    }
  }

  // Auto-register at load time (existing behavior for forward compatibility)
  registerBuiltinStyles();

  window.DataToArt = window.DataToArt || {};
  window.DataToArt.ArtStyles = {

    /**
     * Retrieve a registered style module by key.
     * @param {string} styleKey
     * @returns {Object} style module with init/render/[cleanup] methods
     * @throws {Error} if styleKey is not registered
     */
    getStyle: function(styleKey) {
      if (!registry[styleKey]) {
        throw new Error('ArtStyles: unknown style "' + styleKey + '". Registered: ' + Object.keys(registry).join(', '));
      }
      return registry[styleKey];
    },

    /**
     * List all registered style keys.
     * @returns {string[]}
     */
    listStyles: function() {
      return Object.keys(registry);
    },

    /**
     * Register a new style module.
     * @param {string} styleKey
     * @param {Object} styleModule - must implement at least init() and render()
     */
    registerStyle: function(styleKey, styleModule) {
      if (!styleModule || typeof styleModule.init !== 'function' || typeof styleModule.render !== 'function') {
        throw new Error('ArtStyles: style module must implement init() and render() methods');
      }
      registry[styleKey] = styleModule;
      log('ArtStyles: registered style "' + styleKey + '"');
    },

    /**
     * Re-scan the global namespace for built-in styles.
     * Call this if styles weren't available at load time.
     */
    registerBuiltinStyles: registerBuiltinStyles
  };
})();
