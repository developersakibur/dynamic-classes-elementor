/**
 * DCE Editor Integration & Calculator Logic
 * Enhanced with Responsiveness, Copy Fallback, and Corrected Logic
 */
(function($) {
    'use strict';

    const DCE_Calculator = {
        DEFAULT_CONFIGS: {
            text: { minValue: 14, minVp: 16, maxValue: 40, maxVp: 72 },
            padding: { minValue: 15, minVp: 20, maxValue: 55, maxVp: 100 },
            max_width: { minValue: 320, minVp: 375, maxValue: 1200, maxVp: 1440 }
        },
        DEFAULT_VIEWPORT: {
            minWidth: 375,
            maxWidth: 1440
        },
        configs: {},
        viewport: {},
        currentClampValue: "",
        isShowing: false,

        init: function() {
            console.log('DCE: Calculator Initializing...');
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
                $('#maxSize').val(40);
            }
            this.updateMinSize();
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
            targetSections.forEach(section => {
                if ($('.elementor-control-' + section).hasClass('e-open')) shouldShow = true;
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

        // --- Logic Fixes ---

        getMobileSize: function(desktopPx, propertyType) {
            desktopPx = parseInt(desktopPx);
            if (!desktopPx || desktopPx < 1) return 1;
            
            // Extension logic: if less than 10, just subtract 1
            if (desktopPx < 10) return Math.max(desktopPx - 1, 1);

            const config = this.configs[propertyType];
            if (!config) return Math.round(desktopPx * 0.75);

            if (desktopPx < config.minVp) {
                const diff = Math.abs(config.minVp - config.minValue);
                return Math.max(desktopPx - diff, 1);
            }

            const ratio = (desktopPx - config.minVp) / (config.maxVp - config.minVp);
            const mobileSize = config.minValue + (ratio * (config.maxValue - config.minValue));
            return Math.round(mobileSize);
        },

        updateMinSize: function() {
            const maxPx = parseInt($('#maxSize').val());
            const type = $('input[name="propertyType"]:checked').val();

            if (!isNaN(maxPx) && maxPx >= 10) {
                const calcMin = this.getMobileSize(maxPx, type);
                $('#minSize').val(calcMin);
                this.updateClamp();
            }
        },

        updateClamp: function() {
            const minVW = +$('#minWidth').val();
            const maxVW = +$('#maxWidth').val();
            const minPx = +$('#minSize').val();
            const maxPx = +$('#maxSize').val();

            if (!minVW || !maxVW || !minPx || !maxPx) {
                $('#result').html('<span class="error-message">Enter values (Min 10)</span>');
                this.currentClampValue = "";
                return;
            }

            const slope = (maxPx - minPx) / (maxVW - minVW);
            const vwVal = (slope * 100).toFixed(2);
            const interceptPx = minPx - slope * minVW;

            this.currentClampValue = `clamp(${minPx}px, ${interceptPx.toFixed(2)}px + ${vwVal}vw, ${maxPx}px)`;
            $('#result').text(this.currentClampValue);
        },

        copyClamp: function() {
            if (!this.currentClampValue) return;
            const text = this.currentClampValue;

            const performCopy = (val) => {
                $('#result').addClass('copied');
                setTimeout(() => $('#result').removeClass('copied'), 1500);
            };

            // Modern API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(performCopy);
            } else {
                // Fallback for older browsers or insecure contexts
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    performCopy();
                } catch (err) {
                    console.error('Fallback copy failed', err);
                }
                document.body.removeChild(textArea);
            }
        },

        // --- Event Binding ---

        loadSettings: function() {
            const savedConfigs = localStorage.getItem('dce_property_configs');
            const savedViewport = localStorage.getItem('dce_viewport_settings');
            this.configs = savedConfigs ? JSON.parse(savedConfigs) : { ...this.DEFAULT_CONFIGS };
            this.viewport = savedViewport ? JSON.parse(savedViewport) : { ...this.DEFAULT_VIEWPORT };
        },

        saveSettings: function() {
            localStorage.setItem('dce_property_configs', JSON.stringify(this.configs));
            localStorage.setItem('dce_viewport_settings', JSON.stringify(this.viewport));
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
                localStorage.setItem('dce_config_panel_open', $(this).is(':checked'));
            });
            $('#result').on('click', () => this.copyClamp());
            
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
        },

        applyConfigsToRadios: function() {
            const self = this;
            $('input[name="propertyType"]').each(function() {
                const type = $(this).val();
                const config = self.configs[type];
                if (config) {
                    $(this).data('minVp', config.minVp);
                    $(this).data('maxVp', config.maxVp);
                    $(this).data('minValue', config.minValue);
                    $(this).data('maxValue', config.maxValue);
                }
            });
        },

        loadConfigInputs: function(type) {
            const config = this.configs[type];
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
            this.applyConfigsToRadios();
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
