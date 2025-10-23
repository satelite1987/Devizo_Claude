<?php error_reporting(E_ALL); ini_set('display_errors', '1'); ob_start(); ?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Resetare Parole DEVIZO</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:Arial,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:20px;color:#333}.container{max-width:900px;margin:0 auto}.card{background:#fff;border-radius:8px;padding:25px;margin-bottom:15px;box-shadow:0 10px 40px rgba(0,0,0,0.2)}h1{color:#667eea;margin-bottom:15px;font-size:28px}h2{color:#333;margin-bottom:15px;font-size:20px;border-bottom:2px solid #f0f0f0;padding-bottom:10px}.step{padding:15px;margin:10px 0;border-radius:5px;border-left:4px solid #ccc}.success{background:#d4edda;border-left-color:#28a745;color:#155724}.error{background:#f8d7da;border-left-color:#dc3545;color:#721c24}.warning{background:#fff3cd;border-left-color:#ffc107;color:#856404}.info{background:#d1ecf1;border-left-color:#17a2b8;color:#0c5460}code{background:#f4f4f4;padding:3px 8px;border-radius:4px;font-family:monospace;font-size:14px}table{width:100%;border-collapse:collapse;margin:15px 0}th,td{padding:12px;text-align:left;border-bottom:1px solid #dee2e6}th{background:#667eea;color:#fff;font-weight:600}tr:hover{background:#f8f9fa}.btn{display:inline-block;background:#28a745;color:#fff;padding:15px 30px;text-decoration:none;border-radius:5px;font-weight:bold;font-size:16px;margin:20px 0;transition:background 0.3s}.btn:hover{background:#218838}pre{background:#2d2d2d;color:#f8f8f2;padding:12px;border-radius:5px;overflow-x:auto;font-size:12px}.alert-success{background:#d4edda;border-left:4px solid #28a745;padding:20px;border-radius:5px;margin:20px 0}.alert-danger{background:#f8d7da;border-left:4px solid #dc3545;padding:20px;border-radius:5px;margin:20px 0}.alert-warning{background:#fff3cd;border-left:4px solid #ffc107;padding:20px;border-radius:5px;margin:20px 0}ul{margin:10px 0;padding-left:30px}li{margin:5px 0}
</style>
</head>
<body>
<div class="container">
<div class="card">
<h1>🔐 Resetare Parole DEVIZO</h1>
<p style="color:#666;font-size:14px">Script pentru resetarea parolelor utilizatorilor demo</p>
</div>
<?php
$db_host = 'localhost';
$db_name = 'devizo_db';
$db_user = 'devizo_user';
$db_pass = 'Satelite1987!';
$db_charset = 'utf8mb4';

echo '<div class="card"><h2>Pas 1: Conectare la Baza de Date</h2>';
try{
$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";
$opt = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false];
$pdo = new PDO($dsn,$db_user,$db_pass,$opt);
echo '<div class="step success">✅ Conexiune la baza de date reusita!<br>Baza: <code>'.$db_name.'</code><br>Server: <code>'.$pdo->getAttribute(PDO::ATTR_SERVER_VERSION).'</code></div>';
echo '</div>';

echo '<div class="card"><h2>Pas 2: Generare Hash-uri Parole Noi</h2>';
$parole = [
'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
'demo123' => password_hash('demo123', PASSWORD_DEFAULT),
'user123' => password_hash('user123', PASSWORD_DEFAULT)
];
echo '<div class="step success">✅ Hash-uri BCrypt generate cu succes!</div>';
echo '<table><tr><th>Parola (text)</th><th>Hash BCrypt</th></tr>';
foreach($parole as $txt=>$hash){
echo '<tr><td><code><strong>'.$txt.'</strong></code></td><td style="font-size:11px;word-break:break-all">'.$hash.'</td></tr>';
}
echo '</table></div>';

echo '<div class="card"><h2>Pas 3: Resetare Parole Utilizatori</h2>';
$users = [
['email'=>'admin@devizo.ro','parola_text'=>'admin123','parola_hash'=>$parole['admin123'],'nume'=>'Super Administrator','rol'=>'Super Admin'],
['email'=>'doru@zaninstal.ro','parola_text'=>'demo123','parola_hash'=>$parole['demo123'],'nume'=>'Batagui Doru','rol'=>'Master Firma'],
['email'=>'user@zaninstal.ro','parola_text'=>'user123','parola_hash'=>$parole['user123'],'nume'=>'Utilizator Demo','rol'=>'Utilizator']
];

echo '<table><tr><th>Email</th><th>Nume</th><th>Rol</th><th>Parola Noua</th><th>Status</th><th>Verificare</th></tr>';
$allOk = true;
foreach($users as $u){
echo '<tr><td><code>'.$u['email'].'</code></td><td>'.$u['nume'].'</td><td><strong>'.$u['rol'].'</strong></td><td><code><strong>'.$u['parola_text'].'</strong></code></td>';
try{
$stmt = $pdo->prepare("UPDATE utilizatori SET parola = ? WHERE email = ?");
$stmt->execute([$u['parola_hash'],$u['email']]);
echo '<td style="color:#28a745">✅ ACTUALIZAT</td>';
$stmt = $pdo->prepare("SELECT parola FROM utilizatori WHERE email = ?");
$stmt->execute([$u['email']]);
$check = $stmt->fetch();
if(password_verify($u['parola_text'],$check['parola'])){
echo '<td style="color:#28a745">✅ CORECT</td>';
}else{
echo '<td style="color:#dc3545">❌ GRESIT</td>';
$allOk = false;
}
}catch(PDOException $e){
echo '<td style="color:#dc3545">❌ EROARE</td><td>'.htmlspecialchars($e->getMessage()).'</td>';
$allOk = false;
}
echo '</tr>';
}
echo '</table></div>';

