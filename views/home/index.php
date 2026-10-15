<?php
/**
 * Tela inicial (dashboard).
 *
 * A tela inicial nao depende de nenhuma tabela da aplicacao.
 */
?>
<?php $historico = $historico ?? [] ?>
<section class="painel-boas-vindas mb-4" aria-labelledby="painel-titulo">
    <div>
        <p class="painel-boas-vindas__rotulo mb-1">GESTÃO DA CLÍNICA</p>
        <h1 class="h3 mb-1" id="painel-titulo">Bem-vindo à ClinicaVTR</h1>
        <p class="text-secondary mb-0">Acesse rapidamente as rotinas da ClinicaVTR.</p>
    </div>
</section>
<section class="bem-estar-animais mb-5" aria-label="Dicas de bem-estar animal">
    <div class="bem-estar-marquee" data-bem-estar-marquee>
        <button class="carrossel-bem-estar__pausa" type="button" data-carrossel-pausa aria-controls="faixaBemEstar" aria-label="Pausar apresentação">
            <span aria-hidden="true">&#10074;&#10074;</span> Pausar
        </button>
        <div class="bem-estar-marquee__janela" id="faixaBemEstar" role="region" aria-label="Dicas de bem-estar animal" aria-roledescription="carrossel">
            <div class="bem-estar-marquee__faixa">
                <div class="bem-estar-marquee__grupo">
                    <article class="bem-estar-marquee__cartao">
                        <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&amp;fit=crop&amp;w=700&amp;q=80" alt="Cães aproveitando um passeio ao ar livre">
                        <p>Passeios e brincadeiras ajudam a manter o corpo ativo e a mente feliz.</p>
                    </article>
                    <article class="bem-estar-marquee__cartao">
                        <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&amp;fit=crop&amp;w=700&amp;q=80" alt="Gato descansando tranquilamente">
                        <p>Um ambiente seguro, tranquilo e enriquecido faz toda a diferença.</p>
                    </article>
                    <article class="bem-estar-marquee__cartao">
                        <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&amp;fit=crop&amp;w=700&amp;q=80" alt="Cão recebendo carinho">
                        <p>Carinho, água fresca e acompanhamento veterinário são cuidados essenciais.</p>
                    </article>
                </div>
                <div class="bem-estar-marquee__grupo" aria-hidden="true">
                    <article class="bem-estar-marquee__cartao">
                        <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&amp;fit=crop&amp;w=700&amp;q=80" alt="">
                        <p>Passeios e brincadeiras ajudam a manter o corpo ativo e a mente feliz.</p>
                    </article>
                    <article class="bem-estar-marquee__cartao">
                        <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&amp;fit=crop&amp;w=700&amp;q=80" alt="">
                        <p>Um ambiente seguro, tranquilo e enriquecido faz toda a diferença.</p>
                    </article>
                    <article class="bem-estar-marquee__cartao">
                        <img src="https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&amp;fit=crop&amp;w=700&amp;q=80" alt="">
                        <p>Carinho, água fresca e acompanhamento veterinário são cuidados essenciais.</p>
                    </article>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="mb-5" aria-labelledby="atalhos-titulo">
    <h2 class="h5 mb-3" id="atalhos-titulo">Ações rápidas</h2>
    <div class="atalhos-clinica">
        <a class="atalho-clinica" href="<?= url('consulta/criar') ?>">
            <span class="atalho-clinica__icone" aria-hidden="true">+</span>
            <span><strong>Agendar consulta</strong><small>Marque um horário para o paciente</small></span>
        </a>
        <a class="atalho-clinica" href="<?= url('cadastro/criar') ?>">
            <span class="atalho-clinica__icone" aria-hidden="true">+</span>
            <span><strong>Novo atendimento</strong><small>Registre serviço e dados do tutor</small></span>
        </a>
        <a class="atalho-clinica" href="<?= url('clinicavtr/criar') ?>">
            <span class="atalho-clinica__icone" aria-hidden="true">+</span>
            <span><strong>Cadastrar tutor</strong><small>Inclua os dados de contato do responsável</small></span>
        </a>
        <a class="atalho-clinica" href="<?= url('fichavacinal/criar') ?>">
            <span class="atalho-clinica__icone" aria-hidden="true">+</span>
            <span><strong>Registrar vacina</strong><small>Atualize a ficha de vacinação</small></span>
        </a>
    </div>
</section>
<section class="mb-5" aria-labelledby="historico-fichas-titulo">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h5 mb-0" id="historico-fichas-titulo">Histórico de atendimentos finalizados</h2>
    </div>
    <?php if ($historico === []): ?>
        <p class="text-secondary mb-0">Nenhum atendimento finalizado ainda.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Módulo</th>
                    <th scope="col">Informações</th>
                    <th scope="col">Registro</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($historico as $grupo): ?>
                    <?php foreach ($grupo['registros'] as $indice => $registro): ?>
                        <tr>
                            <?php if ($indice === 0): ?>
                                <th scope="rowgroup" rowspan="<?= count($grupo['registros']) ?>"><?= e($grupo['nome']) ?></th>
                            <?php endif ?>
                            <td><?= e($registro['modulo']) ?></td>
                            <td>
                                <div class="historico-registro__detalhes d-flex flex-wrap gap-2">
                                    <?php foreach ($registro['detalhes'] as $rotulo => $valor): ?>
                                        <span><strong><?= e($rotulo) ?>:</strong> <?= e($valor) ?></span>
                                    <?php endforeach ?>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= url($registro['url']) ?>">Abrir</a>
                                <a class="btn btn-sm btn-outline-primary" href="<?= url('home/relatorio') ?>?nome=<?= rawurlencode($grupo['nome']) ?>">Relatório PDF</a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
