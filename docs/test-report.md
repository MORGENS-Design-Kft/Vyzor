# Device, Browser & OS Analysis Report
**Date range analyzed:** 2026-03-30 to 2026-03-31  
**Context used for trend validation:** 2026-03-29 to 2026-03-31 and 2026-04-01

## Executive Summary

### Primary audience segments
- **Mobile is the dominant device by a very large margin**
  - 356 of 440 sessions = **80.9% mobile**
  - PC: **16.1%**
  - Tablet: **3.0%**
- **The audience is heavily app/in-app browser driven**
  - FacebookApp: 194 sessions = **44.1%**
  - ChromeMobile: 121 sessions = **27.5%**
  - Combined, these two account for **71.6% of all sessions**
- **Operating systems are strongly mobile-led**
  - Android: **59.1%**
  - iOS: **24.8%**
  - Combined mobile OS share: **83.9%**
- **Geography is overwhelmingly domestic**
  - Hungary: 415 sessions = **94.3%**

### High-priority conclusion
The website should be treated as a **mobile-first, Hungary-first, Android-first experience**, with special attention to **Facebook in-app browser compatibility** and **landing page performance for campaign traffic**.

---

## 1) Device Distribution

### Breakdown
- **Mobile:** 356 sessions (**80.9%**)
- **PC:** 71 sessions (**16.1%**)
- **Tablet:** 13 sessions (**3.0%**)

### What this means
- The site is primarily consumed on smartphones.
- Desktop still matters, but it is a secondary audience.
- Tablet traffic is too small to be a primary optimization target, though responsive QA should still include it.

### Trend check
This pattern is consistent across adjacent periods:
- **2026-03-29 to 2026-03-31:** Mobile 83.0%, PC 13.0%, Tablet 4.0%
- **2026-04-01:** Mobile 81.3%, PC 14.7%, Tablet 4.0%

### Implication
This is not a one-off spike. The audience profile is consistently mobile-heavy.

---

## 2) Browser Usage Patterns

### Browser breakdown
- **FacebookApp:** 194 (**44.1%**)
- **ChromeMobile:** 121 (**27.5%**)
- **Chrome:** 55 (**12.5%**)
- **MobileSafari:** 37 (**8.4%**)
- **Firefox:** 12 (**2.7%**)
- **SamsungInternet:** 10 (**2.3%**)
- Others: minimal share

### Key findings
- **In-app browsing is the single biggest browser environment.**
  - FacebookApp alone represents nearly half of traffic.
- **Mobile browser traffic dominates overall browser behavior.**
  - FacebookApp + ChromeMobile + MobileSafari + SamsungInternet + GoogleApp + InstagramApp = overwhelming majority of sessions.
- **Desktop browser diversity is low.**
  - Chrome is the main desktop browser; Edge, Safari, and Firefox are small.

### Compatibility concerns
The biggest risk area is not classic browser fragmentation; it is **in-app browser behavior**, especially:
- FacebookApp
- InstagramApp
- potentially GoogleApp

These environments often introduce issues with:
- cookie/session handling
- external booking redirects
- payment or embedded form flows
- JavaScript event handling
- opening maps, phone links, and booking engines
- cross-domain tracking between `arthotel.hu` and `book.arthotel.hu`

### Trend check
Browser mix is stable:
- FacebookApp remains the #1 browser across all compared periods
- ChromeMobile remains #2
- MobileSafari is consistently relevant but much smaller

This suggests optimization for Facebook in-app browsing should be a standing priority, not a temporary response.

---

## 3) Operating System Breakdown

### OS breakdown
- **Android:** 260 (**59.1%**)
- **iOS:** 109 (**24.8%**)
- **Windows:** 61 (**13.9%**)
- **Linux:** 6 (**1.4%**)
- **MacOSX:** 4 (**0.9%**)

### Interpretation
- The audience is predominantly **Android-led**
- iOS is the second most important environment
- Desktop operating systems are comparatively minor

### Optimization priority by OS
1. **Android**
   - highest-impact QA environment
   - especially Android + FacebookApp + ChromeMobile
2. **iOS**
   - test MobileSafari and FacebookApp on iPhone
3. **Windows desktop**
   - maintain baseline usability for research/booking comparison behavior

---

## 4) Country / Region Distribution

### Country breakdown
- **Hungary:** 415 (**94.3%**)
- Austria: 10 (**2.3%**)
- Slovakia: 5 (**1.1%**)
- Remaining countries: negligible share

### Geographic pattern
- Traffic is overwhelmingly local/domestic.
- There is a small nearby regional audience from neighboring countries, but not enough to drive major localization investment based on this dataset alone.

### What this means
The site should primarily optimize for:
- **Hungarian-language messaging**
- local travel intent
- mobile campaign traffic from Hungarian users
- local trust and booking reassurance

