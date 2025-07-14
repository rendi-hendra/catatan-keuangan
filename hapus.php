<?php
session_start();
include 'config.php';

$id = $_GET['id'];
$user_id = $_SESSION['id'];

// Hanya hapus jika data milik user yg login
$stmt = $conn->prepare("DELETE FROM catatan WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

header("Location: index.php");
