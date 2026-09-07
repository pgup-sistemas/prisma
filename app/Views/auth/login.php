<h5 class="mb-3 text-center" style="color:var(--color-text-primary);">Entrar</h5>

<form method="POST" action="<?= url('/login') ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

    <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" class="form-control" id="email" name="email" required autofocus>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Entrar</button>

    <div class="d-flex justify-content-between mt-3" style="font-size:.85rem;">
        <a href="<?= url('/forgot') ?>">Esqueci minha senha</a>
        <a href="<?= url('/register') ?>">Criar conta</a>
    </div>
</form>
