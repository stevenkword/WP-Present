<button class="js--open-media-modal button">Open a modal</button>
<script type="text/template" id="tmpl-modal-content">
			<div class="modal-inner-left">
				<div class="ui-dialog-buttonpane modal-buttons">
					<button id="publish-button" class="button button-primary">Publish</button>
					<button id="update-button" class="button button-primary">Update</button>
					<button id="cancel-button" class="button">Cancel</button>
				</div>

				<?php WP_Present_Modal_Customizer::instance()->render(); ?>
			</div>
			<div class="modal-inner-right">
				<?php $this->modal_editor(); ?>
			</div>
</script>