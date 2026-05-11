<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');
$tahun_sekarang = (int)date('Y');
$selected_tahun = $this->input->get('tahun') ?: $tahun_sekarang;

if ($Auth['role'] === 'koordinator') {
    $koordinator_list = $this->db->query("SELECT id, nama FROM master_koordinator_blok WHERE id = '".$this->db->escape_str($Auth['id'])."'")->result_array();
    $selected_koor = $Auth['id'];
} else {
    $koordinator_list = $this->db->query("SELECT DISTINCT k.id, k.nama FROM master_koordinator_blok k WHERE EXISTS (SELECT 1 FROM master_rumah r WHERE r.id_koordinator = k.id) ORDER BY k.nama")->result_array();
    $selected_koor = $this->input->get('id_koordinator');
}
?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    --success-gradient: linear-gradient(135deg, #51cf66 0%, #21d4fd 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card {
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    background: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    overflow: hidden;
    position: relative;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

.stat-card .card-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: #fff;
}

.nav-tabs-profil {
    border-bottom: none;
    gap: 10px;
}

.nav-tabs-profil .nav-link {
    border: none;
    border-radius: 12px;
    color: #64748b;
    font-weight: 600;
    padding: 12px 24px;
    transition: all 0.3s;
    background: #f8fafc;
}

.nav-tabs-profil .nav-link:hover {
    background: #f1f5f9;
}

.nav-tabs-profil .nav-link.active {
    background: var(--primary-gradient);
    color: #fff !important;
    box-shadow: 0 10px 15px -3px rgba(102, 126, 234, 0.4);
}

.nav-tabs-profil .nav-link.active i, 
.nav-tabs-profil .nav-link.active span {
    color: #fff !important;
}

.table-profil {
    border-collapse: separate;
    border-spacing: 0 8px;
}

.table-profil thead th {
    background: #f8fafc !important;
    color: #64748b !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    border: none;
    padding: 15px;
}

.table-profil tbody tr {
    background: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    transition: all 0.2s;
}

