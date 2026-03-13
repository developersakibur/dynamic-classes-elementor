# Dynamic Classes for Elementor

> 🎨 Create reusable CSS spacing classes (gap, padding, margin) directly in Elementor Site Settings

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/plugins/dynamic-classes-elementor/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net/)
[![Elementor Version](https://img.shields.io/badge/Elementor-3.5%2B-pink.svg)](https://elementor.com/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

## 📋 Table of Contents

- [Why This Plugin?](#-why-this-plugin)
- [Features](#-features)
- [Installation](#-installation)
- [Usage](#-usage)
- [Performance](#-performance)
- [Security](#-security)
- [Developer Guide](#-developer-guide)
- [Contributing](#-contributing)
- [Support](#-support)

## 🎯 Why This Plugin?

### The Problem
When building websites with Elementor, you often need to:
- Apply the same spacing values repeatedly across different elements
- Maintain consistent design system spacing
- Adjust spacing globally without editing individual elements
- Use fluid, responsive spacing with CSS `clamp()`

### The Solution
**Dynamic Classes for Elementor** lets you define spacing classes once in Site Settings and reuse them anywhere. Change one value, update everywhere instantly.

### Benefits
✅ **Consistency** - Maintain uniform spacing across your entire site  
✅ **Speed** - Apply spacing with a single dropdown selection  
✅ **Flexibility** - Update all instances by changing one definition  
✅ **Organization** - Keep your design system in one centralized location  
✅ **Fluid Spacing** - Built-in `clamp()` support for responsive layouts  

## ✨ Features

### 🚀 Dynamic Class Types
- 🎨 **Gap Classes** — Unified or individual control for Row and Column gaps. Optimized for Flexbox and Grid Containers, as well as legacy Sections and Columns.
- 📦 **Padding Classes** — Surgical control for all four sides (Top, Right, Bottom, Left). Applies to Containers, Sections, Columns, and even individual Widgets.
- 📐 **Margin Classes** — Full margin customization to manage external spacing between elements. Works across all Elementor structural elements and widgets.
- 📏 **Min-Height Classes** — Set fluid or fixed minimum heights. Perfect for ensuring consistent hero sections or feature boxes across screen sizes.
- ↔️ **Max-Width Classes** — Take control of content width. Support for boxed containers (via `--content-width`), full-width containers (via `--width`), and widget constraints.

### 🛠 Technical Highlights
- ⚙️ **Site Settings Integration** — Manage your entire design system spacing from a single centralized tab in Elementor Kit settings.
- 📏 **Clamp Calculator** — Built-in fluid typography and spacing generator to create perfectly responsive `clamp()` values without leaving the editor.
- 🚀 **Zero Bloat** — CSS is generated only for the classes you create and injected inline. No extra CSS files or HTTP requests.
- 🔒 **Secure & Optimized** — Full input sanitization, strict CSS value validation, and compatibility with Elementor's CSS regeneration.
- 🌍 **Translation Ready** — Fully internationalized and ready for your language.

## 📥 Installation

### From WordPress.org
1. Go to `Plugins → Add New`
2. Search for "Dynamic Classes for Elementor"
3. Click `Install Now` and then `Activate`

### Manual Installation
1. Click the green **Code** button at the top of this repository.
2. Select **Download ZIP**.
3. Go to your WordPress admin dashboard.
4. Navigate to `Plugins → Add New → Upload Plugin`.
5. Upload the downloaded ZIP file.
6. Activate the plugin.

### Requirements
- WordPress 5.8+
- Elementor 3.5.0+
- PHP 7.4+

## 🚀 Usage

### Step 1: Define Your Classes

1. Go to **Elementor → Site Settings**
2. Click on the **Dynamic Classes** tab
3. Add your spacing classes using the repeater fields

**Example Gap Class:**
```
Name: gap-sm
Row Gap: clamp(10px, 8.57px + 0.45vw, 15px)
Column Gap: clamp(10px, 8.57px + 0.45vw, 15px)
```

**Example Padding Class:**
```
Name: padding-section
Top: clamp(35px, 27.86px + 2.23vw, 60px)
Right: clamp(15px, 13.57px + 0.45vw, 20px)
Bottom: clamp(35px, 27.86px + 2.23vw, 60px)
Left: clamp(15px, 13.57px + 0.45vw, 20px)
```

**Example Margin Class:**
```
Name: margin-stack
Top: clamp(8px, 7.43px + 0.18vw, 10px)
Right: 0
Bottom: clamp(8px, 7.43px + 0.18vw, 10px)
Left: 0
```

### Step 2: Apply Classes to Elements

1. Edit any Container, Section, or Column in Elementor
2. Go to the **Advanced** tab
3. Scroll to the **Dynamic Classes** section
4. Select your class from the dropdown
5. The spacing is applied automatically via generated CSS

### Step 3: Update Globally

Need to change spacing across your site?
1. Go to **Site Settings → Dynamic Classes**
2. Update the value in the repeater
3. All elements using that class update automatically

---

## 🎁 Default Classes

The plugin ships with a comprehensive set of **Fluid Spacing Classes** out of the box. These use carefully calculated `clamp()` values optimized for a viewport range of **320px to 1280px**.

| Type | Class Range | Description |
| :--- | :--- | :--- |
| **Gap** | `gap-10` → `gap-100` | Fluid row and column gaps for layout consistency. |
| **Padding** | `padding-10-20` → `padding-100-20` | Responsive padding (Vertical-Horizontal patterns). |
| **Margin** | `margin-top-10` → `margin-top-100` | Fluid top margins for perfect element stacking. |
| **Min-Height** | `min-height-400` → `min-height-1400` | Fluid container heights (300px mobile → Target desktop). |
| **Max-Width** | `max-width-400` → `max-width-1400` | Fluid width constraints (375px mobile → Target desktop). |

> 💡 **Note:** You can view, edit, or delete these defaults at any time in **Site Settings → Dynamic Classes**.

## ⚡ Performance

CSS is generated fresh on each page load and injected as an inline style. Because the values are stored in Elementor's Kit (which is cached by Elementor itself), there is no additional database overhead per request.

### CSS Output
- ~50–200 bytes of CSS per class
- Injected via `wp_add_inline_style` — no extra HTTP requests
- Works seamlessly with Elementor's built-in CSS regeneration

### Clearing Styles
If spacing changes aren't appearing on the frontend:
1. In the Elementor editor: **Tools → Regenerate Files & Data**
2. Clear your browser cache
3. Clear any server-side page cache

## 🔒 Security

### Input Sanitization
All user inputs are validated before being written to CSS:

```php
// Class names are sanitized to safe HTML class strings
$class_name = sanitize_html_class($input);

// CSS values are validated against a strict whitelist
$value = $this->validate_css_value($input);
```

### Validation Rules
- **Class names** — Only safe HTML class characters via `sanitize_html_class()`
- **CSS values** — Whitelist of safe units (px, em, rem, %, vh, vw, etc.)
- **CSS functions** — `calc()`, `clamp()`, `min()`, `max()`, `var()` are allowed with balanced parentheses and character-safe content
- **No arbitrary code execution** — All values are escaped before output

### Capability Checks
```php
// Only administrators can access the Dynamic Classes settings tab
if (!current_user_can('manage_options')) {
    return;
}
```

## 👨‍💻 Developer Guide

### Hooks & Filters

#### Modify the Generated CSS

```php
add_filter('dce_dynamic_css', function($css) {
    // Append custom CSS after the generated output
    $css .= '.my-custom-class { gap: 15px; }';
    return $css;
});
```

### Get Classes Programmatically

```php
$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();

$gap_classes     = $kit->get_settings('dce_gap_classes');
$padding_classes = $kit->get_settings('dce_padding_classes');
$margin_classes  = $kit->get_settings('dce_margin_classes');
```

### File Structure

```
dynamic-classes-elementor/
├── assets/                        # CSS and JS for the editor
├── data/                          # Default class definitions (JSON)
├── includes/                      # Core logic and modular classes
├── languages/                     # Translation files
├── dynamic-classes-elementor.php  # Main entry point
└── README.md                      # This file
```

### Code Standards
- Follows WordPress Coding Standards
- Fully documented with PHPDoc
- Sanitization on all inputs, escaping on all outputs
- Translation-ready with `.pot` file support

## 🤝 Contributing

Contributions are welcome!

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -am 'Add new feature'`
4. Push to the branch: `git push origin feature/my-feature`
5. Submit a Pull Request

### Coding Guidelines
- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Add PHPDoc comments for all functions
- Sanitize all inputs, escape all outputs
- Test with WordPress Debug mode enabled (`WP_DEBUG = true`)

## 💬 Support

### Need Help?
- 💬 [Support Forum](https://wordpress.org/support/plugin/dynamic-classes-elementor/)
- 🐛 [Report a Bug](https://github.com/yourusername/dynamic-classes-elementor/issues)
- 💡 [Request a Feature](https://github.com/yourusername/dynamic-classes-elementor/issues)

### Common Issues

**Q: Classes not showing in the dropdown?**  
A: Make sure you've saved your Site Settings after adding or editing classes.

**Q: Spacing changes not appearing on the frontend?**  
A: Go to **Elementor → Tools → Regenerate Files & Data**, then clear your browser and server cache.

**Q: Getting a white screen after activation?**  
A: Check your PHP error log. Ensure PHP 7.4+ and Elementor 3.5.0+ are active.

**Q: The `clear_css_cache()` method isn't working?**  
A: CSS caching was removed in v3.2.0. CSS is now generated fresh on every request — no cache to clear.

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Dynamic Classes for Elementor
Copyright (C) 2024 DEVSR

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

**Made by [DEVSR](https://github.com/yourusername)** | [WordPress.org](https://wordpress.org/plugins/dynamic-classes-elementor/)

⭐ Star this repo if it helped you!
