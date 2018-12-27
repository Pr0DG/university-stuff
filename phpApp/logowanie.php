<?php

session_start();

if (isset($_POST['zaloguj'])) {
    $pdo = new PDO('mysql:host=localhost;dbname=test', 'root', '');
    
    $stmt = $pdo->prepare("SELECT login, blokadaKonta FROM uzytkownicy WHERE login = :login");
    $stmt->execute(['login' => $_POST['login']]);
	$wynik = $stmt->fetch();
    if($wynik['blokadaKonta'] == 'tak') {
        $komunikat = "Konto zablokowane.";
    }
    else {
	    $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE login = :login AND haslo = :haslo");
	    $stmt->execute(['login' => $_POST['login'], 'haslo' => $_POST['haslo']]);
	    $wynik = $stmt->fetch();
	    if ($wynik) {
		    $_SESSION['zalogowany'] = 'tak';
		    $_SESSION['id'] = $wiersz['id'];
		    header("Location: index.php");
	    } else {
            $komunikat = "Wprowadzono zły login lub hasło.";
            
            $stmt = $pdo->prepare("SELECT login FROM uzytkownicy WHERE login = :login");
            $stmt->execute(['login' => $_POST['login']]);
            $wynik = $stmt->fetchAll();
            
            if(!empty($wynik)) {
                if(isset($_SESSION['loginAttArray'])) {
                    $loginAttArray = $_SESSION['loginAttArray'];
                    if(isset($loginAttArray[$_POST['login']])) {
                        if( $loginAttArray[$_POST['login']] < 2) {
                            ++$loginAttArray[$_POST['login']];
                        }
                        else {
                            $stmt = $pdo->prepare("UPDATE uzytkownicy SET blokadaKonta = 'tak' WHERE login = :login");
                            $stmt->execute(['login' => $_POST['login']]);
                            $komunikat = "Konto zablokowane.";
                        } 
                    }
                    else {
                        $loginAttArray[$_POST['login']] = 1;
                    }
                    $_SESSION['loginAttArray'] = $loginAttArray;
                }
                else {
                    $loginAttArray[$_POST['login']] = 1;  
                    $_SESSION['loginAttArray'] = $loginAttArray;
                }
            }
           
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Logowanie</title>
</head>
<body>
    <?php if (!empty($komunikat)): ?>
        <p style="font-weight: bold; color: red;"><?=$komunikat ?></p>
    <?php endif; ?>
    
    <form method="post" action="">
        <table>
            <tr>
                <td>Login</td>
                <td><input type="text" name="login" /></td>
            </tr>
            <tr>
                <td>Hasło</td>
                <td><input type="password" name="haslo" /></td>
            </tr>
            <tr>
                <td colspan="2"><input type="submit" name="zaloguj" value="Zaloguj" /></td>
            </tr>
        </table>
    </form>
</body>
</html>