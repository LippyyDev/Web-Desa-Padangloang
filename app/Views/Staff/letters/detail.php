<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/staff/surat') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0">Detail Surat</h4>
            <div class="text-muted small mt-1">Status: <span class="fw-semibold text-<?php 
            if ($letter['status'] === 'Diterima') echo 'success';
            elseif ($letter['status'] === 'Ditolak') echo 'danger';
            elseif ($letter['status'] === 'Dibaca') echo 'primary';
            else echo 'warning';
        ?>"><?= esc($letter['status']) ?></span></div>
        </div>
    </div>
    <div class="page-header-actions">
        
    </div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="small text-muted mb-2">Pengirim</div>
                <div class="fw-semibold"><?= esc($user['nama_lengkap'] ?? 'User tidak diketahui') ?></div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted mb-2">Waktu Pengiriman</div>
                <div class="fw-semibold"><?= date('d M Y H:i', strtotime($letter['created_at'])) ?></div>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="small text-muted mb-2">Kode Unik</div>
                <div class="fw-semibold"><?= esc($letter['kode_unik']) ?></div>
            </div>
            <div class="col-md-6">
                <div class="small text-muted mb-2">Tipe Surat</div>
                <div class="fw-semibold"><?= esc($letter['tipe_surat']) ?></div>
            </div>
        </div>
        
        <div class="small text-muted mb-2">Perihal</div>
        <div class="text-muted mb-3"><?= esc($letter['judul_perihal']) ?></div>

        <div class="small text-muted mb-2">Isi Surat</div>
        <div class="text-muted"><?= nl2br($letter['isi_surat']) ?></div>

        <div class="small text-muted mt-3 mb-2">Status Surat</div>
        <div>
            <span class="badge bg-<?php 
                if ($letter['status'] === 'Diterima') echo 'success';
                elseif ($letter['status'] === 'Ditolak') echo 'danger';
                elseif ($letter['status'] === 'Dibaca') echo 'primary';
                else echo 'warning';
            ?>"><?= esc($letter['status']) ?></span>
        </div>
        <?php if (!empty($attachments)): ?>
            <div class="mt-3">
                <div class="small text-muted mb-2">Lampiran</div>
                <ol class="ps-3 mb-0">
                    <?php foreach ($attachments as $att): ?>
                        <?php
                        $ext = strtolower(pathinfo($att['file_path'], PATHINFO_EXTENSION));
                        $icon = 'bi-file-earmark';
                        if (in_array($ext, ['pdf'])) $icon = 'bi-file-earmark-pdf-fill text-danger';
                        elseif (in_array($ext, ['doc', 'docx'])) $icon = 'bi-file-earmark-word-fill text-primary';
                        elseif (in_array($ext, ['xls', 'xlsx'])) $icon = 'bi-file-earmark-excel-fill text-success';
                        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $icon = 'bi-file-earmark-image-fill text-info';
                        ?>
                        <li class="mb-1">
                            <a href="<?= base_url($att['file_path']) ?>" target="_blank" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                <i class="bi <?= $icon ?> flex-shrink-0"></i>
                                <span class="attachment-filename"><?= esc($att['original_name'] ?: basename($att['file_path'])) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Unified Reply/Decision Card -->
