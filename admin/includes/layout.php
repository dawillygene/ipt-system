<?php
require_once __DIR__ . '/../constants.php';

function renderAdminLayout($pageTitle = '', $breadcrumbs = [], $additionalHead = '') {
    $currentPage = basename($_SERVER['PHP_SELF']);
    
    if (empty($pageTitle)) {
        foreach (AdminUIConstants::NAV_ITEMS as $item) {
            if (AdminUIConstants::isActiveRoute($item)) {
                $pageTitle = $item['name'];
                break;
            }
        }
    }
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($pageTitle); ?> - IPT Admin</title>
        
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'admin-primary': '<?php echo AdminUIConstants::COLORS['primary']; ?>',
                            'admin-secondary': '<?php echo AdminUIConstants::COLORS['secondary']; ?>',
                            'admin-accent': '<?php echo AdminUIConstants::COLORS['accent']; ?>',
                        }
                    }
                }
            }
        </script>
        
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        
        <?php echo $additionalHead; ?>
        
        <style>
            ::-webkit-scrollbar {
                width: 6px;
            }
            
            ::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            
            ::-webkit-scrollbar-thumb {
                background: #07442d;
                border-radius: 3px;
            }
            
            ::-webkit-scrollbar-thumb:hover {
                background: #206f56;
            }
        </style>
    </head>
    <body class="bg-gray-50">
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Content starts immediately after header without extra spacing -->
        <div class="lg:ml-64 mt-16">
            <?php if (!empty($breadcrumbs)): ?>
            <div class="bg-white shadow-sm border-b border-gray-200 px-4 lg:px-6 py-3">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <li class="flex items-center">
                                <?php if ($index > 0): ?>
                                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                <?php endif; ?>
                                
                                <?php if (isset($crumb['url'])): ?>
                                    <a href="<?php echo htmlspecialchars($crumb['url']); ?>" 
                                       class="text-admin-primary hover:text-admin-accent transition-colors duration-200">
                                        <?php echo htmlspecialchars($crumb['name']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500">
                                        <?php echo htmlspecialchars($crumb['name']); ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            </div>
            <?php endif; ?>
            
            <div class="p-4 lg:p-6">
    <?php
    return ob_get_clean();
}

function renderAdminLayoutEnd() {
    ob_start();
    ?>
            </div>
        </div>
        
        <div id="toast-container" class="fixed top-20 right-6 z-50 space-y-2"></div>
        
        <script>
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                const bgColor = type === 'success' ? 'bg-green-500' : 
                               type === 'error' ? 'bg-red-500' : 
                               type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500';
                               
                toast.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
                toast.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : type === 'warning' ? 'exclamation' : 'info'} mr-2"></i>
                        <span>${message}</span>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                document.getElementById('toast-container').appendChild(toast);
                
                setTimeout(() => {
                    toast.classList.remove('translate-x-full', 'opacity-0');
                }, 100);
                
                setTimeout(() => {
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            }
        </script>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

function renderAdminCard($title, $content, $icon = '', $actions = []) {
    ob_start();
    ?>
    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 overflow-hidden">
        <div class="bg-gradient-to-r from-admin-primary to-admin-secondary px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <?php if ($icon): ?>
                        <i class="<?php echo htmlspecialchars($icon); ?> text-white text-lg"></i>
                    <?php endif; ?>
                    <h3 class="text-lg font-semibold text-white"><?php echo htmlspecialchars($title); ?></h3>
                </div>
                
                <?php if (!empty($actions)): ?>
                    <div class="flex items-center space-x-2">
                        <?php foreach ($actions as $action): ?>
                            <a href="<?php echo htmlspecialchars($action['url']); ?>" 
                               class="px-3 py-1 bg-white/20 text-white text-sm rounded-md hover:bg-white/30 transition-colors duration-200">
                                <?php if (isset($action['icon'])): ?>
                                    <i class="<?php echo htmlspecialchars($action['icon']); ?> mr-1"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($action['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="p-6">
            <?php echo $content; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>