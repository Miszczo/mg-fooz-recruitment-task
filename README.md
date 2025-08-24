## Reviewer Guide (for task verification)

### URLs
- Archive: `/library/`
- Single book: `/library/{post-slug}/`
- Genre taxonomy: `/book-genre/{slug}/`
- Genres index: `/book-genre/`

### Pretty permalinks
- WP Admin → Settings → Permalinks → select "Post name" → Save Changes (flush rewrite).

### Demo data (optional)
- Tools → Demo Seeder → "Generate demo books". This creates demo `library` posts, placeholder cover and ensures default `book-genre` terms.

### FAQ Page
- Create a page named "FAQ" with slug `faq`.
- Insert the block "FAQ Accordion". It comes with predefined items (editable). The block is dynamic (SSR) and includes ARIA behavior (keyboard + deep-link) and JSON-LD `FAQPage` output when present.

### Where to tweak styles
- Global tokens: `wp-content/themes/twentytwentyfive-child/theme.json`.
- Custom CSS: `wp-content/themes/twentytwentyfive-child/assets/css/main.css`.
  - FAQ selectors: `.wp-block-fooz-faq-accordion`, `.wp-block-fooz-faq-item`, `.faq-item__question`, `.faq-item__answer`.

### Header navigation
- Menu items: Library (/library/), Genres (/book-genre/), FAQ (/faq/). If the FAQ page does not yet exist, the link may 404 until created.

## Answers to assignment (Task #1–#5)

### Task #1 – Where to place custom CSS?
- `assets/css/main.css` (child theme), complemented by tokens in `theme.json`. No heavy CSS frameworks.

### Task #2 – Load custom JavaScript in footer
- File: `assets/js/scripts.js`.
- Enqueued in footer via `includes/enqueue-assets.php` (`in_footer=true`).

### Task #3 – CPT "Books" with taxonomy "Genre"
- CPT slug: `library`, taxonomy slug: `book-genre`, both with translatable labels, `show_in_rest=true`.
- Defined in `includes/post-types.php` and `includes/taxonomies.php`.

### Task #4.1 – Single Book page
- Template `templates/single-library.php` displays title, featured image (LCP eager), publication date, genres (links), content.
- Below content: "Related books" list loaded via REST (20 latest excluding current), with spinner, error/empty states, cache and retry.

### Task #4.2 – Genre page (taxonomy)
- Handled by `templates/taxonomy-book-genre.html`, a modern block template for Full Site Editing. It's configured to show 5 posts per page with pagination. Additionally, a custom genres index is available at `/book-genre/`.

### Task #5 – Gutenberg block: FAQ Accordion
- Custom dynamic block pair: `fooz/faq-accordion` (container) + `fooz/faq-item` (items).
- Editor JS: `blocks/faq-accordion/index.js` (registers blocks, predefined items, save() returns null; SSR in PHP).
- PHP SSR: `functions.php` (`render_callback`) outputs accessible markup; JSON-LD `FAQPage` is injected on pages containing the block.


