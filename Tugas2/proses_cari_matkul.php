<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['status'] != 'admin') {
    header("Location: index.php");
    exit();
}

require 'koneksi.php';

// Get search parameters
$idmatkul = isset($_POST['idmatkul']) ? $koneksi->real_escape_string($_POST['idmatkul']) : '';
$namamatkul = isset($_POST['namamatkul']) ? $koneksi->real_escape_string($_POST['namamatkul']) : '';

// Build query
$where = [];
if (!empty($idmatkul)) {
    $where[] = "idmatkul LIKE '%$idmatkul%'";
}
if (!empty($namamatkul)) {
    $where[] = "namamatkul LIKE '%$namamatkul%'";
}

$query = "SELECT * FROM matkul";
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$query .= " ORDER BY idmatkul";

$result = $koneksi->query($query);

if ($result->num_rows > 0) {
    echo '<div class="card">';
    echo '<div class="card-body">';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="thead-dark">
            <tr>
                <th>No.</th>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Jenis</th>
                <th>Semester</th>
                <th>Aksi</th>
            </tr>
          </thead>';
    echo '<tbody>';

    $no = 1;
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($row['idmatkul']) . '</td>';
        echo '<td>' . htmlspecialchars($row['namamatkul']) . '</td>';
        echo '<td>' . htmlspecialchars($row['sks']) . '</td>';
        echo '<td>' . htmlspecialchars($row['jns']) . '</td>';
        echo '<td>' . htmlspecialchars($row['smt']) . '</td>';
        echo '<td>
                <div class="btn-group" role="group">
                    <a href="#" 
                       onclick="loadPage(\'edit_matkul&kode=' . $row['idmatkul'] . '\')" 
                       class="btn btn-warning btn-sm" 
                       data-toggle="tooltip" 
                       title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" 
                            class="btn btn-danger btn-sm btn-hapus" 
                            data-kode="' . $row['idmatkul'] . '"
                            data-toggle="tooltip" 
                            title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
              </td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    
    echo '<div class="mt-3 text-muted">
            <i class="fas fa-info-circle"></i> 
            Ditemukan ' . $result->num_rows . ' data mata kuliah
          </div>';
    
    echo '</div>';
    echo '</div>';

    echo '<script>
        $(document).ready(function() {
            $("[data-toggle=\'tooltip\']").tooltip();
            
            bindDeleteButtons();
        });
        
        function bindDeleteButtons() {
            $(\'.btn-hapus\').on(\'click\', function() {
                let kodeToDelete = $(this).data(\'kode\');
                $(\'#confirmDeleteModal\').modal(\'show\');
                
                $(\'#confirmDeleteBtn\').off(\'click\').on(\'click\', function() {
                    $.ajax({
                        url: \'hapus_matkul.php\',
                        method: \'POST\',
                        data: { idmatkul: kodeToDelete },
                        dataType: \'json\',
                        success: function(response) {
                            $(\'#confirmDeleteModal\').modal(\'hide\');
                            if (response.status === \'success\') {
                                $(\'#successModal\').modal(\'show\');
                                $(\'#successModal\').on(\'hidden.bs.modal\', function () {
                                    $(\'#searchForm\').submit();
                                });
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function() {
                            alert(\'Terjadi kesalahan saat menghapus data\');
                        }
                    });
                });
            });
        }
    </script>';
} else {
    echo '<div class="alert alert-info">
            <i class="fas fa-search"></i> Tidak ada data yang ditemukan.
          </div>';
}

$koneksi->close();
?>