<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package modern_lifafa
 */

?>


<?php if (is_singular() && !dokan_is_seller_dashboard()): ?>
	<footer id="colophon" class="site-footer">
		<div class="site-info">
			<a href="<?php echo esc_url(__('https://wordpress.org/', 'modern_lifafa')); ?>">
				<?php
				/* translators: %s: CMS name, i.e. WordPress. */
				printf(esc_html__('Proudly powered by %s', 'modern_lifafa'), 'WordPress');
				?>
			</a>
			<span class="sep"> | </span>
			<?php
			/* translators: 1: Theme name, 2: Theme author. */
			printf(esc_html__('Theme: %1$s by %2$s.', 'modern_lifafa'), 'modern_lifafa', '<a href="http://underscores.me/">Underscores.me</a>');
			?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
<?php endif; ?>
</div><!-- #page -->
<span id="siteseal">
	<script async type="text/javascript"
		src="https://seal.godaddy.com/getSealBasic?sealID=FMvubElfR8bup4e5j4uVB6D0ky9JooRrdomDoPSSqlPMNtgrdvWQLaJXh4PY"></script>
</span>
<script>
	jQuery(document).ready(function () {
		jQuery('input[type="file"]').on('click', function () {
			jQuery(".file_names").html("");
		})
		if (jQuery('input[type="file"]')[0]) {
			var fileInput = document.querySelector('label[for="et_pb_contact_brand_file_request_0"]');
			fileInput.ondragover = function () {
				this.className = "et_pb_contact_form_label changed";
				return false;
			}
			fileInput.ondragleave = function () {
				this.className = "et_pb_contact_form_label";
				return false;
			}
			fileInput.ondrop = function (e) {
				e.preventDefault();
				var fileNames = e.dataTransfer.files;
				for (var x = 0; x < fileNames.length; x++) {
					console.log(fileNames[x].name);
					$ = jQuery.noConflict();
					jQuery('label[for="et_pb_contact_brand_file_request_0"]').append("<div class='file_names'>" + fileNames[x].name + "</div>");
				}
			}
			jQuery('#et_pb_contact_brand_file_request_0').change(function () {
				var fileNames = $('#et_pb_contact_brand_file_request_0')[0].files[0].name;
				jQuery('label[for="et_pb_contact_brand_file_request_0"]').append("<div class='file_names'>" + fileNames + "</div>");
				jQuery('label[for="et_pb_contact_brand_file_request_0"]').css('background-color', '##eee9ff');
			});
		}
	});

</script>


<?php wp_footer(); ?>

</body>

</html>