<?= $this->extend('Guest/layout') ?>

<?= $this->section('pageClass') ?>profil-page hero-page<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Header Section -->
<section class="about-section position-relative min-vh-100 d-flex flex-column justify-content-center overflow-hidden">
    <div class="about-blob-1"></div>
    <div class="about-blob-2"></div>
    <div class="container position-relative z-1 text-center pt-5 mt-4">
        <div class="hero-badge-label animate-fade-in mx-auto mb-3">
            <span class="badge-icon">🏛️</span>
            <span>Profil Pemerintahan</span>
        </div>
        <h1 class="hero-title animate-slide-up mb-3">
             Tentang <span class="text-gradient">Desa Padangloang</span>
        </h1>
        <p class="hero-subtitle animate-slide-up-delay mx-auto text-muted" style="max-width: 900px;">
            Desa Padangloang adalah desa yang berada di Kecamatan Ujung Loe, Kabupaten Bulukumba, Provinsi Sulawesi Selatan. Desa ini merupakan wilayah pedesaan dengan mayoritas masyarakat bermata pencaharian di sektor pertanian dan usaha lokal serta memiliki peran penting dalam pelayanan administrasi dan pembangunan desa di tingkat kecamatan.
        </p>
    </div>


<!-- Aesthetic Divider -->
<div class="position-relative py-4">
    <div class="container text-center">
        <div class="d-flex align-items-center justify-content-center gap-3">
             <div class="flex-grow-1" style="height: 1px; background: linear-gradient(to right, transparent, rgba(16, 185, 129, 0.3));"></div>
             <span class="text-gradient fw-bold small text-uppercase tracking-widest px-3 border rounded-pill bg-white bg-opacity-5">Menuju Desa Mandiri</span>
             <div class="flex-grow-1" style="height: 1px; background: linear-gradient(to left, transparent, rgba(16, 185, 129, 0.3));"></div>
        </div>
    </div>
</div>

<!-- Visi & Misi Section -->
<div class="position-relative py-5">
    <div class="container position-relative z-1">
        <div class="row g-4">
            <!-- Visi Card -->
            <div class="col-lg-6">
                <div class="glass-card h-100 p-3 p-md-5 position-relative overflow-hidden group-hover">
                    <div class="position-absolute top-0 end-0 p-3 p-md-4 opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="d-none d-md-block"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    </div>
                    <div class="d-flex align-items-center mb-3 mb-md-4">
                        <div class="p-2 p-md-3 rounded-circle bg-primary bg-opacity-10 text-primary me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                        <h3 class="fw-bold mb-0 fs-5 fs-md-3">Visi Desa</h3>
                    </div>
                    <p class="text-muted mb-0 small" style="line-height: 1.6;">
                        <?= $desaProfile['visi'] ?: 'Menjadi desa mandiri, inovatif, dan sejahtera dengan berlandaskan nilai-nilai gotong royong.' ?>
                    </p>
                </div>
            </div>
            
            <!-- Misi Card -->
            <div class="col-lg-6">
                <div class="glass-card h-100 p-3 p-md-5 position-relative overflow-hidden">
                     <div class="position-absolute top-0 end-0 p-3 p-md-4 opacity-10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="d-none d-md-block"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="d-flex align-items-center mb-3 mb-md-4">
                        <div class="p-2 p-md-3 rounded-circle bg-success bg-opacity-10 text-success me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3 class="fw-bold mb-0 fs-5 fs-md-3">Misi Desa</h3>
                    </div>
                    <div class="text-muted small" style="line-height: 1.6;">
                        <?= nl2br($desaProfile['misi'] ?: "Meningkatkan pelayanan publik\nMendorong ekonomi kreatif\nMenguatkan kolaborasi warga") ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>

