<?php
session_start();
error_reporting(E_ALL ^ E_NOTICE);

include('./includes/functions.php');

define('PMDROOT',str_replace('/install/','',dirname(str_replace('\\','/',__FILE__)).'/'));
define('BASE_URL',str_replace('/install/install.php','',preg_replace('/\?.*/', '', getURL())));

if(isset($_GET['email'])) {
    $_POST['admin_email'] = $_GET['email'];
}

if(isset($_POST['complete'])) {
    include('../includes/class_database.php');
    $db = new Database();

    $db_host = $_POST['hostname'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];
    $db_port = $_POST['db_port'];

    if($db_host == "" OR $db_user == "" OR $db_name == "") {
        $errors['database_connect'] = "Database connection details are incorrect.<br>";
    } else {
        if($db) {
            if($db->Connect($db_host, $db_user, $db_pass, $db_name, $db_port)) {
                if($_POST['prefix'] == '') {
                    $errors['prefix_exists'] = 'Please enter a table prefix. (Ex: pmd_)';
                } else {
                    if(!preg_match('/[A-Z]{1}/i',$_POST['prefix']) OR preg_match('/[^A-Z0-9_]+/i',$_POST['prefix'])) {
                        $errors['prefix_format'] = 'Table prefix must consist of letters, numbers and underscores only.';
                    } elseif(count($existing_tables = $db->GetCol("SHOW TABLES LIKE ".$db->Clean($_POST['prefix']."%"))) > 0 AND !isset($_POST['prefix_overwrite'])) {
                        $errors['prefix_exists'] = 'Table prefix in use.  Choose a different prefix, or confirm to overwrite existing data and tables (data will be lost).';
                    }
                }
            } else {
                $errors['database_connect'] = "Database connection details are incorrect.<br>";
            }
        } else {
            $errors['database_connect'] = "Database connection details are incorrect.<br>";
        }
    }
    
    if($_POST['admin_email'] == '' OR $_POST['admin_pass'] == '') {
        $errors['admin_details'] = 'Please fill in all administrator details.<br>';
    }

    if($_POST['admin_pass'] != $_POST['admin_pass2']) {
        $errors['password_mismatch'] = 'The passwords entered do not match.<br>';
    }

    if(!isset($_POST['terms_agree'])) {
        $errors['terms'] = 'You must agree to the phpMyDirectory terms before installing.';
    }

    $password_salt = md5(uniqid(rand(), true));

    $variables = array(
        'url'=>BASE_URL,
        'url_ssl'=>'',
        'url_cdn'=>'',
        'url_cdn_ssl'=>'',
        'db_host'=>$_POST['hostname'],
        'db_user'=>$_POST['db_user'],
        'db_pass'=>addcslashes($_POST['db_pass'],"'\\"),
        'db_name'=>$_POST['db_name'],
        'db_port'=>$_POST['db_port'],
        'db_table_prefix'=>$_POST['prefix'],
        'db_charset'=>$_POST['charset'],
        'db_collate'=>'',
        'admin_email'=>$_POST['admin_email'],
        'admin_pass'=>hash('sha256',$_POST['admin_pass'].$password_salt),
        'admin_password_salt'=>$password_salt,
        'security_key'=>hash('sha256',md5($_POST['hostname'].$_POST['admin_email'].$_POST['admin_pass'])),
        'files_path'=>'NULL',
        'files_url'=>'NULL',
        'pmdroot'=>'',
        'cookie_path'=>'NULL',
        'cookie_domain'=>'NULL',
        'admin_directory'=>'cp'
    );

    if($_POST['charset'] != '') {
        $variables['character_set'] = "CHARACTER SET `".$_POST['charset']."`";
    }

    if(is_array($errors) AND sizeof($errors) < 1) {
        if(isset($_POST['prefix_overwrite'])) {
            foreach((array) $existing_tables as $table) {
                $db->Execute("DROP TABLE ".$db->CleanIdentifier($table));
            }
        }

        try {
            installTables($variables['db_table_prefix'],$variables['db_charset']);
        } catch(Exception $e) {
            exit('Error creating tables: '.$e->getMessage());
        }
        try {
            loadData('email_templates',$variables['db_table_prefix']);
        } catch(Exception $e) {
            exit('Error loading email templates: '.$e->getMessage());
        }
        try {
            loadData('settings',$variables['db_table_prefix']);
        } catch(Exception $e) {
            exit('Error loading settings: '.$e->getMessage());
        }
        try {
            loadData('users_permissions',$variables['db_table_prefix'],'id','?');
        } catch(Exception $e) {
            exit('Error loading user permissions: '.$e->getMessage());
        }
        try {
            loadPhrases($variables['db_table_prefix']);
        } catch(Exception $e) {
            exit('Error loading phrases: '.$e->getMessage());
        }
        try {
            importSQL(PMDROOT.'/install/database/install.sql',$variables);
        } catch(Exception $e) {
            exit('Error importing SQL: '.$e->getMessage());
        }

        if(is_writable('../robots.txt') AND $robots_txt = file_get_contents('../robots.txt')) {
            file_put_contents('../robots.txt',str_replace('#Sitemap: /sitemap.xml','Sitemap: '.BASE_URL.'/sitemap.xml',$robots_txt));
        }

        $defaults_content = buildDefaults($variables);
        if(!writeDefaults($defaults_content)) {
            $_SESSION['defaults_content'] = $defaults_content;
            $install_instructions = '<div class="alert alert-danger"><h4>Error</h4><p>There was a problem saving your configuration file (defaults.php).</p>';
            $install_instructions .= '<p>Please download this file by pressing the button below and upload it to your installation folder.</p>';
            $install_instructions .= '<p>Once defaults.php is uploaded the installation is complete.</p>';
            $install_instructions .= '<form name="downloadfile" action="download_defaults.php" method="post">';
            $install_instructions .= '<input class="btn btn-default" type="submit" name="download" value="Download Configuration File (defaults.php)">';
            $install_instructions .= '</form></div>';
            $install_instructions .= '<div class="alert alert-warning"><h4>Important!</h4>Make sure to delete the /install/ folder for security reasons.</div>';
        } else {
            $install_instructions = '<div class="alert alert-success"><h4>Installation Complete</h4>phpMyDirectory has successfully been installed.  Please review the following notes and options below.</div>';
            $install_instructions .= '<div class="alert alert-warning"><h4>Important!</h4>Make sure to delete the /install/ folder for security reasons.</div>';
            if(substr(decoct(fileperms('../defaults.php')),3) != '644') {
                $install_instructions .= '<div class="alert alert-warning"><h4>Check Permissions</h4>Please make sure the permissions are set to 644 (not writable) on the defaults.php file.</div>';
            }
        }
        $install_instructions .= '
        <div class="row">
            <div class="col-sm-7">
                <div class="well">
                <h4>Manual</h4>
                <p>Review the manual for helpful setup and usage instructions.</p>
                <a class="btn btn-default btn-lg" target="_blank" href="http://manual.phpmydirectory.com"><i class="glyphicon glyphicon-book"></i> View Manual</a>
                </div>
            </div>
            <div class="col-sm-8 col-sm-offset-1">
                <div class="well">
                <h4>Get Started</h4>
                <p>View and access your directory home page and control panel.</p>
                <a class="btn btn-default btn-lg" href="../index.php"><i class="glyphicon glyphicon-home"></i> Directory Home Page</a><br /><br />
                <a class="btn btn-default btn-lg" href="../cp/index.php"><i class="glyphicon glyphicon-user"></i> Control Panel</a>
                </div>
            </div>
            <div class="col-sm-7 col-sm-offset-1">
                <div class="well">
                <h4>Import Old Data</h4>
                <p>Import data from old phpMyDirectory versions.  Please note this section should <b>not</b> be used to import new data.  See the manual for importing new data.</p>
                <a class="btn btn-default btn-lg" href="./import/index.php"><i class="glyphicon glyphicon-download-alt"></i> Import</a>
                </div>
            </div>
        </div>';
    }
}

