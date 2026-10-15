<?php

namespace Testes\Controllers;

use Modelos\Usuario;
use Nucleo\Database;
use Nucleo\Sessao;
use Testes\Suporte\TesteBase;

class AuthControllerTest extends TesteBase
{
    private Usuario $modelo;

    public function preparar(): void
    {
        $this->limparSessao();

        $this->recriarTabelas([
            'usuarios' => "CREATE TABLE `usuarios` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome TEXT NULL,
                        email TEXT NULL,
                        senha TEXT NULL,
                        criado_em TEXT NULL
            )",
        ]);

        $this->modelo = new Usuario();
    }

    public function testeRegistraEntraESai(): void
    {
        $registrar = $this->postar('auth/registrar', [
            'nome'  => 'Ana',
            'email' => 'ana@example.com',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($registrar->redirecionouPara('auth/login'));

        $conta = $this->modelo->buscarPorEmail('ana@example.com');
        $this->assertNaoNulo($conta);

        // A senha nunca fica em texto puro no banco.
        $this->assertDiferente('segredo123', $conta['senha']);
        $this->assertVerdadeiro(password_verify('segredo123', $conta['senha']));

        $login = $this->postar('auth/login', [
            'email' => 'ana@example.com',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($login->foiRedirecionado());
        $this->assertVerdadeiro(autenticado());
        $this->assertIgual($conta['id'], usuario_id());

        $sair = $this->requisitar('auth/sair');
        $this->assertVerdadeiro($sair->redirecionouPara('auth/login'));
        $this->assertFalso(autenticado());
    }

    public function testeRecusaSenhaErrada(): void
    {
        $this->modelo->criarComSenha(['email' => 'ana@example.com'], 'segredo123');

        $login = $this->postar('auth/login', [
            'email' => 'ana@example.com',
            'senha' => 'errada',
        ]);

        $this->assertVerdadeiro($login->redirecionouPara('auth/login'));
        $this->assertFalso(autenticado());
    }

    public function testeRecusaCadastroInvalido(): void
    {
        $curta = $this->postar('auth/registrar', [
            'nome'  => 'Ana',
            'email' => 'ana@example.com',
            'senha' => '123',
        ]);
        $this->assertVerdadeiro($curta->redirecionouPara('auth/registrar'));

        $semEmail = $this->postar('auth/registrar', [
            'nome'  => 'Ana',
            'email' => 'nao-e-um-email',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($semEmail->redirecionouPara('auth/registrar'));

        $this->assertIgual(0, $this->modelo->contar());
    }

    public function testeRecusaEmailRepetido(): void
    {
        $this->modelo->criarComSenha(['email' => 'ana@example.com'], 'segredo123');

        $repetido = $this->postar('auth/registrar', [
            'nome'  => 'Ana',
            'email' => 'ana@example.com',
            'senha' => 'outrasenha',
        ]);

        $this->assertVerdadeiro($repetido->redirecionouPara('auth/registrar'));
        $this->assertIgual(1, $this->modelo->contar());
    }

    public function testeRecusaLoginSemToken(): void
    {
        $this->modelo->criarComSenha(['email' => 'ana@example.com'], 'segredo123');

        $login = $this->postarSemToken('auth/login', [
            'email' => 'ana@example.com',
            'senha' => 'segredo123',
        ]);

        $this->assertVerdadeiro($login->foiRedirecionado());
        $this->assertFalso(autenticado());
    }
}
