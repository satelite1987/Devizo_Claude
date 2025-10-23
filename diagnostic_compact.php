<?php error_reporting(E_ALL); ini_set('display_errors', '1'); ob_start(); ?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Diagnostic DEVIZO</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);padding:20px;color:#333}.container{max-width:800px;margin:0 auto}.card{background:#fff;border-radius:8px;padding:25px;margin-bottom:15px;box-shadow:0 4px 6px rgba(0,0,0,0.1)}h1{color:#1e3c72;margin-bottom:20px}h2{color:#2a5298;margin-bottom:15px;font-size:18px;border-bottom:2px solid #eee;padding-bottom:8px}.test{padding:12px;margin:8px 0;border-radius:5px;border-left:4px solid #ccc}.success{background:#d4edda;border-left-color:#28a745;color:#155724}.error{background:#f8d7da;border-left-color:#dc3545;color:#721c24}.warning{background:#fff3cd;border-left-color:#ffc107;color:#856404}.info{background:#d1ecf1;border-left-color:#17a2b8;color:#0c5460}code{background:#f4f4f4;padding:2px 6px;border-radius:3px;font-family:monospace;font-size:13px}table{width:100%;border-collapse:collapse;margin:10px 0}th,td{padding:10px;text-align:left;border-bottom:1px solid #ddd}th{background:#f8f9fa;font-weight:600}pre{background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:5px;overflow-x:auto;font-size:12px}
</style>
</head>
<body>
<div class="container">
<div class="card">
<h1>🔍 Diagnostic DEVIZO</h1>
<p style="color:#666">Acest script testeaza fiecare componenta pas cu pas...</p>
</div>
<div class="card">
<h2>Test 1: PHP Functioneaza</h2>
<div class="test success">
<strong>✅ SUCCES!</strong> Daca vezi acest mesaj, PHP functioneaza corect!<br>
Versiune PHP: <code><?php echo PHP_VERSION; ?></code><br>
Ora server: <code><?php echo date('Y-m-d H:i:s'); ?></code>
</div>
</div>
<?php
echo '<div class="card"><h2>Test 2: Extensii PHP Necesare</h2>';
$extensii = ['pdo','pdo_mysql','mbstring','openssl','curl','json'];
$ok = true;
foreach($extensii as $ext){
if(extension_loaded($ext)){
echo '<div class="test success">✅ Extensia <code>'.$ext.'</code> este ACTIVA</div>';
}else{
echo '<div class="test error">❌ Extensia <code>'.$ext.'</code> este INACTIVA!</div>';
$ok = false;
}
}
if($ok) echo '<div class="test success"><strong>✅ Toate extensiile necesare sunt active!</strong></div>';
echo '</div>';

echo '<div class="card"><h2>Test 3: Functii PHP Necesare</h2>';
if(function_exists('password_hash')){
echo '<div class="test success">✅ Functia <code>password_hash()</code> exista</div>';
}else{
echo '<div class="test error">❌ Functia <code>password_hash()</code> NU exista!</div>';
}
if(function_exists('password_verify')){
echo '<div class="test success">✅ Functia <code>password_verify()</code> exista</div>';
}else{
echo '<div class="test error">❌ Functia <code>password_verify()</code> NU exista!</div>';
}
if(class_exists('PDO')){
echo '<div class="test success">✅ Clasa <code>PDO</code> exista</div>';
}else{
echo '<div class="test error">❌ Clasa <code>PDO</code> NU exista!</div>';
}
echo '</div>';

echo '<div class="card"><h2>Test 4: Informatii Fisier</h2>';
echo '<div class="test info">';
echo '<strong>Locatie script:</strong><br><code>'.__FILE__.'</code><br><br>';
echo '<strong>Director curent:</strong><br><code>'.__DIR__.'</code><br><br>';
echo '<strong>URL accesat:</strong><br><code>'.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'N/A').'</code>';
echo '</div></div>';

echo '<div class="card"><h2>Test 5: Credentiale Baza de Date</h2>';
echo '<div class="test info"><p>Credentialele configurate:</p><table>';
echo '<tr><th>Parametru</th><th>Valoare</th></tr>';
echo '<tr><td>DB_HOST</td><td><code>localhost</code></td></tr>';
echo '<tr><td>DB_NAME</td><td><code>devizo_db</code></td></tr>';
echo '<tr><td>DB_USER</td><td><code>devizo_user</code></td></tr>';
echo '<tr><td>DB_PASS</td><td><code>Satelite1987!</code></td></tr>';
echo '<tr><td>DB_CHARSET</td><td><code>utf8mb4</code></td></tr>';
echo '</table><p style="margin-top:10px"><strong>⚠️ Verifica ca aceste credentiale sunt CORECTE!</strong></p></div></div>';

