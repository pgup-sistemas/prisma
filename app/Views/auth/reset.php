<h5 class="mb-3 text-center" style="color:var(--color-text-primary);">Redefinir senha</h5>

<form method="POST" action="<?= url('/reset') ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf_token) ?>">
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="mb-3">
        <label for="password" class="form-label">Nova senha</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="8" autofocus>
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmar nova senha</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
    </div>

    <button type="submit" class="btn btn-primary w-100">Redefinir senha</button>
</form>
