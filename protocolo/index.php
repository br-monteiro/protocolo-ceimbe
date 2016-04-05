<?php
/*
 * @file index.php
 * @version 0.2
 * - Arquivo responsável por iniciar o aplicatico
 */

header('Content-type: text/html; charset=UTF-8');

// Arquivo de configuração do Aplicativo
require_once '../../app.protocolo/App/Config/Config.conf.php';
// Arquivo de configuração do Sistema
require_once DRINST . 'vendor/HTR/System/ConfigSystem.conf.php';
// Inclui o autoload do Composer
require_once DRINST . 'vendor/autoload.php';

try {
    // Inicia a Aplicação
    new HTR\Init();
    
} catch(Exception $e) {
    
    echo 
    'Código: <strong>'.$e->getCode().'</strong> - Erro em <strong>'.$e->getFile().'</strong>:<strong>'.$e->getLine().'</strong><br>
    Erro informado: <strong>'.$e->getMessage().'</strong><br>
    <pre>';
    echo $e->getTraceAsString();
    echo '</pre>';

}
