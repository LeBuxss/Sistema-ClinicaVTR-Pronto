<?php

namespace Controllers;

use Modelos\Cadastro;
use Modelos\Clinicavtr;
use Modelos\Consulta;
use Modelos\Fichavacinal;
use Nucleo\Controller;
use Nucleo\RelatorioPdf;
use Nucleo\Sql;

/**
 * Controlador da pagina inicial.
 *
 * Rotas atendidas:
 *   /                -> index()
 *   /home            -> index()
 *   /home/sobre      -> sobre()
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home/index', [
            'titulo' => 'Inicio',
            'historico' => $this->historicoFinalizado(),
        ]);
    }

    public function relatorio(): void
    {
        $nome = $this->get('nome');
        if (!is_string($nome) || trim($nome) === '') {
            $this->redirecionar('home');
        }

        $grupos = $this->historicoFinalizado(trim($nome));
        $linhas = [];
        foreach ($grupos as $grupo) {
            $linhas[] = ['Historico' => 'Paciente: ' . $grupo['nome']];
            foreach ($grupo['registros'] as $registro) {
                $linhas[] = ['Historico' => $registro['modulo'] . ' #' . $registro['id']];
                foreach ($registro['detalhes'] as $rotulo => $valor) {
                    $texto = $rotulo . ': ' . $valor;
                    foreach (explode("\n", wordwrap($texto, 100, "\n", true)) as $trecho) {
                        $linhas[] = ['Historico' => $trecho];
                    }
                }
            }
            $linhas[] = ['Historico' => ' '];
        }

        $this->pdf(
            RelatorioPdf::conteudo('Historico Integrado da ClinicaVTR', ['Historico'], $linhas),
            'historico-clinicavtr.pdf'
        );
    }

    public function sobre(): void
    {
        $this->view('home/sobre', [
            'titulo' => 'Quem somos',
        ]);
    }

    public function pesquisar(): void
    {
        $valorBusca = $this->get('q', '');
        $termo = is_string($valorBusca) ? trim($valorBusca) : '';
        $resultados = [];

        if (mb_strlen($termo) >= 2) {
            $fontes = [
                [
                    'modelo' => new Clinicavtr(),
                    'secao' => 'Cadastro',
                    'rota' => 'clinicavtr',
                    'principal' => 'nome',
                    'campos' => ['nome' => 'Nome', 'email' => 'E-mail'],
                ],
                [
                    'modelo' => new Cadastro(),
                    'secao' => 'Atendimento',
                    'rota' => 'cadastro',
                    'principal' => 'nome',
                    'campos' => [
                        'nome' => 'Nome', 'email' => 'E-mail', 'especie' => 'Espécie',
                        'tutor' => 'Tutor', 'veterinario' => 'Veterinário', 'procedimento' => 'Procedimento',
                    ],
                ],
                [
                    'modelo' => new Consulta(),
                    'secao' => 'Consulta',
                    'rota' => 'consulta',
                    'principal' => 'nome',
                    'campos' => [
                        'nome' => 'Nome', 'especie' => 'Espécie', 'data_consulta' => 'Data da consulta',
                        'raca' => 'Raça', 'sexo' => 'Sexo', 'tutor' => 'Tutor',
                        'telefone_tutor' => 'Telefone do tutor', 'queixa_principal' => 'Queixa principal',
                        'horario_disponivel' => 'Horário',
                        'veterinario_disponivel' => 'Veterinário',
                    ],
                ],
                [
                    'modelo' => new Fichavacinal(),
                    'secao' => 'Ficha Animal',
                    'rota' => 'fichavacinal',
                    'principal' => 'nome',
                    'campos' => [
                        'nome' => 'Nome', 'vacinaaplica' => 'Tipo de vacina', 'lote' => 'Lote',
                        'dataaplicacao' => 'Data aplicada', 'dataretorno' => 'Retorno',
                        'veterinarioresponsalve' => 'Veterinário',
                    ],
                ],
            ];

            foreach ($fontes as $fonte) {
                $campos = array_keys($fonte['campos']);
                $condicoes = array_map(
                    static fn (string $campo): string => Sql::proteger($campo, 'coluna')
                        . ' LIKE ? ESCAPE ' . Sql::ESCAPE_LIKE,
                    $campos
                );
                $colunas = array_merge(['id'], $campos);
                $selecao = implode(', ', array_map(
                    static fn (string $coluna): string => Sql::proteger($coluna, 'coluna'),
                    $colunas
                ));
                $sql = 'SELECT ' . $selecao . ' FROM ' . $fonte['modelo']->tabelaProtegida()
                    . ' WHERE (' . implode(' OR ', $condicoes) . ') ORDER BY id DESC LIMIT 12';
                $registros = $fonte['modelo']->consultar(
                    $sql,
                    array_fill(0, count($campos), Sql::comoLike($termo))
                );

                foreach ($registros as $registro) {
                    $detalhes = [];
                    foreach ($fonte['campos'] as $campo => $rotulo) {
                        $valor = $registro[$campo] ?? null;
                        if ($valor !== null && trim((string) $valor) !== '') {
                            $detalhes[$rotulo] = (string) $valor;
                        }
                    }

                    $principal = trim((string) ($registro[$fonte['principal']] ?? ''));
                    $resultados[] = [
                        'secao' => $fonte['secao'],
                        'titulo' => $principal !== '' ? $principal : $fonte['secao'] . ' #' . $registro['id'],
                        'url' => $fonte['rota'] . '/ver/' . $registro['id'],
                        'detalhes' => $detalhes,
                    ];
                }
            }
        }

        $this->view('home/pesquisar', [
            'titulo' => 'Pesquisa',
            'termo' => $termo,
            'resultados' => $resultados,
        ]);
    }

    private function historicoFinalizado(?string $nomeFiltro = null): array
    {
        $fontes = [
            [
                'modelo' => new Cadastro(),
                'modulo' => 'Atendimento',
                'rota' => 'cadastro',
                'campos' => [
                    'email' => 'E-mail', 'especie' => 'Especie', 'tutor' => 'Tutor',
                    'veterinario' => 'Veterinario', 'procedimento' => 'Procedimento',
                ],
            ],
            [
                'modelo' => new Consulta(),
                'modulo' => 'Consulta',
                'rota' => 'consulta',
                'campos' => [
                    'especie' => 'Especie', 'datanascimente' => 'Nascimento',
                    'data_consulta' => 'Data da consulta', 'sexo' => 'Sexo', 'peso' => 'Peso',
                    'tutor' => 'Tutor', 'horario_disponivel' => 'Horario',
                    'veterinario_disponivel' => 'Veterinario',
                ],
            ],
            [
                'modelo' => new Fichavacinal(),
                'modulo' => 'Ficha Animal',
                'rota' => 'fichavacinal',
                'campos' => [
                    'vacinaaplica' => 'Tipo de vacina', 'lote' => 'Lote',
                    'dataaplicacao' => 'Data aplicada', 'dataretorno' => 'Retorno',
                    'veterinarioresponsalve' => 'Veterinario',
                ],
            ],
        ];
        $grupos = [];

        foreach ($fontes as $fonte) {
            $modelo = $fonte['modelo'];
            $sql = 'SELECT * FROM ' . $modelo->tabelaProtegida() . ' WHERE finalizada = ?';
            $parametros = [1];

            if ($nomeFiltro !== null) {
                $sql .= ' AND LOWER(nome) = LOWER(?)';
                $parametros[] = $nomeFiltro;
            }

            $registros = $modelo->consultar(
                $sql . ' ORDER BY id DESC',
                $parametros
            );

            foreach ($registros as $registro) {
                $nome = trim((string) ($registro['nome'] ?? ''));
                if ($nome === '') {
                    continue;
                }

                $chave = mb_strtolower($nome, 'UTF-8');
                $grupos[$chave] ??= ['nome' => $nome, 'registros' => []];

                $detalhes = [];
                foreach ($fonte['campos'] as $campo => $rotulo) {
                    $valor = $registro[$campo] ?? null;
                    if ($valor !== null && trim((string) $valor) !== '') {
                        $detalhes[$rotulo] = (string) $valor;
                    }
                }

                $grupos[$chave]['registros'][] = [
                    'id' => $registro['id'],
                    'modulo' => $fonte['modulo'],
                    'url' => $fonte['rota'] . '/ver/' . $registro['id'],
                    'detalhes' => $detalhes,
                ];
            }
        }

        ksort($grupos, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values($grupos);
    }
}
