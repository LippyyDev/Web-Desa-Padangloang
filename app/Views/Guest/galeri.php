<?= $this->extend('Guest/layout') ?>

<?= $this->section('content') ?>
<style>
    /* Global Page Background */
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
        mask-image: radial-gradient(circle at center, black 40%, transparent 100%); /* Soft fade at edges */
        -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
    }

    /* Spacing for fixed navbar */
    .custom-header-spacing {
        padding-top: 90px !important;
        padding-bottom: 80px;
    }

    /* Mobile Spacing Adjustment */
    @media (max-width: 768px) {
        .custom-header-spacing {
            padding-top: 30px !important;
            padding-bottom: 60px;
        }
    }

    .gallery-section {
        background: transparent !important;
    }

    /* Remove the old grid pattern from the section */
    .gallery-section::before {
        content: none !important;
        display: none !important;
    }

    /* Section Tag & Heading overrides for dark mode */
    .section-tag .text {
        color: #10b981;
    }
    
    .section-heading {
        color: #fff;
    }
    
    /* Dark Glassmorphism Pagination */
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
</style>

<!-- Global Background Wrapper -->
<div class="global-bg-wrapper">
    <div class="gallery-bg-glow"></div> 
</div>

<section class="gallery-section position-relative min-vh-100 custom-header-spacing">
    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-5">
            <div>
                <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Galeri Desa</span>
                </div>
                <h2 class="section-heading mb-0">Momen & Kegiatan <br><span class="text-gradient-primary">Terbaru</span></h2>
            </div>
            
            <!-- Search Bar -->
            <div class="mt-4 mt-md-0 position-relative" style="width: 100%; max-width: 300px;">
                <input type="text" id="gallery-search" class="form-control glass-search" placeholder="Cari album...">
                <button class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-muted pe-3" onclick="loadAlbums(1)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
            </div>
        </div>
        
        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat album...</p>
        </div>
        
        <!-- Albums Container -->
        <div id="albums-container" class="row g-4">
            <!-- Albums will be loaded here via AJAX -->
        </div>
        
        <!-- Empty State -->
        <div id="empty-state" class="col-12 text-center py-5" style="display: none;">
            <div class="empty-state-glass">
                <div class="icon">📷</div>
                <p id="empty-text">Belum ada album galeri saat ini.</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Gallery pagination" class="mt-5">
            <ul id="pagination-container" class="pagination glass-pagination justify-content-center">
                <!-- Pagination buttons will be loaded here via AJAX -->
            </ul>
        </nav>
    </div>
</section>

<style>
/* Glass Search Input */
.glass-search {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #f8fafc;
    border-radius: 50px;
    padding-left: 1.25rem;
    padding-right: 2.5rem;
    transition: all 0.3s ease;
}

.glass-search:focus {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(59, 130, 246, 0.5);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    color: #fff;
}

.glass-search::placeholder {
    color: #94a3b8;
}
</style>

<script>
let currentPage = 1;
let currentSearch = '';

// Search input handler with debounce
let searchTimeout;
document.getElementById('gallery-search').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        loadAlbums(1);
    } else {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadAlbums(1);
        }, 500);
    }
});

// Load albums function
function loadAlbums(page = 1) {
    currentPage = page;
    const searchInput = document.getElementById('gallery-search');
    currentSearch = searchInput ? searchInput.value : '';
    
    // Show loading spinner
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('albums-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';
    
    // Fetch albums via AJAX
    fetch(`<?= base_url('/galeri/ajax') ?>?page=${page}&search=${encodeURIComponent(currentSearch)}`, {
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
            const albumsContainer = document.getElementById('albums-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            // Clear existing content
            albumsContainer.innerHTML = '';
            paginationContainer.innerHTML = '';
            
            // Check if albums exist
            if (data.albums.length === 0) {
                const emptyText = document.getElementById('empty-text');
                if (currentSearch) {
                    emptyText.textContent = `Tidak ditemukan album dengan kata kunci "${currentSearch}"`;
                } else {
                    emptyText.textContent = 'Belum ada album galeri saat ini.';
                }
                document.getElementById('empty-state').style.display = 'block';
                return;
            }
            
            // Show albums container
            albumsContainer.style.display = 'flex';
            
            // Render albums
            data.albums.forEach(album => {
                const albumMedia = data.albumMedia[album.id] || '';
                const imageUrl = albumMedia ? `<?= base_url() ?>/${albumMedia}` : 'https://via.placeholder.com/600x400?text=Galeri+Desa';
                
                // Date formatting
                const dateObj = new Date(album.tanggal_waktu);
                const day = dateObj.getDate();
                const month = dateObj.toLocaleString('id-ID', { month: 'short' });
                
                // Helper to limit words (simple approximation)
                const desc = album.deskripsi || '';
                const words = desc.replace(/<[^>]*>?/gm, '').split(/\s+/); // strip tags and split
                const truncatedDesc = words.length > 15 ? words.slice(0, 15).join(' ') + '...' : words.join(' ');
                
                // Use a transparent placeholder or low-res placeholder initially
                const placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

                const albumCard = `
                    <div class="col-6 col-md-6 col-lg-4">
                        <div class="glass-card h-100 position-relative">
                            <div class="glass-card-img">
                                <img src="${placeholder}" data-src="${imageUrl}" alt="${escapeHtml(album.nama_album)}" class="lazy-img" style="opacity: 0; transition: opacity 0.5s ease-in-out, transform 0.6s ease;">
                                <div class="glass-date-badge">
                                    <span class="day">${day}</span>
                                    <span class="month">${month}</span>
                                </div>
                            </div>
                            <div class="glass-card-body">
                                <h5 class="glass-title">${escapeHtml(album.nama_album)}</h5>
                                <p class="glass-desc">${escapeHtml(truncatedDesc)}</p>
                                <a href="<?= base_url('/galeri') ?>/${album.id}" class="glass-link stretched-link">
                                    <span>Lihat Album</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                
                albumsContainer.insertAdjacentHTML('beforeend', albumCard);
            });
            
            // Render pagination
            renderPagination(data.pagination);
            
            // Initialize Lazy Loading
            initLazyLoading();
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    })
    .catch(error => {
        console.error('Error loading albums:', error);
        document.getElementById('loading-spinner').style.display = 'none';
        document.getElementById('albums-container').innerHTML = `
            <div class="col-12 text-center text-danger py-5">
                <p>Terjadi kesalahan saat memuat album. Silakan coba lagi.</p>
            </div>
        `;
        document.getElementById('albums-container').style.display = 'block';
    });
}

// Lazy Loading Initialization
function initLazyLoading() {
    const lazyImages = document.querySelectorAll('img.lazy-img');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.onload = () => {
                        img.style.opacity = 1;
                    };
                    img.classList.remove('lazy-img');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px 0px', // Load images 50px before they appear
            threshold: 0.01
        });
        
        lazyImages.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        lazyImages.forEach(img => {
            img.src = img.dataset.src;
            img.style.opacity = 1;
        });
    }
}

// Render pagination
function renderPagination(pagination) {
    const container = document.getElementById('pagination-container');
    const { currentPage, totalPages } = pagination;
    
    if (totalPages <= 1) {
        return; // Don't show pagination if only 1 page
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadAlbums(${currentPage - 1}); return false;" aria-label="Previous">
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
        paginationHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadAlbums(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadAlbums(${currentPage + 1}); return false;" aria-label="Next">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </li>
    `;
    
    container.innerHTML = paginationHTML;
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load albums on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAlbums(1);
});
</script>
<?= $this->endSection() ?>
