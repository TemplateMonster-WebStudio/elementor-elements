<?php
echo <<<HTML
<#
	view.addRenderAttribute('container', {
		'class': ['demo-class'],
		'data-settings': settings.settings_json,
	});
#>
<div {{{view.getRenderAttributeString('container')}}}>
	<h2>{$this->get_title()}</h2>
	<pre><#

		var content = (function(obj) {

			function _print(o, indent){

				var
					_obj = o||{},
					_indent = indent||'',
					_str = '';

				for(var key in _obj) {

					if('object' === typeof _obj[key]) {
						_str += indent + key + ': {\\n' + _print(_obj[key], indent + '\\t') + indent + '}\\n';
					} else {
						_str += indent + key + ': ' + _obj[key].toString() + '\\n';
					}
				}

				return _str;
			}

			return _print(obj, '');
		})(settings);

	#>{{{content}}}</pre>
</div>
HTML;
