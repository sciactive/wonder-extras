<?php

if (!file_exists('release/'))
	die('You must have a release directory with releases in it. You can run assemblerelease.sh first.');

$releases = array_diff(scandir('release/'), array('.', '..'));
foreach ($releases as $key => $cur_release) {
	if (!is_dir('release/'.$cur_release))
		unset($releases[$key]);
}
$releases = array_values($releases);

if (empty($releases))
	die('No releases were found in your release directory.');

function clean_filename($filename) {
	return str_replace('..', 'fail-danger-dont-use-hack-attempt', $filename);
}
if (isset($_REQUEST['directory'])) {
	$directory = $_REQUEST['directory'];
	if (!in_array($directory, $releases))
		die('Directory must be an existing release directory.');
	$directory = 'release/'.$directory;
	if (!file_exists($directory))
		die('Directory doesn\'t exist.');
	$file = clean_filename($_REQUEST['file']);
	switch ($_REQUEST['submit']) {
		case "Build .php":
			include('slim/archive.php');

			$arc = new slim;
			$arc->stub = file_get_contents('extract-template.php');
			$arc->working_directory = $directory;
			$arc->file_integrity = true;
			if ($_REQUEST['remove_self_extract'] != 'ON')
				$arc->ext['keep_self'] = true;
			$arc->add_directory('.');
			header('Content-Type: application/octet-stream');
			header("Content-Disposition: attachment; filename=\"$file.php\"");

			$arc->write('php://output');
			break;
		case "Build .tar.gz":
			header('Content-Type: application/x-gzip');
			header("Content-Disposition: attachment; filename=\"$file.tar.gz\"");
			print `cd $directory && tar -czhf - --exclude-vcs *`;
			break;
		case "Build .tar.bz2":
			header('Content-Type: application/x-bzip-compressed-tar');
			header("Content-Disposition: attachment; filename=\"$file.tar.bz2\"");
			print `cd $directory && tar -cjhf - --exclude-vcs *`;
			break;
		case "Build .zip":
			header('Content-Type: application/zip');
			header("Content-Disposition: attachment; filename=\"$file.zip\"");
			print `cd $directory && find -L ./* | egrep -v ".svn" | zip - -@`;
			break;
	}
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Release Builder</title>
	<meta charset="utf-8" />
	<style type="text/css" media="all">
		/* <![CDATA[ */
		.wrapper {
			margin: 3em;
			font-family: sans;
			font-size: 80%;
		}
		.wrapper fieldset {
			border: 1px solid #040;
			-moz-border-radius: 10px;
		}
		.wrapper legend {
			padding: 0.5em 0.8em;
			border: 2px solid #040;
			color: #040;
			font-size: 120%;
			-moz-border-radius: 10px;
		}
		.wrapper label {
			display: block;
			margin-left: 4em;
		}
		.wrapper label span {
			display: inline-block;
			width: 180px;
		}
		.wrapper input, .wrapper select {
			color: #040;
		}
		.wrapper .buttons {
			text-align: right;
		}
		/* ]]> */
	</style>
	<script type="text/javascript">
		function explain_archive() {
			alert("\
When you create a Slim PHP Self Extractor \"Build .php\", you have the option to\n\
make the Slim archive remove itself after extracting all of its files. This\n\
makes it easier for someone installing Pines, because they don't have to remove\n\
the file manually.");
		}
	</script>
</head>
<body>
<div class="wrapper">
	<form action="" method="post">
		<fieldset>
			<legend>Pines Release Builder</legend>
			<p>
				Use this release builder to build packages from the sources in
				the given directory in your releases directory. After you click
				one of the build options, you will be given the chance to save
				the file to your hard drive.
			</p>
			<label>
				<span>Release directory:</span>
				<select name="directory">
					<?php foreach ($releases as $cur_release) { ?>
					<option value="<?php echo htmlspecialchars($cur_release); ?>"><?php echo htmlspecialchars($cur_release); ?></option>
					<?php } ?>
				</select>
			</label><br />
			<label>
				<span>Filename to save as:</span>
				<input type="text" name="file" value="pines-VERSION-STATE-DBBACKEND" />
			</label><br />
			<label>
				<span>Remove Slim extractor: <a href="javascript:void(0);" onclick="explain_archive();">(?)</a></span>
				<input type="checkbox" name="remove_self_extract" value="ON" />
			</label><br />
			<div class="buttons">
				<input type="submit" value="Build .php" name="submit" />
				<input type="submit" value="Build .tar.gz" name="submit" />
				<input type="submit" value="Build .tar.bz2" name="submit" />
				<input type="submit" value="Build .zip" name="submit" />
			</div>
		</fieldset>
	</form>
</div>
</body>
</html>