<?php

 /**
 * @Model Registros
 * @Version 0.1
 */
namespace App\Models;

use HTR\System\ModelCRUD as CRUD;
use HTR\Helpers\Mensagem\Mensagem as msg;
use HTR\Helpers\Paginator\Paginator;
use Respect\Validation\Validator as v;

class RegistrosModel extends CRUD
{
    /*
     * Nome da entidade (tabela) usada neste Model.
     * Por padrão, é preciso fornecer o nome da entidade como string
     */
    protected $entidade = 'registros';
    protected $id;
    protected $connect;
    protected $number;
    protected $type;
    protected $om;
    protected $value;
    protected $docresult;
    protected $time;

    private $resultPaginator;
    private $navePaginator;

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

    public function paginator($pagina, $om)
    {
        /*
         * Preparando as diretrizes da consulta
         * SELECT * FROM `rtp` GROUP BY `beneficiario` ORDER BY `Registros` DESC
         */
        $dados = [
            'pdo' => $this->pdo,
            'entidade' => $this->entidade,
            'pagina' => $pagina,
            'maxResult' => 10,
            // USAR QUANDO FOR PARA DEMONSTRAR O RESULTADO DE UMA PESQUISA
            'orderBy' => 'number DESC',
            'where' => 'trash = ? AND om = ? GROUP BY `number` ',
            'bindValue' => [0 => 0, 1 => $om]
        ];

        // Instacia o Helper que auxilia na paginação de páginas
        $paginator = new Paginator($dados);
        // Resultado da consulta
        $this->resultPaginator =  $paginator->getResultado();
        // Links para criação do menu de navegação da paginação @return array
        $this->navePaginator = $paginator->getNaveBtn();
    }

    public function paginatorHistorico($pagina, $connect)
    {
        /*
         * Preparando as diretrizes da consulta
         */
        $dados = [
            'pdo' => $this->pdo,
            'entidade' => $this->entidade,
            'pagina' => $pagina,
            'maxResult' => 10,
            'orderBy' => 'number ASC',
            'where' => 'trash = ? AND connect = ?',
            'bindValue' => [0 => 0, 1 => $connect]
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
            'connect' => $this->getConnect(),
            'number' => $this->getNumber(),
            'type' => $this->getType(),
            'om' => $this->getOm(),
            'status' => $this->getStatus(),
            'value' => $this->getValue(),
            'docresult' => $this->getDocresult(),
            'time' => $this->getTime(),
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
            'connect' => $this->getConnect(),
            'number' => $this->getNumber(),
            'type' => $this->getType(),
            'om' => $this->getOm(),
            'status' => $this->getStatus(),
            'value' => $this->getValue(),
            'docresult' => $this->getDocresult(),
            'time' => $this->getTime()

        ];
        if (parent::editar($dados, $this->getId())) {
            msg::showMsg('001', 'success');
        }
    }

    /*
     * Método responsável por alterar os registros
     */
    public function incluiStatus()
    {
        // Valida dados
        // Seta todos os valores
        $this->setId(filter_input(INPUT_POST, 'id'));
        $this->setConnect(filter_input(INPUT_POST, 'connect'));
        $this->setTime(filter_input(INPUT_POST, 'time'));
        $this->setStatus(filter_input(INPUT_POST, 'status'));
        $this->setDocresult(filter_input(INPUT_POST, 'docresult'));

        // Inicia a Validação dos dados
        $this->validateId();
        $this->validateConnect();
        $this->validateTime();
        $this->validateStatus();
        $this->validateDocresult();
        
        // busca alguns dados a partir do connect
        $connect = $this->findByConnect($this->getConnect());
        $this->setNumber($connect['number']);
        $this->setType($connect['type']);
        $this->setOm($connect['om']);
        $this->setValue($connect['value'], true);
        
        // Verifica se há registro igual e evita a duplicação
        $this->notDuplicateStatus();

        $dados = [
            'id' => $this->getId(),
            'connect' => $this->getConnect(),
            'number' => $this->getNumber(),
            'type' => $this->getType(),
            'om' => $this->getOm(),
            'status' => $this->getStatus(),
            'value' => $this->getValue(),
            'docresult' => $this->getDocresult(),
            'time' => $this->getTime(),
            'trash' => 0

        ];
        
        if (parent::novo($dados)) {
            msg::showMsg('111', 'success');
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
            $id = $this->findById($id);
            header('Location: '.APPDIR.'registros/historico/id/'. $id['connect']);
        }
    }

