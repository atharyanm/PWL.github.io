<?php
session_start();
// Cek login dan hak akses
if (!isset($_SESSION['username']) || $_SESSION['status'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Dosen</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .table-hover tbody tr:hover {
            background-color: #f5f5f5;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <a href="#" onclick="loadPage('data_dosen')" class="btn btn-secondary mb-4">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <h2 class="mb-4">
                <i class="fas fa-search"></i> Pencarian Dosen
            </h2>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-filter"></i> Filter Pencarian
                </div>
                <div class="card-body">
                    <form id="searchForm" class="form-row align-items-center">
                        <div class="col-md-4 mb-2">
                            <input type="text" name="searchTerm" id="searchTerm" 
                                   class="form-control" 
                                   placeholder="NPP atau Nama..." 
                                   required>
                        </div>
                        <div class="col-md-5 mb-2">
                            <select name="searchBy" id="searchBy" class="form-control">
                                <option value="npp">Cari berdasarkan NPP</option>
                                <option value="nama">Cari berdasarkan Nama</option>
                                <option value="homebase">Cari berdasarkan Homebase</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Hasil Pencarian -->
            <div id="searchResults" class="mt-3">
                <!-- Tabel hasil pencarian akan dimuat di sini -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data dosen ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Sukses</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Data dosen berhasil dihapus!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Fungsi Pencarian
    $('#searchForm').on('submit', function(event) {
        let nppToDelete;
        event.preventDefault();

        let searchTerm = $('#searchTerm').val();
        let searchBy = $('#searchBy').val();

        $.ajax({
            url: 'proses_cari_dosen.php',
            type: 'POST',
            data: { 
                searchTerm: searchTerm, 
                searchBy: searchBy 
            },
            beforeSend: function() {
                // Tampilkan loading
                $('#searchResults').html(`
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                `);
            },
            success: function(response) {
                $('#searchResults').html(response);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan saat mencari data!'
                });
            }
        });
    });

    // Delegasi Event untuk Tombol Hapus
    $(document).on('click', '.btn-hapus', function() {
        nppToDelete = $(this).data('npp');
        $('#confirmDeleteModal').modal('show');
    });

    $('#confirmDeleteBtn').on('click', function() {
        $.ajax({
            url: 'hapus_dosen.php',
            type: 'POST',
            data: { npp: nppToDelete },
            dataType: 'json',
            success: function(response) {
                $('#confirmDeleteModal').modal('hide');
                if (response.status === 'success') {
                    setTimeout(function() {
                        $('#successDeleteModal').modal('show');
                    }, 500);
                } else {
                    $('#errorMessage').text(response.message);
                    $('#errorModal').modal('show');
                }
            },
            error: function() {
                $('#confirmDeleteModal').modal('hide');
                $('#errorMessage').text('Terjadi kesalahan saat menghapus data.');
                $('#errorModal').modal('show');
            }
        });
    });

    // Refresh search results after successful delete
    $('#successDeleteModal').on('hidden.bs.modal', function() {
        $('#searchForm').submit();
    });
});
</script>
</body>
</html>