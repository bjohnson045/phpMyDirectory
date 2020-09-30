<h1><?php echo $lang['admin_maintenance']; ?></h1>
<table class="table table-bordered">
    <tr>
        <td>Server Time</td>
        <td><?php echo $server_time; ?></td>
    </tr>
    <tr>
        <td>Local Time</td>
        <td><?php echo $local_time; ?></td>
    </tr>
    <tr>
        <td>PHP Version</td>
        <td>
            <span class="label label-lg label-<?php echo intval($php) ? 'success' : 'important'; ?>"><?php echo phpversion(); ?></span>
            <span class="label label-lg label-<?php echo intval($php_ini) ? 'success' : 'important'; ?>">PHP INI Settings</span>
        </td>
    </tr>
    <tr>
        <td>MySQL Version</td>
        <td><span class="label label-lg label-<?php echo intval($mysql) ? 'success' : 'important'; ?>"><?php echo $mysql_version; ?></span></td>
    </tr>
    <tr>
        <td><?php echo $lang['admin_maintenance_requirements']; ?></td>
        <td><?php echo $requirements; ?></td>
    </tr>
</table>
<?php if(!$allow_url_fopen) { ?>
<div class="alert alert-warning">
    <h4>allow_url_fopen disabled</h4>
    <p>The PHP configuration setting allow_url_fopen is disabled.  It is recommended to enable this setting.</p>
</div>
<?php } ?>