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

// ================================
// FILTER
// ================================
$filter_nama   = trim($_GET['filter_nama'] ?? '');
$filter_tempat = trim($_GET['filter_tempat'] ?? '');

$where  = [];
$params = [];
$types  = '';

if ($filter_nama !== '') {
    $where[]  = "nama LIKE ?";
    $params[] = "%{$filter_nama}%";
    $types   .= 's';
}
if ($filter_tempat !== '') {
    $where[]  = "tempat_lahir LIKE ?";
    $params[] = "%{$filter_tempat}%";
    $types   .= 's';
}

$whereSql = count($where) > 0 ? ('WHERE ' . implode(' AND ', $where)) : '';

// ================================
// PAGINASI
// ================================
$per_page     = 5;
$halaman_ini  = isset($_GET['halaman']) ? max(1, (int) $_GET['halaman']) : 1;
$offset       = ($halaman_ini - 1) * $per_page;

// Hitung total data (sesuai filter) untuk jumlah halaman
$sqlCount = "SELECT COUNT(*) AS total FROM nasabah {$whereSql}";
$stmtCount = mysqli_prepare($koneksi, $sqlCount);
if ($types !== '') {
    mysqli_stmt_bind_param($stmtCount, $types, ...$params);
}
mysqli_stmt_execute($stmtCount);
$totalRow   = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount));
$totalData  = (int) $totalRow['total'];
$totalHalaman = max(1, (int) ceil($totalData / $per_page));
mysqli_stmt_close($stmtCount);

// Ambil data sesuai filter + paginasi
$data = [];
$sqlData = "SELECT * FROM nasabah {$whereSql} ORDER BY id ASC LIMIT ? OFFSET ?";
$stmtData = mysqli_prepare($koneksi, $sqlData);
$typesData  = $types . 'ii';
$paramsData = array_merge($params, [$per_page, $offset]);
mysqli_stmt_bind_param($stmtData, $typesData, ...$paramsData);
mysqli_stmt_execute($stmtData);
$resultData = mysqli_stmt_get_result($stmtData);
while ($row = mysqli_fetch_assoc($resultData)) {
    $data[] = $row;
}
mysqli_stmt_close($stmtData);
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
    .filter-box{width:700px; margin:20px auto 0; background:#fff; border:1px solid #333; padding:12px;}
    .filter-box input[type=text]{padding:4px; width:180px;}
    .filter-box button{padding:5px 14px; cursor:pointer;}
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

<div class="filter-box">
    <form method="GET" action="proses.php">
        Nama:
        <input type="text" name="filter_nama" value="<?= htmlspecialchars($filter_nama) ?>" placeholder="Cari nama">
        Tempat Lahir:
        <input type="text" name="filter_tempat" value="<?= htmlspecialchars($filter_tempat) ?>" placeholder="Cari tempat lahir">
        <button type="submit">Filter</button>
        <a href="proses.php">Reset</a>
    </form>
</div>

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
            <td><?= $offset + $i + 1 ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['tempat_lahir']) ?></td>
            <td><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></td>
            <td>Rp. <?= number_format($row['penghasilan'], 0, ',', ',') ?>,-</td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php
// ================================
// LINK PAGINASI (mempertahankan filter yang aktif)
// ================================
$queryFilter = [];
if ($filter_nama !== '')   $queryFilter['filter_nama']   = $filter_nama;
if ($filter_tempat !== '') $queryFilter['filter_tempat'] = $filter_tempat;
?>
<div class="pagination">
    <?php if ($halaman_ini > 1): ?>
        <a href="?<?= http_build_query(array_merge($queryFilter, ['halaman' => $halaman_ini - 1])) ?>">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($p = 1; $p <= $totalHalaman; $p++): ?>
        <?php if ($p == $halaman_ini): ?>
            <span class="active"><?= $p ?></span>
        <?php else: ?>
            <a href="?<?= http_build_query(array_merge($queryFilter, ['halaman' => $p])) ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($halaman_ini < $totalHalaman): ?>
        <a href="?<?= http_build_query(array_merge($queryFilter, ['halaman' => $halaman_ini + 1])) ?>">Next &raquo;</a>
    <?php endif; ?>
</div>

<div class="link-kembali" style="width:700px; margin:0 auto;">
    <a href="index.php">&lt;&lt; Link Kembali</a>
</div>

</body>
</html>