### Secondary opportunity
Austria and Slovakia appear repeatedly in the broader date ranges, suggesting some consistent regional interest. If growth is a goal, nearby-country targeting could be explored with:
- multilingual landing pages
- translated offers
- booking reassurance for international guests

---

## 5) Traffic Source Context and Its Device Implications

Although this is a device/browser report, referrer data is highly relevant because it explains the device mix.

### Top referrers
- `m.facebook.com`: 144 sessions
- `google.com`: 121 sessions
- direct/unknown: 43 sessions
- internal pages also drive onward navigation

### Key insight
Traffic is strongly shaped by:
- **Facebook mobile traffic**
- **Google search traffic**
- internal navigation from offers/gallery/homepage

This explains why:
- mobile dominates
- FacebookApp dominates
- landing page experience matters heavily

### Likely user intent by source
- **Facebook users:** promotional discovery, lower-intent browsing, campaign-driven visits
- **Google users:** higher-intent research, likely comparing offers, rooms, gallery, booking details

---

## 6) Cross-Device Behavior Differences

Direct behavior metrics by device are not provided, so conclusions here are inferred from the audience mix, page popularity, and frustration metrics.

### Likely mobile behavior
Given the traffic composition, mobile users are likely:
- landing directly on promotional pages
- browsing offers and visual content
- moving between homepage, offers, and gallery
- deciding quickly whether content matches their intent

### Likely desktop behavior
Desktop users are fewer, but they are often more likely to:
- compare room details
- inspect practical information
- move deeper into booking evaluation

### Evidence supporting intent-based differences
Popular pages are dominated by:
- homepage
- offers page
- seasonal offer pages
- prize game page
- gallery

This pattern suggests mobile users are engaging in **promotional browsing and visual evaluation**, not deep content exploration.

### Supporting engagement indicators
For 2026-03-30 to 2026-03-31:
- **Pages/session:** 2.31
- **Average scroll depth:** 47.21%
- **Quick backs:** 21.59% of sessions

This combination suggests:
- users often view only a few pages
- many do not deeply consume page content
- a meaningful share quickly return/back out after landing

### Interpretation by device
Because 81% of sessions are mobile, these engagement/friction indicators are likely driven mainly by mobile users.  
That makes mobile landing page clarity especially important.

---

## 7) Friction and Compatibility Signals

## High-priority friction indicators

### Quick backs are elevated
- **21.59% of sessions** had quick backs
- This is also consistent across broader periods:
  - 21.1% for 2026-03-29 to 2026-03-31
  - 16.07% on 2026-04-01

### What it may indicate
- mismatch between ad/social/search expectation and landing page content
- mobile visitors not finding key information quickly
- in-app browser or page speed issues
- promotional pages not surfacing next steps clearly enough

### Dead clicks
- **6.82% of sessions** had dead clicks
- consistent in the broader data range

### What it may indicate
- users tapping non-clickable elements
- image/gallery elements appearing interactive when they are not
- buttons/CTAs not responding as expected in some browsers
- UI ambiguity on mobile

### Script errors
- **2.5% of sessions** affected
- rose to **4.46%** on 2026-04-01, so this deserves monitoring

### What it may indicate
Potential browser-specific or in-app compatibility issues, especially in:
- FacebookApp
- MobileSafari
- cross-domain booking transitions
- third-party widgets/scripts

### Low-severity signals
- Rage clicks: **0.23%**
- Error clicks: **0.23%**
- Excessive scroll: **0%**

These are reassuring. The main problem is not severe broken UX; it is more likely **landing-page mismatch, unclear interaction cues, or app-browser edge cases**.

---

## 8) Page-Level Context Relevant to Devices

### Most visited pages
- Homepage: 232
- Offers page: 148
- Prize game page: 60
- Spring break offer: 51
- Gallery: 36
- Easter offer: 32

### What stands out
- Offer and campaign-related pages are central to the journey
- Gallery traffic is meaningful, suggesting users want visual reassurance
- The booking subdomain appears as a referrer, meaning users are moving between the main site and booking engine

### Device-related implication
On mobile, these pages need to be especially strong at:
- fast loading
- showing pricing/offer relevance quickly
- making booking CTA obvious
- reducing unnecessary scrolling
- keeping visual content tappable and intuitive

---

## 9) Primary Audience Segments

## Segment 1: Facebook mobile users on Android
**Largest segment overall**
- Likely entering via `m.facebook.com`
- Likely browsing in Facebook in-app browser
- Likely campaign or promo oriented
- Likely sensitive to load speed and clarity above the fold

**Priority:** Highest

## Segment 2: Google mobile users on Android/iPhone
- Likely higher-intent search traffic
- More likely to compare offers, rooms, gallery, and practical info
- Needs SEO landing page relevance and strong conversion paths

**Priority:** High

## Segment 3: Desktop research users, mainly Chrome on Windows
- Smaller segment but potentially valuable
- Likely further along in the consideration process
- Needs full-content usability and clean booking handoff

