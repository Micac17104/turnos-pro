<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/helpers.php';

$page_title = 'Mi perfil';
$current    = 'perfil';

// Obtener datos del profesional
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Evitar errores si faltan columnas
$defaults = [
    'name' => '',
    'profession' => '',
    'phone' => '',
    'email' => '',
    'address' => '',
    'city' => '',
    'province' => '',
    'public_description' => '',
    'specialties' => '',
    'slug' => '',
    'profile_image' => ''
];

$user = array_merge($defaults, $user ?: []);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>

<main class="flex-1 p-8">

    <h1 class="text-2xl font-semibold text-slate-900 mb-6">Mi perfil</h1>

    <form method="post" enctype="multipart/form-data"
          action="/turnos-pro/pro/perfil-guardar.php"
          class="space-y-10">

        <!-- DATOS PERSONALES -->
        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Datos personales</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo</label>
                    <input type="text" name="name" required
                           value="<?= h($user['name']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Profesión</label>
                    <input type="text" name="profession" required
                           value="<?= h($user['profession']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                    <input type="text" name="phone"
                           value="<?= h($user['phone']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email"
                           value="<?= h($user['email']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                    <input type="text" name="address"
                           value="<?= h($user['address']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Ciudad</label>
                    <input type="text" name="city"
                           value="<?= h($user['city']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Provincia</label>
                    <input type="text" name="province"
                           value="<?= h($user['province']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

            </div>
        </section>

        <!-- PERFIL PÚBLICO -->
        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Perfil público</h2>

            <div class="space-y-4">

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Descripción pública</label>
                    <textarea name="public_description" rows="4"
                              class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm"><?= h($user['public_description']) ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Especialidades</label>
                    <input type="text" name="specialties"
                           value="<?= h($user['specialties']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">URL pública (slug)</label>
                    <input type="text" name="slug"
                           value="<?= h($user['slug']) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm"
                           placeholder="ej: dr-gustavo-perez">
                    <p class="text-xs text-slate-500 mt-1">
                        Tu página pública será: <strong>/p/<?= h($user['slug']) ?></strong>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Foto de perfil</label>
                    <input type="file" name="profile_image" accept="image/*" class="text-sm">

                    <?php if ($user['profile_image']): ?>
                        <img src="/turnos-pro/uploads/<?= h($user['profile_image']) ?>"
                             class="mt-3 w-24 h-24 rounded-full object-cover border">
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <!-- BOTONES -->
        <div class="flex justify-end gap-3">
            <a href="/turnos-pro/pro/agenda.php"
               class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm hover:bg-slate-300">
                Cancelar
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                Guardar cambios
            </button>
        </div>

    </form>

</main>

<?php require __DIR__ . '/includes/footer.php'; ?>