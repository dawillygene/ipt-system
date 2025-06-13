<?php
/**
 * Tailwind CSS Demo Page
 * Demonstrates the implemented design system components
 */

require_once 'includes/layout.php';

// Start the page
renderHeader('Design System Demo', 'demo');
renderPageHeader(
    'Design System Components', 
    'Preview of all available UI components with Tailwind CSS',
    [
        ['url' => 'index.php', 'label' => 'Back to Home', 'class' => 'btn-outline']
    ]
);
?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    
    <!-- Buttons Section -->
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="text-xl font-semibold">Buttons</h2>
        </div>
        <div class="space-y-4">
            <div class="flex flex-wrap gap-4">
                <button class="btn-primary">Primary Button</button>
                <button class="btn-secondary">Secondary Button</button>
                <button class="btn-success">Success Button</button>
                <button class="btn-warning">Warning Button</button>
                <button class="btn-error">Error Button</button>
                <button class="btn-outline">Outline Button</button>
            </div>
            <div class="flex flex-wrap gap-4">
                <button class="btn-primary" disabled>Disabled Primary</button>
                <button class="btn-outline" disabled>Disabled Outline</button>
            </div>
        </div>
    </div>

    <!-- Forms Section -->
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="text-xl font-semibold">Form Components</h2>
        </div>
        <form class="space-y-6" data-validate>
            <div>
                <label class="form-label" for="demo-name">Full Name</label>
                <input type="text" id="demo-name" name="name" class="form-input" data-rules="required|min:2" placeholder="Enter your full name">
                <div class="form-error hidden"></div>
            </div>
            
            <div>
                <label class="form-label" for="demo-email">Email Address</label>
                <input type="email" id="demo-email" name="email" class="form-input" data-rules="required|email" placeholder="your@email.com">
                <div class="form-error hidden"></div>
                <div class="form-help">We'll never share your email with anyone.</div>
            </div>
            
            <div>
                <label class="form-label" for="demo-role">Role</label>
                <select id="demo-role" name="role" class="form-input" data-rules="required">
                    <option value="">Select your role</option>
                    <option value="student">Student</option>
                    <option value="supervisor">Supervisor</option>
                    <option value="admin">Administrator</option>
                </select>
                <div class="form-error hidden"></div>
            </div>
            
            <div>
                <label class="form-label" for="demo-message">Message</label>
                <textarea id="demo-message" name="message" rows="4" class="form-input" data-rules="required|min:10" placeholder="Your message here..."></textarea>
                <div class="form-error hidden"></div>
            </div>
            
            <div class="flex items-center">
                <input type="checkbox" id="demo-terms" name="terms" class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                <label for="demo-terms" class="ml-2 text-sm text-gray-700">I agree to the terms and conditions</label>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">Submit Form</button>
            </div>
        </form>
    </div>

    <!-- Alerts Section -->
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="text-xl font-semibold">Alerts & Messages</h2>
        </div>
        <div class="space-y-4">
            <div class="alert-success">
                <strong>Success!</strong> Your application has been submitted successfully.
            </div>
            <div class="alert-warning">
                <strong>Warning!</strong> Please complete all required fields before proceeding.
            </div>
            <div class="alert-error">
                <strong>Error!</strong> Unable to save your changes. Please try again.
            </div>
            <div class="alert-info">
                <strong>Info:</strong> Your session will expire in 5 minutes.
            </div>
        </div>
    </div>

    <!-- Badges Section -->
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="text-xl font-semibold">Badges & Status</h2>
        </div>
        <div class="flex flex-wrap gap-4">
            <span class="badge-primary">Primary</span>
            <span class="badge-success">Approved</span>
            <span class="badge-warning">Pending</span>
            <span class="badge-error">Rejected</span>
            <span class="badge-secondary">Draft</span>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="text-xl font-semibold">Data Table</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto" data-table>
                <thead class="table-header">
                    <tr>
                        <th class="table-header-cell" data-sort="name">Name</th>
                        <th class="table-header-cell" data-sort="email">Email</th>
                        <th class="table-header-cell" data-sort="role">Role</th>
                        <th class="table-header-cell" data-sort="status">Status</th>
                        <th class="table-header-cell">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-gray-50">
                        <td class="table-cell" data-sort-value="name">John Doe</td>
                        <td class="table-cell" data-sort-value="email">john@example.com</td>
                        <td class="table-cell" data-sort-value="role">Student</td>
                        <td class="table-cell" data-sort-value="status">
                            <span class="badge-success">Active</span>
                        </td>
                        <td class="table-cell">
                            <div class="flex space-x-2">
                                <button class="text-primary-600 hover:text-primary-900 text-sm">View</button>
                                <button class="text-gray-600 hover:text-gray-900 text-sm">Edit</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                        <td class="table-cell" data-sort-value="name">Jane Smith</td>
                        <td class="table-cell" data-sort-value="email">jane@example.com</td>
                        <td class="table-cell" data-sort-value="role">Supervisor</td>
                        <td class="table-cell" data-sort-value="status">
                            <span class="badge-warning">Pending</span>
                        </td>
                        <td class="table-cell">
                            <div class="flex space-x-2">
                                <button class="text-primary-600 hover:text-primary-900 text-sm">View</button>
                                <button class="text-gray-600 hover:text-gray-900 text-sm">Edit</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-primary-100 rounded-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Total Students</h3>
                    <p class="text-2xl font-bold text-primary-600">1,247</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-success-100 rounded-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Applications</h3>
                    <p class="text-2xl font-bold text-success-600">89</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-warning-100 rounded-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Pending Reviews</h3>
                    <p class="text-2xl font-bold text-warning-600">23</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive Elements -->
    <div class="card">
        <div class="card-header">
            <h2 class="text-xl font-semibold">Interactive Elements</h2>
        </div>
        <div class="space-y-4">
            <div>
                <button class="btn-primary" onclick="IPTSystem.showNotification('This is a success notification!', 'success')">
                    Show Success Notification
                </button>
                <button class="btn-warning ml-3" onclick="IPTSystem.showNotification('This is a warning notification!', 'warning')">
                    Show Warning Notification
                </button>
                <button class="btn-error ml-3" onclick="IPTSystem.showNotification('This is an error notification!', 'error')">
                    Show Error Notification
                </button>
            </div>
            
            <div>
                <button class="btn-primary" data-modal-target="demo-modal">
                    Open Modal Dialog
                </button>
            </div>
            
            <div>
                <button class="btn-outline" data-tooltip="This is a helpful tooltip">
                    Hover for Tooltip
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Demo Modal -->
<div id="demo-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50" data-modal>
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Demo Modal</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-gray-600">This is a demonstration of the modal component. It includes proper backdrop handling and keyboard navigation.</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <button class="btn-outline" data-modal-close>Cancel</button>
            <button class="btn-primary" data-modal-close>Confirm</button>
        </div>
    </div>
</div>

<?php renderFooter(); ?>
