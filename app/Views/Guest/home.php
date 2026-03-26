<?= $this->extend('Guest/layout') ?>

<?= $this->section('pageClass') ?>hero-page<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
    .text-truncate-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        word-break: break-word;
    }
</style>
<section class="hero-section position-relative" id="heroSection">
    <div class="hero-overlay"></div>
    <div class="hero-particles" id="heroParticles"></div>
    
    <!-- Interactive Constellation Canvas -->
    <canvas id="heroInteractiveCanvas" class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; z-index: 1;"></canvas>
    
    <div class="container position-relative hero-content">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <div class="hero-badge-label animate-fade-in">
                    <span class="badge-icon">🏡</span>
                    <span>Website Resmi Pemerintah Desa</span>
                </div>
                <h1 class="hero-title animate-slide-up">
                    Desa <span class="text-gradient">Padangloang</span>
                </h1>
                <p class="hero-tagline animate-slide-up">Kec. Ujung Loe, Kab. Bulukumba, Sulawesi Selatan</p>
                <p class="hero-subtitle animate-slide-up-delay">
                    Akses layanan administrasi desa secara online, informasi pembangunan, 
                    berita terkini, dan galeri kegiatan masyarakat. Wujudkan desa digital 
                    yang transparan, modern, dan melayani.
                </p>
                <div class="hero-buttons animate-fade-in-delay">
                    <a href="#about" class="btn-hero-primary">
                        <span>Eksplorasi Desa</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <a href="<?= base_url('/login') ?>" class="btn-hero-glass">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        <span>Layanan Surat Online</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Scroll Indicator di luar container, di ujung bawah section -->
    <div class="hero-scroll-indicator animate-bounce">
        <span>Gulir ke bawah</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
    </div>
</section>

