<?php
/**
 * Simple Demo Page without Session Dependencies
 * Tests basic asset loading and Tailwind CSS functionality
 */

// Simple asset path function
function getAssetPath($path) {
    return ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPT System - Demo (Simple)</title>
    
    <!-- Tailwind CSS -->
    <link href="<?php echo getAssetPath('css/tailwind.css'); ?>" rel="stylesheet">
    
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo getAssetPath('images/favicon.svg'); ?>">
</head>
<body class="h-full">
    <div class="min-h-full">
        <!-- Simple header -->
        <header class="bg-white shadow border-b border-gray-200">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">IPT</span>
                        </div>
                        <h1 class="text-xl font-semibold text-gray-900">Industrial Training System</h1>
                    </div>
                    <div class="text-sm text-gray-500">Demo Mode</div>
                </div>
            </div>
        </header>

        <!-- Main content -->
        <main class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Page header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Design System Demo</h1>
                    <p class="mt-2 text-gray-600">Preview of all available UI components with Tailwind CSS</p>
                </div>

                <!-- Components showcase -->
                <div class="space-y-8">
                    <!-- Buttons -->
                    <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Buttons</h2>
                        <div class="flex flex-wrap gap-4">
                            <button class="btn btn-primary">Primary Button</button>
                            <button class="btn btn-secondary">Secondary Button</button>
                            <button class="btn btn-success">Success Button</button>
                            <button class="btn btn-warning">Warning Button</button>
                            <button class="btn btn-error">Error Button</button>
                            <button class="btn btn-outline">Outline Button</button>
                        </div>
                    </section>

                    <!-- Cards -->
                    <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Cards</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="card">
                                <h3 class="text-lg font-medium">Student Dashboard</h3>
                                <p class="text-gray-600 mt-2">Manage your application, view training progress, and submit reports.</p>
                            </div>
                            <div class="card">
                                <h3 class="text-lg font-medium">Supervisor Portal</h3>
                                <p class="text-gray-600 mt-2">Monitor students, provide feedback, and track evaluations.</p>
                            </div>
                            <div class="card">
                                <h3 class="text-lg font-medium">Admin Panel</h3>
                                <p class="text-gray-600 mt-2">Comprehensive system administration and user management.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Forms -->
                    <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Form Elements</h2>
                        <div class="max-w-md space-y-4">
                            <div>
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-input" placeholder="Enter your full name">
                            </div>
                            <div>
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-input" placeholder="Enter your email">
                            </div>
                            <div>
                                <label class="form-label">Training Program</label>
                                <select class="form-input">
                                    <option>Select a program</option>
                                    <option>Computer Science</option>
                                    <option>Engineering</option>
                                    <option>Business Administration</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <!-- Alerts -->
                    <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Alerts</h2>
                        <div class="space-y-4">
                            <div class="alert alert-success">
                                <strong>Success!</strong> Your application has been submitted successfully.
                            </div>
                            <div class="alert alert-warning">
                                <strong>Warning!</strong> Please complete your profile information.
                            </div>
                            <div class="alert alert-error">
                                <strong>Error!</strong> There was a problem processing your request.
                            </div>
                            <div class="alert alert-info">
                                <strong>Info!</strong> New training opportunities are available.
                            </div>
                        </div>
                    </section>

                    <!-- Stats -->
                    <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-semibold mb-4">Statistics</h2>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="bg-blue-50 p-6 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">125</div>
                                <div class="text-sm text-blue-800">Active Students</div>
                            </div>
                            <div class="bg-green-50 p-6 rounded-lg">
                                <div class="text-2xl font-bold text-green-600">89</div>
                                <div class="text-sm text-green-800">Completed Programs</div>
                            </div>
                            <div class="bg-yellow-50 p-6 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">34</div>
                                <div class="text-sm text-yellow-800">Active Supervisors</div>
                            </div>
                            <div class="bg-purple-50 p-6 rounded-lg">
                                <div class="text-2xl font-bold text-purple-600">12</div>
                                <div class="text-sm text-purple-800">Partner Organizations</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8 mt-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h3 class="text-lg font-semibold">Industrial Training Practical System</h3>
                    <p class="mt-2 text-gray-400">Streamlining training management for educational institutions</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script src="<?php echo getAssetPath('js/app.js'); ?>"></script>
    <script>
        // Test JavaScript functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Demo page loaded successfully');
            
            // Add click handlers to buttons
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function() {
                    console.log('Button clicked:', this.textContent);
                });
            });
        });
    </script>
</body>
</html>
