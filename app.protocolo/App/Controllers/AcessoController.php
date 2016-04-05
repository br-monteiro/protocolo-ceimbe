<?php

/*
 * @Controller Acesso
 */
namespace App\Controllers;

use HTR\System\ControllerAbstract as Controller;
use HTR\Interfaces\ControllerInterface as CtrlInterface;
use HTR\Helpers\Access\Access;

class AcessoController extends Controller implements CtrlInterface
{
    // Model padrão usado para este Controller
    private $modelPath = 'App\\Models\\AcessoModel';
    private $access;

    /*
     * Inicia os atributos usados na View
     */
    public function __construct()
    {
        parent::__construct();
        /*
         * Nome do controller
         * USADO NOS LINKS DA CAMADA VIEW
         * EXEMPLO:
         * @Controller
         * $this->view->controller = APPDIR.'teste/'
         * 
         * @View
         *  <a href='<?=$this->view->controller;?>novo' > Novo</a>
         * 
         * @Browser
         * <a href='/teste/novo' > Novo</a>
         */
        $this->view->controller = APPDIR.'acesso/';
        // Instancia o Helper que auxilia na proteção e autenticação de usuários
        $this->access = new Access();
    }


    /*
     * Action DEFAULT
     * Atenção: Todo Controller deve conter um método 'indexAction'
     */
    public function indexAction()
    {
        $this->visualizarAction();
    }
    
    public function novoAction()
    {
        // Inicia a proteção das páginas com permissão de acesso apenas para
        // usuários autenticados com o nível 1.
        $this->view->userLoggedIn = $this->access->authenticAccess([1]);
        // Atribui título à página através do atributo padrão '$this->view->title'
        $this->view->title = 'Novo Registro';
        
        // Renderiza a página
        $this->render('form_novo');
        
    }
    
    public function editarAction()
    {
        $this->view->userLoggedIn = $this->access->authenticAccess([1,2]);
        // Instanciando o Model padrão usado.
        $model = new $this->modelPath();

        // Atribui título à página através do atributo padrão '$this->view->title'
        $this->view->title = 'Editando Registro';
        
        // Executa a consulta no Banco de Dados
        // Se o usuário logado tiver um nível diferente de administrador
        // então, retorna o id do usuário logado, senão retorna o id setado na url
        $id = $this->view->userLoggedIn['nivel'] > 1 ? $this->view->userLoggedIn['id'] : $this->getParam('id');
        $this->view->result = $model->findById($id);
                
        $this->render('form_editar');
    }
    
    public function eliminarAction()
    {
        $this->access->authenticAccess([1]);

        $model = new $this->modelPath();

        $model->remover($this->getParam('id'));
    }

    public function visualizarAction()
    {
        // Inicia a proteção das páginas com permissão de acesso apenas para
        // usuários autenticados com o nível 1.
        $this->view->userLoggedIn = $this->access->authenticAccess([1]);
        // Instanciando o Model padrão usado.
        $model = new $this->modelPath();
        // Atribui título à página através do atributo padrão '$this->view->title'
        $this->view->title = 'Lista de Todos os Usuários';

        $model->paginator($this->getParam('pagina'));
        $this->view->result = $model->getResultadoPaginator();
        $this->view->btn = $model->getNavePaginator();

        $this->render('home');
    }
    
    public function registraAction()
    {
        // Inicia a proteção das páginas com permissão de acesso apenas para
        // usuários autenticados com o nível 1.
        $this->access->authenticAccess([1]);
        // Instanciando o Model padrão usado.
        $model = new $this->modelPath();
        $model->novo();
    }
    
    public function alteraAction()
    {
        // Inicia a proteção das páginas com permissão de acesso apenas para
        // usuários autenticados com o nível 1 e 2.
        $this->access->authenticAccess([1,2]);
        // Instanciando o Model padrão usado.
        $model = new $this->modelPath();
        $model->editar();
    }
    
    public function loginAction()
    {
        // evita que o usuário acesse a página de login novamente após a autenticação
        $this->access->notAuthenticatedAccess();
        // Atribui título à página através do atributo padrão '$this->view->title'
        $this->view->title = 'Autenticação de Usuário';

        $this->render('form_login', true, 'blank');
    }
    
    public function logoutAction()
    {
        // Instanciando o Model padrão usado.
        $model = new $this->modelPath();  
        $model->logout();
        header('Location:'. APPDIR);
    }

    public function autenticaAction()
    {
        // Instanciando o Model padrão usado.
        $model = new $this->modelPath();
        $model->login();
    }

    public function mudarSenhaAction()
    {
        $this->access->breakRedirect();
        $this->view->userLoggedIn = $this->access->authenticAccess([1,2]);
        $this->view->title = "Mudando Senha";
        $this->render('form_mudar_senha');
    }

    public function mudandoSenhaAction()
    {
        $model = new $this->modelPath();
        $this->access->breakRedirect();
        $user = $this->access->authenticAccess([1,2]);
        $dados['id'] = $user['id'];
        $dados['password'] = filter_input(INPUT_POST, 'password');
        // Instanciando o Model padrão usado.
        $model->mudarSenha($dados);
    }
}
