<aside class="w-64 bg-white border-r border-slate-200 min-h-screen p-6">

<aside class="w-64 bg-white border-r border-slate-200 min-h-screen p-6">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Panel profesional</h2>
    </div>

    <nav class="space-y-2">
        ...
    </nav>

</aside>

    <nav class="space-y-2">

        <a href="/pro/dashboard.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'dashboard' ? 'bg-slate-100 font-semibold' : '' ?>">
            Dashboard
        </a>

        <a href="/pro/agenda.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'agenda' ? 'bg-slate-100 font-semibold' : '' ?>">
            Agenda
        </a>

        <a href="/pro/pacientes.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'pacientes' ? 'bg-slate-100 font-semibold' : '' ?>">
            Pacientes
        </a>

        <a href="/pro/pagos.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'pagos' ? 'bg-slate-100 font-semibold' : '' ?>">
            Pagos
        </a>

        <a href="/pro/estadisticas.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'estadisticas' ? 'bg-slate-100 font-semibold' : '' ?>">
            Estadísticas
        </a>

        <a href="/pro/notificaciones.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'notificaciones' ? 'bg-slate-100 font-semibold' : '' ?>">
            Notificaciones
        </a>

        <a href="/pro/horarios.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'horarios' ? 'bg-slate-100 font-semibold' : '' ?>">
            Horarios
        </a>

        <a href="/pro/perfil.php"
           class="block px-4 py-2 rounded-lg hover:bg-slate-100 <?= $current === 'perfil' ? 'bg-slate-100 font-semibold' : '' ?>">
            Perfil
        </a>

        <a href="/auth/logout.php"
           class="block px-4 py-2 rounded-lg hover:bg-red-100 text-red-600">
            Cerrar sesión
        </a>

    </nav>

</aside>