<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
$data = json_decode($json);
$error = '';

if (isset($data->errors)) {
	$errorInfo = $data->errors[0];
	$error = 'Code (' . $errorInfo->code . '): ' . $errorInfo->message;
}
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex">
		<title>Twitter Verify</title>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" />	
	</head>
	<body>
		<?php if ($error): ?>
		<div class="alert alert-danger" role="alert" style="text-align:center;">
			<?php echo $error ?>
		</div>
		<?php else: ?>
		<div class="alert alert-success" role="alert" style="text-align:center;">
			Verify successfully!
		</div>
		<?php endif ?>
		
		<?php if (!$error): ?>
			<pre class="data-container container"><?php print_r($data) ?></pre>	
		<?php endif ?>
	</body>
</html>