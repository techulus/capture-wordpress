=== Capture - Screenshots & PDF ===
Contributors: techulus
Tags: screenshot, pdf, capture, webpage, shortcode
Requires at least: 4.7
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Embed website screenshots and PDFs using Capture API with simple shortcodes.

== Description ==

Capture is a WordPress plugin that allows you to easily embed website screenshots and generate PDFs using the Capture API. Simply use shortcodes in your posts and pages to automatically generate and display screenshots or provide PDF download links.

**Features:**

* Easy-to-use shortcodes for screenshots and PDFs
* Configurable default settings
* Responsive images with lazy loading
* Test API connection directly from admin
* Secure API credential storage
* Support for all Capture API parameters
* Clean, semantic HTML output

**Requirements:**

* Capture API account (get yours at https://capture.page/console)
* WordPress 4.7+
* PHP 7.4+

**Shortcode Examples:**

Screenshot: `[capture_screenshot url="https://example.com" vw=1200 vh=800 full=true]`

PDF: `[capture_pdf url="https://example.com" format="A4" text="Download PDF"]`

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/capture-screenshots-pdf/` directory, or install through WordPress admin directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to Settings → Capture to configure your API credentials.
4. Get your API key and secret from https://capture.page/console
5. Test your connection and start using shortcodes!

== Frequently Asked Questions ==

= Do I need a Capture account? =

Yes, you need an API key and secret from Capture. Sign up at https://capture.page/console

= What parameters can I use with shortcodes? =

**Screenshot shortcode parameters:**

*Required:*
* `url` - Target website URL

*Viewport & Capture Area:*
* `vw` - Viewport width in pixels (default: 1440)
* `vh` - Viewport height in pixels (default: 900)
* `scaleFactor` - Screen scale factor (default: 1)
* `top` - Top offset for clipping (default: 0)
* `left` - Left offset for clipping (default: 0)
* `width` - Clipping width (default: viewport width)
* `height` - Clipping height (default: viewport height)

*Timing & Waiting:*
* `waitFor` - CSS selector to wait for
* `waitForId` - Element ID to wait for
* `delay` - Delay in seconds before capture (default: 0)

*Screenshot Options:*
* `full` - Capture full page (true/false)
* `darkMode` - Take dark mode screenshot (true/false)
* `blockCookieBanners` - Dismiss cookie consent (true/false)
* `blockAds` - Block advertisements (true/false)
* `bypassBotDetection` - Solve captchas (true/false)

*Selectors:*
* `selector` - Screenshot specific CSS selector
* `selectorId` - Screenshot specific element ID
* `transparent` - Transparent background (true/false)

*Device & Rendering:*
* `userAgent` - Custom user agent
* `emulateDevice` - Specific device emulation
* `httpAuth` - HTTP Basic Authentication

*Image Processing:*
* `resizeWidth` - Resize image width
* `resizeHeight` - Resize image height
* `type` - Image format (png, jpeg, webp - default: png)
* `bestFormat` - Optimize image format (true/false, default: true)
* `fileName` - Custom filename for the image (e.g., "screenshot.png")

*Caching:*
* `fresh` - Force new screenshot (true/false)

*WordPress Specific:*
* `class` - CSS class for the image
* `alt` - Alt text for the image
* `loading` - Loading attribute (lazy/eager)

**PDF shortcode parameters:**

*Required:*
* `url` - Target website URL

*Page Dimensions:*
* `width` - Paper width (with units)
* `height` - Paper height (with units)
* `format` - Paper size (Letter, Legal, Tabloid, Ledger, A0-A6, default: A4)

*Margins:*
* `marginTop` - Top margin
* `marginRight` - Right margin  
* `marginBottom` - Bottom margin
* `marginLeft` - Left margin

*Rendering:*
* `scale` - Rendering scale (default: 1)
* `landscape` - Paper orientation (true/false, default: false)
* `printBackground` - Print background graphics (true/false)

*Timing:*
* `delay` - Seconds to wait before capturing (default: 0)
* `timestamp` - Force page reload

*Authentication:*
* `httpAuth` - HTTP Basic Authentication (base64url encoded)
* `userAgent` - Custom user agent (base64url encoded)

*File Options:*
* `fileName` - Custom filename for the PDF (e.g., "document.pdf")

*WordPress Specific:*
* `text` - Link text (default: "Download PDF")
* `target` - Link target (_blank/_self)
* `class` - CSS class for the link

= Can I customize the appearance? =

Yes! The plugin includes CSS classes you can target:
* `.capture-screenshot` - For screenshot images
* `.capture-pdf` - For PDF download links
* `.capture-error` - For error messages

= Is it secure? =

Yes, the plugin follows WordPress security best practices:
* Input sanitization and validation
* Nonce verification for admin actions
* Secure credential storage
* Output escaping

== External services ==

This plugin relies on Capture (https://capture.page), a third-party service for generating website screenshots and PDFs. This external service is essential for the plugin's core functionality.

**What the service is and what it is used for:**
Capture (https://capture.page) is a browser automation API that can generate screenshot and PDFs from webpage URLs. It is used by this plugin to process the URLs provided in the shortcodes and return the corresponding screenshot or PDF files.

**What data is sent and when:**
- The target website URL that you want to capture (sent when using shortcodes)
- Screenshot parameters like viewport size, image format, and capture options
- PDF parameters like paper size, orientation, and margins
- Your API credentials (key and hash generated using secret) for authentication
- Data is sent every time a [capture_screenshot] or [capture_pdf] shortcode is processed on your website

**Service links:**
- Terms of Service: https://capture.page/terms
- Privacy Policy: https://capture.page/privacy
- Documentation pages https://docs.capture.page

**API Endpoint:**
The plugin connects to https://cdn.capture.page to generate screenshots and PDFs.

**User consent:**
By using this plugin, website administrators acknowledge that they are sending website URLs and request parameters to Capture service.

== Screenshots ==

1. Admin settings page with API configuration
2. Test connection feature
3. Usage examples and documentation
4. Screenshot shortcode in action
5. PDF download link example

== Changelog ==

= 1.2.0 =
* Added fileName parameter for both screenshot and PDF shortcodes
* Allows custom filenames for downloads (e.g., fileName="screenshot.png" or fileName="report.pdf")

= 1.1.0 =
* Added support for all API parameters
* Set bestFormat to true by default for optimized image formats
* Improved parameter validation and type handling

= 1.0.0 =
* Initial release
* Screenshot and PDF shortcodes
* Admin settings page
* API connection testing
* Responsive design
* Security features

== Upgrade Notice ==

= 1.0.0 =
Initial release of Capture plugin.
