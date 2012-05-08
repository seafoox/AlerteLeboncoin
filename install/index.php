<?php
$configFile = APPLICATION_PATH."/configs/config.ini";

if (file_exists($configFile)) {
    echo "Le fichier de configuration existe déjà.";
    exit;
}
if (!is_writeable(dirname($configFile))) {
    echo "'".$configFile."' doit être accessible en écriture.";
    exit;
}
if (strtolower($_SERVER["REQUEST_METHOD"]) == "post") {
    $configDb = array(
        "charset" => "utf8",
        "host" => $_POST["host"],
        "username" => $_POST["username"],
        "password" => $_POST["password"],
        "dbname" => $_POST["database"]
    );
    $adapter = Zend_Db::factory("Mysqli", $configDb);
    try {
        $adapter->getConnection();
    } catch (Zend_Db_Adapter_Mysqli_Exception $e) {
        $errorSql = $e->getMessage();
    }

    if ($adapter->isConnected()) {
        Zend_Db_Table::setDefaultAdapter($adapter);
        $queries = explode(";", file_get_contents(dirname(__FILE__)."/schema.sql"));
        foreach ($queries AS $query) {
            if (!$query = trim($query)) {
                continue;
            }
            $adapter->query($query);
        }
        if (!empty($_POST["user_email"]) && !empty($_POST["user_password"])) {
            $tb = new Zend_Db_Table("User");
            $user = $tb->createRow(array(
                "email" => $_POST["user_email"],
                "password" => sha1($_POST["user_password"]),
                "date_created" => new Zend_Db_Expr("NOW()")
            ));
            $user->save();
        }
    
        $config = new Zend_Config(array(
            "resources" => array("db" => array("params" => array(
                "host" => $_POST["host"],
                "username" => $_POST["username"],
                "password" => $_POST["password"],
                "dbname" => $_POST["database"]
            ))),
            "email" => array("from" => isset($_POST["email"])?$_POST["email"]:""),
            "key" => isset($_POST["key"])?$_POST["key"]:""
        ));
        $writer = new Zend_Config_Writer_Ini();
        $writer->setRenderWithoutSections()->setConfig($config);
        $writer->write($configFile);
        $installed = true;
    }
}
$checkWritable = array();

if (!is_writable(APPLICATION_PATH."/../var/log")) {
    $checkWritable[] = "« <strong>".realpath(APPLICATION_PATH."/../var/log")."</strong> »"
        ." doit avoir les droits d'écriture.";
}

if (!is_writable(APPLICATION_PATH."/../var/sessions")) {
    $checkWritable[] = "« <strong>".realpath(APPLICATION_PATH."/../var/sessions")."</strong> »"
        ." doit avoir les droits d'écriture.";
}

if (!is_writable(APPLICATION_PATH."/../var/tmp")) {
    $checkWritable[] = "« <strong>".realpath(APPLICATION_PATH."/../var/tmp")."</strong> »"
        ." doit avoir les droits d'écriture.";
}

if (!isset($_POST["key"])) {
    $password = "";
    $chaine = "abcdefghijklmnpqrstuvwxy0123456789";
    $lenght = strlen($chaine);
    srand((double)microtime()*1000);
    for($i=0; $i < 7; $i++) {
        $password .= $chaine[rand() % $lenght];
    }
    $key = sha1($password);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Installation</title>
    </head>
    <body>
        <h1>Installation de l'application</h1>
        
        <?php if (isset($installed)) : ?>
        <p>Installation terminée.</p>
        <p><a href="/">Retour à l'application.</a></p>
        <?php else: ?>
        <?php if ($checkWritable) : ?>
        <ul>
            <li><?php echo implode("</li><li>", $checkWritable); ?></li>
        </ul>
        <?php endif; ?>
        
        <form action="" method="post" style="width: 500px;">
            <fieldset>
                <legend>Base de données</legend>
                <?php if (isset($errorSql)) : ?>
                <p style="color: #EF0000;">
                    Erreur SQL :<br />
                    "<?php echo $errorSql; ?>"<br />
                    <span style="font-weight: bold;">Vérifier vos informations de connexion.</span>
                </p>
                <?php endif; ?>
                <p><label for="host">Host <input id="host" type="text" name="host" value="<?php
                    echo isset($_POST["host"])?$_POST["host"]:"localhost"; ?>" /></label></p>
                <p><label for="username">Utilisateur <input id="username" type="text" name="username" value="<?php
                    echo isset($_POST["username"])?$_POST["username"]:""; ?>" /></label></p>
                <p><label for="password">Mot de passe <input id="password" type="password" name="password" value="<?php
                    echo isset($_POST["password"])?$_POST["password"]:""; ?>" /></label></p>
                <p><label for="database">Base de données <input id="database" type="text" name="database" value="<?php
                    echo isset($_POST["database"])?$_POST["database"]:""; ?>" /></label></p>
            </fieldset>
            <fieldset>
                <legend>Envoi des mails</legend>
                <p><label for="email">Email expéditeur <input id="email" type="text" name="email" value="<?php
                    echo isset($_POST["email"])?$_POST["email"]:""; ?>" /></label></p>
            </fieldset>
            <fieldset>
                <legend>Autre</legend>
                <p><label for="key">Clé <input id="key" type="text" name="key" value="<?php
                    echo isset($_POST["key"])?$_POST["key"]:$key; ?>" size="30" /></label></p>
                <p>Cette clé est utilisée pour effectuer les tâches cron. Elle ne doit pas être communiquée.<br />
                 Pensez à la sauvegarder.</p>
            </fieldset>
            <fieldset>
                <legend>Créer votre premier compte utilisateur</legend>
                <p><label for="user_email">Email <input id="user_email" type="text" name="user_email" value="<?php
                    echo isset($_POST["user_email"])?$_POST["user_email"]:""; ?>" /></label></p>
                <p><label for="user_password">Mot de passe <input id="user_password" type="password" name="user_password" value="<?php
                    echo isset($_POST["user_password"])?$_POST["user_password"]:""; ?>" /></label></p>
            </fieldset>
            <fieldset>
                <legend>Terminer l'installation</legend>
                <p><input type="submit" value="Envoyer" /></p>
            </fieldset>
        </form>
        <?php endif; ?>
    </body>
</html>