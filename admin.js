jQuery(document).ready(function($) {
    let gapIndex = $('.dce-class-row', '#gap-classes-container').length;
    let paddingIndex = $('.dce-class-row', '#padding-classes-container').length;
    let marginIndex = $('.dce-class-row', '#margin-classes-container').length;
    
    // Tab switching
    $('.dce-tab-btn').on('click', function() {
        const tab = $(this).data('tab');
        
        // Update active tab button
        $('.dce-tab-btn').removeClass('active');
        $(this).addClass('active');
        
        // Show/hide tab content
        $('.dce-tab-content').removeClass('active');
        $('#tab-' + tab).addClass('active');
    });
    
    // Add new class
    $('.dce-add-class').on('click', function() {
        const type = $(this).data('type');
        const template = $('#dce-' + type + '-template').html();
        const container = $('#' + type + '-classes-container');
        
        // Remove empty message if exists
        container.find('.dce-empty-message').remove();
        
        let index;
        if (type === 'gap') {
            index = gapIndex++;
        } else if (type === 'padding') {
            index = paddingIndex++;
        } else {
            index = marginIndex++;
        }
        
        // Replace placeholder with actual index
        const newRow = template.replace(/\{\{INDEX\}\}/g, index);
        container.append(newRow);
        
        // Focus on the class name input
        container.find('.dce-class-row:last-child input[type="text"]:first').focus();
    });
    
    // Delete class
    $(document).on('click', '.dce-delete-class', function() {
        if (confirm('Are you sure you want to delete this class?')) {
            $(this).closest('.dce-class-row').fadeOut(300, function() {
                $(this).remove();
                
                // Show empty message if no classes left
                const container = $(this).closest('[id$="-classes-container"]');
                if (container.find('.dce-class-row').length === 0) {
                    const type = container.attr('id').replace('-classes-container', '');
                    const typeName = type.charAt(0).toUpperCase() + type.slice(1);
                    container.html('<p class="dce-empty-message">No ' + type + ' classes yet. Click "Add New ' + typeName + ' Class" to create one.</p>');
                }
            });
        }
    });
    
    // Form submission with AJAX
    $('#dce-main-form').on('submit', function(e) {
        e.preventDefault();
        
        const saveBtn = $(this).find('button[type="submit"]');
        const saveMessage = $('.dce-save-message');
        
        // Disable button and show loading
        saveBtn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt dce-spinning"></span> Saving...');
        saveMessage.removeClass('success error').text('');
        
        // Collect all data
        const formData = new FormData(this);
        formData.append('action', 'dce_save_classes');
        formData.append('nonce', dceAdmin.nonce);
        
        // Convert FormData to object for AJAX
        const data = {};
        formData.forEach((value, key) => {
            // Handle array inputs
            if (key.includes('[')) {
                const matches = key.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
                if (matches) {
                    const arrayName = matches[1];
                    const index = matches[2];
                    const field = matches[3];
                    
                    if (!data[arrayName]) {
                        data[arrayName] = {};
                    }
                    if (!data[arrayName][index]) {
                        data[arrayName][index] = {};
                    }
                    data[arrayName][index][field] = value;
                }
            } else {
                data[key] = value;
            }
        });
        
        // Send AJAX request
        $.ajax({
            url: dceAdmin.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    saveMessage.addClass('success').text('✓ ' + response.data.message);
                    
                    // Reset button after delay
                    setTimeout(function() {
                        saveBtn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save All Changes');
                        saveMessage.text(''); // Clear text immediately
                        saveMessage.fadeOut(300, function() {
                            $(this).removeClass('success error'); // Remove classes after fade out
                        });
                    }, 2000);
                } else {
                    saveMessage.addClass('error').text('✗ Error: ' + response.data.message);
                    saveBtn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save All Changes');
                }
            },
            error: function() {
                saveMessage.addClass('error').text('✗ An error occurred while saving.');
                saveBtn.prop('disabled', false).html('<span class="dashicons dashicons-saved"></span> Save All Changes');
            }
        });
    });
    
    // Auto-sanitize class names (remove spaces and special chars)
    $(document).on('blur', 'input[name*="[name]"]', function() {
        let value = $(this).val();
        value = value.toLowerCase()
                     .replace(/[^a-z0-9-_]/g, '-')
                     .replace(/-+/g, '-')
                     .replace(/^-|-$/g, '');
        $(this).val(value);
    });
});