<div class="barra-pesquisa">
    <form class="barra-pesquisa__form" method="get" action="<?= url('home/pesquisar') ?>" role="search">
        <label class="visually-hidden" for="pesquisa-clinica">Pesquisar na ClinicaVTR</label>
        <input class="form-control" id="pesquisa-clinica" type="search" name="q" placeholder="Pesquisar na ClinicaVTR" minlength="2" required>
        <button class="btn btn-primary" type="submit">Buscar</button>
    </form>
</div>