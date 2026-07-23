<?php
// proses.php
// Simpan data ke MySQL lalu tampilkan semua data nasabah dengan sorting + paginasi

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

// ================================
// SORTING
// ================================
$kolom_valid = [
    'nama'          => 'nama',
    'tempat_lahir'  => 'tempat_lahir',
    'tanggal_lahir' => 'tanggal_lahir',
    'penghasilan'   => 'penghasilan',
];

$sort_by  = $_GET['sort_by'] ?? 'id';
$sort_dir = strtolower($_GET['sort_dir'] ?? 'asc');

if (!array_key_exists($sort_by, $kolom_valid) && $sort_by !== 'id') {
    $sort_by = 'id';
}
if (!in_array($sort_dir, ['asc', 'desc'])) {
    $sort_dir = 'asc';
}

$kolom_sql = $sort_by === 'id' ? 'id' : $kolom_valid[$sort_by];

// ================================
// PAGINASI
// ================================
$per_page    = 5;
$halaman_ini = isset($_GET['halaman']) ? max(1, (int) $_GET['halaman']) : 1;
$offset      = ($halaman_ini - 1) * $per_page;

// Hitung total data untuk jumlah halaman
$totalRow     = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM nasabah"));
$totalData    = (int) $totalRow['total'];
$totalHalaman = max(1, (int) ceil($totalData / $per_page));

// Ambil data sesuai sorting + paginasi
$data = [];
$sqlData = "SELECT * FROM nasabah ORDER BY {$kolom_sql} {$sort_dir} LIMIT ? OFFSET ?";
$stmtData = mysqli_prepare($koneksi, $sqlData);
mysqli_stmt_bind_param($stmtData, "ii", $per_page, $offset);
mysqli_stmt_execute($stmtData);
$resultData = mysqli_stmt_get_result($stmtData);
while ($row = mysqli_fetch_assoc($resultData)) {
    $data[] = $row;
}
mysqli_stmt_close($stmtData);

// Helper untuk membuat link sorting pada header kolom
function urlSort($kolom, $sort_by, $sort_dir, $halaman_ini) {
    $arah = ($sort_by === $kolom && $sort_dir === 'asc') ? 'desc' : 'asc';
    return '?' . http_build_query([
        'sort_by'  => $kolom,
        'sort_dir' => $arah,
        'halaman'  => $halaman_ini,
    ]);
}

function tandaSort($kolom, $sort_by, $sort_dir) {
    if ($sort_by !== $kolom) return '';
    return $sort_dir === 'asc' ? ' &#9650;' : ' &#9660;';
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
    th a{color:#000; text-decoration:none; display:block;}
    th a:hover{text-decoration:underline;}
    .link-kembali{display:block; width:700px; margin:0 auto; text-align:left;}
    a{color:#0066cc; text-decoration:none;}
    .pagination{width:700px; margin:15px auto; text-align:center;}
    .pagination a, .pagination span{
        display:inline-block; padding:5px 10px; margin:0 2px;
        border:1px solid #333; text-decoration:none; color:#333;
    }
    .pagination a:hover{background:#ddd;}
    .pagination .active{background:#333; color:#fff;}
</style>
</head>
<body>

<table>
    <tr>
        <th>No.</th>
        <th><a href="<?= urlSort('nama', $sort_by, $sort_dir, $halaman_ini) ?>">Nama<?= tandaSort('nama', $sort_by, $sort_dir) ?></a></th>
        <th><a href="<?= urlSort('tempat_lahir', $sort_by, $sort_dir, $halaman_ini) ?>">Tempat Lahir<?= tandaSort('tempat_lahir', $sort_by, $sort_dir) ?></a></th>
        <th><a href="<?= urlSort('tanggal_lahir', $sort_by, $sort_dir, $halaman_ini) ?>">Tanggal Lahir<?= tandaSort('tanggal_lahir', $sort_by, $sort_dir) ?></a></th>
        <th><a href="<?= urlSort('penghasilan', $sort_by, $sort_dir, $halaman_ini) ?>">Penghasilan<?= tandaSort('penghasilan', $sort_by, $sort_dir) ?></a></th>
    </tr>
    <?php if (empty($data)): ?>
        <tr><td colspan="5">Belum ada data</td></tr>
    <?php else: ?>
        <?php foreach ($data as $i => $row): ?>
        <tr>
            <td><?= $offset + $i + 1 ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['tempat_lahir']) ?></td>
            <td><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></td>
            <td>Rp. <?= number_format($row['penghasilan'], 0, ',', ',') ?>,-</td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<div class="pagination">
    <?php if ($halaman_ini > 1): ?>
        <a href="?<?= http_build_query(['sort_by' => $sort_by, 'sort_dir' => $sort_dir, 'halaman' => $halaman_ini - 1]) ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalHalaman; $p++): ?>
        <?php if ($p == $halaman_ini): ?>
            <span class="active"><?= $p ?></span>
        <?php else: ?>
            <a href="?<?= http_build_query(['sort_by' => $sort_by, 'sort_dir' => $sort_dir, 'halaman' => $p]) ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($halaman_ini < $totalHalaman): ?>
        <a href="?<?= http_build_query(['sort_by' => $sort_by, 'sort_dir' => $sort_dir, 'halaman' => $halaman_ini + 1]) ?>">Next &raquo;</a>
    <?php endif; ?>
</div>

<div class="link-kembali" style="width:700px; margin:0 auto;">
    <a href="index.php">&lt;&lt; Link Kembali</a>
</div>

</body>
</html>
