<?php
session_start();
session_destroy();
header("Location: /turnos-pro/public/login-paciente.php");
exit;