<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-bell"></i>
        </div>
        <div>
            <h4 class="mb-0">Notifikasi</h4>
            <div class="text-muted small mt-1">Status terbaru surat dan balasan staf.</div>
        </div>
    </div>
    <div>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="markAllAsRead()">
            <i class="bi bi-check2-all me-1"></i> Tandai semua telah dibaca
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="notif-container">
            <div class="list-group-item text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2">Memuat notifikasi...</span>
            </div>
        </div>
    </div>
</div>

<script>
let csrfToken = '<?= csrf_token() ?>';
let csrfHash  = '<?= csrf_hash() ?>';

document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
});

function loadNotifications() {
    fetch('<?= base_url('/user/notifikasi/api') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            [csrfToken]: csrfHash
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data[csrfToken]) {
            csrfHash = data[csrfToken];
        }

        const container = document.getElementById('notif-container');
        container.innerHTML = '';

        if (data.success && data.notifications.length > 0) {
            data.notifications.forEach(notif => {
                const notifUrl = `<?= base_url('/user/notifikasi') ?>/${notif.id}/read`;
                const isRead = parseInt(notif.is_read) === 1;
                
                const dateObj = new Date(notif.created_at);
                const day = String(dateObj.getDate()).padStart(2, '0');
                const month = dateObj.toLocaleString('id-ID', { month: 'short' });
                const year = dateObj.getFullYear();
                const hours = String(dateObj.getHours()).padStart(2, '0');
                const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                const dateStr = `${day} ${month} ${year} ${hours}:${minutes} WITA`;
                
                const html = `
                    <a href="${notifUrl}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start ${!isRead ? 'bg-light' : ''}">
                        <div>
                            <div class="fw-semibold ${!isRead ? 'text-primary' : 'text-dark'}">
                                ${escapeHtml(notif.title)}
                            </div>
                            <div class="small text-muted mt-1">${escapeHtml(notif.message)}</div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">${dateStr}</div>
                            ${!isRead ? '<span class="badge bg-primary rounded-pill mt-2">Baru</span>' : ''}
                        </div>
                    </a>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });
        } else {
            container.innerHTML = '<div class="list-group-item text-muted small text-center py-4">Belum ada notifikasi.</div>';
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        document.getElementById('notif-container').innerHTML = '<div class="list-group-item text-danger small text-center py-4">Gagal memuat notifikasi.</div>';
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function markAllAsRead() {
    const btn = document.querySelector('button[onclick="markAllAsRead()"]');
    if (btn) btn.disabled = true;

    fetch('<?= base_url('/user/notifikasi/mark-all-read') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            [csrfToken]: csrfHash
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data[csrfToken]) {
            csrfHash = data[csrfToken];
        }
        if (data.success) {
            loadNotifications();
        }
        if (btn) btn.disabled = false;
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
        if (btn) btn.disabled = false;
    });
}
</script>
<?= $this->endSection() ?>
