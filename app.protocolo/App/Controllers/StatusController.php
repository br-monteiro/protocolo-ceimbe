<?php

/*
 * Controller Status
 */
namespace App\Controllers;

use HTR\System\ControllerAbstract as Controller;
use HTR\Interfaces\ControllerInterface as CtrlInterface;
use HTR\Helpers\Access\Access;

class StatusController extends Controller implements CtrlInterface
{
    // Model padrão usado para este Controller
    private $modelPath = 'App\\Models\\StatusModel';
    // Recebe a instância do Helper Access
    private $access;

    /*
     * Inicia os atributos usados na View
     */
    public function __construct()
    {
        parent::__construct();
        $this->view->controller = APPDIR.'status/';
        // instancia o helper Access
        $this->access = new Access;
        // inicia a proteção da página
        $this->view->userLoggedIn = $this->access->authenticAccess([1,2]);
    }
    
    /*
     * Action DEFAULT
     * Atenção: Todo Controller deve conter uma Action 'indexAction'
     */
    public function indexAction()
    {
        // usa o metodo 'visualizarAction' como default
        $this->visualizarAction();
    }
    
    /**
     * Action visualizar
     * Usado para visualizar os registros no sistema
     */
    public function visualizarAction()
    {
        // título da página
        $this->view->title = 'Status usados pelo sistema';
        
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // faz a requisição dos registros do banco de dados
        $defaultModel->paginator($this->getParam('pagina'));
        // alimenta a camada de Views com os resultados obtidos
        $this->view->result = $defaultModel->getResultPaginator();
        // faz a requisiçao dos links de navegaçao da paginaçao
        $this->view->btn = $defaultModel->getNavePaginator();
        // Renderiza a página 'home.phtml'
        $this->render('home');
    }
    
    /**
     * Action novo
     * Usado para renderizar o formulário de cadastro
     */
    public function novoAction()
    {
        // título da página
        $this->view->title = 'Formulário de Cadastro de Status';
        // renderiza a página do formulário
        $this->render('form_novo');
    }
    
    /**
     * Action registra
     * Usado para efetuar novos registros no banco de dados
     */
    public function registraAction()
    {
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // requisia a inserção dos dados
        $defaultModel->novo();
    }
    
    /**
     * Action editar
     * Usado para renderizar o formulário de edição de registro
     */
    public function editarAction()
    {
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // titulo da página
        $this->view->title = 'Formulário de Edição de Status';
        // requisita os dados de acordo com o id informado
        $this->view->result = $defaultModel->findById($this->getParam('id'));
        // renderiza a página do formulário
        $this->render('form_editar');
    }
    
    /**
     * Action altera
     * Usado para efetuar a alteração dos registros no banco de dados
     */
    public function alteraAction()
    {
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // requisia a edição dos dados
        $defaultModel->editar();
    }
    
    /**
     * Action eliminar
     * Usado para alterar o status da lixeira ( 0 = normal, 1 = excluido)
     */
    public function eliminarAction()
    {
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // requisia a edição dos dados
        $defaultModel->remover($this->getParam('id'));
    }
}