.table-profil tbody tr:hover {
    transform: scale(1.005);
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.table-profil td {
    padding: 15px;
    border: none;
    vertical-align: middle;
}

.table-profil td:first-child { border-radius: 12px 0 0 12px; }
.table-profil td:last-child { border-radius: 0 12px 12px 0; }

.badge-dimuka { background: var(--primary-gradient); color: #fff; }
.btn-filter { border-radius: 10px; padding: 10px 20px; font-weight: 600; }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-3">
        <div class="card-body px-4 py-3">
            <h4 class="fw-semibold mb-1">🏠 Profil Kepatuhan Warga</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profil Kepatuhan Warga</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card p-3">
                <div class="card-icon" style="background: var(--info-gradient);">
                    <i class="ti ti-home"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" id="statTotal">-</h3>
                    <span class="text-muted small fw-semibold">Total Rumah</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card p-3">
                <div class="card-icon" style="background: var(--danger-gradient);">
                    <i class="ti ti-alert-triangle"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" id="statMenunggak">-</h3>
                    <span class="text-muted small fw-semibold">Menunggak</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card p-3">
                <div class="card-icon" style="background: var(--success-gradient);">
                    <i class="ti ti-circle-check"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" id="statRajin">-</h3>
                    <span class="text-muted small fw-semibold">Rajin Bayar</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="card stat-card p-3">
                <div class="card-icon" style="background: var(--primary-gradient);">
                    <i class="ti ti-star"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-0" id="statDimuka">-</h3>
                    <span class="text-muted small fw-semibold">Bayar Di Muka</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section (Moved below stats) -->
    <div class="mb-4 p-4 border rounded-4 shadow-sm" style="background: #f1f5f9; border: 1px solid #cbd5e1 !important;">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <div style="min-width: 120px; flex: 1;">
                <label class="form-label fw-bold text-slate-700 small">Tahun</label>
                <select id="filterTahun" class="form-select">
                    <?php for ($y = $tahun_sekarang; $y >= 2025; $y--): ?>
                        <option value="<?= $y ?>" <?= $selected_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div style="min-width: 150px; flex: 1;">
                <label class="form-label fw-bold text-slate-700 small">Sampai Bulan</label>
                <select id="filterBulan" class="form-select">
                    <?php 
                    $bulans = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                    foreach($bulans as $num => $nama): 
                        $sel = ($num == (int)date('n')) ? 'selected' : '';
                    ?>
                        <option value="<?= $num ?>" <?= $sel ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($Auth['role'] !== 'koordinator'): ?>
            <div style="min-width: 180px; flex: 1;">
                <label class="form-label fw-bold text-slate-700 small">Koordinator</label>
                <select id="filterKoor" class="form-select">
                    <option value="">Semua Koordinator</option>
                    <?php foreach ($koordinator_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $selected_koor == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="d-flex gap-2">
                <button id="btnFilter" class="btn btn-primary btn-filter shadow-sm" style="background: var(--primary-gradient); border:none;">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                <button id="btnReset" class="btn btn-white btn-filter shadow-sm border text-secondary">
                    <i class="ti ti-refresh me-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card border rounded-4 shadow-sm" style="border-color: #e2e8f0 !important;">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-tabs-profil px-3 pt-3" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tabMenunggak">
                        <i class="bi bi-exclamation-triangle text-danger me-1"></i> Warga Menunggak
                        <span class="badge bg-danger ms-1" id="badgeMenunggak">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tabRajin">
                        <i class="bi bi-check-circle text-success me-1"></i> Warga Rajin Bayar
                        <span class="badge bg-success ms-1" id="badgeRajin">0</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tabDimuka">
                        <i class="bi bi-star-fill text-warning me-1"></i> Bayar Di Muka
                        <span class="badge badge-dimuka ms-1" id="badgeDimuka">0</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content p-3">
                <!-- TAB 1: MENUNGGAK -->
                <div class="tab-pane fade show active" id="tabMenunggak">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" id="titleMenunggak">Daftar Warga Menunggak</h6>
                        <small class="text-muted" id="periodeInfo">-</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-profil table-hover w-100" id="tblMenunggak">
                            <thead><tr>
                                <th width="40">No</th><th>Alamat</th><th>Nama</th><th>No HP</th>
                                <th>Koordinator</th><th>Terbayar</th><th>Tunggakan</th><th>Status</th><th>Aksi</th>
                            </tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: RAJIN BAYAR -->
                <div class="tab-pane fade" id="tabRajin">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">🏆 Warga Rajin Bayar</h6>
                        <small class="text-muted">Lunas semua bulan wajib</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-profil table-hover w-100" id="tblRajin">
                            <thead><tr>
                                <th width="40">No</th><th>Alamat</th><th>Nama</th><th>No HP</th>
                                <th>Koordinator</th><th>Terbayar</th><th>Total Bayar</th><th>Terakhir Bayar</th><th>Terkirim</th><th>Aksi</th>
                            </tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: BAYAR DI MUKA -->
                <div class="tab-pane fade" id="tabDimuka">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">⭐ Warga Bayar Di Muka</h6>
                        <small class="text-muted">Membayar melebihi bulan berjalan</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-profil table-hover w-100" id="tblDimuka">
                            <thead><tr>
                                <th width="40">No</th><th>Alamat</th><th>Nama</th><th>No HP</th>
                                <th>Koordinator</th><th>Bayar Sampai</th><th>Di Muka</th><th>Total Bayar</th><th>Terkirim</th><th>Aksi</th>
                            </tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var BASE = '<?= base_url() ?>';
var dtMenunggak, dtRajin, dtDimuka;
var currentTahun = '<?= $selected_tahun ?>';

function formatHp(hp) {
    if (!hp) return '<span class="text-muted">-</span>';
    var wa = hp.replace(/^0/, '62');
    return '<a href="https://wa.me/'+wa+'" target="_blank" class="text-decoration-none"><i class="bi bi-whatsapp text-success"></i> '+hp+'</a>';
}
function formatRp(n) {
    return 'Rp' + Number(n||0).toLocaleString('id-ID');
}
function encryptUrl(id) {
    return btoa(id); // simple base64 for link, server will handle actual encryption
}

function loadData() {
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    var koor = $('#filterKoor').length ? $('#filterKoor').val() : '';
    currentTahun = tahun;

    // Destroy existing DataTables
    if (dtMenunggak) { dtMenunggak.destroy(); dtMenunggak = null; }
    if (dtRajin) { dtRajin.destroy(); dtRajin = null; }
    if (dtDimuka) { dtDimuka.destroy(); dtDimuka = null; }

    // Loading state
    $('#tblMenunggak tbody, #tblRajin tbody, #tblDimuka tbody').html(
        '<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted">Memuat data...</div></td></tr>'
    );
    $('#statTotal, #statMenunggak, #statRajin, #statDimuka').text('-');

    $.ajax({
        url: BASE + 'ajax-profil-warga',
        data: { tahun: tahun, bulan: bulan, id_koordinator: koor },
        dataType: 'json',
        success: function(res) {
            // Update stats
            $('#statTotal').text(res.stats.total);
            $('#statMenunggak').text(res.stats.menunggak);
            $('#statRajin').text(res.stats.rajin);
            $('#statDimuka').text(res.stats.dimuka);
            $('#badgeMenunggak').text(res.stats.menunggak);
            $('#badgeRajin').text(res.stats.rajin);
            $('#badgeDimuka').text(res.stats.dimuka);
            $('#titleMenunggak').text('Daftar Warga Menunggak — Tahun ' + tahun);
            $('#periodeInfo').text('Periode: ' + res.periode + ' (' + res.total_bulan_wajib + ' bulan wajib)');

            var tbw = res.total_bulan_wajib;

            // Menunggak
            var rows1 = [];
            $.each(res.menunggak, function(i, w) {
                var totalNominal = w.tunggakan * 150000;
                var listBulan = w.bulan_tunggak_list || (w.tunggakan + " bulan");
                var msg = "Assalamualaikum Bapak/Ibu *" + w.nama + "*, kami dari pengurus Paguyuban TSI memberitahukan bahwa terdapat tunggakan IPL untuk rumah *" + w.alamat + "* sebesar *Rp " + totalNominal.toLocaleString('id-ID') + "* (" + listBulan + "). Mohon segera melakukan koordinasi pembayaran melalui Koordinator atau Bendahara. Terima kasih.";
                var waLink = "https://wa.me/" + (w.no_hp ? w.no_hp.replace(/^0/, '62') : "") + "?text=" + encodeURIComponent(msg);

                rows1.push([
                    i+1,
                    '<i class="bi bi-geo-alt-fill text-danger me-1"></i>' + (w.alamat||'-'),
                    '<strong>' + (w.nama||'-') + '</strong>',
                    formatHp(w.no_hp),
                    w.koordinator||'-',
                    '<span class="badge bg-info">'+w.jumlah_bulan_bayar+' / '+tbw+'</span>',
                    '<span class="badge bg-'+w.level+'">'+w.tunggakan+' bulan</span>',
                    '<small class="text-'+w.level+'">'+w.status_tunggak+'</small>',
                    '<div class="d-flex gap-1">' +
                        '<a href="'+BASE+'surat-teguran-pdf?id_rumah='+w.id+'&tahun='+tahun+'&bulan='+bulan+'" target="_blank" class="btn btn-sm btn-outline-danger" title="Cetak PDF"><i class="bi bi-file-earmark-pdf"></i></a>' +
                        '<a href="'+waLink+'" target="_blank" class="btn btn-sm btn-outline-success" title="Kirim WA Manual (wa.me)"><i class="bi bi-whatsapp"></i></a>' +
                        '<button onclick="sendWaOtomatis('+w.id+')" class="btn btn-sm btn-success" title="Kirim WA Otomatis (API + File)"><i class="bi bi-send-check"></i></button>' +
                        '<a href="'+BASE+'pembayaran/'+w.id+'" class="btn btn-sm btn-outline-primary" title="Input Bayar"><i class="bi bi-cash-coin"></i></a>' +
                    '</div>'
                ]);
            });
            dtMenunggak = $('#tblMenunggak').DataTable({
                data: rows1, destroy: true, pageLength: 25,
                language: { search:"Cari:", lengthMenu:"Tampilkan _MENU_", info:"_START_-_END_ dari _TOTAL_", paginate:{previous:"Prev",next:"Next"}, emptyTable:"Semua warga sudah lunas! 🎉", zeroRecords:"Tidak ditemukan" },
                columnDefs: [{ targets: [0,5,6,8], className: 'text-center' }]
            });

            // Rajin
            var rows2 = [];
            $.each(res.rajin, function(i, w) {
                rows2.push([
                    i+1,
                    '<i class="bi bi-geo-alt-fill text-success me-1"></i>' + (w.alamat||'-'),
                    '<strong>' + (w.nama||'-') + '</strong> <i class="bi bi-patch-check-fill text-success"></i>',
                    formatHp(w.no_hp),
                    w.koordinator||'-',
                    '<span class="badge bg-success">'+w.jumlah_bulan_bayar+' bulan ✓</span>',
                    formatRp(w.total_bayar),
                    w.terakhir_bayar ? w.terakhir_bayar.substring(8,10)+'/'+w.terakhir_bayar.substring(5,7)+'/'+w.terakhir_bayar.substring(0,4) : '-',
                    w.wa_count > 0 ? '<span class="badge bg-warning text-dark">'+w.wa_count+'x</span>' : '<span class="text-muted">-</span>',
                    '<button onclick="sendWaKonfirmasi('+w.id+',\'lancar\')" class="btn btn-sm btn-success" title="Kirim Bukti Pembayaran via WA"><i class="bi bi-send-check me-1"></i>Kirim Bukti</button>'
                ]);
            });
            dtRajin = $('#tblRajin').DataTable({
                data: rows2, destroy: true, pageLength: 25,
                language: { search:"Cari:", lengthMenu:"Tampilkan _MENU_", info:"_START_-_END_ dari _TOTAL_", paginate:{previous:"Prev",next:"Next"}, emptyTable:"Belum ada warga lunas semua bulan", zeroRecords:"Tidak ditemukan" },
                columnDefs: [{ targets: [0,5,7,8,9], className: 'text-center' }, { targets: [6], className: 'text-end fw-bold' }]
            });

            // Dimuka
            var rows3 = [];
            $.each(res.dimuka, function(i, w) {
                rows3.push([
                    i+1,
                    '<i class="bi bi-geo-alt-fill text-primary me-1"></i>' + (w.alamat||'-'),
                    '<strong>' + (w.nama||'-') + '</strong> ⭐',
                    formatHp(w.no_hp),
                    w.koordinator||'-',
                    '<span class="badge badge-dimuka">'+w.bayar_sampai+'</span>',
                    '<span class="badge bg-info">+'+w.bulan_dimuka+' bulan</span>',
                    formatRp(w.total_bayar),
                    w.wa_count > 0 ? '<span class="badge bg-warning text-dark">'+w.wa_count+'x</span>' : '<span class="text-muted">-</span>',
                    '<button onclick="sendWaKonfirmasi('+w.id+',\'dimuka\')" class="btn btn-sm btn-success" title="Kirim Bukti Pembayaran via WA"><i class="bi bi-send-check me-1"></i>Kirim Bukti</button>'
                ]);
            });
            dtDimuka = $('#tblDimuka').DataTable({
                data: rows3, destroy: true, pageLength: 25,
                language: { search:"Cari:", lengthMenu:"Tampilkan _MENU_", info:"_START_-_END_ dari _TOTAL_", paginate:{previous:"Prev",next:"Next"}, emptyTable:"Belum ada warga bayar di muka", zeroRecords:"Tidak ditemukan" },
                columnDefs: [{ targets: [0,5,6,8,9], className: 'text-center' }, { targets: [7], className: 'text-end fw-bold' }]
            });
        },
        error: function() {
            $('#tblMenunggak tbody, #tblRajin tbody, #tblDimuka tbody').html(
                '<tr><td colspan="9" class="text-center py-4 text-danger"><i class="bi bi-exclamation-circle" style="font-size:2rem;"></i><p class="mt-2">Gagal memuat data</p></td></tr>'
            );
        }
    });
}

$(document).ready(function() {
    loadData();

    $('#btnFilter').on('click', function() { loadData(); });
    $('#btnReset').on('click', function() {
        $('#filterTahun').val('<?= $tahun_sekarang ?>');
        if ($('#filterKoor').length) $('#filterKoor').val('');
        loadData();
    });

    // Fix DataTable column width on tab switch
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        $.fn.dataTable.tables({visible:true, api:true}).columns.adjust();
    });
});
function sendWaOtomatis(id_rumah) {
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    
    if(!confirm("Kirim surat teguran otomatis ke WhatsApp warga?")) return;

    Swal.fire({
        title: 'Mengirim...',
        text: 'Sedang memproses PDF dan mengirim WhatsApp',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: BASE + 'dashboard/kirim_teguran_wa',
        data: { id_rumah: id_rumah, tahun: tahun, bulan: bulan },
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                Swal.fire('Berhasil!', res.message, 'success');
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        }
    });
}

function sendWaKonfirmasi(id_rumah, tipe) {
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    var label = (tipe === 'lancar') ? 'konfirmasi lunas' : 'konfirmasi bayar di muka';
    
    if(!confirm("Kirim " + label + " via WhatsApp ke warga ini?")) return;

    Swal.fire({
        title: 'Mengirim...',
        text: 'Sedang memproses PDF dan mengirim WhatsApp',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: BASE + 'kirim-konfirmasi-wa',
        data: { id_rumah: id_rumah, tahun: tahun, bulan: bulan, tipe: tipe },
        dataType: 'json',
        success: function(res) {
            if(res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: res.message,
                    confirmButtonColor: '#25D366'
                });
            } else {
                Swal.fire('Gagal', res.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        }
    });
}
</script>
