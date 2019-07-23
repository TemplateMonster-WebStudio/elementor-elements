<?php

$title = $this->get_title();

echo <<<HTML
<div {$container_atts}>
	<h2>{$title}</h2>
</div>
HTML;
