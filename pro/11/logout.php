<?php
session_start();
session_destroy();
header("Location: /turnos-pro/index.php");
exit;