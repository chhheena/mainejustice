<?php 
defined('_JEXEC') or die;
use Joomlart\SocialFeed\Util\CrawlerContent;

if (count($displayData['links'])) {
	$error = false;
	$msg = 'Data is ready to crawl!';
	$content = new CrawlerContent();
	$content->loadData($displayData['links'][0], $displayData['profile']);
	$item = $content->getData();
} else {
	$error = true;
	$msg = 'Error, no data to crawl! Please check your setting.';
}
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex">
		<title>Crawler Verify</title>
		<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" />
	</head>
	<body>
		<?php if ($error): ?>
		<div class="alert alert-danger" role="alert" style="text-align:center;">
			<?php echo $msg ?>
		</div>
		<?php else: ?>
		<div class="alert alert-success" role="alert" style="text-align:center;">
			<?php echo $msg ?>
		</div>
		<?php endif ?>

		<?php if (!$error): ?>
		<div class="container">
			<pre class="container small card card-bodysmall"><?php print_r($displayData['links']) ?></pre>
			<h1>Demo Content</h1>
			<p class="lead">Title</p>
			<?php echo '<pre class="card card-bodysmall">'.print_r($item['title'], 1).'</pre>'; ?>
			<p class="lead">Alias</p>
			<?php echo '<pre class="card card-bodysmall">'.print_r($item['alias'], 1).'</pre>'; ?>
			<p class="lead">Published</p>
			<?php echo '<pre class="card card-bodysmall">'.print_r($item['publish_up'], 1).'</pre>'; ?>
			<p class="lead">Images</p>
			<?php echo '<pre class="card card-bodysmall">'.print_r(json_decode($item['images']), 1).'</pre>'; ?>
			<p class="lead">Intro Text</p>
			<?php echo '<pre class="card card-bodysmall">'.print_r($item['introtext'], 1).'</pre>'; ?>
			<p class="lead">Full Text</p>
			<?php echo '<pre class="card card-bodysmall">'.print_r($item['fulltext'], 1).'</pre>'; ?>
		</div>
		<?php endif ?>
	</body>
</html>