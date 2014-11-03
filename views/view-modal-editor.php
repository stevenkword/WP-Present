<button class="js--open-media-modal button">Open a modal</button>
<script type="text/template" id="tmpl-modal-content">
	<h1>Hi, I&#39;m a Modal!</h1>
	<?php
		wp_editor( $content = '', $editor_id = 'modal_editor', array(
			'wpautop' => false, // use wpautop?
			'media_buttons' => true, // show insert/upload button(s)
			'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
			'textarea_rows' => 20,
			'tabindex' => '',
			'tabfocus_elements' => ':prev,:next', // the previous and next element ID to move the focus to when pressing the Tab key in TinyMCE
			'editor_css' => '<style>wp-editor-area{ background: blue; }</style>', // intended for extra styles for both visual and Text editors buttons, needs to include the <style> tags, can use "scoped".
			'editor_class' => '', // add extra class(es) to the editor textarea
			'teeny' => false, // output the minimal editor config used in Press This
			'dfw' => false, // replace the default fullscreen with DFW (needs specific DOM elements and css)
			/*
			'tinymce' => array(
				'plugins' => 'tabfocus,paste,media,fullscreen,wordpress,wpeditimage,wpgallery,wplink,wpdialogs,wpfullscreen',
			 ),
			*/
			'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
		) );
	?>
</script>