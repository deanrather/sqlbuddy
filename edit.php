<?php
/*

SQL Buddy - Web based MySQL administration
http://www.sqlbuddy.com/

edit.php
- edit specific rows from a database table

MIT license

2008 Calvin Lough <http://calv.in>

*/

include "functions.php";

loginCheck();

requireDatabaseAndTableBeDefined();

if (isset($db))
	$conn->selectDB($db);

if (isset($db))
	$structureSql = $conn->query("DESCRIBE `$table`");

if (isset($_POST['editParts']))
{
	$editParts = $_POST['editParts'];
	$editParts = explode("; ", $editParts);
	
	$totalParts = count($editParts);
	$counter = 0;
	
	$firstField = true;
	
	?>
	<script type="text/javascript" authkey="<?php echo $requestKey; ?>">
	
	if ($('EDITFIRSTFIELD'))
	{
		$('EDITFIRSTFIELD').focus();
	}
	
	</script>
	<?php
	
	foreach ($editParts as $part)
	{
		
		$part = trim($part);
		
		if ($part != "" && $part != ";")
		{
		
		?>
		
		<form id="editform<?php echo $counter; ?>" querypart="<?php echo $part; ?>" onsubmit="saveEdit('editform<?php echo $counter; ?>'); return false;">
		<div class="errormessage" style="margin: 6px 12px 10px; width: 338px; display: none"></div>
		<table class="insert edit" cellspacing="0" cellpadding="0">
		<?php
		
		$dataSql = $conn->query("SELECT * FROM `" . $table . "` " . $part);
		$dataRow = $conn->fetchAssoc($dataSql);
		
		if (@$conn->rowCount($structureSql))
		{
			while ($structureRow = $conn->fetchAssoc($structureSql))
			{
				
				preg_match("/^([a-z]+)(.([0-9]+).)?(.*)?$/", $structureRow['Type'], $matches);
				
				$curtype = $matches[1];
				$cursizeQuotes = $matches[2];
				$cursize = $matches[3];
				$curextra = $matches[4];
				
				echo '<tr>';
				echo '<td class="fieldheader"><span style="color: steelblue">';
				if ($structureRow['Key'] == 'PRI') echo '<u>';
				echo $structureRow['Field'];
				if ($structureRow['Key'] == 'PRI') echo '</u>';
				echo "</span> " . $curtype . $cursizeQuotes . ' ' . $structureRow['Extra'] . '</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td class="inputarea">';
				
				$showLargeEditor[] = "text";
				$showLargeEditor[] = "mediumtext";
				$showLargeEditor[] = "longtext";
				
				if (in_array($structureRow['Type'], $showLargeEditor))
				{
					echo '<textarea name="' . $structureRow['Field'] . '">' . $dataRow[$structureRow['Field']] . '</textarea>';
				}
				elseif (substr($structureRow['Type'], 0, 4) == "enum")
				{
					$trimmed = substr($structureRow['Type'], 6, -2);
					$listOptions = explode("','", $trimmed);
					echo '<select name="' . $structureRow['Field'] . '">';
					echo '<option> - - - - - </option>';
					foreach ($listOptions as $option)
					{
						echo '<option value="' . $option . '"';
						if ($option == $dataRow[$structureRow['Field']])
						{
							echo ' selected="selected"';
						}
						echo '>' . $option . '</option>';
					}
					echo '</select>';
				}
				elseif (substr($structureRow['Type'], 0, 3) == "set")
				{
					$trimmed = substr($structureRow['Type'], 5, -2);
					$listOptions = explode("','", $trimmed);
					foreach ($listOptions as $option)
					{
						$id = $option . rand(1, 1000);
						echo '<label for="' . $id . '"><input name="' . $structureRow['Field'] . '[]" value="' . $option . '" id="' . $id . '" type="checkbox"';
						
						if (strpos($dataRow[$structureRow['Field']], $option) > -1)
							echo ' checked="checked"';
						
						echo '>' . $option . '</label><br />';
					}
				}
				else
				{
					echo '<input type="text"';
					if ($firstField)
						echo ' id="EDITFIRSTFIELD"';
					echo ' name="' . $structureRow['Field'] . '" class="text" value="' . htmlentities($dataRow[$structureRow['Field']], ENT_QUOTES, 'UTF-8') . '" />';
				}
				
				$firstField = false;
				
				?>
				
				</td>
				</tr>
				
				<?php
			}
		}
		
		$conn->dataSeek($structureSql, 0);
		
		?>
		<tr>
		<td>
		<label><input type="radio" name="SB_INSERT_CHOICE" value="SAVE" checked="checked" /><?php echo __("Save changes to original"); ?></label><br />
		<label><input type="radio" name="SB_INSERT_CHOICE" value="INSERT" /><?php echo __("Insert as new row"); ?></label>
		</td>
		</tr>
		<tr>
		<td style="padding-top: 10px; padding-bottom: 25px">
		<input type="submit" class="inputbutton" value="<?php echo __("Submit"); ?>" />&nbsp;&nbsp;<a onclick="cancelEdit('editform<?php echo $counter; ?>')"><?php echo __("Cancel"); ?></a>
		</td>
		</tr>
		</table>
		</form>
		
		
		<?php
		
		$counter++;
		
		}
		
	}
	
}

?>