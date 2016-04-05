<?php

 /**
 * @Model Registros
 * @Version 0.1
 */
namespace App\Models;

use HTR\System\ModelCRUD as CRUD;
use HTR\Helpers\Paginator\Paginator;

class BuscarModel extends CRUD
{
    /*
     * Nome da entidade (tabela) usada neste Model.
     * Por padrão, é preciso fornecer o nome da entidade como string
     */
    protected $entidade = 'registros';

    private $resultPaginator;
    private $navePaginator;

    public function paginator($pagina, $busca)
    {
        /*
         * Preparando as diretrizes da consulta
         * SELECT * FROM `rtp` GROUP BY `beneficiario` ORDER BY `Registros` DESC
         */
        $dados = [
            'pdo' => $this->pdo,
            'entidade' => $this->entidade,
            'pagina' => $pagina,
            'maxResult' => 20,
            // USAR QUANDO FOR PARA DEMONSTRAR O RESULTADO DE UMA PESQUISA
            'orderBy' => 'number ASC',
            'where' => 'trash = ? '
                . 'AND (`number` LIKE ? '
                . 'OR `value` LIKE ? '
                . 'OR `docresult` LIKE ? '
                . 'OR `status` LIKE ? )'
                . 'GROUP BY `connect` ',
            'bindValue' => [
                    0 => 0,
                    1 => '%'.$busca.'%',
                    2 => '%'.$busca.'%',
                    3 => '%'.$busca.'%',
                    4 => '%'.$busca.'%',
                ]
        ];

        // Instacia o Helper que auxilia na paginação de páginas
        $paginator = new Paginator($dados);
        // Resultado da consulta
        $this->resultPaginator =  $paginator->getResultado();
        // Links para criação do menu de navegação da paginação @return array
        $this->navePaginator = $paginator->getNaveBtn();
    }

    // Acessivel para o Controller coletar os resultados
    public function getResultPaginator()
    {
        return $this->resultPaginator;
    }
    
    // Acessivel para o Controller coletar os links da paginação
    public function getNavePaginator()
    {
        return $this->navePaginator;
    }
}
