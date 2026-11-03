# Bubba Hub WordPress Theme

Standalone Bubba Hub 3.0 block theme, migrated from `bubbashub1/bubbawp/theme/bubba-hub`.

**Status:** Development/staging only. Do not activate on the live website until functionality and page parity have been verified.

## Page title heroes (staging)
The default WordPress page template includes a full-width title hero. Each page can choose its own background using the page's **Featured Image** in the editor. Pages without a featured image show the forest-colour overlay. The homepage and activity preview use separate templates.

To change the shared layout, use Appearance → Editor → Templates → Pages. To change only one page's background, edit that page's Featured Image. Existing Site Editor customizations stored in the WordPress database can override updated theme files; check template revisions if changes are not visible.

The hero uses a core Cover block with a Post Title child. Test editor block validation and frontend image rendering on staging before release. This theme change does not alter BubbaWP activity data or plugin features.
