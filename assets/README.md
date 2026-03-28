# Dynamic Classes for Elementor

> 🎨 Create reusable, fluid CSS spacing classes with `clamp()` support — managed directly inside Elementor Site Settings.

[![Version](https://img.shields.io/badge/Version-3.7.0-6366f1.svg)](https://github.com/developersakibur/dynamic-classes-elementor)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![Elementor](https://img.shields.io/badge/Elementor-3.5%2B-pink.svg)](https://elementor.com/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

---

## 🎯 Why This Plugin?

When building with Elementor, the same spacing values end up repeated across dozens of widgets — each one set manually, each one a maintenance problem. Change your section padding once? You're editing every container individually.

**Dynamic Classes for Elementor** solves this by letting you define spacing classes once in Site Settings and apply them via a dropdown in the Advanced tab of any element. Change one value, update your entire site instantly.

---

## ✨ Features

### Class Types
| Type | CSS Properties | Applies To |
|---|---|---|
| **Gap** | `gap`, `row-gap`, `column-gap` | Containers, Sections, Columns |
| **Padding** | `padding` (all 4 sides) | Containers, Sections, Columns, Widgets |
| **Margin** | `margin` (all 4 sides) | Containers, Sections, Columns, Widgets |
| **Min-Height** | `min-height` | Containers, Sections, Columns |
| **Max-Width** | `max-width`, `--content-width` | Containers, Sections, Columns, Widgets |

### Built-in `clamp()` Calculator
An overlay calculator appears inside the Elementor editor whenever you open the **Dynamic Classes** settings tab. It generates pixel-perfect fluid `clamp()` values for any min/max range — copy the value directly into any class field.

### Export / Import / Reset
Manage your class library from **Settings → Dynamic Classes** in the WordPress admin:
- **Export** — download all classes as a dated JSON backup
- **Import** — merge classes from a JSON file (duplicates are skipped, existing classes are never overwritten)
- **Reset** — restore factory defaults (with confirmation)

### Zero Bloat
CSS is generated only for the classes you define, injected as an inline style via `wp_add_inline_style`. No extra HTTP requests, no separate CSS files.

### Safe Updates
Plugin updates **never overwrite** your custom classes. Defaults are only seeded into empty class types (e.g. on a fresh install).

---

## 📥 Installation

### From WordPress Admin
1. Go to **Plugins → Add New**
2. Search for **Dynamic Classes for Elementor**
3. Click **Install Now** → **Activate**

### Manual (ZIP)
1. Download this repository as a ZIP
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and activate

### Requirements
- WordPress 5.8+
- Elementor 3.5.0+
- PHP 7.4+

---

## 🚀 Usage

### Step 1 — Define your classes

1. Open **Elementor → Site Settings**
2. Click the **Dynamic Classes** tab
3. Add classes using the repeater fields

**Gap example:**
```
Name:       gap-40
Row Gap:    clamp(15px, 6.20px + 2.35vw, 40px)
Column Gap: clamp(15px, 6.20px + 2.35vw, 40px)
```

**Padding example:**
```
Name:   padding-section
Top:    clamp(60px, 43.38px + 4.23vw, 100px)
Right:  clamp(20px, 15.49px + 1.41vw, 40px)
Bottom: clamp(60px, 43.38px + 4.23vw, 100px)
Left:   clamp(20px, 15.49px + 1.41vw, 40px)
```

### Step 2 — Apply to elements

1. Select any Container, Section, Column, or Widget in Elementor
2. Go to the **Advanced** tab (or **Style** tab for widgets)
3. Open the **Dynamic Classes** section
4. Pick a class from the dropdown

### Step 3 — Update globally

Need to change spacing across your whole site?

1. Go to **Site Settings → Dynamic Classes**
2. Edit the value in the repeater
3. Every element using that class updates automatically — no individual editing needed

---

## 🛠 Settings Page

Access via **Settings → Dynamic Classes** in the WordPress admin sidebar.

| Action | Description |
|---|---|
| **Open in Elementor** | Jump directly to the Dynamic Classes tab in the Elementor kit editor |
| **Export JSON** | Download all current classes as a backup file |
| **Import JSON** | Merge classes from a previously exported file |
| **Reset to Defaults** | Replace all classes with plugin factory defaults |

The stats bar at the top shows how many classes you have defined per type.

---

## ⚡ Performance

- CSS is generated from Elementor's Kit settings, which Elementor caches automatically — no extra database queries per request
- Kit data is fetched **once per class type per request** via an internal instance cache, even when dozens of elements render in the editor
- Inline CSS output via `wp_add_inline_style` — zero additional HTTP requests
- CSS is not generated on WP admin pages unrelated to the editor

---

## 🔒 Security

- All inputs sanitised with `sanitize_html_class()` and a strict CSS value whitelist
- CSS functions (`clamp()`, `calc()`, `min()`, `max()`, `var()`) validated for balanced parentheses and safe characters only
- All AJAX actions are nonce-verified and capability-gated (`manage_options`)
- No arbitrary code execution — all values are escaped before output

---

## 👨‍💻 Developer Guide

### Filters

**Modify generated CSS:**
```php
add_filter( 'dce_dynamic_css', function( $css ) {
    $css .= '.my-extra-class { gap: 15px; }';
    return $css;
});
```

**Modify default class data:**
```php
add_filter( 'dce_default_data', function( $data ) {
    $data['gap'][] = [
        'name'       => 'gap-custom',
        'row_gap'    => '20px',
        'column_gap' => '20px',
    ];
    return $data;
});
```

### Access kit classes programmatically
```php
$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();

$gap_classes     = $kit->get_settings( 'dce_gap_classes' );
$padding_classes = $kit->get_settings( 'dce_padding_classes' );
$margin_classes  = $kit->get_settings( 'dce_margin_classes' );
$min_h_classes   = $kit->get_settings( 'dce_min_height_classes' );
$max_w_classes   = $kit->get_settings( 'dce_max_width_classes' );
```

### File Structure
```
dynamic-classes-elementor/
├── assets/
│   ├── css/
│   │   ├── dce-editor.css          # clamp() calculator styles (light mode)
│   │   └── dce-settings-page.css   # WP admin settings page styles
│   └── js/
│       ├── dce-editor.js           # calculator logic
│       └── dce-settings-page.js    # export / import / reset JS
├── data/
│   └── default-classes.json        # factory default class definitions
├── includes/
│   ├── class-ajax-handler.php      # export / import / reset AJAX
│   ├── class-css-generator.php     # builds the CSS string
│   ├── class-data-loader.php       # reads default-classes.json
│   ├── class-element-controls.php  # injects dropdowns into element panels
│   ├── class-plugin.php            # main bootstrap class
│   ├── class-settings-page.php     # WP admin submenu page
│   └── class-settings-tab.php      # Elementor kit settings tab
├── languages/
├── dynamic-classes-elementor.php   # plugin entry point
└── README.md
```

---

## 📦 Default Classes

The plugin ships with pre-calculated fluid classes for a **320px → 1280px** viewport range.

| Type | Classes | Pattern |
|---|---|---|
| Gap | `gap-10` → `gap-100` | 9 steps |
| Padding | `padding-10-10` → `padding-100-20` | Vertical-Horizontal patterns |
| Margin | `margin-top-10` → `margin-top-100` | Top-only fluid margins |
| Min-Height | `min-height-300` → `min-height-1400` | 300px mobile floor |
| Max-Width | `max-width-400` → `max-width-1400` | 375px mobile floor |

All defaults can be edited or deleted in **Site Settings → Dynamic Classes**. They are restored only if you use the **Reset** action.

---

## 📋 Changelog

### 3.7.0
- Added **Settings → Dynamic Classes** submenu in WordPress admin
- Added **Export** — download all classes as JSON
- Added **Import** — merge classes from JSON (non-destructive)
- Added **Reset** — restore factory defaults with confirmation
- Calculator redesigned to **light mode**
- Settings link on Plugins page now points to the new admin page

### 3.6.0
- Fixed critical bug: plugin updates no longer overwrite user-defined classes
- Added instance-level cache to `DCE_CSS_Generator` — kit queried once per type per request
- CSS output moved off generic admin hook; now only runs on frontend + editor preview

### 3.5.0
- Added Min-Height and Max-Width class types
- Built-in clamp() calculator overlay in Elementor editor
- Full support for Elementor Flexbox Containers (e-con)

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
4. Sanitize all inputs, escape all outputs
5. Submit a Pull Request

---

## 💬 Support

- [WordPress Support Forum](https://wordpress.org/support/plugin/dynamic-classes-elementor/)
- [Report a Bug](https://github.com/developersakibur/dynamic-classes-elementor/issues)
- [Request a Feature](https://github.com/developersakibur/dynamic-classes-elementor/issues)

---

## 📄 License

GPL v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html)

---

**Made by [DEVSR](https://github.com/developersakibur)** ⭐ Star this repo if it helped you!
