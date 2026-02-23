<?= $this->extend('Staff/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h4>Berita Desa</h4>
    <div class="text-muted small">Kelola berita dan dokumentasi.</div>
</div>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div class="col-12 col-md-5">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari berita...">
            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display: none;">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
    <a href="<?= base_url('/staff/berita/tambah') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Tambah Berita
    </a>
</div>

<div id="newsContainer" class="row g-3">
    <!-- Berita akan di-load via AJAX -->
</div>

<div id="loadingIndicator" class="text-center py-4">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div id="paginationContainer" class="d-flex justify-content-center mt-4" style="display: none;">
    <nav>
        <ul class="pagination" id="pagination">
            <!-- Pagination akan di-generate via JavaScript -->
        </ul>
    </nav>
</div>

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
    const limit = 12;

    function loadNews(page, search = '') {
        if (isLoading) return;
        
        isLoading = true;
        $('#loadingIndicator').show();
        $('#newsContainer').empty();
        $('#paginationContainer').hide();
        $('#emptyMessage').hide();

        $.ajax({
            url: '<?= base_url('/staff/berita/api') ?>',
            type: 'GET',
            data: {
                page: page,
                limit: limit,
                search: search
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
                            '<a href="<?= base_url('/staff/berita/') ?>' + item.id + '/hapus" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Hapus berita ini?\')"><i class="bi bi-trash"></i> Hapus</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    });
                    $('#newsContainer').html(html);
                    
                    currentPage = page;
                    totalPages = response.total_pages;
                    currentSearch = search;
                    
                    // Generate pagination
                    generatePagination(page, totalPages);
                    
                    if (totalPages > 1) {
                        $('#paginationContainer').show();
                    }
                } else {
                    $('#emptyMessage').show();
                    $('#paginationContainer').hide();
                }
                
                isLoading = false;
                $('#loadingIndicator').hide();
            },
            error: function() {
                isLoading = false;
                $('#loadingIndicator').hide();
                alert('Terjadi kesalahan saat memuat data.');
            }
        });
    }

    function generatePagination(current, total) {
        if (total <= 1) {
            $('#paginationContainer').hide();
            return;
        }

        let html = '';
        const maxVisible = 5;
        let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
        let endPage = Math.min(total, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        // Previous button
        html += '<li class="page-item' + (current === 1 ? ' disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (current - 1) + '">Sebelumnya</a>' +
            '</li>';

        // First page
        if (startPage > 1) {
            html += '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
            if (startPage > 2) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Page numbers
        for (let i = startPage; i <= endPage; i++) {
            html += '<li class="page-item' + (i === current ? ' active' : '') + '">' +
                '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a>' +
                '</li>';
        }

        // Last page
        if (endPage < total) {
            if (endPage < total - 1) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            html += '<li class="page-item"><a class="page-link" href="#" data-page="' + total + '">' + total + '</a></li>';
        }

        // Next button
        html += '<li class="page-item' + (current === total ? ' disabled' : '') + '">' +
            '<a class="page-link" href="#" data-page="' + (current + 1) + '">Selanjutnya</a>' +
            '</li>';

        $('#pagination').html(html);
    }

    // Pagination click handler
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        if ($(this).parent().hasClass('disabled')) return;
        const page = parseInt($(this).data('page'));
        if (page && page !== currentPage) {
            loadNews(page, currentSearch);
        }
    });

    // Search handler with debounce
    let searchTimeout;
    function performSearch() {
        const search = $('#searchInput').val().trim();
        currentSearch = search;
        loadNews(1, search);
        
        if (search) {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
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
        performSearch();
    });

    // Load initial data
    loadNews(1);
});
</script>
<?= $this->endSection() ?>


