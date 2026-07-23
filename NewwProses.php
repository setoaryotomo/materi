<?php
// proses.php
// Simpan data ke MySQL lalu tampilkan semua data nasabah (poin e)

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama          = trim($_POST['nama'] ?? '');
    $tempat_lahir  = trim($_POST['tempat_lahir'] ?? '');
    $tanggal_lahir = trim($_POST['tanggal_lahir'] ?? ''); // format dd/mm/yyyy dari form
    $penghasilan   = trim($_POST['penghasilan'] ?? '');

    // Validasi server-side (jangan hanya andalkan JS)
    if ($nama === '') {
        header('Location: index.php');
        exit;
    }

    // Ubah tanggal dd/mm/yyyy -> yyyy-mm-dd untuk kolom DATE
    $tanggal_sql = null;
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $tanggal_lahir, $m)) {
        $tanggal_sql = "{$m[3]}-{$m[2]}-{$m[1]}";
    }

    // Hilangkan koma format ribuan sebelum simpan sebagai angka
    $penghasilan_angka = (int) str_replace(',', '', $penghasilan);

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO nasabah (nama, tempat_lahir, tanggal_lahir, penghasilan) VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssi", $nama, $tempat_lahir, $tanggal_sql, $penghasilan_angka);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect supaya refresh tidak insert dobel
    header('Location: proses.php');
    exit;
}

// Ambil semua data dari database
$data = [];
$result = mysqli_query($koneksi, "SELECT * FROM nasabah ORDER BY id ASC");
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Hasil Data Nasabah</title>
<style>
    body{font-family:Arial, sans-serif; background:#f2f2f2;}
    table{border-collapse:collapse; width:700px; margin:40px auto; background:#fff;}
    table, th, td{border:1px solid #333;}
    th, td{padding:8px; text-align:center;}
    th{background:#ddd;}
    .link-kembali{display:block; width:700px; margin:0 auto; text-align:left;}
    a{color:#0066cc; text-decoration:none;}
</style>
</head>
<body>

<table>
    <tr>
        <th>No.</th>
        <th>Nama</th>
        <th>Tempat Lahir</th>
        <th>Tanggal Lahir</th>
        <th>Penghasilan</th>
    </tr>
    <?php if (empty($data)): ?>
        <tr><td colspan="5">Belum ada data</td></tr>
    <?php else: ?>
        <?php foreach ($data as $i => $row): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['tempat_lahir']) ?></td>
            <td><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></td>
            <td>Rp. <?= number_format($row['penghasilan'], 0, ',', ',') ?>,-</td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<div class="link-kembali" style="width:700px; margin:0 auto;">
    <a href="index.php">&lt;&lt; Link Kembali</a>
</div>

</body>
</html>
