<?php

/*
 * @Model Index
 */
namespace App\Models;

use HTR\System\ModelCRUD as CRUD;
use HTR\Helpers\Paginator\Paginator;

class IndexModel extends CRUD
{
    /*
     * Nome da entidade (tabela) usada neste Model.
     * Por padrão, é preciso fornecer o nome da entidade como string
     */
    protected $entidade = 'produtos';
    
    private $resultadoPaginator;
    private $navPaginator;
    
    /*
     * Método uaso para retornar todos os dados da tabela.
     */
    public function returnAll()
    {
        /*
         * Método padrão do sistema usado para retornar todos os dados da tabela
         */
        return $this->findAll();
    }
    
    public function paginator($pagina)
    {
        $dados = [
            'pdo' => $this->pdo,
            'entidade' => 'produtos',
            'pagina' => $pagina,
            'maxResult' => 10,
            'where' => null,
            'bindValue' => null
        ];
        
        $paginator = new Paginator($dados);
        $this->resultadoPaginator =  $paginator->getResultado();
        $this->navPaginator = $paginator->getNaveBtn();
    }
    
    public function getResultadoPaginator()
    {
        return $this->resultadoPaginator;
    }
    
    public function getNavePaginator()
    {
        return $this->navPaginator;
    }

}
