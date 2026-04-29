/**
 * DCE Editor Integration & Calculator Logic
 * Enhanced with Fluid Clamp Generator Logic & Design
 */
(function($) {
    'use strict';

    const DCE_Calculator = {
        BRAND_NAME: "developersakibur",
        DEFAULT_CONFIGS: {
            text: { minValue: 14, minVp: 16, maxValue: 30, maxVp: 48 },
            padding: { minValue: 15, minVp: 20, maxValue: 55, maxVp: 100 },
        },
        DEFAULT_VIEWPORT: {
            minWidth: 375,
            maxWidth: 1440
        },
        configs: {},
        viewport: {},
        currentClampValue: "",
        isShowing: false,
        saveTimeout: null,

        init: function() {
            this.loadSettings();
            this.injectTemplate();
            this.startWatcher();
            this.bindCalculatorEvents();
            
            this.applyConfigsToRadios();
            this.applyViewportValues();
            
            const selectedType = $('input[name="propertyType"]:checked').val();
            this.loadConfigInputs(selectedType);

            // Set default maxSize if empty
            if (!$('#maxSize').val()) {
                $('#maxSize').val(48);
            }
            this.updateMinSize();
            this.updateSliderPreview();
        },

        injectTemplate: function() {
            if ($('#dce-calculator-wrapper').length) return;
            $('body').append(DCE_Editor.template);
        },

        startWatcher: function() {
            const self = this;
            
            // Monitor panel for section changes
            const observer = new MutationObserver(() => self.checkActiveSections());
            const watchPanel = () => {
                const $panel = $('#elementor-panel-content-wrapper');
                if ($panel.length) {
                    observer.observe($panel[0], { attributes: true, subtree: true, attributeFilter: ['class'] });
                } else {
                    setTimeout(watchPanel, 500);
                }
            };
            watchPanel();

            // Responsiveness: Update position on window resize
            $(window).on('resize', () => {
                if (this.isShowing) this.updatePosition();
            });

            // Heartbeat for state and position
            setInterval(() => {
                this.checkActiveSections();
                if (this.isShowing) this.updatePosition();
            }, 500);

            if (window.elementor && elementor.channels) {
                elementor.channels.editor.on('site_settings:closed', () => this.hide());
            }
        },

        checkActiveSections: function() {
            const targetSections = [
                'section_gap_classes', 
                'section_padding_classes', 
                'section_margin_classes', 
                'section_min_height_classes',
                'section_max_width_classes'
            ];
            let shouldShow = false;
            
            // If any of these sections exist in the panel, it means the Dynamic Classes tab is active.
            // Elementor's Kit manager swaps the content of the panel when switching tabs.
            targetSections.forEach(section => {
                if ($('.elementor-control-' + section).length > 0) {
                    shouldShow = true;
                }
            });

            if (shouldShow && !this.isShowing) {
                this.show();
            } else if (!shouldShow && this.isShowing) {
                this.hide();
            }
        },

        updatePosition: function() {
            const $preview = $('#elementor-preview');
            if ($preview.length) {
                const rect = $preview[0].getBoundingClientRect();
                $('#dce-calculator-wrapper').css({
                    top: rect.top,
                    left: rect.left,
                    width: rect.width,
                    height: rect.height
                });
            }
        },

        show: function() {
            this.isShowing = true;
            this.updatePosition();
            $('#dce-calculator-wrapper').css({ display: 'flex', position: 'fixed', zIndex: 99999 }).hide().fadeIn(200);
            $('#elementor-preview-iframe').css('opacity', '0');
        },

        hide: function() {
            this.isShowing = false;
            $('#dce-calculator-wrapper').fadeOut(200);
            $('#elementor-preview-iframe').css('opacity', '1');
        },

        // --- Logic from popup.js ---

        getMobileSize: function(desktopPx, propertyType) {
            const isNegative = desktopPx < 0;
            const absPx = Math.abs(desktopPx);

            const config = this.configs[propertyType];
            if (!config) {
                const result = Math.round(absPx * 0.75);
                return isNegative ? -result : result;
            }

            const minVP = parseInt(config.minVp);
            const maxVP = parseInt(config.maxVp);
            const minValue = parseInt(config.minValue);
            const maxValue = parseInt(config.maxValue);
            const smallSubtract = Math.abs(minVP - minValue);

            let result;
            if (absPx < minVP) {
                result = absPx - smallSubtract;
            } else {
                const ratio = (absPx - minVP) / (maxVP - minVP);
                result = minValue + ratio * (maxValue - minValue);
            }

            const finalResult = Math.round(result);
            return isNegative ? -finalResult : finalResult;
        },

        updateMinSize: function() {
            const maxPxValue = $('#maxSize').val().trim();
            const type = $('input[name="propertyType"]:checked').val();

            if (maxPxValue !== "") {
                const maxPx = parseFloat(maxPxValue);
                const calculatedMin = this.getMobileSize(maxPx, type);
                $('#minSize').val(calculatedMin);
                this.updateClamp();
            }
        },

        updateClamp: function() {
            const minVWValue = $('#minWidth').val().trim();
            const maxVWValue = $('#maxWidth').val().trim();
            const minPxValue = $('#minSize').val().trim();
            const maxPxValue = $('#maxSize').val().trim();

            $('#result').removeClass("copied");

            if (minVWValue === "" || maxVWValue === "" || minPxValue === "" || maxPxValue === "") {
                $('#result').html('<span class="error-message">Enter values</span>');
                this.currentClampValue = "";
                return;
            }

            const minVW = parseFloat(minVWValue);
            const maxVW = parseFloat(maxVWValue);
            const minPx = parseFloat(minPxValue);
            const maxPx = parseFloat(maxPxValue);

            if (maxVW === minVW) {
                $('#result').html('<span class="error-message">Viewport widths must differ</span>');
                this.currentClampValue = "";
                return;
            }

            // Check if we should use the "calc(-1 * clamp(...))" pattern for negative values
            const bothNegative = minPx < 0 && maxPx < 0;

            if (bothNegative) {
                const absMinPx = Math.abs(minPx);
                const absMaxPx = Math.abs(maxPx);
                const slope = (absMaxPx - absMinPx) / (maxVW - minVW);
                const vwVal = (slope * 100).toFixed(2);
                const interceptPx = absMinPx - slope * minVW;
                const actualMin = Math.min(absMinPx, absMaxPx);
                const actualMax = Math.max(absMinPx, absMaxPx);

                const innerValuePositive = `${actualMin}px, ${interceptPx.toFixed(2)}px + ${vwVal}vw, ${actualMax}px`;
                this.currentClampValue = `calc(-1 * clamp(${innerValuePositive}))`;
                $('#result').text(`-1 * (${innerValuePositive})`);
            } else {
                const slope = (maxPx - minPx) / (maxVW - minVW);
                const vwVal = (slope * 100).toFixed(2);
                const interceptPx = minPx - slope * minVW;
                const actualMin = Math.min(minPx, maxPx);
                const actualMax = Math.max(minPx, maxPx);

                const innerValue = `${actualMin}px, ${interceptPx.toFixed(2)}px + ${vwVal}vw, ${actualMax}px`;
                this.currentClampValue = `clamp(${innerValue})`;
                $('#result').text(innerValue);
            }
            this.updateSliderPreview();
        },

        copyClamp: function() {
            if (!this.currentClampValue) return;
            const text = this.currentClampValue;

            const performCopy = () => {
                $('#result').addClass('copied');
                setTimeout(() => $('#result').removeClass('copied'), 1500);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(performCopy).catch(() => this.fallbackCopy(text, performCopy));
            } else {
                this.fallbackCopy(text, performCopy);
            }
        },

        fallbackCopy: function(text, callback) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                if (callback) callback();
            } catch (err) {
                console.error("Fallback copy failed", err);
            }
            document.body.removeChild(textArea);
        },

        updateSliderPreview: function() {
            const minVW = parseFloat($('#minWidth').val()) || this.DEFAULT_VIEWPORT.minWidth;
            const maxVW = parseFloat($('#maxWidth').val()) || this.DEFAULT_VIEWPORT.maxWidth;
            const vw = parseFloat($('#vpPreviewSlider').val());

            $('#vpPreviewSlider').attr('min', minVW);
            $('#vpPreviewSlider').attr('max', maxVW);

            const percent = (vw - minVW) / (maxVW - minVW) * 100;
            $('#sliderTrackFill').css('width', Math.max(0, Math.min(100, percent)) + "%");

            $('#sliderCurrentVp').text(vw);
            $('#sliderCurrentClamp').text(this.calcClampAtVp(vw));
        },

        calcClampAtVp: function(vw) {
            const minPx = parseFloat($('#minSize').val());
            const maxPx = parseFloat($('#maxSize').val());
            const minVW = parseFloat($('#minWidth').val()) || this.DEFAULT_VIEWPORT.minWidth;
            const maxVW = parseFloat($('#maxWidth').val()) || this.DEFAULT_VIEWPORT.maxWidth;

            if (isNaN(minPx) || isNaN(maxPx)) return "—";

            const slope = (maxPx - minPx) / (maxVW - minVW);
            const intercept = minPx - slope * minVW;
            const preferred = intercept + slope * vw;
            const clampMin = Math.min(minPx, maxPx);
            const clampMax = Math.max(minPx, maxPx);
            return Math.min(Math.max(preferred, clampMin), clampMax).toFixed(2);
        },

        // --- Settings Management ---

        loadSettings: function() {
            const savedConfigs = localStorage.getItem('dce_property_configs');
            const savedViewport = localStorage.getItem('dce_viewport_settings');
            const panelOpen = localStorage.getItem('dce_config_panel_open');

            this.configs = savedConfigs ? JSON.parse(savedConfigs) : { ...this.DEFAULT_CONFIGS };
            this.viewport = savedViewport ? JSON.parse(savedViewport) : { ...this.DEFAULT_VIEWPORT };

            // Default to open if never set
            if (panelOpen === null || panelOpen === 'true') {
                $('#configToggle').prop('checked', true);
                $('.config-section').show();
            } else {
                $('#configToggle').prop('checked', false);
                $('.config-section').hide();
            }
        },

        saveSettings: function() {
            clearTimeout(this.saveTimeout);
            this.saveTimeout = setTimeout(() => {
                localStorage.setItem('dce_property_configs', JSON.stringify(this.configs));
                localStorage.setItem('dce_viewport_settings', JSON.stringify(this.viewport));
                localStorage.setItem('dce_config_panel_open', $('#configToggle').is(':checked'));
            }, 300);
        },

        bindCalculatorEvents: function() {
            const self = this;
            $('#maxSize').on('input', () => this.updateMinSize());
            $('#minSize').on('input', () => this.updateClamp());
            $('#minWidth, #maxWidth').on('input', () => this.saveViewportSettings());
            
            $('input[name="propertyType"]').on('change', function() {
                self.loadConfigInputs($(this).val());
                self.updateMinSize();
            });

            $('#configMinValue, #configMinVp, #configMaxValue, #configMaxVp').on('input', () => this.saveConfigFromInputs());
            
            $('#configToggle').on('change', function() {
                $('.config-section').toggle($(this).is(':checked'));
                self.saveSettings();
            });

            $('#result').on('click', () => this.copyClamp());
            $('#vpPreviewSlider').on('input', () => this.updateSliderPreview());

            // Mouse wheel support
            $('.dce-calculator-container input[type="number"]').on('wheel', function(e) {
                if (document.activeElement === this) {
                    e.preventDefault();
                    const delta = e.originalEvent.deltaY < 0 ? 1 : -1;
                    const val = parseInt($(this).val()) || 0;
                    $(this).val(val + delta).trigger('input');
                }
            });
        },

        applyViewportValues: function() {
            $('#minWidth').val(this.viewport.minWidth);
            $('#maxWidth').val(this.viewport.maxWidth);
            $('#vpPreviewSlider').val(this.viewport.minWidth);
        },

        applyConfigsToRadios: function() {
            // Already handled by loadConfigInputs and data attributes if needed,
            // but we use the central this.configs object in getMobileSize.
        },

        loadConfigInputs: function(type) {
            const config = this.configs[type] || this.DEFAULT_CONFIGS[type];
            if (config) {
                $('#configMinValue').val(config.minValue);
                $('#configMinVp').val(config.minVp);
                $('#configMaxValue').val(config.maxValue);
                $('#configMaxVp').val(config.maxVp);
            }
        },

        saveConfigFromInputs: function() {
            const type = $('input[name="propertyType"]:checked').val();
            this.configs[type] = {
                minVp: parseInt($('#configMinVp').val()) || 1,
                maxVp: parseInt($('#configMaxVp').val()) || 1,
                minValue: parseInt($('#configMinValue').val()) || 1,
                maxValue: parseInt($('#configMaxValue').val()) || 1
            };
            this.saveSettings();
            this.updateMinSize();
        },

        saveViewportSettings: function() {
            this.viewport.minWidth = parseInt($('#minWidth').val()) || this.DEFAULT_VIEWPORT.minWidth;
            this.viewport.maxWidth = parseInt($('#maxWidth').val()) || this.DEFAULT_VIEWPORT.maxWidth;
            this.saveSettings();
            this.updateClamp();
        }
    };

    $(window).on('load', function() {
        DCE_Calculator.init();
    });

})(jQuery);
