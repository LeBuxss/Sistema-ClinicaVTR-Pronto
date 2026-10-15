<?php

namespace Controllers;

use Modelos\Consulta;
use Nucleo\Controller;
use Nucleo\RelatorioPdf;
use Nucleo\Sql;

class ConsultaController extends Controller
{
    private Consulta $modelo;

    public function __construct()
    {
        $this->modelo = new Consulta();
    }

    /** GET /consulta */
    public function index(): void
    {
        $this->view('consulta/index', [
            'titulo'    => 'Consulta',
            'registros' => $this->modelo->todos(),
        ]);
    }

    /** GET /consulta/criar */
    public function criar(): void
    {
        $this->view('consulta/formulario', [
            'titulo'   => 'Nova consulta',
            'registro' => null,
        ]);
    }

    /** POST /consulta/salvar */
    public function salvar(): void
    {
        $this->exigirFormularioValido();
        $finalizar = $this->post('finalizar') === '1';

        $dados = [
            'nome' => $this->post('nome'),
            'especie' => $this->post('especie'),
            'raca' => $this->post('raca'),
            'datanascimente' => $this->post('datanascimente'),
            'data_consulta' => $this->post('data_consulta'),
            'sexo' => $this->post('sexo'),
            'peso' => $this->post('peso'),
            'tutor' => $this->post('tutor'),
            'telefone_tutor' => $this->post('telefone_tutor'),
            'queixa_principal' => $this->post('queixa_principal'),
            'observacoes_clinicas' => $this->post('observacoes_clinicas'),
            'horario_disponivel' => $this->post('horario_disponivel'),
            'veterinario_disponivel' => $this->post('veterinario_disponivel'),
            'finalizada' => $finalizar ? 1 : 0,
        ];

        $erros = $this->modelo->validar($dados);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'consulta/criar');
        }

        $id = $this->modelo->criar($dados);

        if ($finalizar) {
            $this->mensagem('sucesso', 'Consulta finalizada e adicionada ao historico.');
            $this->redirecionar('home');
        }

        $this->mensagem('sucesso', 'Consulta criado com sucesso.');
        $this->redirecionar('consulta/ver/' . $id);
    }

    /** GET /consulta/ver/1 */
    public function ver(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('consulta/ver', [
            'titulo'   => 'Consulta',
            'registro' => $registro,
        ]);
    }

    /** GET /consulta/editar/1 */
    public function editar(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('consulta/formulario', [
            'titulo'   => 'Editar Consulta',
            'registro' => $registro,
        ]);
    }

    /** POST /consulta/atualizar/1 */
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
            'especie' => $this->post('especie'),
            'raca' => $this->post('raca'),
            'datanascimente' => $this->post('datanascimente'),
            'data_consulta' => $this->post('data_consulta'),
            'sexo' => $this->post('sexo'),
            'peso' => $this->post('peso'),
            'tutor' => $this->post('tutor'),
            'telefone_tutor' => $this->post('telefone_tutor'),
            'queixa_principal' => $this->post('queixa_principal'),
            'observacoes_clinicas' => $this->post('observacoes_clinicas'),
            'horario_disponivel' => $this->post('horario_disponivel'),
            'veterinario_disponivel' => $this->post('veterinario_disponivel'),
            'finalizada' => $finalizar ? 1 : (int) ($registroAtual['finalizada'] ?? 0),
        ];

        $erros = $this->modelo->validar($dados, $id);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'consulta/editar/' . $id);
        }

        $this->modelo->atualizar($id, $dados);

        if ($finalizar) {
            $this->mensagem('sucesso', 'Consulta finalizada e adicionada ao historico.');
            $this->redirecionar('home');
        }

        $this->mensagem('sucesso', 'Consulta atualizado com sucesso.');
        $this->redirecionar('consulta/ver/' . $id);
    }

    /**
     * GET /consulta/relatorio
     *
     * Cada campo da query string vira um filtro:
     *     /consulta/relatorio?nome=teste
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

        $filtro = $this->get('especie');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`especie` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('datanascimente');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`datanascimente` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('data_consulta');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`data_consulta` = ?';
            $parametros[] = $filtro;
        }

        $filtro = $this->get('sexo');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`sexo` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('peso');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`peso` = ?';
            $parametros[] = $filtro;
        }

        $filtro = $this->get('tutor');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`tutor` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('horario_disponivel');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`horario_disponivel` = ?';
            $parametros[] = $filtro;
        }

        $filtro = $this->get('veterinario_disponivel');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`veterinario_disponivel` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $sql = 'SELECT * FROM ' . $this->modelo->tabelaProtegida();

        if ($condicoes !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }

        $sql .= ' ORDER BY id DESC';

        $registros = $this->modelo->consultar($sql, $parametros);
        $pdf = RelatorioPdf::conteudo('Relatorio de consulta', ['id', 'nome', 'especie', 'raca', 'datanascimente', 'data_consulta', 'sexo', 'peso', 'tutor', 'telefone_tutor', 'queixa_principal', 'observacoes_clinicas', 'horario_disponivel', 'veterinario_disponivel'], $registros);

        $this->pdf($pdf, 'consulta.pdf');
    }

    /**
     * POST /consulta/excluir/1
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

        $this->mensagem('sucesso', 'Consulta excluido com sucesso.');
        $this->redirecionar('consulta');
    }
}
