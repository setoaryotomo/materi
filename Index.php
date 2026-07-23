<?php
// index.php
// Halaman Form Input Data Nasabah
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Data Nasabah</title>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- jQuery UI (untuk datepicker & autocomplete) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<style>
    body{font-family:Arial, sans-serif; background:#f2f2f2;}
    table{border-collapse:collapse; width:500px; margin:40px auto; background:#fff;}
    table, th, td{border:1px solid #333;}
    td, th{padding:8px;}
    th{background:#ddd;}
    input[type=text]{width:200px; padding:4px;}
    .error-msg{color:red; font-size:12px; margin-left:8px;}
    input.error{border:1px solid red; background:#ffecec;}
    button{padding:6px 16px; cursor:pointer;}
</style>
</head>
<body>

<form id="formNasabah" action="proses.php" method="POST" autocomplete="off">
<table>
    <tr><th colspan="2">DATA NASABAH</th></tr>
    <tr>
        <td>Nama</td>
        <td>
            : <input type="text" id="nama" name="nama">
            <span class="error-msg" id="err-nama"></span>
        </td>
    </tr>
    <tr>
        <td>Tempat Lahir</td>
        <td>
            : <input type="text" id="tempat_lahir" name="tempat_lahir">
        </td>
    </tr>
    <tr>
        <td>Tanggal Lahir</td>
        <td>
            : <input type="text" id="tanggal_lahir" name="tanggal_lahir" placeholder="dd/mm/yyyy">
        </td>
    </tr>
    <tr>
        <td>Penghasilan</td>
        <td>
            : Rp. <input type="text" id="penghasilan" name="penghasilan">
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center;">
            <button type="submit" id="btnSimpan">Simpan</button>
        </td>
    </tr>
</table>
</form>

<script>
$(function() {

    // ================================
    // b) AUTOCOMPLETE Tempat Lahir
    // ================================
    var kotaIndonesia = [
        "Banjar", "Banjarmasin", "Bangkalan", "Bandung", "Bandar Lampung",
        "Bekasi", "Bogor", "Batam", "Balikpapan", "Bali",
        "Jakarta", "Jayapura", "Jambi",
        "Semarang", "Surabaya", "Surakarta", "Sukabumi", "Samarinda",
        "Medan", "Makassar", "Malang", "Manado", "Mataram",
        "Yogyakarta", "Padang", "Palembang", "Pontianak", "Palu",
        "Denpasar", "Depok", "Cirebon", "Cimahi", "Tangerang",
        "Tasikmalaya", "Kediri", "Kupang", "Gorontalo", "Ambon"
    ];

    $("#tempat_lahir").autocomplete({
        source: kotaIndonesia,
        minLength: 1
    });

    // ================================
    // c) DATEPICKER Tanggal Lahir
    // ================================
    $("#tanggal_lahir").datepicker({
        dateFormat: "dd/mm/yy",
        changeMonth: true,
        changeYear: true,
        yearRange: "1950:2020"
    });

    // ================================
    // d) FORMAT NUMERIC Penghasilan
    // ================================
    function formatRibuan(angka) {
        angka = angka.toString().replace(/\D/g, ""); // hanya angka
        if (angka === "") return "";
        return angka.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    $("#penghasilan").on("input", function() {
        var raw = $(this).val();
        $(this).val(formatRibuan(raw));
    });

    // ================================
    // a) VALIDASI FORM sebelum submit
    // ================================
    $("#formNasabah").on("submit", function(e) {
        var valid = true;
        $("#err-nama").text("");
        $("#nama").removeClass("error");

        if ($.trim($("#nama").val()) === "") {
            $("#err-nama").text("nama tidak boleh kosong");
            $("#nama").addClass("error");
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });

});
</script>

</body>
</html>
