<?php
// ajax/buscar_criancas.php
error_reporting(0);
session_start();
require_once '../config/database.php';
require_once '../controllers/CriancasController.php';

header('Content-Type: application/json');

try {
    // Verificar se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Obter o termo de busca - CORREÇÃO: usar $_POST em vez de JSON
    $termo = $_POST['termo'] ?? '';
    
    if (empty($termo) || strlen($termo) < 2) {
        echo json_encode([
            'success' => true,
            'criancas' => [],
            'total' => 0,
            'message' => 'Digite pelo menos 2 caracteres'
        ]);
        exit;
    }
    
    $criancasController = new CriancasController();
    $criancas = $criancasController->searchCriancas($termo);
    
    echo json_encode([
        'success' => true,
        'criancas' => $criancas,
        'total' => count($criancas)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'criancas' => []
    ]);
}
?>