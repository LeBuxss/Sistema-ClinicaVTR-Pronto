<div class="mb-4">
    <h1 class="h3 mb-1">Pesquisa na ClinicaVTR</h1>
</div>

<form class="pesquisa-form" method="get" action="<?= url('home/pesquisar') ?>" role="search">
    <label class="visually-hidden" for="termo-pesquisa">Pesquisar na ClinicaVTR</label>
    <input class="form-control" id="termo-pesquisa" type="search" name="q" value="<?= e($termo) ?>" minlength="2" placeholder="Nome, espécie, vacina ou procedimento" required>
    <button class="btn btn-primary" type="submit">Buscar</button>
</form>

<?php if ($termo === ''): ?>
    <p class="text-secondary mt-4">Digite um termo para pesquisar nos registros da clínica.</p>
<?php elseif (mb_strlen($termo) < 2): ?>
    <p class="text-secondary mt-4">Digite ao menos 2 caracteres.</p>
<?php elseif ($resultados === []): ?>
    <p class="text-secondary mt-4">Nenhum resultado encontrado para “<?= e($termo) ?>”.</p>
<?php else: ?>
    <p class="text-secondary mt-4"><?= count($resultados) ?> resultado(s) para “<?= e($termo) ?>”.</p>
    <div class="pesquisa-resultados">
        <?php foreach ($resultados as $resultado): ?>
            <article class="pesquisa-resultado">
                <div class="pesquisa-resultado__cabecalho">
                    <span class="pesquisa-resultado__secao"><?= e($resultado['secao']) ?></span>
                    <a href="<?= url($resultado['url']) ?>"><?= e($resultado['titulo']) ?></a>
                </div>
                <p class="pesquisa-resultado__detalhes">
                    <?php foreach ($resultado['detalhes'] as $rotulo => $valor): ?>
                        <span><strong><?= e($rotulo) ?>:</strong> <?= e($valor) ?></span>
                    <?php endforeach ?>
                </p>
            </article>
        <?php endforeach ?>
    </div>
<?php endif ?>