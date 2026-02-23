<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Berita Desa</h4>
        <div class="text-muted small">Kelola berita dan dokumentasi.</div>
    </div>
    <div class="page-header-actions">
        <div class="page-header-icon">
            <i class="bi bi-newspaper"></i>
        </div>
        <a href="<?= base_url('/staff/berita/tambah') ?>" class="page-header-icon page-header-icon-add" title="Tambah Berita">
            <i class="bi bi-plus-circle"></i>
        </a>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" id="filterDateStart" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" id="filterDateEnd" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tampilkan</label>
                <select class="form-select" id="lengthSelect">
                    <option value="12">12</option>
                    <option value="24">24</option>
                    <option value="36">36</option>
                    <option value="48">48</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <div class="news-search-container">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari berita...">
            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display: none;">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
</div>

<div id="newsContainer" class="row g-3">
    <!-- Berita akan di-load via AJAX -->
</div>

<div id="loadingIndicator" class="text-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div id="customPagination" class="custom-pagination mt-4" style="display: none;"></div>

<div id="emptyMessage" class="col-12 text-center text-muted py-4" style="display: none;">
    Belum ada berita.
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage = 1;
    let isLoading = false;
    let currentSearch = '';
    let totalPages = 1;
    let limit = parseInt($('#lengthSelect').val()) || 12;

    function loadNews(page, search = '', itemsPerPage = limit) {
        if (isLoading) return;
        
        isLoading = true;
        $('#loadingIndicator').show();
        $('#newsContainer').empty();
        $('#customPagination').hide();
        $('#emptyMessage').hide();

        const dateStart = $('#filterDateStart').val();
        const dateEnd = $('#filterDateEnd').val();

        $.ajax({
            url: '<?= base_url('/staff/berita/api') ?>',
            type: 'GET',
            data: {
                page: page,
                limit: itemsPerPage,
                search: search,
                date_start: dateStart,
                date_end: dateEnd
            },
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(function(item) {
                        const shortContent = item.isi.length > 100 ? item.isi.substring(0, 100) + '...' : item.isi;
                        html += '<div class="col-md-6 col-lg-4 col-xl-3">' +
                            '<div class="card border-0 shadow-sm h-100 gallery-card">' +
                            '<img src="' + item.thumbnail + '" ' +
                            'class="card-img-top gallery-card-img" ' +
                            'alt="' + item.judul + '" ' +
                            'loading="lazy">' +
                            '<div class="card-body">' +
                            '<div class="small text-muted mb-1">' + item.tanggal_waktu + '</div>' +
                            '<h5 class="card-title">' + item.judul + '</h5>' +
                            '<p class="text-muted small">' + shortContent + '</p>' +
                            '<div class="d-flex gap-2">' +
                            '<a href="<?= base_url('/staff/berita/') ?>' + item.id + '/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a> ' +
                            '<a href="<?= base_url('/staff/berita/') ?>' + item.id + '/hapus" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    });
                    $('#newsContainer').html(html);
                    
                    currentPage = page;
                    totalPages = response.total_pages;
                    currentSearch = search;
                    limit = itemsPerPage;
                    
                    // Generate pagination
                    renderPagination();
                    
                    if (totalPages > 1) {
                        $('#customPagination').show();
                    }
                } else {
                    $('#emptyMessage').show();
                    $('#customPagination').hide();
                }
                
                isLoading = false;
                $('#loadingIndicator').hide();
            },
            error: function() {
                isLoading = false;
                $('#loadingIndicator').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memuat data.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        });
    }

    function renderPagination() {
        const pagination = $('#customPagination');
        pagination.empty();
        
        if (totalPages <= 1) {
            pagination.hide();
            return;
        }
        
        let paginationHTML = '<div class="pagination-wrapper">';
        
        // Previous button
        if (currentPage > 1) {
            paginationHTML += `<button class="pagination-btn" data-page="${currentPage - 1}">
                <i class="bi bi-chevron-left"></i>
            </button>`;
        } else {
            paginationHTML += `<button class="pagination-btn disabled" disabled>
                <i class="bi bi-chevron-left"></i>
            </button>`;
        }
        
        // Page numbers (max 3 buttons)
        let startPage = Math.max(1, currentPage - 1);
        let endPage = Math.min(totalPages, startPage + 2);
        
        // Adjust if we're near the end
        if (endPage - startPage < 2) {
            startPage = Math.max(1, endPage - 2);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHTML += `<button class="pagination-btn active">${i}</button>`;
            } else {
                paginationHTML += `<button class="pagination-btn" data-page="${i}">${i}</button>`;
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<button class="pagination-btn" data-page="${currentPage + 1}">
                <i class="bi bi-chevron-right"></i>
            </button>`;
        } else {
            paginationHTML += `<button class="pagination-btn disabled" disabled>
                <i class="bi bi-chevron-right"></i>
            </button>`;
        }
        
        paginationHTML += '</div>';
        pagination.html(paginationHTML);
    }

    function goToPage(page) {
        if (page < 1 || page > totalPages) return;
        loadNews(page, currentSearch, limit);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Pagination click handler using event delegation
    $(document).on('click', '.pagination-btn:not(.disabled):not(.active)', function() {
        const page = parseInt($(this).data('page'));
        if (page && page !== currentPage) {
            goToPage(page);
        }
    });

    // Search handler with debounce
    let searchTimeout;
    function performSearch() {
        const search = $('#searchInput').val().trim();
        currentSearch = search;
        loadNews(1, search, limit);
        
        if (search) {
            $('#clearSearchBtn').show();
            $('.news-search-container .input-group').addClass('has-clear-btn');
        } else {
            $('#clearSearchBtn').hide();
            $('.news-search-container .input-group').removeClass('has-clear-btn');
        }
    }

    // Auto search on input with debounce (500ms delay)
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500);
    });

    // Clear search
    $('#clearSearchBtn').on('click', function() {
        $('#searchInput').val('');
        $('#clearSearchBtn').hide();
        $('.news-search-container .input-group').removeClass('has-clear-btn');
        performSearch();
    });

    // Length select handler
    $('#lengthSelect').on('change', function() {
        limit = parseInt($(this).val());
        loadNews(1, currentSearch, limit);
    });

    // Auto filter function
    let filterTimeout;
    function applyFilter() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            currentPage = 1;
            loadNews(1, currentSearch, limit);
        }, 300);
    }
    
    // Auto filter on change
    $('#filterDateStart, #filterDateEnd').on('change', function() {
        applyFilter();
    });

    // Load initial data
    loadNews(1);
});
</script>
<?= $this->endSection() ?>



