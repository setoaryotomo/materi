<?php
// proses.php
// Menampilkan hasil inputan data nasabah (poin e)

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama          = htmlspecialchars(trim($_POST['nama'] ?? ''));
    $tempat_lahir  = htmlspecialchars(trim($_POST['tempat_lahir'] ?? ''));
    $tanggal_lahir = htmlspecialchars(trim($_POST['tanggal_lahir'] ?? ''));
    $penghasilan   = htmlspecialchars(trim($_POST['penghasilan'] ?? ''));

    // Validasi server-side juga (jangan hanya andalkan JS)
    if ($nama === '') {
        header('Location: index.php');
        exit;
    }

    if (!isset($_SESSION['data_nasabah'])) {
        $_SESSION['data_nasabah'] = [];
    }

    $_SESSION['data_nasabah'][] = [
        'nama'          => $nama,
        'tempat_lahir'  => $tempat_lahir,
        'tanggal_lahir' => $tanggal_lahir,
        'penghasilan'   => $penghasilan,
    ];
}

$data = $_SESSION['data_nasabah'] ?? [];
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
            <td><?= $row['nama'] ?></td>
            <td><?= $row['tempat_lahir'] ?></td>
            <td><?= $row['tanggal_lahir'] ?></td>
            <td>Rp. <?= $row['penghasilan'] ?>,-</td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<div class="link-kembali" style="width:700px; margin:0 auto;">
    <a href="index.php">&lt;&lt; Link Kembali</a>
</div>

</body>
</html>
