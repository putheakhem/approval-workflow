# Laravel Approval Workflow Documentation

This directory contains the documentation website for the Laravel Approval Workflow package.

## Files

- `index.html` - Landing page with features and quick start
- `documentation.html` - Complete API reference and usage guide
- `404.html` - Custom 404 error page
- `_redirects` - Netlify redirect rules
- `netlify.toml` - Netlify deployment configuration

## Deployment

### Deploy to Netlify

1. Connect your repository to Netlify
2. Set build directory to `docs`
3. No build command needed (static HTML)
4. Deploy!

### Manual Deployment

Simply upload the contents of this directory to any static hosting service.

## Local Development

Open `index.html` or `documentation.html` in your browser:

```bash
open docs/index.html
```

Or use a simple HTTP server:

```bash
cd docs
python3 -m http.server 8000
# Visit http://localhost:8000
```

## Features

- Modern, responsive design with Tailwind CSS
- Syntax highlighting with Highlight.js
- Smooth scrolling navigation
- Mobile-friendly layout
- SEO optimized

## Technology Stack

- HTML5
- Tailwind CSS (CDN)
- Highlight.js for code syntax
- Vanilla JavaScript
- Inter font family

---

**Package**: putheakhem/approval-workflow  
**Documentation**: https://approval-workflow.netlify.app (example)  
**Repository**: https://github.com/putheakhem/approval-workflow  
**License**: MIT