<h5 class="mb-3 text-center" style="color:var(--color-text-primary);">Recuperar senha</h5>

<form method="POST" action="<?= url('/forgot') ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

    <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary w-100">Enviar link de recuperação</button>

    <div class="text-center mt-3" style="font-size:.85rem;">
        <a href="<?= url('/login') ?>">Voltar ao login</a>
    </div>
</form>
