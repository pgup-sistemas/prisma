<h5 class="mb-3 text-center" style="color:var(--color-text-primary);">Criar conta</h5>

<form method="POST" action="<?= url('/register') ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" class="form-control" id="name" name="name" required autofocus minlength="2" maxlength="120">
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="8">
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmar senha</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
    </div>

    <button type="submit" class="btn btn-primary w-100">Criar conta</button>

    <div class="text-center mt-3" style="font-size:.85rem;">
        <a href="<?= url('/login') ?>">Já tenho conta</a>
    </div>
</form>
