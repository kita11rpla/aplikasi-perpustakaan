<?php
include "Koneksi.php";
$result= mysqli_query ($koneksi,"SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
    <title>Tabel Pengguna</title>
    <style>
    h2 {text-align:center;}
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <h2>Daftar Pengguna</h2>
    <table class="table table-striped table-hover table-bordered align-middle shadow-sm rounded overflow-hidden">
    <thead class="table-success">
        <tr>
            <th scope="col" class="text-center">No</th>
            <th scope="col">Nama</th>
            <th scope="col">Email</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        while ($row = mysqli_fetch_assoc($result)) : 
        ?>
        <tr>
            <td class="text-center fw-bold"><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
    
    <div class="container mb-5">
    <div class="d-flex justify-content-center align-items-center mb-4 mt-5">
        <a href="dashboard.php" class="btn btn-danger fw-bold px-5">
            Kembali
        </a>
    </div>
    </div>

</body>
</html>