<?php

namespace Controllers;

use Modelos\Cadastro;
use Nucleo\Controller;
use Nucleo\RelatorioPdf;
use Nucleo\Sql;

class CadastroController extends Controller
{
    private Cadastro $modelo;

    public function __construct()
    {
        $this->modelo = new Cadastro();
    }

    /** GET /cadastro */
    public function index(): void
    {
        $this->view('cadastro/index', [
            'titulo'    => 'Atendimento',
            'registros' => $this->modelo->todos(),
        ]);
    }

    /** GET /cadastro/criar */
    public function criar(): void
    {
        $this->view('cadastro/formulario', [
            'titulo'   => 'Novo Atendimento',
            'registro' => null,
        ]);
    }

    /** POST /cadastro/salvar */
    public function salvar(): void
    {
        $this->exigirFormularioValido();
        $finalizar = $this->post('finalizar') === '1';

        $dados = [
            'nome' => $this->post('nome'),
            'email' => $this->post('email'),
            'especie' => $this->post('especie'),
            'tutor' => $this->post('tutor'),
            'veterinario' => $this->post('veterinario'),
            'procedimento' => $this->post('procedimento'),
            'finalizada' => $finalizar ? 1 : 0,
        ];

        $erros = $this->modelo->validar($dados);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'cadastro/criar');
        }

        $id = $this->modelo->criar($dados);

        if ($finalizar) {
            $this->mensagem('sucesso', 'Atendimento finalizado e adicionado ao historico.');
            $this->redirecionar('home');
        }

        $this->mensagem('sucesso', 'Atendimento criado com sucesso.');
        $this->redirecionar('cadastro/ver/' . $id);
    }

    /** GET /cadastro/ver/1 */
    public function ver(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('cadastro/ver', [
            'titulo'   => 'Atendimento',
            'registro' => $registro,
        ]);
    }

    /** GET /cadastro/editar/1 */
    public function editar(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('cadastro/formulario', [
            'titulo'   => 'Editar Atendimento',
            'registro' => $registro,
        ]);
    }

    /** POST /cadastro/atualizar/1 */
    public function atualizar(string $id): void
    {
        $this->exigirFormularioValido();

        $registroAtual = $this->modelo->buscar($id);
        if ($registroAtual === null) {
            $this->naoEncontrado();
        }

        $finalizar = $this->post('finalizar') === '1';
        $dados = [
            'nome' => $this->post('nome'),
            'email' => $this->post('email'),
            'especie' => $this->post('especie'),
            'tutor' => $this->post('tutor'),
            'veterinario' => $this->post('veterinario'),
            'procedimento' => $this->post('procedimento'),
            'finalizada' => $finalizar ? 1 : (int) ($registroAtual['finalizada'] ?? 0),
        ];

        $erros = $this->modelo->validar($dados, $id);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'cadastro/editar/' . $id);
        }

        $this->modelo->atualizar($id, $dados);

        if ($finalizar) {
            $this->mensagem('sucesso', 'Atendimento finalizado e adicionado ao historico.');
            $this->redirecionar('home');
        }

        $this->mensagem('sucesso', 'Atendimento atualizado com sucesso.');
        $this->redirecionar('cadastro/ver/' . $id);
    }

    /**
     * GET /cadastro/relatorio
     *
     * Cada campo da query string vira um filtro:
     *     /cadastro/relatorio?nome=teste
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

        $filtro = $this->get('email');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`email` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('especie');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`especie` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('tutor');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`tutor` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('veterinario');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`veterinario` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('procedimento');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`procedimento` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $sql = 'SELECT * FROM ' . $this->modelo->tabelaProtegida();

        if ($condicoes !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }

        $sql .= ' ORDER BY id DESC';

        $registros = $this->modelo->consultar($sql, $parametros);
        $pdf = RelatorioPdf::conteudo('Relatorio de Atendimento', ['id', 'nome', 'email', 'especie', 'tutor', 'veterinario', 'procedimento'], $registros);

        $this->pdf($pdf, 'cadastro.pdf');
    }

    /**
     * POST /cadastro/excluir/1
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

        $this->mensagem('sucesso', 'Atendimento excluido com sucesso.');
        $this->redirecionar('cadastro');
    }
}
