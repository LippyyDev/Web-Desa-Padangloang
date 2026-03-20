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
        mask-image: radial-gradient(circle at center, black 40%, transparent 100%);
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

    .news-section {
        background: transparent !important;
    }
    
    .news-section::before {
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
    
    /* Overrides for news card in dark mode */
    .news-card-horizontal {
        background: rgba(255, 255, 255, 0.95);
        border: none;
        display: flex;
        flex-direction: row;
        overflow: hidden;
        min-height: 250px; /* Enforce min height */
        position: relative;
    }
    
    .news-img-wrapper {
        position: relative;
        flex: 0 0 40%;
        width: 40%;
        overflow: hidden;
    }
    
    .news-img-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .news-title {
        color: #1e293b;
    }
    
    .news-excerpt {
        color: #64748b;
    }
    
    /* Mobile adjustments for card */
    @media (max-width: 576px) {
        .news-card-horizontal {
            flex-direction: column;
            min-height: auto;
        }
        
        .news-img-wrapper {
            width: 100%;
            height: 200px;
            flex: none;
        }
        
        .news-img-wrapper img {
            position: absolute; /* Keep absolute so it fills the 200px wrapper */
        }
    }
</style>

<!-- Global Background Wrapper -->
<div class="global-bg-wrapper">
    <div class="gallery-bg-glow"></div> 
</div>

<section class="news-section position-relative min-vh-100 custom-header-spacing">
    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-5">
            <div>
                <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Informasi Terkini</span>
                </div>
                <h2 class="section-heading mb-0">Kabar & Berita <br><span class="text-gradient-primary">Desa Padangloang</span></h2>
            </div>
            
            <!-- Search Bar -->
            <div class="mt-4 mt-md-0 position-relative" style="width: 100%; max-width: 300px;">
                <input type="text" id="news-search" class="form-control glass-search" placeholder="Cari berita...">
                <button class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-muted pe-3" onclick="loadNews(1)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
            </div>
        </div>
        
        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat berita...</p>
        </div>
        
        <!-- News Container -->
        <div id="news-container" class="row g-4">
            <!-- News will be loaded here via AJAX -->
        </div>
        
        <!-- Empty State -->
        <div id="empty-state" class="col-12 text-center py-5" style="display: none;">
            <div class="empty-state-glass">
                <div class="icon">📰</div>
                <p id="empty-text">Belum ada berita saat ini.</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="News pagination" class="mt-5">
            <ul id="pagination-container" class="pagination glass-pagination justify-content-center">
                <!-- Pagination buttons will be loaded here via AJAX -->
            </ul>
        </nav>
    </div>
</section>

<script>
let csrfToken = '<?= csrf_token() ?>';
let csrfHash  = '<?= csrf_hash() ?>';

let currentPage = 1;
let currentSearch = '';

// Search input handler with debounce
let searchTimeout;
document.getElementById('news-search').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        loadNews(1);
    } else {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadNews(1);
        }, 500);
    }
});

// Load news function
function loadNews(page = 1) {
    currentPage = page;
    const searchInput = document.getElementById('news-search');
    currentSearch = searchInput ? searchInput.value : '';
    
    // Show loading spinner
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('news-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';
    
    // Fetch news via AJAX POST
    fetch(`<?= base_url('/berita/api') ?>`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            [csrfToken]: csrfHash,
            page: page,
            search: currentSearch
        })
    })
    .then(response => response.json())
    .then(data => {
        // Refresh CSRF token dari response
        if (data[csrfToken]) {
            csrfHash = data[csrfToken];
        }

        // Hide loading spinner
        document.getElementById('loading-spinner').style.display = 'none';
        
        if (data.success) {
            const newsContainer = document.getElementById('news-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            // Clear existing content
            newsContainer.innerHTML = '';
            paginationContainer.innerHTML = '';
            
            // Check if news exist
            if (data.news.length === 0) {
                const emptyText = document.getElementById('empty-text');
                if (currentSearch) {
                    emptyText.textContent = `Tidak ditemukan berita dengan kata kunci "${currentSearch}"`;
                } else {
                    emptyText.textContent = 'Belum ada berita saat ini.';
                }
                document.getElementById('empty-state').style.display = 'block';
                return;
            }
            
            // Show news container
            newsContainer.style.display = 'flex';
            
            // Render news
            data.news.forEach(item => {
                const imageUrl = item.thumbnail ? `<?= base_url() ?>/${item.thumbnail}` : 'https://via.placeholder.com/600x400?text=Berita';
                const dateObj = new Date(item.tanggal_waktu);
                const day = dateObj.getDate();
                const month = dateObj.toLocaleString('id-ID', { month: 'short' });
                
                // Strip HTML tags and limit words
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = item.isi;
                const plainText = tempDiv.textContent || tempDiv.innerText || '';
                const limitedText = plainText.split(' ').slice(0, 15).join(' ') + (plainText.split(' ').length > 15 ? '...' : '');
                
                // Use a transparent placeholder or low-res placeholder initially
                const placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

                const newsCard = `
                    <div class="col-md-6 col-lg-6">
                        <div class="news-card-horizontal h-100 position-relative">
                            <div class="news-img-wrapper">
                                <img src="${placeholder}" data-src="${imageUrl}" alt="${escapeHtml(item.judul)}" class="lazy-img" style="opacity: 0; transition: opacity 0.5s ease-in-out, transform 0.5s ease;">
                                <div class="news-date-floating">
                                    <span class="fw-bold">${day}</span>
                                    <small>${month}</small>
                                </div>
                            </div>
                            <div class="news-body">
                                <h5 class="news-title">${escapeHtml(item.judul)}</h5>
                                <p class="news-excerpt">${escapeHtml(limitedText)}</p>
                                <a href="<?= base_url('/berita') ?>/${item.id}" class="news-read-more stretched-link">
                                    <span>Baca Selengkapnya</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                
                newsContainer.insertAdjacentHTML('beforeend', newsCard);
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
        console.error('Error loading news:', error);
        document.getElementById('loading-spinner').style.display = 'none';
        document.getElementById('news-container').innerHTML = `
            <div class="col-12 text-center text-danger py-5">
                <p>Terjadi kesalahan saat memuat berita. Silakan coba lagi.</p>
            </div>
        `;
        document.getElementById('news-container').style.display = 'block';
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
            <a class="page-link" href="#" onclick="loadNews(${currentPage - 1}); return false;" aria-label="Previous">
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
                <a class="page-link" href="#" onclick="loadNews(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadNews(${currentPage + 1}); return false;" aria-label="Next">
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

// Load news on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNews(1);
});
</script>
<?= $this->endSection() ?>
