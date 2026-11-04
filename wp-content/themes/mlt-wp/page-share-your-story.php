<?php
/**
 * Share Your Story Page Template matching src/app/share-your-story/page.tsx & ShareStoryForm.tsx 1:1.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topic_labels = function_exists( 'mlt_get_topic_labels' ) ? mlt_get_topic_labels() : array();
$contact      = function_exists( 'mlt_get_contact_info' ) ? mlt_get_contact_info() : array( 'whatsapp_url' => 'https://wa.me/254727246599' );

get_header();
?>
<main data-testid="share-your-story-page" class="mx-auto max-w-2xl px-6 py-16">
	<?php
	mlt_render_breadcrumb(
		array(
			array( 'label' => 'Home', 'href' => '/' ),
			array( 'label' => 'Stories', 'href' => '/stories' ),
			array( 'label' => 'Share Your Story' ),
		)
	);
	?>
	<h1 class="text-3xl font-bold">Share Your Story</h1>
	<p class="mt-4 text-neutral-600">
		For the first time, another man might realise he&apos;s not the only one going through
		this — because you shared yours. Your name is optional, and nothing is published without
		your consent.
	</p>

	<form data-testid="share-story-form" class="mt-8 space-y-6">
		<div>
			<label for="name" class="block text-sm font-medium">
				Name (optional — leave blank to stay anonymous)
			</label>
			<input
				id="name"
				name="name"
				type="text"
				maxlength="100"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
		</div>
		<div>
			<label for="ageOrCategory" class="block text-sm font-medium">
				Age / category (optional, e.g. &ldquo;34, father of two&rdquo;)
			</label>
			<input
				id="ageOrCategory"
				name="ageOrCategory"
				type="text"
				maxlength="100"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
		</div>
		<fieldset>
			<legend class="text-sm font-medium">What&apos;s your story about? (choose at least one)</legend>
			<div class="mt-2 grid grid-cols-2 gap-2 text-sm text-neutral-600 sm:grid-cols-3">
				<?php foreach ( $topic_labels as $value => $label ) : ?>
					<label class="flex items-center gap-2">
						<input type="checkbox" name="<?php echo esc_attr( $value ); ?>" />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>
		<div>
			<label for="storyText" class="block text-sm font-medium">
				Your story (text, video link below, or both)
			</label>
			<textarea
				id="storyText"
				name="storyText"
				rows="6"
				maxlength="5000"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			></textarea>
		</div>
		<div>
			<label for="videoUrl" class="block text-sm font-medium">
				Video link (optional, e.g. YouTube or Vimeo)
			</label>
			<input
				id="videoUrl"
				name="videoUrl"
				type="url"
				class="mt-1 w-full rounded-md border border-neutral-300 px-3 py-2"
			/>
		</div>
		<div class="space-y-3 rounded-md bg-neutral-100 p-4 text-sm text-neutral-700">
			<p class="font-medium text-neutral-900">Before you submit</p>
			<p>
				Every story is reviewed by our team before it&apos;s published — it won&apos;t appear on
				the site right away.
			</p>
			<label class="flex items-start gap-2">
				<input type="checkbox" name="processingConsentGiven" required class="mt-1" />
				<span>
					I consent to Men Let&apos;s Talk collecting and storing the information I submit in
					this form, as described in our
					<a href="/privacy" class="underline">Privacy Notice</a>.
				</span>
			</label>
			<label class="flex items-start gap-2">
				<input type="checkbox" name="consentGiven" required class="mt-1" />
				<span>
					I consent to Men Let&apos;s Talk publishing this story — including my name and
					category, if I&apos;ve provided them — on the website and its social channels. It may
					be lightly edited for length or clarity, and I can ask for it to be removed at any
					time by
					<a href="<?php echo esc_url( $contact['whatsapp_url'] ); ?>" class="underline">contacting Men Let&apos;s Talk on WhatsApp</a>.
				</span>
			</label>
		</div>
		<p data-testid="share-story-error" class="text-sm text-red-600" style="display:none;"></p>
		<button
			type="submit"
			class="rounded-md bg-neutral-900 px-6 py-3 font-semibold text-white disabled:opacity-60"
		>Share Your Story</button>
	</form>

	<p data-testid="share-story-success" class="mt-8 text-neutral-700" style="display:none;">
		Thank you for sharing — your story has been submitted for review. Our team will read it,
		and once approved, it&apos;ll appear on our Stories page.
	</p>
</main>
<?php
get_footer();
