<?php
// ajax/detalhes_crianca.php
error_reporting(0);
session_start();
require_once '../config/database.php';
require_once '../controllers/CriancasController.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID não fornecido');
    }
    
    $criancaId = (int)$_GET['id'];
    $criancasController = new CriancasController();
    $crianca = $criancasController->getById($criancaId);
    
    if (!$crianca) {
        throw new Exception('Criança não encontrada');
    }
    
    echo json_encode([
        'success' => true,
        'crianca' => $crianca
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>