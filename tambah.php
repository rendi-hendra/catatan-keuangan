<?php
session_start();
include 'config.php';

$keterangan = $_POST['keterangan'];
$jumlah     = $_POST['jumlah'];
$jenis      = $_POST['jenis'];
$tanggal    = date('Y-m-d H:i:s');
$user_id    = $_SESSION['id'];

$stmt = $conn->prepare("INSERT INTO catatan (user_id, keterangan, jumlah, jenis, tanggal) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isiss", $user_id, $keterangan, $jumlah, $jenis, $tanggal);
$stmt->execute();

header("Location: index.php");
