<?= $this->extend('Guest/layout') ?>

<?= $this->section('content') ?>


<!-- Global Background Wrapper -->
<div class="global-bg-wrapper">
    <div class="gallery-bg-glow"></div> 
</div>

<style>
    /* Inherit global styles from galeri.php logic */
    body {
        background-color: #0f172a; /* Dark Navy */
        color: #f8fafc;
    }

    .global-bg-wrapper {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        overflow: hidden;
        pointer-events: none;
    }

    /* Grid Pattern */
    .global-bg-wrapper::before {
        content: "";
        position: absolute;
        inset: 0;
        background-image: 
            linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        background-size: 50px 50px;
        z-index: 1;
        mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
        -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
    }

    /* Spacing for fixed navbar - Match galeri.php exactly */
    .custom-header-spacing {
        padding-top: 90px !important;
        padding-bottom: 80px;
    }

    @media (max-width: 768px) {
        .custom-header-spacing {
            padding-top: 30px !important;
            padding-bottom: 60px;
        }
    }

    .gallery-section {
        background: transparent !important;
    }

    /* Remove the old grid pattern from the section to avoid "cutting off" content visual issues */
    .gallery-section::before {
        content: none !important;
        display: none !important;
    }
    
    /* Section Tag & Heading overrides */
    .section-tag .text {
        color: #10b981;
    }
    
    .section-heading {
        color: #fff;
    }

    .project-card-modern {
        background: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        overflow: hidden;
    }

    .project-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        border-color: rgba(59, 130, 246, 0.3);
    }

    /* Fixed Aspect Ratio Wrapper using padding-bottom technique or aspect-ratio property */
    .project-card-img-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 75%; /* 4:3 Aspect Ratio */
        overflow: hidden;
        border-radius: 16px; /* Rounded corners for the whole card feel since body is gone */
        background-color: #1e293b;
    }

    .project-card-img-wrapper img, 
    .project-card-img-wrapper iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s ease;
    }

    .project-card-modern:hover .project-card-img-wrapper img {
        transform: scale(1.1);
        cursor: pointer;
    }
    
    /* Overlay icon on hover for images */
    .hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none; /* Let clicks pass through to img */
    }
    
    .project-card-modern:hover .hover-overlay {
        opacity: 1;
    }

    /* Lightbox Styles */
    .lightbox-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(5px);
        justify-content: center;
        align-items: center;
    }
    
    .lightbox-content {
        max-width: 90%;
        max-height: 90vh;
        border-radius: 8px;
        box-shadow: 0 0 50px rgba(0,0,0,0.5);
        animation: zoomIn 0.3s ease;
    }
    
    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 30px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
        cursor: pointer;
        z-index: 10000;
    }
    
    .lightbox-close:hover,
    .lightbox-close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    @keyframes zoomIn {
        from {transform:scale(0.9); opacity:0} 
        to {transform:scale(1); opacity:1}
    }

    /* Dark Glassmorphism Pagination - Match galeri.php */
    .glass-pagination .page-link {
        background: rgba(15, 23, 42, 0.6); /* Dark navy/black background */
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #94a3b8; /* Slate-400 */
        margin: 0 4px;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-weight: 500;
        height: 40px;
        min-width: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .glass-pagination .page-link:hover {
        background: rgba(30, 41, 59, 0.8);
        border-color: rgba(255, 255, 255, 0.2);
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .glass-pagination .page-item.active .page-link {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.8) 0%, rgba(37, 99, 235, 0.9) 100%);
        border-color: rgba(59, 130, 246, 0.5);
        color: #fff;
        box-shadow: 0 0 15px rgba(37, 99, 235, 0.5);
    }

    .glass-pagination .page-item.disabled .page-link {
        background: rgba(15, 23, 42, 0.3);
        color: #475569;
        border-color: rgba(255, 255, 255, 0.05);
        cursor: not-allowed;
    }

    /* Empty State */
    .empty-state-glass {
        background: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(12px);
        border: 1px dashed rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 3rem;
        text-align: center;
        color: #94a3b8;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .empty-state-glass .icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.8;
        filter: grayscale(100%) brightness(1.5); /* Make standard emojis look better on dark */
    }
</style>

<section class="gallery-section position-relative min-vh-100 custom-header-spacing">
    <div class="container position-relative z-1">
        <!-- Header Section -->
        <div class="d-flex flex-column mb-5">
            <div class="mb-3">
                 <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Detail Album</span>
                </div>
                <!-- Typography from home.php project section -->
                <h2 class="section-heading mb-0">
                    <?= esc($album['nama_album']) ?> <br>
                    <span class="text-gradient-primary">Desa Padangloang</span>
                </h2>
                
                <div class="d-flex align-items-center gap-3 mt-4 mb-3">
                    <div class="badge bg-white text-dark px-3 py-2 rounded-pill fw-semibold border d-flex align-items-center gap-2">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                         <?= date('d F Y', strtotime($album['tanggal_waktu'])) ?>
                    </div>
                </div>

                <p class="text-light text-break fs-5 mb-0" style="max-width: 100%; line-height: 1.6; opacity: 0.9;">
                    <?= esc($album['deskripsi'] ?? '') ?>
                </p>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <a href="<?= base_url('/galeri') ?>" class="btn-modern-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Media Grid Container -->
        <div id="mediaGrid" class="row g-4">
            <!-- Media will be loaded here via AJAX -->
        </div>

        <!-- Pagination Container -->
        <nav aria-label="Media pagination" class="mt-5">
            <ul id="paginationContainer" class="pagination glass-pagination justify-content-center">
                <!-- Pagination buttons will be loaded here via AJAX -->
            </ul>
        </nav>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightboxModal" class="lightbox-modal" onclick="closeLightbox(event)">
    <span class="lightbox-close" onclick="closeLightbox(event)">&times;</span>
    <img class="lightbox-content" id="lightboxImage">
