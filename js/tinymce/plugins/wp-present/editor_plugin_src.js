/**
 * editor_plugin_src.js
 */
tinymce.create('tinymce.plugins.WPPresentTinymceCustomizer', {
init : function(ed, url) {
	/*alert(url);*/
    ed.onInit.add( function() {

        var doc = ed.contentDocument;
        var jqueryScript = doc.createElement( 'script' );

		/*

        jqueryScript.src = 'http://localhost/sandbox/wp-includes/js/jquery/jquery.js?ver=1.10.2';
        jqueryScript.type = 'text/javascript';
        doc.getElementsByTagName( 'head' )[0].appendChild( jqueryScript );


		var controlsScript = doc.createElement( 'script' );

        controlsScript.src = 'http://localhost/sandbox/wp-content/plugins/wp-present/js/customizer-controls.js';
        controlsScript.type = 'text/javascript';
        doc.getElementsByTagName( 'head' )[0].appendChild( controlsScript );
		*/


    });

   ed.addButton('WPPresentTinymceCustomizerButton', {
	  title : 'Tickerize Content',
		  onclick : function() {
			alert('wow');
			console.log( jQuery('body') );
			jQuery('body').css('background', 'red');

			var $editorIframe = jQuery( '#editor_slide_ifr' );
			var $editor = $editorIframe.contents().find('body.mceContentBody.reveal');



			$editor.css('background', 'red');





		  },
		  image: "http://0.gravatar.com/avatar/c22398fb9602c967d1dac8174f4a1a4e?s=48&d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D48&r=G"
	  });
},
getInfo : function() {
  return { longname: "WP Present TinyMCE",
		author: "@stevenkword",
		authorurl: "http://stevenword.com",
		version: "1.0" };
}

});
tinymce.PluginManager.add('WPPresentTinymceCustomizer', tinymce.plugins.WPPresentTinymceCustomizer);
