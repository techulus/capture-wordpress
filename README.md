# Capture WordPress Plugin

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-6.3%2B-blue)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://www.php.net/)

Embed website screenshots and generate PDFs using the [Capture API](https://capture.page) with simple WordPress shortcodes.

## Features

- 📸 **Screenshot Shortcodes** - Easily embed website screenshots
- 📄 **PDF Generation** - Create downloadable PDFs from web pages
- ⚙️ **Configurable Defaults** - Set default viewport, delay, and other settings
- 📱 **Responsive Images** - Screenshots adapt to different screen sizes
- 🧪 **Test Connection** - Verify API credentials directly from admin

## Quick Start

### Requirements

- WordPress 4.7+
- PHP 7.4+
- [Capture API account](https://capture.page/console) (get your API key and secret)

### Installation

#### Via WordPress Admin
1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the ZIP file and activate the plugin
4. Go to **Settings > Capture** to configure your API credentials

#### Manual Installation
1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. Configure your API settings

## Usage

### Basic Screenshot
```
[capture_screenshot url="https://example.com"]
```

### Advanced Screenshot
```
[capture_screenshot url="https://example.com" vw=1440 vh=900 full=true darkMode=true blockAds=true type="webp"]
```

### PDF Download
```
[capture_pdf url="https://example.com" format="A4" text="Download PDF"]
```

## Screenshot Parameters

### Required
- `url` - Target website URL

### Viewport & Capture Area
- `vw` - Viewport width (default: 1440)
- `vh` - Viewport height (default: 900)  
- `scaleFactor` - Screen scale factor (default: 1)
- `top` - Top offset for clipping
- `left` - Left offset for clipping
- `width` - Clipping width
- `height` - Clipping height

### Screenshot Options
- `full` - Capture full page (true/false)
- `darkMode` - Dark mode screenshot (true/false)
- `blockAds` - Block advertisements (true/false)
- `blockCookieBanners` - Dismiss cookie consent (true/false)
- `selector` - Screenshot specific CSS selector
- `transparent` - Transparent background (true/false)
- `delay` - Delay before capture in seconds
- `type` - Image format (png, jpeg, webp)
- `fresh` - Force new screenshot (true/false)

### WordPress Specific
- `class` - CSS class for the image
- `alt` - Alt text for accessibility
- `loading` - Loading attribute (lazy/eager)

## PDF Parameters

### Required
- `url` - Target website URL

### Page Setup
- `format` - Paper size (A4, Letter, Legal, etc.)
- `landscape` - Orientation (true/false)
- `scale` - Rendering scale (default: 1)
- `marginTop/Bottom/Left/Right` - Page margins

### Options
- `printBackground` - Include background graphics (true/false)
- `delay` - Wait time before capture
- `text` - Link text (default: "Download PDF")
- `target` - Link target (_blank/_self)
- `class` - CSS class for the link

## Documentation

- 📖 [Complete Parameter Reference](https://docs.capture.page/docs/screenshot-options)
- 🎯 [PDF Options](https://docs.capture.page/docs/pdf-options)
- 🌐 [Capture API Documentation](https://docs.capture.page)

## Development

### Local Development with Docker

1. Clone this repository
2. Run `docker-compose up -d`
3. Access WordPress at `http://localhost:8080`
4. The plugin will be automatically available in the WordPress plugins directory

### File Structure

```
capture-wordpress/
├── assets/
│   ├── css/capture-style.css
│   └── js/capture-admin.js
├── includes/
│   ├── class-capture-admin.php
│   └── class-capture-api.php
├── capture-screenshots-pdf.php
├── readme.txt (WordPress.org format)
└── README.md (This file)
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Support

- 🐛 [Report Issues](https://github.com/techulus/capture-wordpress/issues)