<?php
declare(strict_types=1);

/*
 * Vista del login V3.
 * Solo muestra HTML y Bootstrap.
 * No conoce MariaDB ni el secreto JWT.
 */
?>
<div class="row justify-content-center py-4">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <h1 class="h3 mb-3">
                    Iniciar sesion
                </h1>

                <p class="text-muted">
                    Sistema de Investigacion - Version 3
                </p>

                <?php if (!empty($erroresValidacion)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($erroresValidacion as $error): ?>
                            <p class="mb-1">
                                <?= htmlspecialchars(
                                    (string) $error,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="/login" method="POST">

                    <input
                        type="hidden"
                        name="csrf"
                        value="<?= htmlspecialchars(
                            (string) $csrf,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                    >

                    <div class="mb-3">
                        <label for="usuario" class="form-label">
                            Usuario
                        </label>

                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            class="form-control"
                            maxlength="100"
                            autocomplete="username"
                            value="<?= htmlspecialchars(
                                (string) ($valores['usuario'] ?? ''),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label for="contrasena" class="form-label">
                            Contrasena
                        </label>

                        <input
                            type="password"
                            id="contrasena"
                            name="contrasena"
                            class="form-control"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Ingresar
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>