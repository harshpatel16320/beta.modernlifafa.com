=== Block Lab Pro ===
Contributors: blocklabstudio
Tags: gutenberg, blocks, custom blocks, block builder, fields, dynamic content, editor
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Donate link: https://blocklab.dev/donate

Custom Gutenberg blocks. Blocks with fields, styling, and dynamic content preview in real time.

== Description ==

**Block Lab Pro** empowers developers and content creators to build custom Gutenberg blocks without writing JavaScript. Design sophisticated blocks with custom fields, live styling controls, and real-time previews — all from a visual interface.

**Why Code When You Can Build Visually?**

Stop wrestling with React and block.json files. Block Lab Pro handles the complex parts of block development while giving you complete control over the output.

**Key Features:**

*   **Visual Block Builder** – Create blocks using an intuitive drag-and-drop interface
*   **Custom Fields** – 20+ field types including text, image, repeater, color, and more
*   **Live Preview** – See exactly how your block will look while editing
*   **Dynamic Content** – Pull data from posts, users, custom fields, and external sources
*   **Template Editor** – Control block HTML output with Twig-like templating
*   **Style Controls** – Built-in spacing, colors, typography, and responsive settings
*   **Block Patterns** – Save and reuse block configurations across your site
*   **Export/Import** – Share blocks between projects or with clients

**Available Field Types:**

*   Text, Textarea, WYSIWYG Editor
*   Image, Gallery, File
*   Select, Multiselect, Checkbox, Radio
*   Number, Range Slider
*   Date, Time, DateTime
*   Color Picker
*   URL, Email, Password
*   Toggle, Button Group
*   Repeater (repeatable field groups)
*   Relationship (link to posts, users, terms)
*   Rich Text with formatting

**Perfect For:**

*   Custom call-to-action blocks
*   Team member profiles
*   Testimonial sliders
*   Pricing tables
*   Location maps with custom data
*   Product showcases
*   Event listings
*   Portfolio grids
*   Any custom content block

== Installation ==

= From WordPress.org (Recommended): =

1. Navigate to **Plugins → Add New** in your WordPress admin.
2. Search for "Block Lab Pro".
3. Click **Install Now**, then **Activate**.

= Manual Upload: =

1. Download the plugin zip file.
2. Navigate to **Plugins → Add New → Upload Plugin**.
3. Choose the downloaded zip file and click **Install Now**.
4. Activate the plugin.

= Via FTP: =

1. Upload the plugin files to `/wp-content/plugins/block-lab-pro/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Block Lab → Add New** to create your first custom block.
4. Configure fields, styling, and template.
5. Your block will appear in the Gutenberg inserter.

== Frequently Asked Questions ==

= Do I need to know React or JavaScript? =

No! Block Lab Pro generates all the necessary code for you. You only need to know basic HTML/CSS if you want to customize templates.

= Can I use custom post types in my blocks? =

Yes. The Relationship field type allows you to query and display content from any post type, including custom ones.

= Is the live preview accurate? =

Yes. The editor preview matches the frontend output 1:1, including responsive breakpoints and dynamic data.

= Can I export blocks to use on other sites? =

Yes. Blocks can be exported as JSON files and imported on any site running Block Lab Pro.

= Does it work with multisite? =

Yes. You can network-activate the plugin or activate per-site. Blocks can be shared across the network.

= Can I add custom CSS to blocks? =

Yes. Each block has a dedicated CSS panel, and you can assign custom classes for theme-level styling.

= Does it support block styles and variations? =

Yes. You can create multiple style variations for each block that users can switch between in the editor.

= Where can I get support? =

Pro users can email pro@blocklab.dev or access priority support at https://blocklab.dev/support.

== Screenshots ==

1. Visual block builder interface with field palette
2. Live preview showing block in editor and frontend
3. Template editor with syntax highlighting
4. Field configuration panel with validation options
5. Style controls for spacing, colors, and typography
6. Dynamic content query builder
7. Block export/import management screen

== Changelog ==

= 1.0.0 - 2025-01-15 =
* Initial release
* Visual drag-and-drop block builder
* 20+ custom field types
* Real-time frontend preview in editor
* Dynamic content integration (posts, users, terms)
* Template system with conditional logic
* Built-in responsive style controls
* Block patterns and reusable configurations
* Export/Import functionality
* Multisite support
* Relationship field for content querying

== Upgrade Notice ==

= 1.0.0 =
Initial release. Build custom Gutenberg blocks visually with Block Lab Pro!

== Credits ==

**Copyright:** BlockLab Studio (pro@blocklab.dev)

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

== Additional Info ==

**Plugin URI:** https://blocklab.dev/pro  
**Author URI:** https://blocklab.dev  
**Text Domain:** block-lab-pro  
**Domain Path:** /languages/

**Documentation:** https://blocklab.dev/docs  
**Changelog:** https://blocklab.dev/changelog  

**Privacy Policy:** Block Lab Pro does not collect personal data from site visitors. Block configurations are stored locally in your WordPress database.

**Third-Party Libraries:**
*   CodeMirror (template editing) - MIT License
*   React Select (field selectors) - MIT License
*   Day.js (date handling) - MIT License