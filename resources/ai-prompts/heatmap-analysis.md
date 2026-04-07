## Heatmap Analysis Instructions

You have been provided with click/tap heatmap data exported from Microsoft Clarity. Analyse this data and include the following in your report:
- **Most clicked elements**: Identify the top interactive elements and what they suggest about user intent and priorities.
- **Navigation patterns**: Which menu items, links, and CTAs get the most attention? Are users finding what they need?
- **Cookie consent impact**: Quantify how much interaction is consumed by cookie banners (e.g. Cookiebot) vs actual page content.
- **CTA effectiveness**: Compare click rates on primary CTAs (booking buttons, forms) vs secondary elements. Are the main conversion actions getting enough clicks?
- **Dead clicks / rage clicks**: Look for clicks on non-interactive elements (text, images, containers) that suggest users expect them to be clickable — these are UX issues.
- **Mobile-specific patterns**: If the data is from mobile, note touch-specific issues like small tap targets or accidental taps.
- **Scroll depth signals**: Elements deep in the page that still get clicks indicate engaged users; high-ranked elements only at the top suggest users don't scroll.
- **Actionable recommendations**: Based on the heatmap patterns, suggest concrete UI/UX improvements.

The CSV columns are: Rank, Button (CSS selector), Taps/Clicks, % of total taps/clicks. The CSS selectors describe the element's position in the DOM — use class names and IDs to infer what the element is (e.g. `.btn-yellow` is likely a primary CTA, `#accordion` is an FAQ section, `.slick-arrow` is a carousel navigation).
