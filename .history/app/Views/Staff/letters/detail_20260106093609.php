<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4><?= esc($letter['judul_perihal']) ?></h4>
        <div class="text-muted small">Status: <span class="badge bg-primary"><?= esc($letter['status']) ?></span></div>
    </div>
    <div class="page-header-actions">
        <?php if ($letter['tipe_surat'] === 'Keterangan Usaha' || $letter['tipe_surat'] === 'Keterangan Tidak Mampu'): ?>
            <a href="<?= base_url('/staff/surat/' . $letter['id'] . '/word') ?>" class="btn btn-sm btn-outline-primary" title="Export Word">
                <i class="bi bi-file-earmark-word"></i> Export Word
            </a>
        <?php endif; ?>
        <a href="<?= base_url('/staff/surat') ?>" class="page-header-icon">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="small text-muted mb-2">Tipe Surat</div>
        <div class="fw-semibold mb-3"><?= esc($letter['tipe_surat']) ?></div>
        <div class="small text-muted mb-2">Isi Surat</div>
        <div class="text-muted"><?= nl2br($letter['isi_surat']) ?></div>
        <?php if (!empty($attachments)): ?>
            <div class="mt-3">
                <div class="small text-muted mb-2">Lampiran</div>
                <?php foreach ($attachments as $att): ?>
                    <div class="mb-1"><a href="<?= base_url($att['file_path']) ?>" target="_blank"><?= esc($att['original_name'] ?: basename($att['file_path'])) ?></a></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="fw-semibold mb-3">Kirim Balasan</h5>
        <form method="post" enctype="multipart/form-data" action="<?= base_url('/staff/surat/' . $letter['id'] . '/balas') ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Isi Balasan</label>
                <textarea class="form-control" name="reply_text" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Lampiran (opsional)</label>
                <input type="file" class="form-control" name="reply_attachments[]" multiple>
            </div>
            <button class="btn btn-primary w-100" type="submit">Kirim Balasan</button>
        </form>
    </div>
</div>

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
                                <div class="small text-muted"><?= date('d M Y H:i', strtotime($reply['created_at'])) ?></div>
                            </div>
                            <?php if ($reply['staff_id'] == $currentStaffId): ?>
                            <div>
                                <a href="<?= base_url('/staff/surat/' . $letter['id'] . '/balasan/' . $reply['id'] . '/hapus') ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Yakin ingin menghapus balasan ini?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted mb-2"><?= nl2br($reply['reply_text']) ?></div>
                        <?php if (!empty($replyAttachments[$reply['id']])): ?>
                            <div class="small mt-2">Lampiran:
                                <?php foreach ($replyAttachments[$reply['id']] as $att): ?>
                                    <a href="<?= base_url($att['file_path']) ?>" target="_blank"><?= esc($att['original_name'] ?: 'lampiran') ?></a>
                                <?php endforeach; ?>
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


