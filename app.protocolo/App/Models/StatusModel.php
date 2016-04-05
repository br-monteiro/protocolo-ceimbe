<?php

 /**
 * @Model Status
 * @Version 0.1
 */
namespace App\Models;

use HTR\System\ModelCRUD as CRUD;
use HTR\Helpers\Mensagem\Mensagem as msg;
use HTR\Helpers\Paginator\Paginator;
use Respect\Validation\Validator as v;

class StatusModel extends CRUD
{
    /*
     * Nome da entidade (tabela) usada neste Model.
     * Por padrão, é preciso fornecer o nome da entidade como string
     */
    protected $entidade = 'status';
    protected $id;
    protected $initials;
    protected $description;

    private $resultPaginator;
    private $navePaginator;
    
    /**
     * @return array Todos os resultados exceto os da lixeira
     */
    public function findNoTrash()
    {
        $stmt = $this->pdo->prepare("SELECT initials FROM {$this->entidade} "
            . "WHERE trash = :trash ORDER BY initials ASC;");
        $stmt->bindValue(':trash', 0);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

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
        /*
         * Preparando as diretrizes da consulta
         */
        $dados = [
            'pdo' => $this->pdo,
            'entidade' => $this->entidade,
            'pagina' => $pagina,
            'maxResult' => 20,
            // USAR QUANDO FOR PARA DEMONSTRAR O RESULTADO DE UMA PESQUISA
            'orderBy' => 'initials ASC',
            'where' => 'trash = ?',
            'bindValue' => [0 => 0]
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

    /*
     * Método responsável por salvar os registros
     */
    public function novo()
    {
        // Valida dados
        $this->validateAll();
        // Verifica se há registro igual e evita a duplicação
        $this->notDuplicate();
        
        $dados = [
            'id' => $this->getId(),
            'initials' => $this->getInitials(),
            'description' => $this->getDescription(),
            'trash' => 0
        ];
        
        if (parent::novo($dados)) {
            msg::showMsg('111', 'success');
        }
    }

    /*
     * Método responsável por alterar os registros
     */
    public function editar()
    {
        // Valida dados
        $this->validateAll();
        // Verifica se há registro igual e evita a duplicação
        $this->notDuplicate();
        
        $dados = [
            'initials' => $this->getInitials(),
            'description' => $this->getDescription(),
        ];
        
        if (parent::editar($dados, $this->getId())) {
            msg::showMsg('001', 'success');
        }
    }

    /*
     * Método responsável por remover os registros do sistema
     */
    public function remover($id)
    {
        $dados = [
            'trash' => 1
        ];
        
        if (parent::editar($dados, $id)) {
            header('Location: '.APPDIR.'status/visualizar/');
        }
    }

    /*
     * Evita a duplicidade de registros no sistema
     */
    private function notDuplicate()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->entidade} WHERE id != ? AND initials = ? AND trash = ?");
        $stmt->bindValue(1, $this->getId());
        $stmt->bindValue(2, $this->getInitials());
        $stmt->bindValue(3, '0');
        $stmt->execute();
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            msg::showMsg('Já existe um registro com este(s) caractere(s) no campo ' 
                . '<strong>Sigla</strong>.'
                . '<script>focusOn("initials")</script>', 'warning');
        }
    }

    /*
     * Validação dos Dados enviados pelo formulário
     */
    private function validateAll()
    {
        // Seta todos os valores
        $this->setId(filter_input(INPUT_POST, 'id'));
        $this->setInitials(filter_input(INPUT_POST, 'initials'));
        $this->setDescription(filter_input(INPUT_POST, 'description'));

        // Inicia a Validação dos dados
        $this->validateId();
        $this->validateInitials();
        $this->validateDescription();
    }

    private function setId($value)
    {
        $this->id = $value ? : time();
        return $this;
    }

    private function validateId()
    {
        $value = v::int()->validate($this->getId());
        if (!$value) {
            msg::showMsg('O campo id deve ser preenchido corretamente.'
                . '<script>focusOn("id");</script>', 'danger');
        }
        return $this;
    }

    private function validateInitials()
    {
        $value = v::string()->notEmpty()->length(1, 20)->validate($this->getInitials());
        if (!$value) {
            msg::showMsg('O campo sigla deve ser preenchido corretamente.'
                . '<script>focusOn("initials");</script>', 'danger');
        }
        return $this;
    }

    private function validateDescription()
    {
        $value = v::string()->notEmpty()->validate($this->getDescription());
        if (!$value) {
            msg::showMsg('O campo descrição deve ser preenchido corretamente.'
                . '<script>focusOn("description");</script>', 'danger');
        }
        return $this;
    }
}
