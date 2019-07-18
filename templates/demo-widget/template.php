<?php

$title = $this->get_title();
$content = print_r( $this->get_settings_for_display(), true );

echo <<<HTML
<div {$container_atts}>
	<h2>{$title}</h2>
	<pre>{$content}</pre>
</div>
HTML;
