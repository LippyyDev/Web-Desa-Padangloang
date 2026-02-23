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

    /* Spacing for fixed navbar */
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

    .news-section {
        background: transparent !important;
    }

    .news-section::before {
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

    /* News Content Card */
    .news-content-card {
        background: rgba(30, 41, 59, 0.4);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 3rem;
    }

    .news-thumbnail {
        width: 100%;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        max-height: 450px;
        object-fit: cover;
    }

    .news-article {
        color: #e2e8f0;
        line-height: 1.8;
        font-size: 1.05rem;
    }

    /* Media Cards */
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

    .project-card-img-wrapper {
        position: relative;
        width: 100%;
        padding-bottom: 75%; /* 4:3 Aspect Ratio */
        overflow: hidden;
        border-radius: 16px;
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
        pointer-events: none;
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

    /* Dark Glassmorphism Pagination */
    .glass-pagination .page-link {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #94a3b8;
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

<section class="news-section position-relative min-vh-100 custom-header-spacing">
    <div class="container position-relative z-1">
        <!-- Header Section -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-5">
            <div>
                 <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Detail Berita</span>
                </div>
                <h2 class="section-heading mb-0">
                    <?= esc($item['judul']) ?> <br>
                    <span class="text-gradient-primary">Desa Padangloang</span>
                </h2>
                
                <div class="d-flex align-items-center gap-3 mt-4 mb-3">
                    <div class="badge bg-white text-dark px-3 py-2 rounded-pill fw-semibold border d-flex align-items-center gap-2">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                         <?= date('d F Y', strtotime($item['tanggal_waktu'])) ?>
                    </div>
                </div>
            </div>
            <a href="<?= base_url('/berita') ?>" class="btn-modern-outline mt-4 mt-md-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                <span>Kembali</span>
            </a>
        </div>

        <!-- News Content -->
        <div class="news-content-card">
            <img src="<?= $item['thumbnail'] ? base_url($item['thumbnail']) : 'https://via.placeholder.com/900x420?text=Berita' ?>" class="news-thumbnail" alt="<?= esc($item['judul']) ?>">
            <article class="news-article">
                <?= $item['isi'] ?>
            </article>
        </div>

        <!-- Media Section Header -->
        <div class="mb-4">
            <h4 class="text-white fw-bold">Media Tambahan</h4>
            <p class="text-muted">Foto dan video terkait berita ini</p>
        </div>

        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat media...</p>
        </div>

        <!-- Media Container -->
        <div id="media-container" class="row g-4">
            <!-- Media will be loaded here via AJAX -->
        </div>

        <!-- Empty State -->
        <div id="empty-state" class="col-12 text-center py-5" style="display: none;">
            <div class="empty-state-glass">
                <div class="icon">📷</div>
                <p>Belum ada media tambahan untuk berita ini.</p>
            </div>
        </div>

        <!-- Pagination -->
        <nav aria-label="Media pagination" class="mt-5">
            <ul id="pagination-container" class="pagination glass-pagination justify-content-center">
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
let currentPage = 1;
const newsId = <?= $item['id'] ?>;

// Load media function
function loadMedia(page = 1) {
    currentPage = page;
    
    // Show loading spinner
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('media-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';
    
    // Fetch media via AJAX
    fetch(`<?= base_url('/berita') ?>/${newsId}/ajax?page=${page}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading spinner
        document.getElementById('loading-spinner').style.display = 'none';
        
        if (data.success) {
            const mediaContainer = document.getElementById('media-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            // Clear existing content
            mediaContainer.innerHTML = '';
            paginationContainer.innerHTML = '';
            
            // Check if media exist
            if (data.media.length === 0) {
                document.getElementById('empty-state').style.display = 'block';
                return;
            }
            
            // Show media container
            mediaContainer.style.display = 'flex';
            
            // Render media
            data.media.forEach(item => {
                let mediaHTML = '';
                
                if (item.media_type === 'video_link') {
                    // Video Embed
                    const embedUrl = item.embed_url || item.media_path;
                    mediaHTML = `
                        <div class="col-md-6 col-lg-4">
                            <div class="project-card-modern h-100 border-0 shadow-sm">
                                <div class="project-card-img-wrapper">
                                    <iframe src="${escapeHtml(embedUrl)}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    // Image with Lightbox
                    const imageUrl = `<?= base_url() ?>/${item.media_path}`;
                    mediaHTML = `
                        <div class="col-md-6 col-lg-4">
                            <div class="project-card-modern h-100 border-0 shadow-sm">
                                <div class="project-card-img-wrapper" onclick="openLightbox('${imageUrl}')">
                                    <img src="${imageUrl}" alt="Media" loading="lazy">
                                    <div class="hover-overlay">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                mediaContainer.insertAdjacentHTML('beforeend', mediaHTML);
            });
            
            // Render pagination
            renderPagination(data.pagination);
            
            // Scroll to media section
            document.querySelector('.news-content-card').scrollIntoView({ behavior: 'smooth', block: 'end' });
        }
    })
    .catch(error => {
        console.error('Error loading media:', error);
        document.getElementById('loading-spinner').style.display = 'none';
        document.getElementById('media-container').innerHTML = `
            <div class="col-12 text-center text-danger py-5">
                <p>Terjadi kesalahan saat memuat media. Silakan coba lagi.</p>
            </div>
        `;
        document.getElementById('media-container').style.display = 'block';
    });
}

// Render pagination
function renderPagination(pagination) {
    const container = document.getElementById('pagination-container');
    const { currentPage, totalPages } = pagination;
    
    if (totalPages <= 1) {
        return; // No pagination needed
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadMedia(${currentPage - 1}); return false;" aria-label="Previous">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </a>
        </li>
    `;
    
    // Calculate page range (max 7 buttons)
    const maxButtons = 7;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    let endPage = Math.min(totalPages, startPage + maxButtons - 1);
    
    // Adjust if we're near the end
    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    // Page number buttons
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadMedia(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadMedia(${currentPage + 1}); return false;" aria-label="Next">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </li>
    `;
    
    container.innerHTML = paginationHTML;
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

// Helper function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Load media on page load
document.addEventListener('DOMContentLoaded', function() {
    loadMedia(1);
});
</script>
<?= $this->endSection() ?>
