/**
 * IPT System - Main JavaScript file
 * Handles interactive components and utilities
 */

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeComponents();
    setupCSRFTokens();
    setupMobileMenu();
    setupFormValidation();
    setupModalHandlers();
});

/**
 * Initialize all interactive components
 */
function initializeComponents() {
    // Initialize dropdowns
    initializeDropdowns();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize form enhancements
    initializeFormEnhancements();
    
    // Initialize data tables
    initializeDataTables();
}

/**
 * Setup CSRF tokens for AJAX requests
 */
function setupCSRFTokens() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (token) {
        // Set default headers for fetch requests
        window.csrfToken = token.getAttribute('content');
        
        // Setup for jQuery if available
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
        }
    }
}

/**
 * Mobile menu functionality
 */
function setupMobileMenu() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    }
}

/**
 * Form validation setup
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!validateForm(form)) {
                event.preventDefault();
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
        });
    });
}

/**
 * Validate individual form field
 */
function validateField(field) {
    const value = field.value.trim();
    const rules = field.dataset.rules ? field.dataset.rules.split('|') : [];
    const errorElement = field.parentNode.querySelector('.form-error');
    
    let isValid = true;
    let errorMessage = '';
    
    for (const rule of rules) {
        const [ruleName, ruleValue] = rule.split(':');
        
        switch (ruleName) {
            case 'required':
                if (!value) {
                    isValid = false;
                    errorMessage = 'This field is required.';
                }
                break;
                
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (value && !emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address.';
                }
                break;
                
            case 'min':
                if (value && value.length < parseInt(ruleValue)) {
                    isValid = false;
                    errorMessage = `Minimum ${ruleValue} characters required.`;
                }
                break;
                
            case 'max':
                if (value && value.length > parseInt(ruleValue)) {
                    isValid = false;
                    errorMessage = `Maximum ${ruleValue} characters allowed.`;
                }
                break;
                
            case 'match':
                const matchField = document.querySelector(`[name="${ruleValue}"]`);
                if (matchField && value !== matchField.value) {
                    isValid = false;
                    errorMessage = 'Fields do not match.';
                }
                break;
        }
        
        if (!isValid) break;
    }
    
    // Update field styling and error message
    if (isValid) {
        field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        field.classList.add('border-gray-300', 'focus:border-primary-500', 'focus:ring-primary-500');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    } else {
        field.classList.remove('border-gray-300', 'focus:border-primary-500', 'focus:ring-primary-500');
        field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        if (errorElement) {
            errorElement.textContent = errorMessage;
            errorElement.classList.remove('hidden');
        }
    }
    
    return isValid;
}

/**
 * Validate entire form
 */
function validateForm(form) {
    const fields = form.querySelectorAll('input[data-rules], select[data-rules], textarea[data-rules]');
    let isFormValid = true;
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isFormValid = false;
        }
    });
    
    return isFormValid;
}

/**
 * Initialize dropdown menus
 */
function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        
        if (trigger && menu) {
            trigger.addEventListener('click', function(event) {
                event.preventDefault();
                menu.classList.toggle('hidden');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!dropdown.contains(event.target)) {
                    menu.classList.add('hidden');
                }
            });
        }
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        const tooltip = createTooltip(element.dataset.tooltip);
        
        element.addEventListener('mouseenter', function() {
            showTooltip(element, tooltip);
        });
        
        element.addEventListener('mouseleave', function() {
            hideTooltip(tooltip);
        });
    });
}

/**
 * Create tooltip element
 */
function createTooltip(text) {
    const tooltip = document.createElement('div');
    tooltip.className = 'absolute z-50 px-2 py-1 text-sm text-white bg-gray-900 rounded shadow-lg opacity-0 transition-opacity duration-200 pointer-events-none';
    tooltip.textContent = text;
    document.body.appendChild(tooltip);
    return tooltip;
}

/**
 * Show tooltip
 */
function showTooltip(element, tooltip) {
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    tooltip.classList.remove('opacity-0');
    tooltip.classList.add('opacity-100');
}

/**
 * Hide tooltip
 */
function hideTooltip(tooltip) {
    tooltip.classList.remove('opacity-100');
    tooltip.classList.add('opacity-0');
}

/**
 * Initialize form enhancements
 */
function initializeFormEnhancements() {
    // File upload previews
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFilePreview(input);
        });
    });
    
    // Auto-save drafts
    const autosaveForms = document.querySelectorAll('form[data-autosave]');
    autosaveForms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', debounce(() => {
                saveFormDraft(form);
            }, 1000));
        });
    });
}

