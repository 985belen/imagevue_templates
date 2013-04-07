/**
 * editor_plugin_src.js
 *
 */
(function() {
	tinymce.create('tinymce.plugins.ImagevueContactForm', {
		init : function(ed, url) {
			var t = this;

			t.editor = ed;

			ed.addCommand('mceImagevueContactForm', function() {
				ed.execCommand('mceInsertContent', false, '<img src="' + url + '/img/contactform.gif" />');
			});

			ed.addButton('ivcontactform', {title : 'Insert Contact Form', cmd : 'mceImagevueContactForm'});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = o.content.replace(
					/<img[^>]+src="contactform"[^>]*>/igm,
					'<img src="' + url + '/img/contactform\.gif" />'
				);
			});

			ed.onGetContent.add(function(ed, o) {
				o.content = o.content.replace(
					/<img[^>]+src="[^">]+\/img\/contactform.gif"[^>]+>/igm,
					'<img src="contactform" />'
				);
			});

			ed.onNodeChange.add(function(ed, cm, e) {
				var regexp = /\/img\/contactform\.gif$/i;
				if ('img' == e.nodeName.toLowerCase() && regexp.test(e.src)) {
					cm.setActive('ivcontactform', true);
					return false;
				} else
					cm.setActive('ivcontactform', false);
			});
		},

		getInfo : function() {
			return {
				longname : 'Insert Contact Form',
				author : 'Imagevuex',
				authorurl : 'http://imagevuex.com',
				infourl : 'http://imagevuex.com',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('ivcontactform', tinymce.plugins.ImagevueContactForm);
})();