</div>

<script>
    const albumId = <?= $album['id'] ?>;
    let csrfToken = '<?= csrf_token() ?>';
    let csrfHash  = '<?= csrf_hash() ?>';

    let currentPage = 1;
    let isLoading = false;

    // Load media with pagination
    async function loadMedia(page = 1) {
        if (isLoading) return;
        
        isLoading = true;
        currentPage = page;
        
        const mediaGrid = document.getElementById('mediaGrid');
        const paginationContainer = document.getElementById('paginationContainer');
        
        // Show loading state
        mediaGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-light mt-3">Memuat media...</p>
            </div>
        `;
        
        try {
            const response = await fetch(`<?= base_url('galeri/detail-api') ?>/${albumId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    [csrfToken]: csrfHash,
                    page: page
                })
            });
            const data = await response.json();
            
            // Refresh CSRF token dari response
            if (data[csrfToken]) {
                csrfHash = data[csrfToken];
            }

            if (data.success) {
                renderMedia(data.media);
                renderPagination(data.pagination);
            } else {
                showError('Gagal memuat media');
            }
        } catch (error) {
            console.error('Error loading media:', error);
            showError('Terjadi kesalahan saat memuat media');
        } finally {
            isLoading = false;
        }
    }

    // Render media items
    function renderMedia(media) {
        const mediaGrid = document.getElementById('mediaGrid');
        
        if (!media || media.length === 0) {
            mediaGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="empty-state-glass">
                        <div class="icon">📷</div>
                        <p>Belum ada media di album ini.</p>
                    </div>
                </div>
            `;
            return;
        }
        
        let html = '';
        media.forEach(item => {
            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="project-card-modern h-100 border-0 shadow-sm">
            `;
            
            if (item.media_type === 'video_link') {
                // Video Embed
                const embedUrl = item.embed_url || item.media_path;
                html += `
                    <div class="project-card-img-wrapper">
                        <iframe src="${escapeHtml(embedUrl)}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                `;
            } else {
                // Image with Lightbox
                const mediaPath = `<?= base_url() ?>/${item.media_path}`;
                html += `
                    <div class="project-card-img-wrapper" onclick="openLightbox('${mediaPath}')">
                        <img src="${mediaPath}" alt="Media <?= esc($album['nama_album']) ?>" loading="lazy">
                        <div class="hover-overlay">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        </div>
                    </div>
                `;
            }
            
            html += `
                    </div>
                </div>
            `;
        });
        
        mediaGrid.innerHTML = html;
    }

    // Render pagination
    function renderPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        const { currentPage, totalPages } = pagination;
        
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Previous button
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadMedia(${currentPage - 1}); return false;" aria-label="Previous">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </li>
        `;
        
        // Page numbers - Maximum 7 buttons
        const maxVisiblePages = 7;
        let startPage, endPage;
        
        if (totalPages <= maxVisiblePages) {
            // Show all pages if total is less than max
            startPage = 1;
            endPage = totalPages;
        } else {
            // Calculate start and end page to center current page
            const halfVisible = Math.floor(maxVisiblePages / 2);
            startPage = Math.max(1, currentPage - halfVisible);
            endPage = startPage + maxVisiblePages - 1;
            
            // Adjust if we're near the end
            if (endPage > totalPages) {
                endPage = totalPages;
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
        }
        
        // Render page numbers
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="loadMedia(${i}); return false;">${i}</a>
                </li>
            `;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="loadMedia(${currentPage + 1}); return false;" aria-label="Next">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </li>
        `;
        
        container.innerHTML = html;
    }

    // Show error message
    function showError(message) {
        const mediaGrid = document.getElementById('mediaGrid');
        mediaGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="empty-state-glass">
                    <div class="icon">⚠️</div>
                    <p>${escapeHtml(message)}</p>
                </div>
            </div>
        `;
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Lightbox functions
    function openLightbox(imageSrc) {
        var modal = document.getElementById("lightboxModal");
        var modalImg = document.getElementById("lightboxImage");
        modal.style.display = "flex";
        setTimeout(() => { modal.style.opacity = "1"; }, 10);
        modalImg.src = imageSrc;
        document.body.style.overflow = "hidden";
    }

    function closeLightbox(event) {
        if (event.target.id === 'lightboxModal' || event.target.className === 'lightbox-close') {
            var modal = document.getElementById("lightboxModal");
            modal.style.display = "none";
            document.body.style.overflow = "auto";
            document.getElementById("lightboxImage").src = "";
        }
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
             var modal = document.getElementById("lightboxModal");
             if (modal.style.display === "flex") {
                 modal.style.display = "none";
                 document.body.style.overflow = "auto";
             }
        }
    });

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        loadMedia(1);
    });
</script>
<?= $this->endSection() ?>