echo '<div class="card"><h2>Test 6: Conectare la Baza de Date</h2>';
$db_host = 'localhost';
$db_name = 'devizo_db';
$db_user = 'devizo_user';
$db_pass = 'Satelite1987!';
$db_charset = 'utf8mb4';

try{
$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
echo '<div class="test info"><strong>Încerc să conectez...</strong><br>DSN: <code>'.htmlspecialchars($dsn).'</code></div>';
$opt = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false];
$pdo = new PDO($dsn,$db_user,$db_pass,$opt);
echo '<div class="test success"><strong>✅ CONEXIUNE REUSITA!</strong><br>Baza: <code>'.$db_name.'</code><br>Server: <code>'.$pdo->getAttribute(PDO::ATTR_SERVER_VERSION).'</code></div>';

echo '</div><div class="card"><h2>Test 7: Verificare Tabela "utilizatori"</h2>';
$stmt = $pdo->query("SHOW TABLES LIKE 'utilizatori'");
$exists = $stmt->fetch();
if($exists){
echo '<div class="test success">✅ Tabela <code>utilizatori</code> EXISTA</div>';
$stmt = $pdo->query("SELECT COUNT(*) as total FROM utilizatori");
$res = $stmt->fetch();
echo '<div class="test info"><strong>Numar utilizatori:</strong> <code>'.$res['total'].'</code></div>';
$stmt = $pdo->query("SELECT id,email,nume,activ FROM utilizatori ORDER BY id");
$users = $stmt->fetchAll();
if(count($users)>0){
echo '<table><tr><th>ID</th><th>Email</th><th>Nume</th><th>Activ</th></tr>';
foreach($users as $u){
echo '<tr><td>'.$u['id'].'</td><td><code>'.htmlspecialchars($u['email']).'</code></td><td>'.htmlspecialchars($u['nume']).'</td><td>'.($u['activ']?'✅ Da':'❌ Nu').'</td></tr>';
}
echo '</table>';
}else{
echo '<div class="test warning">⚠️ Tabela exista dar nu contine utilizatori!</div>';
}
}else{
echo '<div class="test error">❌ Tabela <code>utilizatori</code> NU EXISTA!</div>';
echo '<div class="test warning">Reimporta <code>database.sql</code> in phpMyAdmin!</div>';
}
}catch(PDOException $e){
echo '<div class="test error"><strong>❌ EROARE conectare!</strong><br><br><strong>Mesaj:</strong><br><pre>'.htmlspecialchars($e->getMessage()).'</pre><br><strong>Cauze:</strong><ul style="margin:10px 0;padding-left:25px"><li>Credentiale gresite</li><li>Baza <code>devizo_db</code> nu exista</li><li>User <code>devizo_user</code> nu are acces</li></ul></div>';
}
echo '</div>';

echo '<div class="card"><h2>Test 8: Test Functii Password</h2>';
$tp = 'admin123';
$th = password_hash($tp,PASSWORD_DEFAULT);
echo '<div class="test info"><strong>Parola test:</strong> <code>'.$tp.'</code><br><strong>Hash generat:</strong><br><code style="word-break:break-all">'.$th.'</code></div>';
if(password_verify($tp,$th)){
echo '<div class="test success">✅ password_verify() functioneaza CORECT!</div>';
}else{
echo '<div class="test error">❌ password_verify() NU functioneaza!</div>';
}
echo '</div>';

echo '<div class="card"><h2>Concluzie</h2><div class="test success"><strong>✅ Daca vezi ACEST mesaj, scriptul functioneaza complet!</strong><br><br>Verifica mesajele de mai sus pentru probleme.</div></div>';
echo '<div class="card"><h2>Ce urmeaza?</h2><div class="test info"><p><strong>Daca TOATE testele sunt ✅ VERZI:</strong></p><p>Spune-mi si iti trimit scriptul de resetare parole!</p><br><p><strong>Daca vezi ❌ ERORI:</strong></p><p>Fa screenshot si trimite-mi-l.</p></div></div>';
?>
<div class="card" style="text-align:center;color:#999;font-size:13px">
<p>DEVIZO Diagnostic Script v1.0</p>
<p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
</div>
</div>
</body>
</html>
<?php ob_end_flush(); ?>
