<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Desa Padangloang' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/guest/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/guest-navbar.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/guest/components.css') ?>">
    <?= csrf_meta() ?>
    <style>
        /* Mobile-style Slide Notification - Dark Glassmorphism */
        .slide-notification {
            position: fixed;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 99999;
            width: auto;
            min-width: 280px;
            max-width: min(600px, 90vw);
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: top 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            pointer-events: none;
        }
        
        .slide-notification.show {
            top: 20px;
        }
        
        .slide-notification-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 14px 18px;
            white-space: nowrap;
        }
        
        .slide-notification-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .slide-notification-message {
            flex: 1;
            font-size: 14px;
            font-weight: 500;
            color: #f1f5f9;
            line-height: 1.4;
            white-space: normal;
            word-wrap: break-word;
        }
        
        /* Success - Green */
        .slide-notification-success {
            border-left: 3px solid #10b981;
        }
        
        .slide-notification-success .slide-notification-icon {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }
        
        /* Error - Red */
        .slide-notification-error {
            border-left: 3px solid #ef4444;
        }
        
        .slide-notification-error .slide-notification-icon {
            background: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }
        
        /* Info - Blue */
        .slide-notification-info {
            border-left: 3px solid #3b82f6;
        }
        
        .slide-notification-info .slide-notification-icon {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }
        
        /* Warning - Orange */
        .slide-notification-warning {
            border-left: 3px solid #f59e0b;
        }
        
        .slide-notification-warning .slide-notification-icon {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .slide-notification {
                min-width: 260px;
                max-width: 95vw;
            }
            
            .slide-notification-content {
                padding: 12px 16px;
            }
            
            .slide-notification-message {
                font-size: 13px;
            }
            
            .slide-notification-icon {
                width: 26px;
                height: 26px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<?php if (!isset($hideNavbar) || !$hideNavbar): ?>
<?= $this->include('Components/GuestNavbar') ?>
<?php endif; ?>

<main class="page-wrapper <?= $this->renderSection('pageClass') ?>">
    <?= $this->renderSection('content') ?>

</main>


<?php if (!isset($hideFooter) || !$hideFooter): ?>
<?= $this->include('Components/Footer') ?>
<?php endif; ?>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/components/guest-navbar.js') ?>"></script>
<script src="<?= base_url('assets/js/guest/main.js') ?>"></script>
<script>
// Mobile-style slide notification system
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `slide-notification slide-notification-${type}`;
    notification.innerHTML = `
        <div class="slide-notification-content">
            <span class="slide-notification-icon">${getIcon(type)}</span>
            <span class="slide-notification-message">${message}</span>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Trigger slide down animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        // Remove from DOM after animation
        setTimeout(() => {
            notification.remove();
        }, 400);
    }, 3000);
}

function getIcon(type) {
    const icons = {
        'success': '✓',
        'error': '✕',
        'info': 'ℹ',
        'warning': '⚠'
    };
    return icons[type] || icons['info'];
}

// Show flash messages
<?php if (session()->getFlashdata('error')): ?>
    showNotification('<?= addslashes(session()->getFlashdata('error')) ?>', 'error');
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    showNotification('<?= addslashes(session()->getFlashdata('success')) ?>', 'success');
<?php endif; ?>
<?php if (session()->getFlashdata('info')): ?>
    showNotification('<?= addslashes(session()->getFlashdata('info')) ?>', 'info');
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    showNotification('<?= addslashes(session()->getFlashdata('warning')) ?>', 'warning');
<?php endif; ?>
</script>
<script>
// Process email queue in background (non-blocking)
(function() {
    // Wait for page to fully load
    if (document.readyState === 'complete') {
        processEmailQueue();
    } else {
        window.addEventListener('load', processEmailQueue);
    }
    
    function processEmailQueue() {
        // Use fetch with keepalive to ensure request completes even if page unloads
        fetch('<?= base_url('/api/email-queue/process') ?>', {
            method: 'GET',
            keepalive: true,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).catch(function(err) {
            // Silently fail - email will be processed on next request
            console.debug('Email queue processing failed (non-critical):', err);
        });
    }
})();
</script>
</body>
</html>