<section id="about" class="about-section position-relative">
    <div class="about-blob-1"></div>
    <div class="about-blob-2"></div>
    
    <div class="container position-relative z-1">
        <div class="row align-items-center justify-content-between g-5">
            <div class="col-lg-6 order-2 order-lg-1">
                <?php 
                $mapsEmbed = $desaProfile['maps_embed_url'] ?? null;
                $defaultEmbed = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3979.9782469360237!2d120.245!3d-5.435!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNcKwMjYnMDYuMCJTIDEyMMKwMTQnNDIuMCJF!5e0!3m2!1sen!2sid!4v1704090000';
                ?>
                <div class="map-modern-wrapper animate-slide-right">
                    <div class="map-frame">
                        <?php if ($mapsEmbed): ?>
                            <iframe src="<?= esc($mapsEmbed) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <?php else: ?>
                            <iframe src="<?= $defaultEmbed ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <?php endif; ?>
                    </div>
                    <!-- Decorative Floating Elements -->
                    <div class="map-badge badge-location">
                        <span class="icon">📍</span>
                        <div class="text">
                            <span class="label">Lokasi</span>
                            <span class="value">Ujung Loe</span>
                        </div>
                    </div>
                    <div class="map-badge badge-population">
                        <span class="icon">👥</span>
                        <div class="text">
                            <span class="label">Penduduk</span>
                            <span class="value">Ramah & Guyub</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 col-xl-5 order-1 order-lg-2">
                <div class="about-content animate-slide-left">
                    <div class="section-tag">
                        <span class="line"></span>
                        <span class="text">Tentang Desa</span>
                    </div>
                    
                    <h2 class="section-heading">
                        Mengenal Lebih Dekat <br>
                        <span class="text-gradient-primary">Desa Padangloang</span>
                    </h2>
                    
                    <p class="section-description">
                        <?= esc($desaProfile['deskripsi_lokasi'] ?? 'Padangloang adalah desa yang terus bergerak maju, menjunjung tinggi nilai gotong royong, transparansi, dan inovasi untuk kesejahteraan masyarakat.') ?>
                    </p>
                    
                    <div class="visi-misi-wrapper">
                        <div class="vm-card">
                            <div class="vm-icon visi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            </div>
                            <div class="vm-content">
                                <h6>Visi Utama</h6>
                                <p><?= esc($desaProfile['visi'] ?: 'Menjadi desa mandiri yang berdaya saing dan sejahtera.') ?></p>
                            </div>
                        </div>
                        
                        <div class="vm-card">
                            <div class="vm-icon misi-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <div class="vm-content">
                                <h6>Misi Kami</h6>
                                <p><?= esc($desaProfile['misi'] ?: 'Meningkatkan pelayanan publik dan pemberdayaan masyarakat.') ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="about-actions">
                        <a href="<?= base_url('/profil') ?>" class="btn-modern-primary">
                            <span>Selengkapnya</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </a>
                        <a href="<?= base_url('/galeri') ?>" class="btn-modern-outline">
                            <span>Lihat Galeri</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="gallery-section position-relative">
    <div class="gallery-bg-glow"></div>
    
    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-5">
            <div>
                <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Galeri Desa</span>
                </div>
                <h2 class="section-heading mb-0">Momen & Kegiatan <br><span class="text-gradient-primary">Terbaru</span></h2>
            </div>
            <a href="<?= base_url('/galeri') ?>" class="btn-modern-outline mt-3 mt-md-0">
                <span>Lihat Semua Galeri</span>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($albums as $album): ?>
                <div class="col-6 col-md-6 col-lg-4">
                    <div class="glass-card h-100 position-relative">
                        <div class="glass-card-img">
                            <img src="<?= $album['thumbnail'] ? base_url($album['thumbnail']) : 'https://via.placeholder.com/600x400?text=Galeri+Desa' ?>" alt="<?= esc($album['nama_album']) ?>">
                            <div class="glass-date-badge">
                                <span class="day"><?= date('d', strtotime($album['tanggal_waktu'])) ?></span>
                                <span class="month"><?= date('M', strtotime($album['tanggal_waktu'])) ?></span>
                            </div>
                        </div>
                        <div class="glass-card-body">
                            <h5 class="glass-title"><?= esc($album['nama_album']) ?></h5>
                            <p class="glass-desc text-truncate-2"><?= esc(strip_tags($album['deskripsi'] ?? '')) ?></p>
                            <a href="<?= base_url('/galeri/' . $album['id']) ?>" class="glass-link stretched-link">
                                <span>Lihat Album</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($albums)): ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state-glass">
                        <div class="icon">📷</div>
                        <p>Belum ada album galeri saat ini.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="news-section position-relative">
    <div class="news-bg-glow"></div>
    
    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-5">
            <div class="text-start">
                <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Informasi Terkini</span>
                </div>
                <h2 class="section-heading mb-0">Kabar & Berita <br><span class="text-gradient-primary">Desa Padangloang</span></h2>
            </div>
            <a href="<?= base_url('/berita') ?>" class="btn-modern-outline mt-3 mt-md-0">
                <span>Lihat Semua Berita</span>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($news as $item): ?>
                <div class="col-6 col-lg-6">
                    <div class="news-card-horizontal h-100 position-relative">
                        <div class="news-img-wrapper">
                            <!-- Fallback logic for image -->
                            <?php $img = $item['gambar'] ?? $item['thumbnail'] ?? null; ?>
                            <img src="<?= $img ? base_url($img) : 'https://via.placeholder.com/600x400?text=Berita+Desa' ?>" alt="<?= esc($item['judul']) ?>">
                            <div class="news-date-floating">
                                <span class="fw-bold"><?= date('d', strtotime($item['tanggal_waktu'])) ?></span>
                                <small><?= date('M', strtotime($item['tanggal_waktu'])) ?></small>
                            </div>
                        </div>
                        <div class="news-body">
                            <h5 class="news-title"><?= esc($item['judul']) ?></h5>
                            <p class="news-excerpt text-truncate-3"><?= esc(strip_tags($item['isi'] ?? '')) ?></p>
                            <a href="<?= base_url('/berita/' . $item['id']) ?>" class="news-read-more stretched-link">
                                <span>Baca Selengkapnya</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($news)): ?>
                <div class="col-12 text-center py-5">
                    <div class="empty-state-glass">
                        <div class="icon">📰</div>
                        <p>Belum ada berita terbaru saat ini.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="project-section position-relative">
    <!-- Reuse Gallery Glow Effect -->
    <div class="gallery-bg-glow"></div>
    
    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-5">
            <div>
                 <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Transparansi</span>
                </div>
                <h2 class="section-heading mb-0">Project Pembangunan <br><span class="text-gradient-primary">Desa Padangloang</span></h2>
            </div>
            <a href="<?= base_url('/project') ?>" class="btn-modern-outline mt-3 mt-md-0">Lihat Semua Project</a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($projects as $project): ?>
                <?php
                    // Determine badge class based on status
                    $statusClass = 'bg-secondary-subtle text-secondary border-secondary-subtle'; // Default
                    $status = strtolower($project['status']);
                    
                    if (str_contains($status, 'rencana')) {
                        $statusClass = 'badge-planning';
                    } elseif (str_contains($status, 'proses') || str_contains($status, 'jalan')) {
                        $statusClass = 'badge-process';
                    } elseif (str_contains($status, 'selesai')) {
                        $statusClass = 'badge-completed';
                    }
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm project-card-modern position-relative">
                        <div class="project-card-img">
                            <!-- Fallback logic for image -->
                            <?php $img = $project['gambar'] ?? $project['thumbnail'] ?? null; ?>
                            <img src="<?= $img ? base_url($img) : 'https://via.placeholder.com/600x400?text=Project+Desa' ?>" alt="<?= esc($project['judul']) ?>">
                            <div class="project-date-badge">
                                <?= date('d M Y', strtotime($project['tanggal_waktu'])) ?>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="badge <?= $statusClass ?> px-3 py-2 rounded-pill fw-semibold border">
                                    <?= esc($project['status']) ?>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-3"><?= esc($project['judul']) ?></h5>
                            
                            <div class="project-budget mb-3 p-3 rounded-3">
                                <div class="small text-muted mb-1">Anggaran Project</div>
                                <div class="fw-bold text-primary fs-5">Rp<?= number_format($project['anggaran'] ?? 0, 0, ',', '.') ?></div>
                            </div>
                            
                            <p class="text-muted small mb-4 text-truncate-3"><?= esc(strip_tags($project['deskripsi'] ?? '')) ?></p>
                            
                            <a href="<?= base_url('/project/' . $project['id']) ?>" class="btn btn-outline-primary btn-sm w-100 rounded-pill py-2 fw-semibold stretched-link">
                                Detail Progress
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($projects)): ?>
                <div class="col-12 text-center text-muted py-5">
                    Belum ada data pembangunan.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?= $this->endSection() ?>


