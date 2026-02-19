<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Recuperar contraseña</title>

<style>
body { background:#f1f5f9; font-family:Arial; display:flex; justify-content:center; align-items:center; height:100vh; }
.box { background:white; padding:40px; border-radius:20px; width:350px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.08); }
input { width:100%; padding:14px; margin:8px 0; border-radius:12px; border:1px solid #cbd5e1; }
button { width:100%; padding:14px; background:#0ea5e9; color:white; border:none; border-radius:12px; font-weight:600; cursor:pointer; }
button:hover { opacity:0.9; }
</style>

</head>
<body>

<div class="box">
    <h2>Recuperar contraseña</h2>
    <p>Ingresá tu email y te enviaremos un enlace para restablecerla.</p>

    <form method="post" action="send-reset-email.php">
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit">Enviar enlace</button>
    </form>

    <p style="margin-top:12px;">
        <a href="login.php" style="color:#0ea5e9;">Volver al login</a>
    </p>
</div>

</body>
</html>