<!-- Administrasi Penduduk Section -->
<section class="gallery-section position-relative">
     <div class="gallery-bg-glow"></div>
     <div class="container position-relative z-1">
        <div class="text-start text-md-center mb-5">
            <div class="section-tag mb-2 justify-content-start justify-content-md-center">
                <span class="line"></span>
                <span class="text">Statistik</span>
            </div>
            <h2 class="section-heading">Data Administrasi <span class="text-gradient-primary">Penduduk</span></h2>
        </div>

        <div class="row g-3 g-md-4">
            <?php 
            $stats = [
                ['label' => 'Total Penduduk', 'value' => $desaProfile['jumlah_penduduk'] ?? 0, 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>'],
                ['label' => 'Kepala Keluarga', 'value' => $desaProfile['jumlah_kk'] ?? 0, 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-info"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'],
                ['label' => 'Laki-Laki', 'value' => $desaProfile['jumlah_laki'] ?? 0, 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'],
                ['label' => 'Perempuan', 'value' => $desaProfile['jumlah_perempuan'] ?? 0, 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: #ec4899;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'],
                ['label' => 'Penduduk Sementara', 'value' => $desaProfile['penduduk_sementara'] ?? 0, 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-warning"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>'],
                ['label' => 'Mutasi Penduduk', 'value' => $desaProfile['mutasi_penduduk'] ?? 0, 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-secondary"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>'],
            ];
            ?>
            
            <?php foreach($stats as $stat): ?>
            <div class="col-6 col-md-4">
                <div class="glass-card text-center p-4 h-100 hover-lift decoration-none">
                    <div class="mb-2 fs-1 op-50"><?= $stat['icon'] ?></div>
                    <div class="display-6 fw-bold text-gradient-primary mb-1"><?= number_format($stat['value'], 0, ',', '.') ?></div>
                    <div class="small text-muted text-uppercase tracking-wider fw-semibold"><?= $stat['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
     </div>
</section>

<!-- Perangkat Desa Section -->
<!-- Perangkat Desa Section -->
<section class="about-section position-relative overflow-hidden">
    <div class="about-blob-1"></div>
    <div class="about-blob-2"></div>
    <div class="container position-relative z-1">
        <div class="text-start text-md-center mb-5">
            <div class="section-tag mb-2 justify-content-start justify-content-md-center">
                <span class="line"></span>
                <span class="text">Struktur Organisasi</span>
            </div>
            <h2 class="section-heading mb-0">Perangkat <span class="text-gradient-primary">Desa</span></h2>
        </div>

        <?php if (!empty($perangkatDesa)): ?>
            <div class="row g-4">
                <?php foreach ($perangkatDesa as $perangkat): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="glass-card h-100 text-center p-2 p-lg-4 position-relative overflow-hidden hover-card">
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-gradient-primary opacity-0 hover-opacity-10 transition-all"></div>
                            
                            <div class="position-relative mb-2 mb-md-3 d-inline-block">
                                <img src="<?= esc($perangkat['foto_url']) ?>" 
                                     alt="<?= esc($perangkat['nama']) ?>" 
                                     class="rounded-circle shadow-lg position-relative" 
                                     style="width: clamp(70px, 20vw, 110px); height: clamp(70px, 20vw, 110px); object-fit: cover; border: 3px solid rgba(255,255,255,0.2);">
                            </div>
                            
                            <h5 class="fw-bold mb-1 mb-md-2 fs-6 fs-md-5"><?= esc($perangkat['nama']) ?></h5>
                            <div class="mb-2 mb-md-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10 rounded-pill px-2 px-md-3 py-1 py-md-2 fw-medium small">
                                    <?= esc($perangkat['jabatan']) ?>
                                </span>
                            </div>
                            
                            <?php if ($perangkat['kontak']): ?>
                                <div class="d-inline-flex align-items-center justify-content-center px-2 px-md-3 py-1 py-md-2 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1 me-md-2 opacity-75"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span class="small" style="font-size: 0.75rem;"><?= esc($perangkat['kontak']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="empty-state-glass p-5 rounded-4">
                    <div class="icon fs-1 mb-3">👥</div>
                    <p class="mb-0 text-muted">Belum ada data perangkat desa.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>


<!-- Lokasi & Kontak Section -->
<div class="position-relative py-5">
    <div class="container text-center">
        <div class="d-flex align-items-center justify-content-center gap-3">
             <div class="flex-grow-1" style="height: 1px; background: linear-gradient(to right, transparent, rgba(16, 185, 129, 0.3));"></div>
             <span class="text-gradient fw-bold small text-uppercase tracking-widest px-3 py-1 border rounded-pill bg-white bg-opacity-5">Siap Melayani Masyarakat</span>
             <div class="flex-grow-1" style="height: 1px; background: linear-gradient(to left, transparent, rgba(16, 185, 129, 0.3));"></div>
        </div>
    </div>
</div>
    <div class="container position-relative z-1">
        <div class="row g-4 d-flex align-items-stretch">
            
            <div class="col-lg-5">
                <div class="glass-card h-100 p-4 p-md-5">
                    <div class="section-tag mb-4">
                        <span class="line"></span>
                        <span class="text">Hubungi Kami</span>
                    </div>
                    <h3 class="fw-bold mb-4">Kontak <span class="text-gradient-primary">Desa</span></h3>
                    
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0 mt-1">
                                <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold mb-1">Whatsapp</h6>
                                <p class="text-muted mb-0"><?= $desaProfile['kontak_wa'] ?: '-' ?></p>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-shrink-0 mt-1">
                                <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold mb-1">Email</h6>
                                <p class="text-muted mb-0"><?= $desaProfile['kontak_email'] ?: '-' ?></p>
                            </div>
                        </div>

                        <div class="d-flex">
                            <div class="flex-shrink-0 mt-1">
                                <div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 10-8 10s-8-4-8-10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                            </div>
                            <div class="ms-3">
                                <h6 class="fw-bold mb-1">Alamat Kantor</h6>
                                <p class="text-muted mb-0"><?= $desaProfile['alamat_kantor'] ?: '-' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="glass-card h-100 p-2 p-md-2 d-flex flex-column">
                    <?php 
                    $mapsUrl = $desaProfile['maps_url'] ?? null;
                    $mapsEmbed = $desaProfile['maps_embed_url'] ?? null;
                    $defaultEmbed = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3979.9782469360237!2d120.245!3d-5.435!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNcKwMjYnMDYuMCJTIDEyMMKwMTQnNDIuMCJF!5e0!3m2!1sen!2sid!4v1704090000';
                    ?>
                    
                    <div class="rounded-3 overflow-hidden flex-grow-1 position-relative" style="min-height: 400px; height: 100%;">
                        <?php if ($mapsEmbed): ?>
                            <iframe src="<?= esc($mapsEmbed) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="border:0; width: 100%; height: 100%;"></iframe>
                        <?php elseif ($mapsUrl): ?>
                            <div class="d-flex justify-content-center align-items-center h-100 bg-light rounded-3 p-5 text-center">
                                <div>
                                    <div class="mb-3 text-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 10-8 10s-8-4-8-10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <h5 class="fw-bold">Lokasi Desa</h5>
                                    <a href="<?= esc($mapsUrl) ?>" target="_blank" class="btn btn-primary rounded-pill px-4 mt-2">
                                        Buka di Google Maps
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <iframe src="<?= $defaultEmbed ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="border:0; width: 100%; height: 100%;"></iframe>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
        
        <?php if (!empty($desaProfile['deskripsi_lokasi'])): ?>
            <div class="mt-4 text-center">
                <p class="text-muted small mx-auto" style="max-width: 800px;"><?= nl2br(esc($desaProfile['deskripsi_lokasi'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?= $this->endSection() ?>
