<?php

/*
 * Controller Index
 */
namespace App\Controllers;

use HTR\System\ControllerAbstract as Controller;
use HTR\Interfaces\ControllerInterface as CtrlInterface;
use HTR\Helpers\Access\Access;
use App\Models\OmModel as Om;

class IndexController extends Controller implements CtrlInterface
{
    // Model padrão usado para este Controller
    private $modelPath = 'App\\Models\\RegistrosModel';
    // recebe uma instancia do Helper Access
    private $access;
    
    /*
     * Inicia os atributos usados na View
     */
    public function __construct()
    {
        parent::__construct();
        $this->view->controller = APPDIR.'index/';
        // instancia do helper Access
        $this->access = new Access();
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
}
