<?php
// /pro/includes/sidebar.php

$current = $current ?? ''; // ej: 'dashboard', 'agenda', 'pacientes', etc.
?>
<aside class="w-64 bg-white border-r border-slate-200 flex flex-col">
    <div class="px-6 py-5 border-b border-slate-200">
        <div class="text-lg font-semibold text-slate-900">TurnosPro</div>
        <div class="text-sm text-slate-500">Panel profesional</div>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1">

        <!-- DASHBOARD -->
        <a href="/turnos-pro/pro/dashboard.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'dashboard' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Dashboard
        </a>

        <!-- AGENDA -->
        <a href="/turnos-pro/pro/agenda.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'agenda' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Agenda
        </a>

        <!-- PACIENTES -->
        <a href="/turnos-pro/pro/pacientes.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'pacientes' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Pacientes
        </a>

        <!-- PAGOS -->
        <a href="/turnos-pro/pro/pagos.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'pagos' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Pagos
        </a>

        <!-- ESTADÍSTICAS -->
        <a href="/turnos-pro/pro/estadisticas.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'estadisticas' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Estadísticas
        </a>

        <!-- NOTIFICACIONES -->
        <a href="/turnos-pro/pro/notificaciones.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'notificaciones' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Notificaciones
        </a>

        <!-- PERFIL -->
        <a href="/turnos-pro/pro/perfil.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'perfil' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Perfil
        </a>

        <!-- HORARIOS -->
        <a href="/turnos-pro/pro/horarios.php"
           class="flex items-center px-3 py-2 rounded-lg text-sm <?= $current === 'horarios' ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' ?>">
            Horarios
        </a>

    </nav>

    <div class="px-4 py-4 border-t border-slate-200">
        <a href="/turnos-pro/auth/logout.php"
           class="block w-full text-left text-sm text-red-600 hover:text-red-700">
            Cerrar sesión
        </a>
    </div>
</aside>