    /*
     * Evita a duplicidade de registros no sistema
     */
    private function notDuplicate()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->entidade} "
            . "WHERE id != ? AND number = ? AND type = ? AND om = ? AND trash = ?");
        $stmt->bindValue(1, $this->getId());
        $stmt->bindValue(2, $this->getNumber());
        $stmt->bindValue(3, $this->getType());
        $stmt->bindValue(4, $this->getOm());
        $stmt->bindValue(5, 0);
        $stmt->execute();
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            msg::showMsg('Já existe um registro com este(s) dados', 'warning');
        }
    }
    
    private function notDuplicateStatus()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->entidade} "
            . "WHERE id != ? AND number = ? AND type = ? AND om = ? AND status = ? AND time = ? AND trash = ?");
        $stmt->bindValue(1, $this->getId());
        $stmt->bindValue(2, $this->getNumber());
        $stmt->bindValue(3, $this->getType());
        $stmt->bindValue(4, $this->getOm());
        $stmt->bindValue(5, $this->getStatus());
        $stmt->bindValue(6, $this->getTime());
        $stmt->bindValue(7, 0);
        $stmt->execute();
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            msg::showMsg('Já existe um registro com este(s) dados', 'warning');
        }
    }

    /*
     * Validação dos Dados enviados pelo formulário
     */
    private function validateAll()
    {
        // Seta todos os valores
        $this->setId(filter_input(INPUT_POST, 'id'));
        $this->setConnect(filter_input(INPUT_POST, 'connect'));
        $this->setNumber(filter_input(INPUT_POST, 'number'));
        $this->setOm(filter_input(INPUT_POST, 'om'));
        $this->setTime(filter_input(INPUT_POST, 'time'));
        $this->setType(filter_input(INPUT_POST, 'type'));
        $this->setValue(filter_input(INPUT_POST, 'value'));
        $this->setDocresult(filter_input(INPUT_POST, 'docresult'));
        $this->setStatus(filter_input(INPUT_POST, 'status'));

        // Inicia a Validação dos dados
        $this->validateId();
        $this->validateConnect();
        $this->validateNumber();
        $this->validateOm();
        $this->validateTime();
        $this->validateType();
        $this->validateValue();
        $this->validateDocresult();
        $this->validateStatus();
    }

    private function setId($value)
    {
        $this->id = $value ? : time();
        return $this;
    }

    private function setConnect($value)
    {
        $this->connect = $value ? : time();
        return $this;
    }
    
    private function setTime($value)
    {
        // armazena os valores temporários das explosões de string
        $tmp = [];
        // separa a string no caracter -
        $str = explode('-', $value);
        // separa a string no caracter /
        $tmp[] = explode('/', $str[0]);
        // separa a string no caracter :
        $tmp[] = explode(':', $str[1]);
        // junta os dois arrays
        $tmp[0] = array_merge($tmp[0], $tmp[1]);
        // converte em timestamp
        $this->time = mktime($tmp[0][3], $tmp[0][4], 0, $tmp[0][1], $tmp[0][0], $tmp[0][2]);
    }
    
    /**
     * seta os valores para Vlue
     * @param float $value
     * @param boolean $interno
     */
    private function setValue($value, $interno = null)
    {
        if ($interno) {
            // seta o valor de $value a $this->value se o valor de interno for true
            $this->value = $value;
            
        } else {
            // retira os pontos da string
            $value = str_replace('.', '', $value);
            // transforma a virgula em ponto e seta para $this->value o resultado
            $this->value = str_replace(',', '.', $value);
        }
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

    private function validateConnect()
    {
        $value = v::int()->validate($this->getConnect());
        if (!$value) {
            msg::showMsg('O campo connect deve ser preenchido corretamente.'
                . '<script>focusOn("connect");</script>', 'danger');
        }
        return $this;
    }

    private function validateNumber()
    {
        $value = v::string()->notEmpty()->length(1, 10)->validate($this->getNumber());
        if (!$value) {
            msg::showMsg('O campo Número deve ser preenchido corretamente.'
                . '<script>focusOn("number");</script>', 'danger');
        }
        return $this;
    }

    private function validateOm()
    {
        $value = v::string()->notEmpty()->length(1, 6)->validate($this->getOm());
        if (!$value) {
            msg::showMsg('O campo OM deve ser preenchido corretamente.'
                . '<script>focusOn("om");</script>', 'danger');
        }
        return $this;
    }

    private function validateTime()
    {
        $value = v::int()->validate($this->getTime());
        if (!$value) {
            msg::showMsg('O campo Data/Hora deve ser preenchido corretamente.'
                . '<script>focusOn("time");</script>', 'danger');
        }
        return $this;
    }

    private function validateType()
    {
        $value = v::string()->notEmpty()->length(1, 20)->validate($this->getType());
        if (!$value) {
            msg::showMsg('O campo Tipo deve ser preenchido corretamente.'
                . '<script>focusOn("type");</script>', 'danger');
        }
        return $this;
    }

    private function validateValue()
    {
        $value = v::float()->validate($this->getValue());
        if (!$value) {
            msg::showMsg('O campo Valor deve ser preenchido corretamente.'
                . '<script>focusOn("value");</script>', 'danger');
        }
        return $this;
    }

    private function validateDocresult()
    {
        $value = v::string()->notEmpty()->length(1, 15)->validate($this->getStatus());
        if (!$value) {
            msg::showMsg('O campo Documento Resultante deve ser preenchido corretamente.'
                . '<script>focusOn("docresult");</script>', 'danger');
        }
        return $this;
    }

    private function validateStatus()
    {
        $value = v::string()->notEmpty()->length(1, 20)->validate($this->getStatus());
        if (!$value) {
            msg::showMsg('O campo Status deve ser preenchido corretamente.'
                . '<script>focusOn("status");</script>', 'danger');
        }
        return $this;
    }
}