if(!isset($_POST['complete']) OR sizeof($errors) > 0) {
    $javascript = '<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/jquery.js"></script>';
    $javascript .= '<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/jquery_custom.js"></script>';
    $javascript .= '<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/qTip/jquery_qtip.js"></script>';

    $error = 0;
    if(validPermissions() != -1) {
        $error = 1;
        $results[]['result'] = 'File and folder permissions errors.  The file defaults.php and all folders in /files/ must be writable.';
    }
    if(!validPHPVersion('5.6.0')) {
        $error = 1;
        $results[]['result'] = 'PHP Version not compatible. Current PHP version is '.PHP_VERSION.'. Required version is 5.6 or above.';
    }
    if(!validGD()) {
        $error = 1;
        $results[]['result'] = 'The PHP GD2 image library must be installed to resize and process images.';
    }
    if(!validCURL()) {
        $error = 1;
        $results[]['result'] = 'The PHP CURL extension is required.';
    }
    if(!validionCube('5.0')) {
        $error = 1;
        $results[]['result'] = 'ionCube 5.0+ is required and is either not installed or is an old version.  Please see the phpMyDirectory manual for instructions or try the <a target="_blank" href="../ioncube/loader-wizard.php">ionCube wizard</a>.';
    }

    $phpversion = phpversion();
}
include('./template/header.tpl');
include('./template/install.tpl');
include('./template/footer.tpl');
?>