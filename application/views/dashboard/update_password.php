<!-- /D:/laragon-6.0.0/www/perum_tsi/application/views/dashboard/update_password.php -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .update-password-card {
        max-width: 400px;
        margin: 60px auto;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        border-radius: 16px;
        padding: 32px 24px;
        background: #fff;
    }
    .update-password-card .fa-key {
        font-size: 2.5rem;
        color: #0d6efd;
        margin-bottom: 16px;
    }
    .form-label {
        font-weight: 500;
    }
</style>

<div class="update-password-card">
    <div class="text-center">
        <i class="fa-solid fa-key"></i>
        <h3 class="mb-4 mt-2">Update Password</h3>
    </div>
    <form id="updatePasswordForm">
        <div class="mb-3">
            <label for="old_password" class="form-label"><i class="fa-solid fa-lock"></i> Password Lama</label>
            <input type="password" class="form-control" id="old_password" name="old_password" placeholder="Masukkan password lama" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label"><i class="fa-solid fa-unlock"></i> Password Baru</label>
            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Masukkan password baru" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 mt-3">
            <i class="fa-solid fa-arrow-right"></i> Update Password
        </button>
    </form>
</div>

<script>
document.getElementById('updatePasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);

    fetch('<?= site_url('dashboard/act_update_password') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.message,
                showConfirmButton: false,
                timer: 2000
            });
            form.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: 'Terjadi kesalahan.'
        });
    });
});
</script>