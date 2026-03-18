<?= $this->extend('User/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div class="d-flex align-items-center gap-3">
        <div class="page-header-icon">
            <i class="bi bi-envelope"></i>
        </div>
        <div>
            <h4 class="mb-0">Surat Saya</h4>
            <div class="text-muted small mt-1">Kelola pengajuan surat ke staf desa</div>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="<?= base_url('/user/surat/buat') ?>" class="page-header-icon page-header-icon-add" title="Buat Surat">
            <i class="bi bi-plus-circle"></i>
        </a>
    </div>
</div>
<!-- Template Section -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold">Template Surat</div>
            <div class="small text-muted">Download template surat untuk diisi manual</div>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2 g-md-3 template-buttons">
            <div class="col-6 col-md-4 col-lg">
                <a href="<?= base_url('/user/surat/template/keterangan-usaha') ?>" class="btn btn-success w-100 template-btn">
                    <i class="bi bi-download"></i> <span>KET USAHA</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <a href="<?= base_url('/user/surat/template/keterangan-tidak-mampu') ?>" class="btn btn-warning w-100 template-btn">
                    <i class="bi bi-download"></i> <span>KET TIDAK MAMPU</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <a href="<?= base_url('/user/surat/template/keterangan-belum-menikah') ?>" class="btn btn-danger w-100 template-btn">
                    <i class="bi bi-download"></i> <span>KET BELUM MENIKAH</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <a href="<?= base_url('/user/surat/template/keterangan-domisili') ?>" class="btn btn-info w-100 template-btn">
                    <i class="bi bi-download"></i> <span>KET DOMISILI</span>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <a href="<?= base_url('/user/surat/template/undangan') ?>" class="btn btn-primary w-100 template-btn">
                    <i class="bi bi-download"></i> <span>UNDANGAN</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" id="filterDateStart" class="form-control">
            </div>
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label">Tanggal Sampai</label>
                <input type="date" id="filterDateEnd" class="form-control">
            </div>
            <div class="col-12 col-md-4 col-lg">
                <label class="form-label">Tipe Surat</label>
                <select id="filterTipeSurat" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="Keterangan Usaha">Keterangan Usaha</option>
                    <option value="Keterangan Tidak Mampu">Keterangan Tidak Mampu</option>
                    <option value="Keterangan Belum Menikah">Keterangan Belum Menikah</option>
                    <option value="Keterangan Domisili">Keterangan Domisili</option>
                    <option value="Undangan">Undangan</option>
                    <option value="Lain Lain">Lain Lain</option>
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label">Status</label>
                <select id="filterStatus" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Dibaca">Dibaca</option>
                    <option value="Diterima">Diterima</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>
            <div class="col-6 col-md-4 col-lg">
                <label class="form-label">Tampilkan</label>
                <select id="lengthSelect" class="form-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="-1">Semua</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Search Box -->
<div class="mb-4">
    <div class="letter-search-container">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="searchInput" class="form-control" placeholder="Cari surat...">
            <button class="btn btn-outline-secondary" type="button" id="clearSearchBtn" style="display: none;">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
</div>

<!-- Desktop Table View -->
<div class="card desktop-table-view">
    <div class="card-body">
        <div class="table-responsive">
            <table id="lettersTable" class="table align-middle mb-0" style="width:100%">
                <thead>
                <tr>
                    <th>Kode Unik</th>
                    <th>Perihal</th>
                    <th>Tipe</th>
                    <th>Status</th>
                    <th>Terkirim</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mobile/Tablet Card View -->
<div class="mobile-card-view">
    <div id="lettersCardContainer"></div>
    <div id="customPagination" class="custom-pagination"></div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
let csrfToken = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';

let currentPage = 1;
let itemsPerPage = 10;
let totalRecords = 0;
let totalPages = 1;

// Initialize itemsPerPage from select
$(document).ready(function() {
    const lengthValue = parseInt($('#lengthSelect').val()) || 10;
    itemsPerPage = lengthValue === -1 ? 10000 : lengthValue;
});

function getStatusBadge(status) {
    const statusClass = {
        'Menunggu': 'bg-warning',
        'Dibaca': 'bg-primary',
        'Diterima': 'bg-success',
        'Ditolak': 'bg-danger'
    };
    return '<span class="badge ' + (statusClass[status] || 'bg-warning') + '">' + status + '</span>';
}

function loadCards(page = 1) {
    const container = $('#lettersCardContainer');
    container.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    
    const lengthValue = parseInt($('#lengthSelect').val()) || 10;
    const currentLength = lengthValue === -1 ? 10000 : lengthValue;
    itemsPerPage = currentLength;
    
    const start = (page - 1) * itemsPerPage;
    const dateStart = $('#filterDateStart').val();
    const dateEnd = $('#filterDateEnd').val();
    const status = $('#filterStatus').val();
    const search = $('#searchInput').val().trim();
    
    $.ajax({
        url: '<?= base_url('/user/surat/api') ?>',
        type: 'POST',
        data: {
            [csrfToken]: csrfHash,
            draw: page,
            start: start,
            length: itemsPerPage,
                order: [{ column: 4, dir: 'desc' }],
            date_start: dateStart,
            date_end: dateEnd,
                tipe_surat_filter: $('#filterTipeSurat').val(),
            status_filter: status,
            search_custom: search
        },
        success: function(response) {
            csrfHash = response[csrfToken];
            container.empty();
            
            if (!response.data || response.data.length === 0) {
                container.html('<div class="text-center py-5"><p class="text-muted">Tidak ada data surat</p></div>');
                totalRecords = 0;
                totalPages = 1;
                renderPagination();
                return;
            }
            
            totalRecords = response.recordsFiltered || response.recordsTotal || 0;
            totalPages = Math.ceil(totalRecords / itemsPerPage);
            currentPage = page;
            
            response.data.forEach(function(letter) {
                const card = `
                    <div class="letter-card">
                        <div class="letter-card-header">
                            <h5 class="letter-card-title">${letter.judul_perihal}</h5>
                            <div class="letter-card-badge">
                                ${getStatusBadge(letter.status)}
                            </div>
                        </div>
                        <div class="letter-card-body">
                            <div class="letter-card-info">
                                <div class="letter-card-item">
                                    <i class="bi bi-hash"></i>
                                    <span class="fw-bold">${letter.kode_unik || '-'}</span>
                                </div>
                                <div class="letter-card-item">
                                    <i class="bi bi-file-text"></i>
                                    <span>${letter.tipe_surat}</span>
                                </div>
                                <div class="letter-card-item">
                                    <i class="bi bi-clock"></i>
                                    <span>${letter.sent_at}</span>
                                </div>
                            </div>
                            <div class="letter-card-actions">
                                <a href="<?= base_url('/user/surat/') ?>${letter.id}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                                <a href="<?= base_url('/user/surat/') ?>${letter.id}/hapus" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                container.append(card);
            });
            
            renderPagination();
        },
        error: function() {
            container.html('<div class="text-center py-5"><p class="text-danger">Error loading data</p></div>');
        }
    });
}

function renderPagination() {
    const pagination = $('#customPagination');
    pagination.empty();
    
    if (totalPages <= 1) return;
    
    let paginationHTML = '<div class="pagination-wrapper">';
    
    // Previous button
    if (currentPage > 1) {
        paginationHTML += `<button class="pagination-btn" onclick="goToPage(${currentPage - 1})">
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
            paginationHTML += `<button class="pagination-btn" onclick="goToPage(${i})">${i}</button>`;
        }
    }
    
    // Next button
    if (currentPage < totalPages) {
        paginationHTML += `<button class="pagination-btn" onclick="goToPage(${currentPage + 1})">
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
    loadCards(page);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function checkViewMode() {
    const width = $(window).width();
    if (width <= 991) {
        $('.desktop-table-view').hide();
        $('.mobile-card-view').show();
        if (currentPage === 1) {
            loadCards(1);
        }
    } else {
        $('.desktop-table-view').show();
        $('.mobile-card-view').hide();
    }
}

$(document).ready(function() {
    // Initialize DataTable for desktop (server-side)
    const dataTable = $('#lettersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('/user/surat/api') ?>',
            type: 'POST',
            dataSrc: function(json) {
                csrfHash = json[csrfToken];
                return json.data;
            },
            data: function(d) {
                d[csrfToken] = csrfHash;
                d.date_start = $('#filterDateStart').val();
                d.date_end = $('#filterDateEnd').val();
                d.tipe_surat_filter = $('#filterTipeSurat').val();
                d.status_filter = $('#filterStatus').val();
                d.search_custom = $('#searchInput').val().trim();
            }
        },
        columns: [
            { 
                data: 'kode_unik',
                className: 'small text-muted fw-bold'
            },
            { 
                data: 'judul_perihal',
                className: 'fw-semibold',
                visible: false
            },
            { 
                data: 'tipe_surat'
            },
            { 
                data: 'status',
                render: function(data) {
                    return getStatusBadge(data);
                }
            },
            { 
                data: 'sent_at',
                className: 'small text-muted',
                visible: false
            },
            { 
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return '<div class="btn-group" role="group">' +
                        '<a href="<?= base_url('/user/surat/') ?>' + data + '" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Detail</a> ' +
                        '<a href="<?= base_url('/user/surat/') ?>' + data + '/hapus" class="btn btn-sm btn-outline-danger">' +
                        '<i class="bi bi-trash"></i> Hapus</a>' +
                        '</div>';
                }
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            }
        },
        dom: 'rt<"row"<"col-sm-12 col-md-12"p>>',
        info: false,
        searching: false,
        order: [[4, 'desc']],
        pageLength: 10
    });
    
    // Check initial view mode
    checkViewMode();
    
    // Handle window resize
    $(window).resize(function() {
        checkViewMode();
    });
    
    // Search handler with debounce
    let searchTimeout;
    let currentSearch = '';
    function performSearch() {
        const search = $('#searchInput').val().trim();
        currentSearch = search;
        
        if (search) {
            $('#clearSearchBtn').show();
            $('.letter-search-container .input-group').addClass('has-clear-btn');
        } else {
            $('#clearSearchBtn').hide();
            $('.letter-search-container .input-group').removeClass('has-clear-btn');
        }
        
        // Update DataTable search
        if (typeof dataTable !== 'undefined') {
            dataTable.ajax.reload();
        }
        
        // Update mobile card view
        if ($('.mobile-card-view').is(':visible')) {
            currentPage = 1;
            loadCards(1);
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
        $('.letter-search-container .input-group').removeClass('has-clear-btn');
        performSearch();
    });
    
    // Auto filter function
    let filterTimeout;
    function applyFilter() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            currentPage = 1;
            if (typeof dataTable !== 'undefined') {
                dataTable.ajax.reload();
            }
            if ($('.mobile-card-view').is(':visible')) {
                loadCards(1);
            }
        }, 300);
    }
    
    // Auto filter on change
    $('#filterDateStart, #filterDateEnd, #filterTipeSurat, #filterStatus').on('change', function() {
        applyFilter();
    });
    
    // Length select handler
    $('#lengthSelect').on('change', function() {
        const length = parseInt($(this).val());
        if (typeof dataTable !== 'undefined') {
            if (length === -1) {
                dataTable.page.len(10000).draw();
            } else {
                dataTable.page.len(length).draw();
            }
        }
        if ($('.mobile-card-view').is(':visible')) {
            currentPage = 1;
            loadCards(1);
        }
    });
});
</script>
<?= $this->endSection() ?>



