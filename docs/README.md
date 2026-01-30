# Laravel Approval Workflow Documentation

This directory contains the documentation website for the Laravel Approval Workflow package.

## Overview

Laravel Approval Workflow is a flexible, database-driven approval workflow engine for Laravel applications. It supports multi-step approval chains, parallel approvals, SLA monitoring, and dynamic assignment.

## Features

- **Multiple Workflow Versions**: Define and evolve workflows over time without breaking existing instances
- **Dynamic Assignment**: Assign tasks to users, roles (Spatie Permission integration), or managers
- **Flexible Modes**: Support for 'any' (one person approves) or 'all' (consensus required) modes
- **SLA Monitoring**: Built-in support for task deadlines and breach recording
- **Delegation**: Automatic redirection of tasks based on user availability (vacation/out-of-office)
- **Audit Trail**: Detailed event logging for every action
- **Conditional Transitions**: Override default flow based on actions (approve/reject/changes_requested)

## Documentation Files

### Static Website Files

- `index.html` - Landing page with features and quick start guide
- `documentation.html` - Complete API reference and usage guide with code examples
- `404.html` - Custom 404 error page
- `_redirects` - Netlify redirect rules
- `netlify.toml` - Netlify deployment configuration

### Additional Documentation

- `../README.md` - Main package README with installation and basic usage
- `../EXAMPLES.md` - Detailed code examples for all features

## Deployment

### Deploy to Netlify

1. Connect your repository to Netlify
2. Set build directory to `docs`
3. No build command needed (static HTML)
4. Deploy!

### Deploy to GitHub Pages

1. Go to repository Settings → Pages
2. Set source to deploy from a branch
3. Select `main` branch and `/docs` folder
4. Save and wait for deployment

### Manual Deployment

Simply upload the contents of this directory to any static hosting service (Vercel, AWS S3, etc.).

## Local Development

Open the HTML files directly in your browser:

```bash
open docs/index.html
# or
open docs/documentation.html
```

Or use a simple HTTP server:

```bash
cd docs
python3 -m http.server 8000
# Visit http://localhost:8000
```

## Technology Stack

- **HTML5**: Semantic markup
- **Tailwind CSS**: Utility-first CSS framework (CDN)
- **Highlight.js**: Code syntax highlighting
- **Vanilla JavaScript**: Interactive features
- **Inter Font Family**: Typography
- **Kantumruy Pro**: Khmer language support

## Updating Documentation

When updating the package features or API:

1. Update `../README.md` - Main package documentation
2. Update `../EXAMPLES.md` - Code examples
3. Update `index.html` - Landing page features
4. Update `documentation.html` - API reference and usage guide
5. Test all code examples to ensure they work
6. Deploy updated documentation

## SEO & Meta Tags

All HTML pages include:
- Proper title tags
- Meta descriptions
- Open Graph tags
- Twitter Card tags
- Structured data for search engines

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Graceful degradation for older browsers
- Accessibility features (ARIA labels, keyboard navigation)

---

**Package**: putheakhem/approval-workflow  
**Author**: Puthea Khem  
**Repository**: https://github.com/putheakhem/approval-workflow  
**License**: MIT

## Support

For issues, questions, or feature requests:
- Open an issue on GitHub
- Check the EXAMPLES.md for detailed usage patterns
- Review the test suite for additional examples