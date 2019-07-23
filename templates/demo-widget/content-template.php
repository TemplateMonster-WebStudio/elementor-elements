<?php
echo <<<HTML
<#
	view.addRenderAttribute('container', {
		'class': ['demo-class'],
	});
#>
<div {{{view.getRenderAttributeString('container')}}}>
	<h2>{$this->get_title()}</h2>
	<# console.log( view ); #>
</div>
HTML;
