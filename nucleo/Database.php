<?php

namespace Nucleo;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Responsavel pela conexao com o banco de dados.
 *
 * Usa o padrao "Singleton": a conexao e criada uma unica vez e reaproveitada
 * em toda a aplicacao. O banco e MySQL/MariaDB (o do XAMPP), configurado em
 * configuracoes/banco.php.
 */
class Database
{
    private static ?PDO $conexao = null;

    /**
     * Devolve a conexao PDO, criando-a na primeira chamada.
     */
    public static function conexao(): PDO
    {
        if (self::$conexao === null) {
            self::$conexao = self::criar();
        }

        return self::$conexao;
    }

    /**
     * Injeta uma conexao pronta. Usado pelos testes para apontar
     * para um banco de dados descartavel.
     */
    public static function definirConexao(?PDO $pdo): void
    {
        self::$conexao = $pdo;
    }

    /**
     * Fecha/esquece a conexao atual.
     */
    public static function desconectar(): void
    {
        self::$conexao = null;
    }

    /**
     * Monta a conexao com o MySQL configurado em configuracoes/banco.php.
     */
    private static function criar(): PDO
    {
        $c   = Config::obter('banco.mysql');
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'],
            $c['porta'],
            $c['banco'],
            $c['charset']
        );

        try {
            return new PDO($dsn, $c['usuario'], $c['senha'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Nao foi possivel conectar ao banco \"{$c['banco']}\" em {$c['host']}: "
                . $e->getMessage() . "\n"
                . "Confira se o MySQL esta iniciado (painel do XAMPP) e se usuario e senha\n"
                . 'em configuracoes/banco.php estao corretos. Depois rode: php instalar.php',
                0,
                $e
            );
        }
    }

    /**
     * Cria o banco de dados caso ele ainda nao exista.
     *
     * Sem isso seria obrigatorio abrir o phpMyAdmin e criar o banco na mao
     * antes de rodar o instalador.
     *
     * Conectamos ao servidor SEM escolher um banco (o DSN nao leva dbname),
     * criamos o banco e so entao a conexao normal consegue se conectar.
     *
     * @param string|null $banco nome do banco; por padrao o de configuracoes/banco.php
     */
    public static function criarBancoSeNaoExistir(?string $banco = null): bool
    {
        $c = Config::obter('banco.mysql');

        // O nome do banco nao pode ser parametro do PDO (e identificador,
        // nao valor), entao passa pela validacao da classe Sql.
        $nome    = Sql::identificador($banco ?? $c['banco'], 'banco de dados');
        $charset = Sql::identificador($c['charset'], 'charset');

        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $c['host'], $c['porta'], $charset);

        try {
            $servidor = new PDO($dsn, $c['usuario'], $c['senha'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $servidor->exec("CREATE DATABASE IF NOT EXISTS `{$nome}` CHARACTER SET {$charset}");
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Nao foi possivel criar o banco \"{$nome}\": " . $e->getMessage(),
                0,
                $e
            );
        }

        return true;
    }

    /**
     * Cria as tabelas lendo banco/esquema.sql, o arquivo que os comandos do
     * console mantem atualizado.
     */
    public static function migrar(): void
    {
        $arquivo = CAMINHO_RAIZ . '/banco/esquema.sql';

        if (!is_file($arquivo)) {
            throw new RuntimeException("Arquivo de esquema nao encontrado: {$arquivo}");
        }

        self::executarArquivoSql($arquivo);
        self::garantirColunasClinicavtr();
        self::garantirColunasConsulta();
    }

    /**
     * Garante compatibilidade com bancos antigos da tabela clinicavtr.
     *
     * O provider de autenticação de `Clinicavtr` usa `email` e `senha`, mas
     * esquemas antigos criavam a tabela sem essas colunas.
     */
    private static function garantirColunasClinicavtr(): void
    {
        $pdo = self::conexao();

        $existeTabela = $pdo->query(
            "SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'clinicavtr'"
        )->fetch();

        if (($existeTabela['total'] ?? 0) == 0) {
            return;
        }

        $colunas = array_map(
            static fn (array $linha): string => strtolower((string) $linha['Field']),
            $pdo->query('SHOW COLUMNS FROM `clinicavtr`')->fetchAll()
        );

        if (!in_array('email', $colunas, true)) {
            $pdo->exec('ALTER TABLE `clinicavtr` ADD COLUMN `email` VARCHAR(255) NULL AFTER `cpf`');
        }

        if (!in_array('senha', $colunas, true)) {
            $pdo->exec('ALTER TABLE `clinicavtr` ADD COLUMN `senha` VARCHAR(255) NULL AFTER `email`');
        }
    }

    private static function garantirColunasConsulta(): void
    {
        $pdo = self::conexao();
        $existeTabela = $pdo->query(
            "SELECT COUNT(*) AS total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'consulta'"
        )->fetch();

        if (($existeTabela['total'] ?? 0) == 0) {
            return;
        }

        $colunas = array_map(
            static fn (array $linha): string => strtolower((string) $linha['Field']),
            $pdo->query('SHOW COLUMNS FROM `consulta`')->fetchAll()
        );
        $novasColunas = [
            'raca' => 'VARCHAR(120) NULL AFTER `especie`',
            'telefone_tutor' => 'VARCHAR(30) NULL AFTER `tutor`',
            'queixa_principal' => 'VARCHAR(500) NULL AFTER `telefone_tutor`',
            'observacoes_clinicas' => 'MEDIUMTEXT NULL AFTER `queixa_principal`',
        ];

        foreach ($novasColunas as $nome => $definicao) {
            if (!in_array($nome, $colunas, true)) {
                $pdo->exec("ALTER TABLE `consulta` ADD COLUMN `{$nome}` {$definicao}");
                $colunas[] = $nome;
            }
        }
    }


    /**
     * Executa um arquivo .sql comando por comando.
     */
    public static function executarArquivoSql(string $arquivo): void
    {
        if (!is_file($arquivo)) {
            throw new RuntimeException("Arquivo SQL nao encontrado: {$arquivo}");
        }

        $sql = file_get_contents($arquivo);

        // Remove os comentarios de linha para nao atrapalhar a divisao por ";".
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);

        $pdo = self::conexao();

        foreach (explode(';', $sql) as $comando) {
            $comando = trim($comando);

            if ($comando !== '') {
                $pdo->exec($comando);
            }
        }
    }
}
