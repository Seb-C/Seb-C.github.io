<?php

date_default_timezone_set('Europe/Paris');

function getPageContent(array $articles, $modelFileName) {
	ob_start();
	include($modelFileName);
	return ob_get_clean();
}

// Removing html pages previously generated
$files = glob('../*.{html,xml}', GLOB_BRACE);
foreach($files as $file) {
	unlink($file);
}

// Creating articles pages
$files = glob('articles/*.html', GLOB_BRACE);
$articlesData = array();
foreach($files as $file) {
	// Getting article content
	ob_start();
	include($file);
	$articleContent = ob_get_clean();
	$filename = basename($file);
	
	// Getting title (first h1)
	preg_match('/<h1>(.*)<\/h1>/', $articleContent, $matches);
	$title = $matches[1];

	// Getting date (first <time> datetime attribute)
	preg_match('/<time datetime="([^"]*)">(.*)<\/time>/', $articleContent, $matches);
	$datetime = isset($matches[1]) ? $matches[1] : '';
	$datetimeFormatted = isset($matches[2]) ? $matches[2] : '';
	$timestamp = empty($datetime) ? 0 : strtotime($datetime);
	
	$articlesData[] = $articleData = array(
		'datetime'          => $datetime,
		'datetimeFormatted' => $datetimeFormatted,
		'timestamp'         => $timestamp,
		'title'             => $title,
		'filename'          => $filename,
		'content'           => $articleContent,
	);
	
	// Setting article page itself
	$pageContent = getPageContent(array($articleData), 'model.html');
	file_put_contents('../'.$filename, $pageContent);
}

// Sorting articles (last one at first)
usort($articlesData, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// Creating RSS file
$rssContent = getPageContent($articlesData, 'feedModel.xml');
file_put_contents('../feed.xml', $rssContent);

// Creating homepage file
$articlesDataHome = array();
foreach($articlesData as $i => $articleData) {
	if($i >= 2) {
		$articleData['content'] = '
			<time datetime="'.$articleData['datetime'].'">'.$articleData['datetimeFormatted'].'</time>
			<a href="'.$articleData['filename'].'"><h1>'.$articleData['title'].'</h1></a>
		';
	}
	$articlesDataHome[] = $articleData;
}
$homeContent = getPageContent($articlesDataHome, 'model.html');
file_put_contents('../index.html', $homeContent);

echo "DONE\n";







