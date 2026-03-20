<div class="sidebar">
    <h2>TurnosPro</h2>

   <a href="centro-dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Dashboard
</a>

<a href="centro-profesionales.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Profesionales
</a>

<a href="centro-turnos.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Turnos
</a>

<a href="centro-agenda.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Agenda diaria
</a>

<a href="centro-agenda-semanal.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Agenda semanal
</a>

<a href="centro-pacientes.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Pacientes
</a>

<a href="centro-configuracion.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Configuración
</a>

<a href="centro-recordatorios.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Recordatorios
</a>

<a href="planes.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Pagar suscripción
</a>

<a href="/cancelar-suscripcion.php"
   onclick="return confirm('¿Seguro que querés cancelar tu suscripción? Perderás acceso al panel.');"
   class="block px-4 py-2 text-red-600 hover:bg-red-100 rounded-lg">
   Cancelar suscripción
</a>

    <a href="../auth/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
    Salir
</a>
</div>

<style>
.sidebar {
    width: 240px;
    background: #0f172a;
    color: white;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    padding: 24px 16px;
    box-sizing: border-box;
}
.sidebar h2 {
    margin: 0 0 24px;
    font-size: 20px;
}
.sidebar a {
    display: block;
    padding: 10px 0;
    color: #cbd5e1;
    text-decoration: none;
    font-size: 15px;
}
.sidebar a:hover {
    color: white;
}
.sidebar .logout {
    margin-top: 20px;
    color: #f87171;
}
</style>