<?php

namespace Testes\Controllers;

use Modelos\Clinicavtr;
use Nucleo\Database;
use Nucleo\Sessao;
use Testes\Suporte\TesteBase;

class AuthClinicavtrControllerTest extends TesteBase
{
    private Clinicavtr $modelo;

    public function preparar(): void
    {
        $this->limparSessao();

        $this->recriarTabelas([
            'clinicavtr' => "CREATE TABLE `clinicavtr` (
                id INT AUTO_INCREMENT PRIMARY KEY,
                campo TEXT NULL,
                nome TEXT NULL,
                cpf VARCHAR(14) NULL,
                email TEXT NULL,
                senha TEXT NULL
            )",
        ]);

        $this->modelo = new Clinicavtr();
    }

    public function testeRegistraEntraESai(): void
    {
        $formulario = $this->requisitar('auth-clinicavtr/registrar');
        $this->assertContem('Nome', $formulario->html);
        $this->assertContem('name="nome"', $formulario->html);
        $this->assertContem('CPF', $formulario->html);
        $this->assertContem('name="cpf"', $formulario->html);

        $registrar = $this->postar('auth-clinicavtr/registrar', [
            'nome' => 'Ana Silva',
            'email' => 'ana@example.com',
            'cpf' => '529.982.247-25',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($registrar->redirecionouPara('auth-clinicavtr/login'));

        $conta = $this->modelo->buscarPorEmail('ana@example.com');
        $this->assertNaoNulo($conta);
        $this->assertIgual('Ana Silva', $conta['nome']);
        $this->assertIgual('529.982.247-25', $conta['cpf']);

        // A senha nunca fica em texto puro no banco.
        $this->assertDiferente('segredo123', $conta['senha']);
        $this->assertVerdadeiro(password_verify('segredo123', $conta['senha']));

        $login = $this->postar('auth-clinicavtr/login', [
            'email' => 'ana@example.com',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($login->foiRedirecionado());
        $this->assertVerdadeiro(autenticado('clinicavtr'));
        $this->assertIgual($conta['id'], usuario_id('clinicavtr'));

        $sair = $this->requisitar('auth-clinicavtr/sair');
        $this->assertVerdadeiro($sair->redirecionouPara('auth-clinicavtr/login'));
        $this->assertFalso(autenticado('clinicavtr'));
    }

    public function testeRecusaSenhaErrada(): void
    {
        $this->modelo->criarComSenha(['email' => 'ana@example.com'], 'segredo123');

        $login = $this->postar('auth-clinicavtr/login', [
            'email' => 'ana@example.com',
            'senha' => 'errada',
        ]);

        $this->assertVerdadeiro($login->redirecionouPara('auth-clinicavtr/login'));
        $this->assertFalso(autenticado('clinicavtr'));
    }

    public function testeRecusaCadastroInvalido(): void
    {
        $curta = $this->postar('auth-clinicavtr/registrar', [
            'email' => 'ana@example.com',
            'senha' => '123',
        ]);
        $this->assertVerdadeiro($curta->redirecionouPara('auth-clinicavtr/registrar'));

        $semEmail = $this->postar('auth-clinicavtr/registrar', [
            'email' => 'nao-e-um-email',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($semEmail->redirecionouPara('auth-clinicavtr/registrar'));

        $semCpf = $this->postar('auth-clinicavtr/registrar', [
            'nome' => 'Ana Silva',
            'email' => 'ana@example.com',
            'cpf' => '',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($semCpf->redirecionouPara('auth-clinicavtr/registrar'));

        $cpfInvalido = $this->postar('auth-clinicavtr/registrar', [
            'nome' => 'Ana Silva',
            'email' => 'ana@example.com',
            'cpf' => '111.111.111-11',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($cpfInvalido->redirecionouPara('auth-clinicavtr/registrar'));

        $semNome = $this->postar('auth-clinicavtr/registrar', [
            'email' => 'ana@example.com',
            'cpf' => '529.982.247-25',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($semNome->redirecionouPara('auth-clinicavtr/registrar'));

        $this->assertIgual(0, $this->modelo->contar());
    }

    public function testeRecusaEmailRepetido(): void
    {
        $this->modelo->criarComSenha(['email' => 'ana@example.com'], 'segredo123');

        $repetido = $this->postar('auth-clinicavtr/registrar', [
            'nome' => 'Ana Silva',
            'email' => 'ana@example.com',
            'cpf' => '529.982.247-25',
            'senha' => 'outrasenha',
        ]);

        $this->assertVerdadeiro($repetido->redirecionouPara('auth-clinicavtr/registrar'));
        $this->assertIgual(1, $this->modelo->contar());
    }

    public function testeRecusaLoginSemToken(): void
    {
        $this->modelo->criarComSenha(['email' => 'ana@example.com'], 'segredo123');

        $login = $this->postarSemToken('auth-clinicavtr/login', [
            'email' => 'ana@example.com',
            'senha' => 'segredo123',
        ]);

        $this->assertVerdadeiro($login->foiRedirecionado());
        $this->assertFalso(autenticado('clinicavtr'));
    }

    /**
     * O CRUD /clinicavtr tambem recebe e-mail e senha: grava o hash,
     * mantem a senha quando o campo vem em branco, recusa e-mail
     * repetido, e a conta cadastrada ali consegue entrar.
     */
    public function testeCrudGravaEmailESenha(): void
    {
        $salvar = $this->postar('clinicavtr/salvar', [
            'nome' => 'Teste',
            'cpf' => '529.982.247-25',
            'email' => 'bia@example.com',
            'senha' => 'segredo123',
        ]);

        $registro = $this->modelo->buscarPorEmail('bia@example.com');
        $this->assertNaoNulo($registro, 'O salvar() do CRUD deve gravar o e-mail');
        $this->assertVerdadeiro($salvar->redirecionouPara('clinicavtr/ver/' . $registro['id']));
        $this->assertDiferente('segredo123', $registro['senha']);
        $this->assertVerdadeiro(password_verify('segredo123', (string) $registro['senha']));

        $atualizar = $this->postar('clinicavtr/atualizar/' . $registro['id'], [
            'nome' => 'Teste',
            'cpf' => '529.982.247-25',
            'email' => 'bia@example.com',
            'senha' => '',
        ]);
        $this->assertVerdadeiro($atualizar->redirecionouPara('clinicavtr/ver/' . $registro['id']));
        $this->assertIgual($registro['senha'], $this->modelo->buscar($registro['id'])['senha']);

        $repetido = $this->postar('clinicavtr/salvar', [
            'nome' => 'Teste',
            'cpf' => '529.982.247-25',
            'email' => 'bia@example.com',
            'senha' => 'outrasenha',
        ]);
        $this->assertVerdadeiro($repetido->redirecionouPara('clinicavtr/criar'));
        $this->assertIgual(1, $this->modelo->contar());

        $this->limparSessao();

        $login = $this->postar('auth-clinicavtr/login', [
            'email' => 'bia@example.com',
            'senha' => 'segredo123',
        ]);
        $this->assertVerdadeiro($login->foiRedirecionado());
        $this->assertVerdadeiro(autenticado('clinicavtr'));
    }
}
