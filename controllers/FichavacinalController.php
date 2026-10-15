<?php

namespace Controllers;

use Modelos\Fichavacinal;
use Nucleo\Controller;
use Nucleo\RelatorioPdf;
use Nucleo\Sql;

class FichavacinalController extends Controller
{
    private Fichavacinal $modelo;

    public function __construct()
    {
        $this->modelo = new Fichavacinal();
    }

    /** GET /fichavacinal */
    public function index(): void
    {
        $this->view('fichavacinal/index', [
            'titulo'    => 'Ficha Animal',
            'registros' => $this->modelo->todos(),
        ]);
    }

    /** GET /fichavacinal/criar */
    public function criar(): void
    {
        $this->view('fichavacinal/formulario', [
            'titulo'   => 'Nova Ficha Animal',
            'registro' => null,
        ]);
    }

    /** POST /fichavacinal/salvar */
    public function salvar(): void
    {
        $this->exigirFormularioValido();
        $finalizar = $this->post('finalizar') === '1';

        $dados = [
            'nome' => $this->post('nome'),
            'vacinaaplica' => $this->post('vacinaaplica'),
            'lote' => $this->post('lote'),
            'dataaplicacao' => $this->post('dataaplicacao'),
            'dataretorno' => $this->post('dataretorno'),
            'veterinarioresponsalve' => $this->post('veterinarioresponsalve'),
            'finalizada' => $finalizar ? 1 : 0,
        ];

        $erros = $this->modelo->validar($dados);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'fichavacinal/criar');
        }

        $id = $this->modelo->criar($dados);

        if ($finalizar) {
            $this->mensagem('sucesso', 'Ficha Animal finalizada e adicionada ao historico.');
            $this->redirecionar('home');
        }

        $this->mensagem('sucesso', 'Ficha Animal criada com sucesso.');
        $this->redirecionar('fichavacinal/ver/' . $id);
    }

    /** GET /fichavacinal/ver/1 */
    public function ver(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('fichavacinal/ver', [
            'titulo'   => 'Ficha Animal',
            'registro' => $registro,
        ]);
    }

    /** GET /fichavacinal/editar/1 */
    public function editar(string $id): void
    {
        $registro = $this->modelo->buscar($id);

        if ($registro === null) {
            $this->naoEncontrado();
        }

        $this->view('fichavacinal/formulario', [
            'titulo'   => 'Editar Ficha Animal',
            'registro' => $registro,
        ]);
    }

    /** POST /fichavacinal/atualizar/1 */
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
            'vacinaaplica' => $this->post('vacinaaplica'),
            'lote' => $this->post('lote'),
            'dataaplicacao' => $this->post('dataaplicacao'),
            'dataretorno' => $this->post('dataretorno'),
            'veterinarioresponsalve' => $this->post('veterinarioresponsalve'),
            'finalizada' => $finalizar ? 1 : (int) ($registroAtual['finalizada'] ?? 0),
        ];

        $erros = $this->modelo->validar($dados, $id);

        if ($erros !== []) {
            $this->voltarComErros($erros, 'fichavacinal/editar/' . $id);
        }

        $this->modelo->atualizar($id, $dados);

        if ($finalizar) {
            $this->mensagem('sucesso', 'Ficha Animal finalizada e adicionada ao historico.');
            $this->redirecionar('home');
        }

        $this->mensagem('sucesso', 'Ficha Animal atualizada com sucesso.');
        $this->redirecionar('fichavacinal/ver/' . $id);
    }

    /**
     * GET /fichavacinal/relatorio
     *
     * Cada campo da query string vira um filtro:
    *     /fichavacinal/relatorio?vacinaaplica=teste
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

        $filtro = $this->get('vacinaaplica');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`vacinaaplica` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('lote');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`lote` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('dataaplicacao');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`dataaplicacao` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('dataretorno');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`dataretorno` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $filtro = $this->get('veterinarioresponsalve');
        if (is_scalar($filtro) && (string) $filtro !== '') {
            $condicoes[] = '`veterinarioresponsalve` LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE;
            $parametros[] = Sql::comoLike((string) $filtro);
        }

        $sql = 'SELECT * FROM ' . $this->modelo->tabelaProtegida();

        if ($condicoes !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $condicoes);
        }

        $sql .= ' ORDER BY id DESC';

        $registros = $this->modelo->consultar($sql, $parametros);
        $colunas = ['id', 'Nome', 'Tipo de Vacina', 'lote', 'Data Aplicada', 'Retorno', 'Veterinario'];
        $linhas = array_map(static fn (array $registro): array => [
            'id' => $registro['id'],
            'Nome' => $registro['nome'],
            'Tipo de Vacina' => $registro['vacinaaplica'],
            'lote' => $registro['lote'],
            'Data Aplicada' => $registro['dataaplicacao'],
            'Retorno' => $registro['dataretorno'],
            'Veterinario' => $registro['veterinarioresponsalve'],
        ], $registros);
        $pdf = RelatorioPdf::conteudo('Relatorio de Ficha Animal', $colunas, $linhas);

        $this->pdf($pdf, 'fichavacinal.pdf');
    }

    /**
     * POST /fichavacinal/excluir/1
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

        $this->mensagem('sucesso', 'Ficha Animal excluida com sucesso.');
        $this->redirecionar('fichavacinal');
    }
}
