<?php

/*
 * @Model Acesso
 */
namespace App\Models;

use HTR\System\ModelCRUD as CRUD;
use HTR\Helpers\Mensagem\Mensagem as msg;
use HTR\Helpers\Paginator\Paginator;
use HTR\Helpers\Session\Session;
use HTR\Helpers\Criptografia\Criptografia as Cripto;
use Respect\Validation\Validator as v;

class AcessoModel extends CRUD
{
    // Tabela usada neste Model
    protected $entidade = ENTLOG;
    
    protected $id;
    protected $username;
    protected $password;
    protected $name;
    protected $email;
    protected $nivel;
    protected $active;
    protected $time;

    // Recebe o resultado da consulta feita no Banco de Dados
    private $resultadoPaginator;
    // Recebe o Array de links da navegação da paginação
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
            'entidade' => $this->entidade,
            'pagina' => $pagina,
            'maxResult' => 20,
            //'where' => 'nome LIKE ?',
            //'bindValue' => [0 => '%MONTEIRO%']
        ];
        
        $paginator = new Paginator($dados);
        $this->resultadoPaginator =  $paginator->getResultado();
        $this->navPaginator = $paginator->getNaveBtn();
    }
    
    public function novo()
    {
        // Seta automaticamente os atributos necessários
        $this->startSeters()
            // Valida os Dados enviados através do formulário
            ->validaPassword()
            ->validaUsername()
            ->validaName()
            ->validaEmail()
            ->validaNivel()
            // Verifica se há registro igual
            ->evitarDuplicidade();

        $dados = [
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'nivel' => $this->getNivel(),
            COLMOP => 1,
            'active' => 1, // 1-ativo; 0-inativo  Default : 1
            'created_at' => $this->getTime(),
            'updated_at' => $this->getTime()
        ];

        if (parent::novo($dados)) {
            msg::showMsg('111', 'success');
        } else {
            msg::showMsg('000', 'danger');
        }
    }
    
    public function editar()
    {
        // Seta automaticamente os atributos necessários
        $this->startSeters()
            // Valida os Dados enviados através do formulário
            ->validaId()
            ->validaUsername()
            ->validaName()
            ->validaEmail()
            ->validaNivel()
            // Verifica se há registro igual
            ->evitarDuplicidade();

        $dados = [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'nivel' => $this->getNivel(),
            'active' => $this->getActive(), // 1-ativo; 0-inativo  Default : 1
            'updated_at' => $this->getTime()
        ];

        if ($this->getPassword()) {
            $this->validaPassword();
            $dados['password'] = $this->getPassword();
        }

        // Verifica se há uma sessão iniciada
        if (!isset($_SESSION['userId'])) {
            $session = new Session();
            $session->startSession();
        }
        // consulta dados o usuário logado
        $user = $this->findById($_SESSION['userId']);
        if ($user['nivel'] == 1) {
            // Compara o ID do usuário logado com o do enviado pelo formulário
            if ($user['id'] != $this->getId()) {
                $dados['trocar_senha'] = 1;
            }
        } else {
            // Para usuário com o nível diferente de 1-Addministrador
            $this->setId($user['id']);
            $dados['active'] = $user['active'];
            $dados['nivel'] = $user['nivel'];
            $dados['om'] = $user['om'];
        }
        if (parent::editar($dados, $this->getId())) {
            msg::showMsg('001', 'success');
        }
    }
    
    public function remover($id)
    {
        if (parent::remover($id)) {
            header('Location: '.APPDIR.'acesso/visualizar/');
        }
    }
    
    public function findById($id)
    {
        $cripto = new Cripto;
        $value = parent::findById($id);
        
        if ($value) {
            // decodifica os campos de USERNAME e EMAIL
            $value['username'] = $cripto->decode($value['username']);
            $value['email'] = $cripto->decode($value['email']);
            return $value;
        }

        msg::showMsg('Este registro não foi encontrado. Você será redirecionado em 5 segundos.'
            . '<meta http-equiv="refresh" content="0;URL='.APPDIR.'acesso" />', 'danger', false);
    }
    
    /*
     * Método usado para alterar a senha do usuário no primeiro acesso
     */
    public function mudarSenha(array $dados)
    {
        $this->setTime()
            ->setPassword($dados['password'])
            ->validaPassword();
        
        $dadosAlt = [
            'password' => $this->getPassword(),
            COLMOP => 0,
            'updated_at' => $this->getTime()
        ];

        if (parent::editar($dadosAlt, $dados['id'])) {
            msg::showMsg('A senha foi alterada com sucesso! '
                . 'Você será redirecionado para a página inicial em 5 segundos.'
                . '<meta http-equiv="refresh" content="5;URL='.APPDIR.'" />', 'success');
        }
    }

    /*
     * Evita o registro de dados repetidos no Banco de Dados
     */
    private function evitarDuplicidade()
    {
        /// Evita a duplicidade de registros
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->entidade} WHERE id != ? AND name = ?");
        $stmt->bindValue(1, $this->getId());
        $stmt->bindValue(2, $this->getName());
        $stmt->execute();
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            msg::showMsg('Já existe um registro com este Nome.'
                . '<script>focusOn("name")</script>', 'warning');
        }

        $stmt = $this->pdo->prepare("SELECT * FROM {$this->entidade} WHERE id != ? AND email = ?");
        $stmt->bindValue(1, $this->getId());
        $stmt->bindValue(2, $this->getEmail());
        $stmt->execute();
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            msg::showMsg('Já existe um registro com este E-mail.'
                . '<script>focusOn("email")</script>', 'warning');
        }

        $stmt = $this->pdo->prepare("SELECT * FROM {$this->entidade} WHERE id != ? AND username = ?");
        $stmt->bindValue(1, $this->getId());
        $stmt->bindValue(2, $this->getUsername());
        $stmt->execute();
        if ($stmt->fetch(\PDO::FETCH_ASSOC)) {
            msg::showMsg('O Login indicado não pode ser usado. Por favor, escolha outro Login.'
                . '<script>focusOn("username")</script>', 'warning');
        }
    }

    /*
     * Método de Login no sistema
     */
    public function login()
    {
        // Recebe o valor enviado pelo formulário de login
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        
        // Verifica se todos os campos foram preenchidos
        if ($username && $password) {
            $cripto = new Cripto;
            // cripitografa os dados enviados
            $username = $cripto->encode($username);
            // consulta se existe um susário registrado com o USERNAME fornecido
            $result = $this->findByUsername($username);

            if (!$result) {            
                // retorna a mensagem de dialogo
                msg::showMsg('<strong>Usuário Inválido.</strong>'
                    . ' Verifique se digitou corretamente.'
                    . '<script>focusOn("username");</script>', 'warning');
            }

            if ($result['active'] === '2') {            
                // retorna a mensagem de dialogo
                msg::showMsg('<strong>Usuário Bloqueado!</strong><br>'
                    . ' Consulte o Administrador do Sistema para mais informações.'
                    . '<br><style>body{background-color:#CD2626;</style>'
                    . ADCONT, 'danger');
            }

            // verifica a autenticidade da senha
            if ($cripto->passVerify($password, $result['password'])) {
                // Caso seja um usuário autêntico, inicia a sessão
                $this->registerSession($result);
                return true; // stop script
            } else {
                // retorna a mensagem de dialogo
                msg::showMsg('<strong>Senha Inválida.</strong>'
                    . ' Verifique se digitou corretamente.'
                    . '<script>focusOn("password");</script>', 'warning');
            }
        }
        // retorna a mensagem de dialogo
        msg::showMsg('Todos os campos são preenchimento obrigatório.', 'danger');
    }
    
    /*
     * Método usado para auxialiar a autenticação de usuário
     * Inicia a Sessão
     */
    private function registerSession($dados)
    {
        $session = new Session();
        $session->startSession();
        $_SESSION['token'] = $session->getToken();
        $_SESSION['userId'] = $dados['id'];
        echo '<meta http-equiv="refresh" content="0;URL='.APPDIR.'" />'
            . '<script>window.location = "'.APPDIR.'"; </script>';
    }
    
    /*
     * Método usado para deslogar usuário
     */
    public function logout()
    {
        $session = new Session();
        return $session->stopSession(); 
    }


    /*
     * Seta os valores aos atributos
     */
    private function startSeters()
    {
        // Seta todos os valores
        $this->setTime(time())
            ->setId(filter_input(INPUT_POST, 'id'))
            ->setUsername(filter_input(INPUT_POST, 'username'))
            ->setPassword(filter_input(INPUT_POST, 'password'))
            ->setName(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS))
            ->setEmail(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))
            ->setNivel(filter_input(INPUT_POST, 'nivel', FILTER_SANITIZE_NUMBER_INT))
            ->setActive(filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT));
        return $this;
    }

    /// Seters
    private function setId($value = null)
    {
        $this->id = !empty($value) ? $value : $this->getTime() ;
        return $this;
    }

    public function getResultadoPaginator()
    {
        return $this->resultadoPaginator;
    }
    
    public function getNavePaginator()
    {
        return $this->navPaginator;
    }

    // Validação
    private function validaId()
    {
        $value = v::int()->validate($this->getId());
        if (!$value) {
            msg::showMsg('O ID deve ser um número inteiro válido.', 'danger');
        }
        return $this;
    }

    private function validaUsername()
    {
        $value = v::string()->notEmpty()->validate($this->getUsername());
        if (!$value) {
            msg::showMsg('O campo Login deve ser preenchido corretamente.'
                . '<script>focusOn("username");</script>', 'danger');
        }

        $this->criptoVar('username', $this->getUsername());

        return $this;
    }

    private function validaPassword()
    {
        $value = v::string()->notEmpty()->length(8, null)->validate($this->getPassword());
        if (!$value) {
            msg::showMsg('O campo Senha deve ser preenchido corretamente'
                . ' com no <strong>mínimo 8 caracteres</strong>.'
                . '<script>focusOn("password");</script>', 'danger');
        }
        
        $this->criptoVar('password', $this->getPassword(), true);
        
        return $this;
    }

    private function validaName()
    {
        $value = v::string()->notEmpty()->validate($this->getName());
        if (!$value) {
            msg::showMsg('O campo Nome deve ser preenchido corretamente.'
                . '<script>focusOn("name");</script>', 'danger');
        }
        return $this;
    }

    private function validaEmail()
    {
        $value = v::email()->notEmpty()->validate($this->getEmail());
        if (!$value) {
            msg::showMsg('O campo E-mail deve ser preenchido corretamente.'
                . '<script>focusOn("email");</script>', 'danger');
        }
        $this->criptoVar('email', $this->getEmail());
        return $this;
    }

    private function validaNivel()
    {
        $value = v::int()->notEmpty()->validate($this->getNivel());
        if (!$value) {
            msg::showMsg('O campo Nível de Acesso deve ser deve ser preenchido corretamente.'
                    . '<script>focusOn("nivel");</script>', 'danger');
        }
        return $this;
    }
    
    private function criptoVar($attribute, $value, $password = false)
    {
        $cripto = new Cripto;
        if (!$password) {
            $this->$attribute = $cripto->encode($value);
        } else {
            $this->$attribute = $cripto->passHash($value);
        }
        return $this;
    }
}
