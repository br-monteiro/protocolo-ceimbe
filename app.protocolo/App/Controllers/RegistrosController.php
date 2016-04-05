<?php

/*
 * Controller Registros
 */
namespace App\Controllers;

use HTR\System\ControllerAbstract as Controller;
use HTR\Interfaces\ControllerInterface as CtrlInterface;
use HTR\Helpers\Access\Access;
use App\Models\OmModel as Om;
use App\Models\StatusModel as Status;

class RegistrosController extends Controller implements CtrlInterface
{
    // Model padrão usado para este Controller
    private $modelPath = 'App\\Models\\RegistrosModel';
    // Recebe a instância do Helper Access
    private $access;
    
    /*
     * Inicia os atributos usados na View
     */
    public function __construct()
    {
        parent::__construct();
        $this->view->controller = APPDIR.'registros/';
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
        // usa o metodo 'verAction' como default
        $this->visualizarAction();
    }
    
    /**
     * Action ver
     * Usado para visualizar os registros no sistema
     */
    public function visualizarAction()
    {
        // título da página
        $this->view->title = 'Registros de Documentos';
        // instancia o Model Om
        $omModel = new Om;
        // inicia a paginação da página
        $omModel->paginator($this->getParam('pagina'));
        // alimenta os dados de Om na camada de View
        $this->view->result = $omModel->getResultPaginator();
        $this->view->btn = $omModel->getNavePaginator();
        
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
        $this->view->title = 'Formulário de Cadastro de Documentos';
        // instancia o Model Om
        $omModel = new Om;
        // alimenta os dados de Om na camada de View
        $this->view->resultOm = $omModel->findNoTrash();
        // instancia o Model Status
        $statusModel = new Status;
        // alimenta os dados de Status na camada de View
        $this->view->resultStatus = $statusModel->findNoTrash();
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
        // requisita a inserção dos dados
        $defaultModel->novo();
    }
    
    /**
     * Action editar
     * Usado para renderizar o formulário de edição de registro
     */
    public function editarAction()
    {
        // titulo da página
        $this->view->title = 'Edição de Registro';
        // instancia o Model Om
        $omModel = new Om;
        // alimenta os dados de Om na camada de View
        $this->view->resultOm = $omModel->findNoTrash();
        // instancia o Model Status
        $statusModel = new Status;
        // alimenta os dados de Status na camada de View
        $this->view->resultStatus = $statusModel->findNoTrash();
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // realiza a busca dos dados no banco de acordo com o id informado
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
        // requisita a edição dos dados
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
    
    /**
     * Action protocolados
     * Usado para listar a lista de documentos cadastrados por OM
     */
    public function protocoladoAction()
    {
        // titulo da página
        $this->view->title = 'Lista de Documentos Protocolados por OM';
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // inicia a paginação
        $defaultModel->paginator($this->getParam('pagina'), $this->getParam('om'));
        // alimenta a camada de Views com os dados de Documentos
        $this->view->result = $defaultModel->getResultPaginator();
        // alimenta a camada de Views com os links usados na paginação
        $this->view->btn = $defaultModel->getNavePaginator();
        
        // renderiza a página de lista de registros protocolados
        $this->render('lista_protocolados');
        
    }
    
    /**
     * Action historico
     * Usado para demonstrar o histórico dos registros, bem como os respectivos status
     */
    public function historicoAction()
    {
        // titulo da página
        $this->view->title = 'Histórico de documentos';
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // inicia a paginação
        $defaultModel->paginatorHistorico($this->getParam('pagina'), $this->getParam('id'));
        // alimenta a camada de Views com os dados de Documentos
        $this->view->result = $defaultModel->getResultPaginator();
        // alimenta a camada de Views com os links usados na paginação
        $this->view->btn = $defaultModel->getNavePaginator();
        
        // renderiza a página de lista de registros protocolados
        $this->render('historico');
    }
    
    /**
     * Action novo
     * Usado para renderizar o formulário de cadastro
     */
    public function statusAction()
    {
        // título da página
        $this->view->title = 'Formulário de Inclusão de Status';
        // instancia o Model Om
        $omModel = new Om;
        // alimenta os dados de Om na camada de View
        $this->view->resultOm = $omModel->findNoTrash();
        // instancia o Model Status
        $statusModel = new Status;
        // alimenta os dados de Status na camada de View
        $this->view->resultStatus = $statusModel->findNoTrash();
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // realiza a busca dos dados no banco de acordo com o id informado
        $this->view->result = $defaultModel->findById($this->getParam('id'));
        // renderiza a página do formulário
        $this->render('form_novo_status');
    }
    
    /**
     * Action incluirStatus
     * Usado para incluir os registros com o status modificado
     */
    public function incluiStatusAction()
    {
        // instancia o Model Default deste controller
        $defaultModel = new $this->modelPath;
        // requisita a inserção dos dados
        $defaultModel->incluiStatus();
    }
}
