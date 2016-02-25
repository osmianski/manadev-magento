<?php if(in_array($_SERVER['REMOTE_ADDR'], array('78.60.93.251', '127.0.0.1')) || strpos($_SERVER['REMOTE_ADDR'], '192.168.') === 0) : ?>
	<?php 
	$workPath = dirname(__FILE__);
	if (!($mageLocalXml = simplexml_load_string(file_get_contents($workPath.'/app/etc/local.xml')))) {
        throw new Exception(sprintf('ERROR: %s file not found.', $workPath.'/app/etc/local.xml'));
    }  
	$connectionXml = $mageLocalXml->global->resources->default_setup->connection;
	?>
	<?php if(isset($_POST['query'])) : ?>
	<?php
		try {
			//$_POST['query'] = str_replace("\'", "'", $_POST['query']);
            if (strpos((string)$connectionXml->host, '/') !== false) {
                $host = 'unix_socket=' . (string)$connectionXml->host;
            }
            elseif (strpos((string)$connectionXml->host, ':') !== false) {
                list($host, $port) = explode(':', (string)$connectionXml->host);
                $host = 'host=' . $host . ';port='.$port;
            }
            else {
                $host = 'host=' . (string)$connectionXml->host;
            }
            /* @var $db PDO */ $db = new PDO('mysql:'.$host.';dbname='.(string)$connectionXml->dbname,
				(string)$connectionXml->username,
				(string)$connectionXml->password);
			foreach (explode(";", $_POST['query']) as $query) {
                if (strpos(strtoupper(trim($query)), 'SELECT') === 0) {
                    $result = $db->query($query);
                    ?>
                    <?php if ($result) : ?>
                    <table border="1">
                        <tr>
                            <?php for ($columnIndex = 0; $columnIndex < $result->columnCount(); $columnIndex++) : ?>
                                <?php $column  = $result->getColumnMeta($columnIndex); ?>
                                <th><?php echo $column['name'] ?></th>
                            <?php endfor; ?>
                        </tr>
                        <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <?php foreach ($row as $value) : ?>
                                <td><?php echo htmlentities($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                    <?php
                        $result->closeCursor();
                        else: ?>
                        <?php echo json_encode($db->errorInfo()) ?>
                    <?php endif; ?>
                    <?php
                }
                else {
                    echo sprintf('%d rows affected.', $db->exec($query));
                }
            }
			$db = null;
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
	<form method="POST" action="mysql.php">
		<p>DATABASE: <?php echo (string)$connectionXml->dbname ?>, PREFIX: <?php echo (string)$mageLocalXml->global->resources->db->table_prefix ?></p>
        <textarea name="query" cols="100" rows="25"><?php if (isset($_POST['query'])) : ?><?php echo $_POST['query'] ?><?php endif;?></textarea>
		<button type="submit">Go</button>
	</form>
<?php endif; ?>