/**
 * DCE Settings Page JS
 * Handles: import (AJAX), reset (AJAX), export (native form POST).
 */
(function ($) {
    'use strict';

    if (typeof DCE_Settings === 'undefined') {
        return;
    }
    const S = DCE_Settings; // localised data

    // ── Helpers ───────────────────────────────────────────────────────────────

    function showNotice($el, message, type) {
        $el
            .removeClass('dce-notice--success dce-notice--error')
            .addClass('dce-notice--' + type)
            .text(message)
            .slideDown(200);
    }

    function hideNotice($el) {
        $el.slideUp(150);
    }

    // ── Import ────────────────────────────────────────────────────────────────

    const $importArea   = $('#dce-import-area');
    const $importFile   = $('#dce-import-file');
    const $importBtn    = $('#dce-import-btn');
    const $importLabel  = $('#dce-import-btn-label');
    const $importNotice = $('#dce-import-notice');

    // Drag-over highlight
    $importArea.on('dragover dragenter', function (e) {
        e.preventDefault();
        $(this).addClass('dce-import-area--active');
    }).on('dragleave drop', function (e) {
        e.preventDefault();
        $(this).removeClass('dce-import-area--active');
        if (e.type === 'drop') {
            const file = e.originalEvent.dataTransfer.files[0];
            if (file) handleFileSelected(file);
        }
    });

    // File chosen via dialog
    $importFile.on('change', function () {
        if (this.files && this.files[0]) {
            handleFileSelected(this.files[0]);
        }
    });

    function handleFileSelected(file) {
        if (!file.name.endsWith('.json')) {
            showNotice($importNotice, 'Please select a .json file.', 'error');
            return;
        }
        hideNotice($importNotice);
        $importArea
            .removeClass('dce-import-area--active')
            .addClass('dce-import-area--has-file');
        $importArea.find('.dce-import-area__label').text(file.name);
        $importBtn.prop('disabled', false);
        $importBtn.data('file', file);
    }

    // Run import
    $importBtn.on('click', function () {
        const file = $(this).data('file');
        if (!file) return;

        if (!confirm(S.confirm_import)) return;

        const formData = new FormData();
        formData.append('action', 'dce_import');
        formData.append('nonce', S.import_nonce);
        formData.append('dce_import_file', file);

        $importLabel.text(S.i18n.importing);
        $importBtn.prop('disabled', true);

        $.ajax({
            url: S.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    showNotice($importNotice, res.data.message, 'success');
                    // Reset file area
                    $importArea.removeClass('dce-import-area--has-file');
                    $importArea.find('.dce-import-area__label').text('Click to choose a JSON file');
                    $importFile.val('');
                } else {
                    showNotice($importNotice, res.data.message || S.i18n.error, 'error');
                    $importBtn.prop('disabled', false);
                }
            },
            error: function () {
                showNotice($importNotice, S.i18n.error, 'error');
                $importBtn.prop('disabled', false);
            },
            complete: function () {
                $importLabel.text('Import JSON');
            }
        });
    });

    // ── Reset ─────────────────────────────────────────────────────────────────

    const $resetBtn    = $('#dce-reset-btn');
    const $resetLabel  = $('#dce-reset-btn-label');
    const $resetNotice = $('#dce-reset-notice');

    $resetBtn.on('click', function () {
        if (!confirm(S.confirm_reset)) return;

        $resetLabel.text(S.i18n.resetting);
        $resetBtn.prop('disabled', true);

        $.post(S.ajax_url, {
            action: 'dce_reset',
            nonce: S.reset_nonce
        })
        .done(function (res) {
            if (res.success) {
                showNotice($resetNotice, res.data.message, 'success');
                // Refresh the stats bar after a short pause
                setTimeout(function () { location.reload(); }, 1800);
            } else {
                showNotice($resetNotice, res.data.message || S.i18n.error, 'error');
                $resetBtn.prop('disabled', false);
                $resetLabel.text('Reset All Classes');
            }
        })
        .fail(function () {
            showNotice($resetNotice, S.i18n.error, 'error');
            $resetBtn.prop('disabled', false);
            $resetLabel.text('Reset All Classes');
        });
    });

})(jQuery);
