var tabel = null;
$(document).ready(function() {
    tabel = $('#table-laporan').DataTable({
      "processing": true,
      "responsive":true,
      "serverSide": true,
      "ordering": true, 
      "order": [[ 0, 'asc' ]], 
      "ajax":
      {
        "url": "<?= base_url('laporan_view');?>",
        "type": "POST"
      },
      "deferRender": true,
      "aLengthMenu": [[10, 50],[10, 50]],
      "columns": [
      {"data": 'nama',"sortable": false, 
      render: function (data, type, row, meta) {
        return meta.row + meta.settings._iDisplayStart + 1;
      }  
    },
    { "data": "nis" }, 
    { "data": "nama" }, 
    { "data": "kelas" },  
    { "data": "tanggal" },  
    { "data": "jam_masuk" },  
    { "data": "keterangan" }, 
    { "data": "jam_pulang" },  
    { "data": "keterangan_pulang" }, 
    ],
  });
});