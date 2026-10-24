# Demo Logo & Favicon Assets

Brand: Demo
Style: metallic gold/silver D emblem and DEMO wordmark, transparent background.

Files:
- `logo.png`: full transparent primary logo (high-resolution PNG; wordmark included).
- `favicon.png`: transparent square icon derived from the D emblem/orbital mark; optimized for small sizes.
- `favicon.ico`: multi-resolution browser favicon (16, 32, 48 px).
- PNG favicon and web-app icon sizes for common browser, mobile, and manifest use.

Integration notes:
- Use `logo.png` for brand/logo placements and preserve its aspect ratio (`height: auto`, `object-fit: contain`).
- Use `favicon.ico` and `favicon-96x96.png` for browser favicon references.
- Use `apple-touch-icon.png` for Apple touch icons and the 192/512 icons in the web manifest as needed.
- The browser favicon uses the D emblem rather than the complete DEMO wordmark because full text is unreadable at 16–32 px.
- All PNG assets preserve transparency; do not flatten them onto a black/white background.
