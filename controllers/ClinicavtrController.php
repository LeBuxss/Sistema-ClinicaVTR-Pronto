<?php

namespace Controllers;

use Modelos\Clinicavtr;
use Nucleo\Controller;
use Nucleo\RelatorioPdf;
use Nucleo\Sql;

class ClinicavtrController extends Controller
{
    private Clinicavtr $modelo;

    public function __construct()
    {
        $this->modelo = new Clinicavtr();
    }

    /** GET /clinicavtr */
    public function index(): void
    {
        $this->view('clinicavtr/index', [
            'titulo'    => 'Cadastro',
            'registros' => $this->modelo->todos(),
        ]);
    }

    /** GET /clinicavtr/criar */
    public function criar(): void
    {
        $this->view('clinicavtr/formulario', [
            'titulo'   => 'Novo Cadastro',
            'registro' => null,
        ]);
    }

    /** POST /clinicavtr/salvar */
    public function salvar(): void
    {
        $this->exigirFormularioValido();

        $dados = [
            'nome' => $this->post('nome'),
            'cpf' => $this->post('cpf'),
            'email' => $this->post('email'),
            'senha' => $this->post('senha'),
        ];

        $erros = $this->modelo->validar($dados);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'clinicavtr/criar');
        }

        $id = $this->modelo->criar($dados);

        $this->mensagem('sucesso', 'Cadastro criado com sucesso.');
        $this->redirecionar('clinicavtr/ver/' . $id);
    }

    /** GET /clinicavtr/ver/1 */
    public function ver(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('clinicavtr/ver', [
            'titulo'   => 'Cadastro',
            'registro' => $registro,
        ]);
    }

    /** GET /clinicavtr/editar/1 */
    public function editar(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('clinicavtr/formulario', [
            'titulo'   => 'Editar Cadastro',
            'registro' => $registro,
        ]);
    }

    /** POST /clinicavtr/atualizar/1 */
    public function atualizar(string $id): void
    {
        $this->exigirFormularioValido();

        if (!$this->modelo->existe($id)) {
            $this->naoEncontrado();
        }

        $dados = [
            'nome' => $this->post('nome'),
            'cpf' => $this->post('cpf'),
            'email' => $this->post('email'),
            'senha' => $this->post('senha'),
        ];

        $erros = $this->modelo->validar($dados, $id);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'clinicavtr/editar/' . $id);
        }

        $this->modelo->atualizar($id, $dados);

        $this->mensagem('sucesso', 'Cadastro atualizado com sucesso.');
        $this->redirecionar('clinicavtr/ver/' . $id);
    }

    /**
     * GET /clinicavtr/relatorio
     *
     * Cada campo da query string vira um filtro:
    *     /clinicavtr/relatorio?nome=teste
     */
    public function relatorio(): void
    {
        $condicoes  = [];
        $parametros = [];

        $filtro = $this->get('id');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`id` = ?';
            $parametros[] = $filtro;
        }

        $filtro = $this->get('nome');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`nome` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('cpf');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`cpf` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $sql = 'SELECT * FROM ' . $this->modelo->tabelaProtegida();

        if ($condicoes !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }

        $sql .= ' ORDER BY id DESC';

        $registros = $this->modelo->consultar($sql, $parametros);
        $colunas = ['id', 'Nome', 'CPF', 'email'];
        $linhas = array_map(static fn (array $registro): array => [
            'id' => $registro['id'],
            'Nome' => $registro['nome'],
            'CPF' => $registro['cpf'],
            'email' => $registro['email'],
        ], $registros);
        $pdf = RelatorioPdf::conteudo('Relatorio de Cadastro', $colunas, $linhas);

        $this->pdf($pdf, 'clinicavtr.pdf');
    }

    /**
     * POST /clinicavtr/excluir/1
     *
     * So aceita POST com token: um link ou um <img> em outro site
     * nao conseguem apagar registros.
     */
    public function excluir(string $id): void
    {
        $this->exigirFormularioValido();

        if (!$this->modelo->excluir($id)) {
            $this->naoEncontrado();
        }

        $this->mensagem('sucesso', 'Cadastro excluido com sucesso.');
        $this->redirecionar('clinicavtr');
    }
}