echo '<div class="card"><h2>Pas 4: Test Autentificare</h2>';
echo '<p>Testez daca fiecare utilizator se poate autentifica...</p>';
foreach($users as $u){
$stmt = $pdo->prepare("SELECT id,email,nume,parola FROM utilizatori WHERE email = ?");
$stmt->execute([$u['email']]);
$dbUser = $stmt->fetch();
if($dbUser && password_verify($u['parola_text'],$dbUser['parola'])){
echo '<div class="step success">✅ <strong>'.$u['email'].'</strong> cu parola <code>'.$u['parola_text'].'</code><br><small style="color:#666">password_verify() = SUCCESS</small></div>';
}else{
echo '<div class="step error">❌ <strong>'.$u['email'].'</strong> cu parola <code>'.$u['parola_text'].'</code> NU functioneaza!</div>';
$allOk = false;
}
}
echo '</div>';

if($allOk){
echo '<div class="alert-success">';
echo '<h2 style="color:#28a745;margin-bottom:15px">🎉 SUCCES COMPLET!</h2>';
echo '<p style="font-size:18px;margin:15px 0"><strong>Toate parolele au fost resetate si verificate cu succes!</strong></p>';
echo '<p>Puteti acum sa va autentificati la aplicatia DEVIZO:</p>';
echo '<table style="margin:20px 0"><tr><th>Rol</th><th>Email</th><th>Parola</th></tr>';
echo '<tr><td><strong>Super Admin</strong></td><td><code>admin@devizo.ro</code></td><td><code><strong>admin123</strong></code></td></tr>';
echo '<tr><td><strong>Master Firma</strong></td><td><code>doru@zaninstal.ro</code></td><td><code><strong>demo123</strong></code></td></tr>';
echo '<tr><td><strong>Utilizator</strong></td><td><code>user@zaninstal.ro</code></td><td><code><strong>user123</strong></code></td></tr>';
echo '</table>';
echo '<a href="login.php" class="btn">🚀 INTRA IN APLICATIE</a>';
echo '</div>';
}else{
echo '<div class="alert-danger"><h2 style="color:#dc3545">❌ AU APARUT PROBLEME</h2><p>Unele parole nu au fost resetate corect. Verificati erorile de mai sus.</p></div>';
}

echo '<div class="card"><h2>Informatii Tehnice</h2>';
echo '<div class="info"><p><strong>De ce nu functiona autentificarea?</strong></p>';
echo '<p>Hash-ul initial era un hash de test Laravel pentru parola "password", NU pentru "admin123", "demo123" sau "user123".</p>';
echo '<p style="margin-top:10px"><strong>Solutia:</strong></p>';
echo '<p>Acest script a generat hash-uri BCrypt NOI pentru fiecare parola demo folosind <code>password_hash()</code> cu algoritm PASSWORD_DEFAULT (BCrypt cost 10).</p></div></div>';

}catch(PDOException $e){
echo '<div class="alert-danger"><h2 style="color:#dc3545">❌ EROARE la conectarea la baza de date!</h2>';
echo '<p><strong>Mesaj:</strong></p><pre>'.htmlspecialchars($e->getMessage()).'</pre>';
echo '<p style="margin-top:15px"><strong>Verificari necesare:</strong></p><ul>';
echo '<li>Credentialele sunt corecte?</li>';
echo '<li>Baza de date <code>devizo_db</code> exista?</li>';
echo '<li>Utilizatorul <code>devizo_user</code> are acces?</li></ul></div>';
echo '</div></div></body></html>';
exit;
}
?>
<div class="alert-warning">
<h2 style="color:#856404">⚠️ SECURITATE - ACTIUNE URGENTA!</h2>
<p style="font-size:18px"><strong>STERGETI IMEDIAT acest fisier dupa utilizare!</strong></p>
<p>Fisiere care TREBUIE sterse din <code>/public_html/</code>:</p>
<ul style="font-size:16px">
<li><code><strong>resetare_parole_final.php</strong></code> (ACEST FISIER!)</li>
<li><code>diagnostic.php</code></li>
<li><code>test.php</code> (daca exista)</li>
<li><code>Screenshot.png</code></li>
</ul>
<p style="font-size:16px;margin-top:15px;color:#721c24"><strong>Aceste fisiere contin credentiale si informatii sensibile!</strong></p>
</div>
<div class="card" style="text-align:center;color:#999;font-size:13px">
<p>DEVIZO - Resetare Parole v1.0</p>
<p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
</div>
</div>
</body>
</html>
<?php ob_end_flush(); ?>
