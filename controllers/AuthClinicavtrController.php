<?php

namespace Controllers;

use Modelos\Clinicavtr;
use Nucleo\Controller;
use Nucleo\Sessao;
use Nucleo\Validador;

class AuthClinicavtrController extends Controller
{
    private Clinicavtr $modelo;

    public function __construct()
    {
        $this->modelo = new Clinicavtr();
    }

    /** GET e POST /auth-clinicavtr/login */
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
                Sessao::definir('autenticacao_clinicavtr_id', $registro['id']);
                Sessao::definir('usuario_clinicavtr_id', $registro['id']);

                $this->mensagem('sucesso', 'Bem-vindo!');
                $this->redirecionar();
            }

            Sessao::guardarEntrada(['email' => $email]);
            $this->mensagem('erro', 'E-mail ou senha invalidos.');
            $this->redirecionar('auth-clinicavtr/login');
        }

        // Template proprio: a tela de login nao mostra o menu lateral.
        $this->view('auth/clinicavtr/login', ['titulo' => 'Entrar'], 'template/layout-login');
    }

    /** GET e POST /auth-clinicavtr/registrar */
    public function registrar(): void
    {
        if ($this->ehPost()) {
            $this->exigirTokenValido();

            $senha = (string) $this->post('senha', '');
            $dados = [
                'nome' => (string) $this->post('nome', ''),
                'email' => (string) $this->post('email', ''),
                'cpf' => (string) $this->post('cpf', ''),
            ];

            $erros = (new Validador($dados + ['senha' => $senha]))
                ->obrigatorio('nome', 'Nome')
                ->maximo('nome', 255)
                ->obrigatorio('email', 'e-mail')
                ->email('email', 'e-mail')
                ->obrigatorio('cpf', 'CPF')
                ->personalizada('cpf', $this->modelo->cpfValido($dados['cpf']), 'Informe um CPF valido.')
                ->obrigatorio('senha')
                ->minimo('senha', 6)
                ->erros();

            if ($erros === [] && $this->modelo->buscarPorEmail($dados['email']) !== null) {
                $erros['email'] = 'Este e-mail ja esta cadastrado.';
            }

            if ($erros !== []) {
                $this->voltarComErros($erros, 'auth-clinicavtr/registrar');
            }

            // criarComSenha() aplica password_hash(): a senha nunca
            // chega ao banco em texto puro.
            $this->modelo->criarComSenha($dados, $senha);

            $this->mensagem('sucesso', 'Conta criada. Agora entre com seus dados.');
            $this->redirecionar('auth-clinicavtr/login');
        }

        $this->view('auth/clinicavtr/registrar', ['titulo' => 'Criar conta'], 'template/layout-login');
    }

    /** GET /auth-clinicavtr/sair */
    public function sair(): void
    {
        Sessao::remover('autenticacao_clinicavtr_id');
        Sessao::remover('usuario_clinicavtr_id');
        Sessao::regenerar();

        $this->mensagem('sucesso', 'Sessao encerrada.');
        $this->redirecionar('auth-clinicavtr/login');
    }
}
