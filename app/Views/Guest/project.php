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
    /* Project Card Styles */
    .project-card-modern {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: none;
        display: flex;
        flex-direction: column;
    }

    .project-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .project-card-img {
        position: relative;
        height: 220px;
        width: 100%;
        overflow: hidden;
    }

    /* Mobile adjustments for card image */
    @media (max-width: 576px) {
        .project-card-img {
            height: 180px;
        }
    }

    .project-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .project-card-modern:hover .project-card-img img {
        transform: scale(1.05);
    }

    .project-date-badge {
        position: absolute;
        top: 1rem; /* Keep it at the top */
        right: 1rem; /* Keep it at the right */
        left: auto; /* Ensure it doesn't stretch from left */
        width: auto; /* Fit content */
        max-width: fit-content;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(4px);
        color: #fff;
        padding: 0.25rem 0.75rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 2; /* Ensure it's above image */
    }

    /* Badge Custom Styles for Dark Mode Context if needed, 
       but standard bootstrap badges usually work okay. 
       We might need to tweak specific project badges if they look off on dark bg.
    */
    .badge-planning {
        background-color: rgba(13, 110, 253, 0.2);
        color: #6ea8fe;
        border: 1px solid rgba(13, 110, 253, 0.3);
    }
    
    .badge-process {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffe69c;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }
    
    .badge-completed {
        background-color: rgba(25, 135, 84, 0.2);
        color: #75b798;
        border: 1px solid rgba(25, 135, 84, 0.3);
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
</style>

<!-- Global Background Wrapper -->
<div class="global-bg-wrapper">
    <div class="gallery-bg-glow"></div> 
</div>

<section class="position-relative min-vh-100 custom-header-spacing">
    <div class="container position-relative z-1">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-end mb-5">
            <div>
                 <div class="section-tag mb-2">
                    <span class="line"></span>
                    <span class="text">Transparansi</span>
                </div>
                <h2 class="section-heading mb-0">Project Pembangunan <br><span class="text-gradient-primary">Desa Padangloang</span></h2>
            </div>
            
            <!-- Search Bar -->
            <div class="mt-4 mt-md-0 position-relative" style="width: 100%; max-width: 300px;">
                <input type="text" id="project-search" class="form-control glass-search" placeholder="Cari project...">
                <button class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-muted pe-3" onclick="loadProjects(1)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
            </div>
        </div>
        
        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat project...</p>
        </div>
        
        <!-- Projects Container -->
        <div id="projects-container" class="row g-4">
            <!-- Projects will be loaded here via AJAX -->
        </div>
        
        <!-- Empty State -->
        <div id="empty-state" class="col-12 text-center py-5" style="display: none;">
            <div class="empty-state-glass">
                <div class="icon">🏗️</div>
                <p id="empty-text">Belum ada data pembangunan.</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Project pagination" class="mt-5">
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
document.getElementById('project-search').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        loadProjects(1);
    } else {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadProjects(1);
        }, 500);
    }
});

// Load projects function
function loadProjects(page = 1) {
    currentPage = page;
    const searchInput = document.getElementById('project-search');
    currentSearch = searchInput ? searchInput.value : '';
    
    // Show loading spinner
    document.getElementById('loading-spinner').style.display = 'block';
    document.getElementById('projects-container').style.display = 'none';
    document.getElementById('empty-state').style.display = 'none';
    
    // Fetch projects via AJAX POST
    fetch(`<?= base_url('/project/api') ?>`, {
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
            const projectsContainer = document.getElementById('projects-container');
            const paginationContainer = document.getElementById('pagination-container');
            
            // Clear existing content
            projectsContainer.innerHTML = '';
            paginationContainer.innerHTML = '';
            
            // Check if projects exist
            if (data.projects.length === 0) {
                const emptyText = document.getElementById('empty-text');
                if (currentSearch) {
                    emptyText.textContent = `Tidak ditemukan project dengan kata kunci "${currentSearch}"`;
                } else {
                    emptyText.textContent = 'Belum ada data pembangunan.';
                }
                document.getElementById('empty-state').style.display = 'block';
                return;
            }
            
            // Show projects container
            projectsContainer.style.display = 'flex';
            
            // Render projects
            data.projects.forEach(project => {
                const dateObj = new Date(project.tanggal_waktu);
                // Format to e.g. "10 Jan 2026"
                const formattedDate = dateObj.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                
                // Format budget
                const budget = parseInt(project.anggaran || 0);
                const formattedBudget = budget.toLocaleString('id-ID');
                
                // Use raw text since LandingController escapes and strips it
                const limitedText = project.deskripsi || '';

                // Status Badge Logic
                let statusClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                const status = (project.status || '').toLowerCase();
                if (status.includes('rencana')) statusClass = 'badge-planning';
                else if (status.includes('proses') || status.includes('jalan')) statusClass = 'badge-process';
                else if (status.includes('selesai')) statusClass = 'badge-completed';
                
                // Use a transparent placeholder
                const placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

                const imageHtml = project.thumbnail
                    ? `<img src="${placeholder}" data-src="<?= base_url() ?>/${project.thumbnail}" alt="${escapeHtml(project.judul)}" class="lazy-img" style="opacity: 0; transition: opacity 0.5s ease-in-out, transform 0.6s ease;">`
                    : `<div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light position-absolute top-0 start-0"><svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#9ca3af" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/></svg></div>`;

                const projectCard = `
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm project-card-modern position-relative">
                            <div class="project-card-img">
                                ${imageHtml}
                                <div class="project-date-badge">
                                    ${formattedDate}
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="badge ${statusClass} px-3 py-2 rounded-pill fw-semibold border">
                                        ${escapeHtml(project.status || 'Unknown')}
                                    </div>
                                </div>
                                
                                <h5 class="fw-bold mb-3 text-dark">${escapeHtml(project.judul)}</h5>
                                
                                <div class="project-budget mb-3 p-3 rounded-3">
                                    <div class="small text-muted mb-1">Anggaran Project</div>
                                    <div class="fw-bold text-primary fs-5">Rp${formattedBudget}</div>
                                </div>
                                
                                <p class="text-muted small mb-4 text-truncate-3">${limitedText}</p>
                                
                                <a href="<?= base_url('/project') ?>/${project.id}" class="btn btn-outline-primary btn-sm w-100 rounded-pill py-2 fw-semibold stretched-link">
                                    Detail Progress
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                
                projectsContainer.insertAdjacentHTML('beforeend', projectCard);
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
        console.error('Error loading projects:', error);
        document.getElementById('loading-spinner').style.display = 'none';
        document.getElementById('projects-container').innerHTML = `
            <div class="col-12 text-center text-danger py-5">
                <p>Terjadi kesalahan saat memuat project. Silakan coba lagi.</p>
            </div>
        `;
        document.getElementById('projects-container').style.display = 'block';
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
        <a class="page-link" href="#" onclick="loadProjects(${currentPage - 1}); return false;" aria-label="Previous">
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
                <a class="page-link" href="#" onclick="loadProjects(${i}); return false;">${i}</a>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="loadProjects(${currentPage + 1}); return false;" aria-label="Next">
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

// Load projects on page load
document.addEventListener('DOMContentLoaded', function() {
    loadProjects(1);
});
</script>
<?= $this->endSection() ?>
