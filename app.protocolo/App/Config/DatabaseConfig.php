<?php

/*
 * @file DatabaseConfig.php
 * @version 0.1
 * - Class que configura as diretrizes para conexão com o Banco de Dados
 */
namespace App\Config;

class DatabaseConfig
{
    public $db = [
            // base64_encode;
            'server'=>'bG9jYWxob3N0', // localhost
            'dbname'=>'dGVzdGU=', // teste
            'username'=>'cm9vdA==', // root
            'password'=>'',
            'options'=>[\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"],
            // Altere este campo apenas se for usar a Base de Dados Sqlite
            // Default Value : null
            'sqlite' => 'protocolo.db'
    ];
}
