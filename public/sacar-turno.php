<?php
session_save_path(__DIR__ . '/../sessions');
session_start();

require __DIR__ . '/paciente-layout.php';
require __DIR__ . '/../config.php';
?>

<h1 class="text-2xl font-bold text-slate-900 mb-6">Sacar turno</h1>

<p class="text-slate-600 mb-8">
    Elegí qué querés buscar y en qué zona.
</p>

<form method="GET" action="buscar-turnos.php" class="bg-white p-6 rounded-xl shadow border max-w-xl space-y-6">

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">¿Qué querés buscar?</label>
        <select name="tipo" class="w-full border rounded-lg p-2">
            <option value="centros">Centros médicos</option>
            <option value="profesionales">Profesionales independientes</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Zona / Ciudad</label>
        <select name="city" class="w-full border rounded-lg p-2">
            <option value="Lomas de Zamora">Lomas de Zamora</option>
            <option value="Banfield">Banfield</option>
            <option value="Lanús">Lanús</option>
            <option value="La Plata">La Plata</option>
            <option value="Quilmes">Quilmes</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Especialidad</label>
        <select name="profession" class="w-full border rounded-lg p-2">
            <option value="">Todas</option>
            <option value="Psicología">Psicología</option>
            <option value="Nutrición">Nutrición</option>
            <option value="Kinesiología">Kinesiología</option>
            <option value="Fonoaudiología">Fonoaudiología</option>
            <option value="Psiquiatría">Psiquiatría</option>
        </select>
    </div>

    <button class="w-full py-3 bg-sky-600 text-white rounded-lg font-semibold hover:bg-sky-700 transition">
        Buscar
    </button>

</form>

<?php
echo "</main></div></body></html>";
?>