/**
 * Handle file upload preview
 */
function handleFilePreview(input) {
    const file = input.files[0];
    const previewContainer = document.querySelector(input.dataset.preview);
    
    if (!file || !previewContainer) return;
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContainer.innerHTML = `
                <img src="${e.target.result}" class="max-w-full h-auto rounded-lg shadow-sm" alt="Preview">
            `;
        };
        reader.readAsDataURL(file);
    } else {
        previewContainer.innerHTML = `
            <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm text-gray-600">${file.name}</span>
            </div>
        `;
    }
}

/**
 * Save form draft to localStorage
 */
function saveFormDraft(form) {
    const formData = new FormData(form);
    const data = {};
    
    for (const [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    const draftKey = `draft_${form.id || 'form'}`;
    localStorage.setItem(draftKey, JSON.stringify(data));
}

/**
 * Load form draft from localStorage
 */
function loadFormDraft(form) {
    const draftKey = `draft_${form.id || 'form'}`;
    const savedData = localStorage.getItem(draftKey);
    
    if (savedData) {
        const data = JSON.parse(savedData);
        
        for (const [key, value] of Object.entries(data)) {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = value;
            }
        }
    }
}

/**
 * Clear form draft
 */
function clearFormDraft(form) {
    const draftKey = `draft_${form.id || 'form'}`;
    localStorage.removeItem(draftKey);
}

/**
 * Initialize data tables
 */
function initializeDataTables() {
    const tables = document.querySelectorAll('[data-table]');
    
    tables.forEach(table => {
        setupTableSorting(table);
        setupTableFiltering(table);
        setupTablePagination(table);
    });
}

/**
 * Setup table sorting
 */
function setupTableSorting(table) {
    const headers = table.querySelectorAll('th[data-sort]');
    
    headers.forEach(header => {
        header.classList.add('cursor-pointer', 'hover:bg-gray-100');
        header.addEventListener('click', function() {
            sortTable(table, header.dataset.sort, header.dataset.direction || 'asc');
        });
    });
}

/**
 * Sort table by column
 */
function sortTable(table, column, direction) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';
        const bValue = b.querySelector(`[data-sort-value="${column}"]`)?.textContent || '';
        
        if (direction === 'asc') {
            return aValue.localeCompare(bValue);
        } else {
            return bValue.localeCompare(aValue);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
    
    // Update sort direction
    const header = table.querySelector(`th[data-sort="${column}"]`);
    header.dataset.direction = direction === 'asc' ? 'desc' : 'asc';
}

/**
 * Setup modal handlers
 */
function setupModalHandlers() {
    // Modal triggers
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(event) {
            event.preventDefault();
            const targetId = trigger.dataset.modalTarget;
            const modal = document.getElementById(targetId);
            if (modal) {
                showModal(modal);
            }
        });
    });
    
    // Modal close buttons
    const modalCloseButtons = document.querySelectorAll('[data-modal-close]');
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = button.closest('[data-modal]');
            if (modal) {
                hideModal(modal);
            }
        });
    });
    
    // Close modal on backdrop click
    const modals = document.querySelectorAll('[data-modal]');
    modals.forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                hideModal(modal);
            }
        });
    });
}

/**
 * Show modal
 */
function showModal(modal) {
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

/**
 * Hide modal
 */
function hideModal(modal) {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

/**
 * Utility: Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Utility: Show loading state
 */
function showLoading(element, text = 'Loading...') {
    element.innerHTML = `
        <div class="flex items-center justify-center space-x-2">
            <div class="loading"></div>
            <span>${text}</span>
        </div>
    `;
    element.disabled = true;
}

/**
 * Utility: Hide loading state
 */
function hideLoading(element, originalText) {
    element.innerHTML = originalText;
    element.disabled = false;
}

/**
 * Utility: Show notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} fixed top-20 right-4 z-50 animate-slide-in`;
    notification.innerHTML = `
        <span>${message}</span>
        <button type="button" class="ml-4 inline-flex text-sm" onclick="this.parentElement.remove()">
            <span class="sr-only">Dismiss</span>
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s';
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 500);
    }, 5000);
}

/**
 * Utility: AJAX request with CSRF protection
 */
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
    };
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };
    
    try {
        const response = await fetch(url, mergedOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API request failed:', error);
        throw error;
    }
}

// Export functions for use in other scripts
window.IPTSystem = {
    showModal,
    hideModal,
    showNotification,
    apiRequest,
    showLoading,
    hideLoading,
    validateForm,
    saveFormDraft,
    loadFormDraft,
    clearFormDraft
};