**Priority:** Medium

## Segment 4: iPhone users in MobileSafari / FacebookApp
- Smaller than Android, but still material
- Should be tested separately because iOS browser behavior often differs from Android, especially around forms, sticky elements, and external links

**Priority:** Medium-high

---

## 10) Optimization Priorities

## Priority 1: Optimize for Facebook in-app browser
**Why**
- FacebookApp is 44.1% of all sessions
- Referrals from Facebook are the top external source
- In-app browsers commonly create UX and tracking problems

**Actions**
- Test all high-traffic pages in Facebook in-app browser on Android and iPhone
- Verify:
  - CTA buttons
  - booking links
  - sticky headers/bars
  - form interactions
  - phone/map links
  - gallery/lightbox interactions
- Review whether booking links should:
  - open in external browser where appropriate
  - or be more clearly explained to users

## Priority 2: Improve mobile landing-page clarity on top entry pages
**Why**
- Quick back rate is high
- Mobile dominates traffic
- Popular entry pages are homepage, offers, campaign pages, gallery

**Actions**
- On homepage and offer pages, place these elements above the fold:
  - key value proposition
  - 1 primary CTA
  - offer validity/details summary
  - booking CTA
  - trust signals
- Make offer pages immediately answer:
  - what is included
  - for whom
  - dates
  - price cue / request action
  - next step to book

## Priority 3: Audit dead-click areas on mobile
**Why**
- 6.82% session impact is notable
- Mobile users often tap images, cards, icons, and headings expecting interaction

**Actions**
- Review session recordings/heatmaps for:
  - gallery pages
  - offer cards
  - homepage hero/banner
  - image blocks
- Convert frequently tapped visual elements into actual links or add clearer affordance
- Improve button styling and touch feedback

## Priority 4: Validate booking journey across domains
**Why**
- `book.arthotel.hu` appears in referrers
- Cross-domain transitions can break in app browsers or affect attribution

**Actions**
- Test handoff from:
  - homepage
  - offers pages
  - room pages
  - gallery pages
- Validate:
  - session continuity
  - booking engine load speed
  - mobile responsiveness
  - no script/cookie blockers in FacebookApp and MobileSafari

## Priority 5: Monitor script errors by browser/OS
**Why**
- Script errors are present and increased on 2026-04-01
- The biggest affected environments are likely mobile/in-app, given audience mix

**Actions**
- Break down script errors by:
  - browser
  - OS
  - page
- Prioritize fixes on pages with high traffic and high business intent
- Check third-party scripts, tracking tags, booking widgets, and gallery components

---

## 11) Recommended QA Matrix

Given the traffic mix, the minimum testing matrix should be:

### Must-test
- **Android + FacebookApp**
- **Android + ChromeMobile**
- **iPhone + FacebookApp**
- **iPhone + MobileSafari**

### Secondary
- **Windows + Chrome**
- **Samsung Internet on Android**
- **Firefox desktop/mobile where feasible**

### Pages to test first
- Homepage
- Offers listing page
- Spring break offer page
- Prize game page
- Gallery page
- Rooms page
- Booking handoff to `book.arthotel.hu`

---

## 12) Business Recommendations

## Immediate actions
- Audit Facebook in-app browser experience on top landing pages
- Reduce mobile quick backs by strengthening message-to-landing-page match
- Fix dead-click patterns on image-heavy and offer-heavy pages
- Test booking CTA and redirect behavior across top mobile environments

## Next-step improvements
- Create mobile-first campaign landing page templates for offers
- Add clearer CTA hierarchy on homepage and offer pages
- Improve visual click affordance on cards, galleries, and banners
- Review page speed for social traffic landings

## Strategic opportunities
- Since Hungary drives 94%+ of traffic, focus messaging and UX primarily on Hungarian domestic travelers
- Consider a lightweight secondary experience for nearby countries if growth is desired, especially Austria and Slovakia
- Use different landing experiences for:
  - Facebook promotional traffic
  - Google research/search traffic

---

## Final Assessment

## Strongest audience definition
The core audience is:
- **Hungarian**
- **mobile-first**
- **primarily Android**
- **heavily Facebook-driven**
- browsing mainly through **Facebook in-app browser** and **mobile Chrome**

## Main risk areas
- Facebook in-app browser compatibility
- mobile landing-page mismatch causing quick backs
- dead clicks on tappable-looking UI elements
- possible script issues affecting mobile/in-app experiences

## Main optimization focus
If only a few improvements can be made first, prioritize:
1. **FacebookApp mobile experience**
2. **Top mobile landing pages for offers/homepage**
3. **Booking path reliability across domains**
4. **Dead-click and script-error cleanup on high-traffic pages**

If you want, I can turn this into a **shorter executive version** or a **prioritized action plan by page and browser**.