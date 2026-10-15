<?php

namespace Controllers;

use Modelos\Usuario;
use Nucleo\Controller;
use Nucleo\Sessao;
use Nucleo\Validador;

class AuthController extends Controller
{
    private Usuario $modelo;

    public function __construct()
    {
        $this->modelo = new Usuario();
    }

    /** GET e POST /auth/login */
    public function login(): void
    {
        if ($this->ehPost()) {
            $this->exigirTokenValido();

            $email = (string) $this->post('email', '');
            $senha = (string) $this->post('senha', '');

            $registro = $this->modelo->autenticar($email, $senha);

            if ($registro !== null) {
                // Troca o id da sessao: sem isso um id capturado antes
                // do login continuaria valendo depois ("session fixation").
                Sessao::regenerar();
                Sessao::definir('autenticacao_id', $registro['id']);
                Sessao::definir('usuario_id', $registro['id']);

                $this->mensagem('sucesso', 'Bem-vindo!');
                $this->redirecionar();
            }

            Sessao::guardarEntrada(['email' => $email]);
            $this->mensagem('erro', 'E-mail ou senha invalidos.');
            $this->redirecionar('auth/login');
        }

        // Template proprio: a tela de login nao mostra o menu lateral.
        $this->view('auth/login', ['titulo' => 'Entrar'], 'template/layout-login');
    }

    /** GET e POST /auth/registrar */
    public function registrar(): void
    {
        if ($this->ehPost()) {
            $this->exigirTokenValido();

            $senha = (string) $this->post('senha', '');
            $dados = [
                'nome'  => (string) $this->post('nome', ''),
                'email' => (string) $this->post('email', ''),
            ];

            $erros = (new Validador($dados + ['senha' => $senha]))
                    ->obrigatorio('nome')
                ->obrigatorio('email', 'e-mail')
                ->email('email', 'e-mail')
                ->obrigatorio('senha')
                ->minimo('senha', 6)
                ->erros();

            if ($erros === [] && $this->modelo->buscarPorEmail($dados['email']) !== null) {
                $erros['email'] = 'Este e-mail ja esta cadastrado.';
            }

            if ($erros !== []) {
                $this->voltarComErros($erros, 'auth/registrar');
            }

            // criarComSenha() aplica password_hash(): a senha nunca
            // chega ao banco em texto puro.
            $this->modelo->criarComSenha($dados, $senha);

            $this->mensagem('sucesso', 'Conta criada. Agora entre com seus dados.');
            $this->redirecionar('auth/login');
        }

        $this->view('auth/registrar', ['titulo' => 'Criar conta'], 'template/layout-login');
    }

    /** GET /auth/sair */
    public function sair(): void
    {
        Sessao::remover('autenticacao_id');
        Sessao::remover('usuario_id');
        Sessao::regenerar();

        $this->mensagem('sucesso', 'Sessao encerrada.');
        $this->redirecionar('auth/login');
    }
}
