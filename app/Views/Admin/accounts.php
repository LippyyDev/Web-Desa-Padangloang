<?= $this->extend('Admin/layout') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <div>
        <h4>Kelola Akun</h4>
        <div class="text-muted small">Buat dan atur role akun.</div>
    </div>
    <div class="page-header-actions">
        <div class="page-header-icon">
            <i class="bi bi-people"></i>
        </div>
        <a href="<?= base_url('/admin/akun/tambah') ?>" class="page-header-icon page-header-icon-add" title="Tambah Akun">
            <i class="bi bi-plus-circle"></i>
        </a>
    </div>
</div>

<!-- Desktop Table View -->
<div class="card desktop-table-view">
    <div class="card-body">
    <div class="table-responsive">
            <table id="accountsTable" class="table align-middle mb-0" style="width:100%">
            <thead>
            <tr>
                    <th>Foto</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Dibuat</th>
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
    <div id="accountsCardContainer"></div>
    <div id="customPagination" class="custom-pagination"></div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<script>
let currentPage = 1;
let itemsPerPage = 10;
let totalRecords = 0;
let totalPages = 1;

function getStatusBadge(status) {
    const statusClass = status === 'aktif' ? 'bg-success' : 'bg-secondary';
    return '<span class="badge ' + statusClass + '">' + status + '</span>';
}

function getRoleBadge(role) {
    return '<span class="badge bg-primary text-uppercase">' + role + '</span>';
}

function loadCards(page = 1) {
    const container = $('#accountsCardContainer');
    container.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
    
    const start = (page - 1) * itemsPerPage;
    
    $.ajax({
        url: '<?= base_url('/admin/akun/api') ?>',
        type: 'GET',
        data: {
            draw: page,
            start: start,
            length: itemsPerPage,
            order: [{ column: 5, dir: 'desc' }]
        },
        success: function(response) {
            container.empty();
            
            if (!response.data || response.data.length === 0) {
                container.html('<div class="text-center py-5"><p class="text-muted">Tidak ada data akun</p></div>');
                totalRecords = 0;
                totalPages = 1;
                renderPagination();
                return;
            }
            
            totalRecords = response.recordsFiltered || response.recordsTotal || 0;
            totalPages = Math.ceil(totalRecords / itemsPerPage);
            currentPage = page;
            
            response.data.forEach(function(account) {
                const fotoUrl = account.foto_profil ? '<?= base_url() ?>' + account.foto_profil : '<?= base_url('assets/img/guest.webp') ?>';
                const card = `
                    <div class="account-card">
                        <div class="account-card-header">
                            <div class="account-card-profile">
                                <img src="${fotoUrl}" alt="Foto Profil" class="account-card-photo">
                                <div class="account-card-title-wrapper">
                                    <h5 class="account-card-title">${account.username}</h5>
                                    <div class="account-card-subtitle">${account.email}</div>
                                </div>
                            </div>
                            <div class="account-card-badges">
                                ${getStatusBadge(account.status)}
                                ${getRoleBadge(account.role)}
                            </div>
                        </div>
                        <div class="account-card-body">
                            <div class="account-card-info">
                                <div class="account-card-item">
                                    <i class="bi bi-calendar"></i>
                                    <span>Dibuat: ${account.created_at}</span>
                                </div>
                            </div>
                            <div class="account-card-actions">
                                <a href="<?= base_url('/admin/akun/') ?>${account.id}/edit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="<?= base_url('/admin/akun/') ?>${account.id}/hapus" class="btn btn-sm btn-outline-danger">
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

// Tunggu jQuery dan semua script ter-load
(function() {
    function loadDataTables() {
        // Cek apakah jQuery sudah ter-load
        if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
            setTimeout(loadDataTables, 100);
            return;
        }

        // Load DataTables script jika belum ter-load
        if (typeof $.fn.DataTable === 'undefined') {
            const dataTablesScript = document.createElement('script');
            dataTablesScript.src = 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js';
            dataTablesScript.onload = function() {
                const bootstrapScript = document.createElement('script');
                bootstrapScript.src = 'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js';
                bootstrapScript.onload = function() {
                    initDataTable();
                };
                document.head.appendChild(bootstrapScript);
            };
            document.head.appendChild(dataTablesScript);
        } else {
            initDataTable();
        }
    }

    function initDataTable() {
        $(document).ready(function() {
            // Initialize DataTable for desktop (server-side)
            $('#accountsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= base_url('/admin/akun/api') ?>',
                    type: 'GET',
                    dataSrc: 'data'
                },
                columns: [
                    { 
                        data: 'foto_profil',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            const fotoUrl = data ? '<?= base_url() ?>' + data : '<?= base_url('assets/img/guest.webp') ?>';
                            return '<img src="' + fotoUrl + '" alt="Foto Profil" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">';
            }
                    },
                    { 
                        data: 'username',
                        className: 'fw-semibold'
                    },
                    { 
                        data: 'email'
                    },
                    { 
                        data: 'role',
                        render: function(data) {
                            return getRoleBadge(data);
        }
                    },
                    { 
                        data: 'status',
                        render: function(data) {
                            return getStatusBadge(data);
        }
                    },
                    { 
                        data: 'created_at',
                        className: 'small text-muted'
                    },
                    { 
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return '<div class="btn-group" role="group">' +
                                '<a href="<?= base_url('/admin/akun/') ?>' + data + '/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a> ' +
                                '<a href="<?= base_url('/admin/akun/') ?>' + data + '/hapus" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Hapus</a>' +
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
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-12"p>>',
                info: false,
                order: [[5, 'desc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]]
            });
            
            // Check initial view mode
            checkViewMode();
            
            // Handle window resize
            $(window).resize(function() {
                checkViewMode();
            });
        });
    }

    // Mulai proses setelah DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadDataTables);
    } else {
        loadDataTables();
    }
})();
</script>
<?= $this->endSection() ?>