<?php if (in_array($letter['status'], ['Menunggu', 'Dibaca', 'Diterima'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <h5 class="fw-semibold mb-3">
            <?= $letter['status'] === 'Diterima' ? 'Kirim Balasan' : 'Tindakan & Balasan' ?>
        </h5>
        
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/surat/' . $letter['id'] . '/balas') ?>">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label class="form-label">Isi Balasan</label>
                <textarea class="form-control" name="reply_text" rows="5" required placeholder="Tulis balasan surat di sini..."></textarea>
            </div>
            
            <?php if (in_array($letter['tipe_surat'], ['Keterangan Usaha', 'Keterangan Tidak Mampu', 'Keterangan Belum Menikah', 'Keterangan Domisili', 'Undangan'])): ?>
                <div class="mb-3">
                    <a href="<?= base_url('/staff/surat/' . $letter['id'] . '/word') ?>" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-download"></i> Download Template Balasan
                    </a>
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <label class="form-label">Lampiran (opsional)</label>
                <input type="file" class="form-control" name="reply_attachments[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp">
            </div>
            
            <div class="d-grid gap-2">
                <?php if ($letter['status'] === 'Diterima'): ?>
                    <button class="btn btn-primary" type="submit" name="action" value="reply">
                        <i class="bi bi-send"></i> Kirim Balasan
                    </button>
                <?php else: ?>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <button class="btn btn-success w-100 btn-action-accept" type="button">
                                <i class="bi bi-check-circle"></i> Terima & Kirim Balasan
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button class="btn btn-danger w-100 btn-action-reject" type="button">
                                <i class="bi bi-x-circle"></i> Tolak & Kirim Balasan
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($letter['status'] === 'Ditolak'): ?>
<!-- Rejected Message Card -->
<div class="card mb-3">
    <div class="card-body">
        <div class="alert alert-danger mb-0">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Surat Ditolak</strong><br>
            Surat ini telah ditolak dan tidak dapat dibalas lagi.
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Balasan Sebelumnya
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($replies as $reply): ?>
            <?php 
            $replyProfile = $replyProfiles[$reply['id']] ?? null;
            $fotoProfil = !empty($replyProfile['foto_profil']) 
                ? base_url($replyProfile['foto_profil']) 
                : base_url('assets/img/guest.webp');
            $namaPengirim = $replyProfile['nama_lengkap'] ?? $replyProfile['username'] ?? 'Staff';
            ?>
            <div class="list-group-item">
                <div class="d-flex align-items-start gap-3">
                    <img src="<?= $fotoProfil ?>" alt="<?= esc($namaPengirim) ?>" 
                         class="rounded-circle" 
                         style="width: 48px; height: 48px; object-fit: cover; flex-shrink: 0;">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold"><?= esc($namaPengirim) ?></div>
                                <div class="small text-muted"><?= date('d M Y H:i', strtotime($reply['created_at'])) ?> WITA</div>
                            </div>
                            <?php if ($reply['staff_id'] == $currentStaffId): ?>
                            <div>
                                <a href="<?= base_url('/staff/surat/' . $letter['id'] . '/balasan/' . $reply['id'] . '/hapus') ?>" 
                                   class="btn btn-sm btn-outline-danger delete-reply-btn" 
                                   data-message="Yakin ingin menghapus balasan ini?">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted mb-2"><?= nl2br($reply['reply_text']) ?></div>
                        <?php if (!empty($replyAttachments[$reply['id']])): ?>
                            <div class="small mt-2">Lampiran:
                                <ol class="ps-3 mb-0 mt-1">
                                    <?php foreach ($replyAttachments[$reply['id']] as $att): ?>
                                        <?php
                                        $ext = strtolower(pathinfo($att['file_path'], PATHINFO_EXTENSION));
                                        $icon = 'bi-file-earmark';
                                        if (in_array($ext, ['pdf'])) $icon = 'bi-file-earmark-pdf-fill text-danger';
                                        elseif (in_array($ext, ['doc', 'docx'])) $icon = 'bi-file-earmark-word-fill text-primary';
                                        elseif (in_array($ext, ['xls', 'xlsx'])) $icon = 'bi-file-earmark-excel-fill text-success';
                                        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) $icon = 'bi-file-earmark-image-fill text-info';
                                        ?>
                                        <li class="mb-1">
                                            <a href="<?= base_url($att['file_path']) ?>" target="_blank" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                                <i class="bi <?= $icon ?> flex-shrink-0"></i>
                                                <span class="attachment-filename"><?= esc($att['original_name'] ?: 'lampiran') ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($replies)): ?>
            <div class="list-group-item text-muted small">Belum ada balasan.</div>
        <?php endif; ?>
    </div>
</div>

<?= $this->section('scripts') ?>

// Handle delete reply confirmation
document.querySelectorAll('.delete-reply-btn').forEach(btn => {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        const url = this.href;
        const message = this.dataset.message || 'Apakah Anda yakin ingin menghapus data ini?';
        
        const result = await Swal.fire({
            title: 'Konfirmasi Hapus',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });
        
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
});

// Handle accept confirmation
const acceptBtn = document.querySelector('.btn-action-accept');
if (acceptBtn) {
    acceptBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const result = await Swal.fire({
            title: 'Konfirmasi Penerimaan',
            text: 'Yakin ingin menerima surat ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754', // success color
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Terima!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            submitFormWithAction('accept');
        }
    });
}

// Handle reject confirmation
const rejectBtn = document.querySelector('.btn-action-reject');
if (rejectBtn) {
    rejectBtn.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const result = await Swal.fire({
            title: 'Konfirmasi Penolakan',
            text: 'Yakin ingin menolak surat ini? Surat yang ditolak tidak dapat dibalas lagi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // danger color
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tolak!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            submitFormWithAction('reject');
        }
    });
}

// Helper to submit form with specific action value
function submitFormWithAction(actionValue) {
    const form = document.querySelector('form[action*="/balas"]');
    if (form) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = actionValue;
        form.appendChild(input);
        form.submit();
    }
}
<?= $this->endSection() ?>

<?= $this->endSection() ?>


