<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= base_url('/user/surat') ?>" class="page-header-icon">
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
        <a href="<?= base_url('/user/surat/' . $letter['id'] . '/edit') ?>" class="page-header-icon" title="Edit Surat">
            <i class="bi bi-pencil"></i>
        </a>
        <a href="<?= base_url('/user/surat/' . $letter['id'] . '/hapus') ?>" class="page-header-icon page-header-icon-delete" title="Hapus Surat">
            <i class="bi bi-trash"></i>
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="small text-muted mb-2">Waktu Pengiriman</div>
        <div class="fw-semibold mb-3"><?= date('d M Y H:i', strtotime($letter['created_at'])) ?></div>
        
        <div class="small text-muted mb-2">Kode Unik</div>
        <div class="fw-semibold mb-3"><?= esc($letter['kode_unik']) ?></div>

        <div class="small text-muted mb-2">Tipe Surat</div>
        <div class="fw-semibold mb-3"><?= esc($letter['tipe_surat']) ?></div>

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

<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-reply-fill"></i>
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
<?= $this->endSection() ?>



