# Ranyati ecosystem SEO rollout

Implementation date: April 2026. Two codebases: `ranyati/apps/group` (apex + motivations + storage hosts) and `NRAPA/` (nrapa host).

---

## A. Summary per site

### ranyati.co.za (apex — group app)

- Removed static `public/robots.txt` so the dynamic `/robots.txt` route applies per host; added `Disallow: /admin`.
- Extended `sitemap.xml` for apex with `/about`, `/services`, `/contact`, `/faq`, `/guides`.
- New layout `layouts/seo-apex.blade.php` with Organization + WebSite JSON-LD (real Centurion address and published contact details).
- Five new support pages under `resources/views/seo/*.blade.php`.
- Footer on `welcome.blade.php`: minimal text nav to new pages (hero/layout unchanged).

### motivations.ranyati.co.za (group app)

- Six new URL targets: five topic pages + `/faq` (host-only; apex uses `/faq` with different view).
- Sitemap extended; resources hub sidebar/footer link into SEO URLs.
- `motivations.blade.php` footer: one-line ecosystem links.
- `layouts/resources.blade.php`: optional `breadcrumb_mode=flat`, fixed BreadcrumbList JSON-LD (includes `item` URLs), optional `@section('canonical')`.

### storage.ranyati.co.za (group app)

- Four new SEO pages + sitemap entries; storage resources index sidebar/footer updated.
- `storage-landing.blade.php` footer: ecosystem links.

### nrapa.ranyati.co.za (NRAPA app)

- New `Route::prefix('info')` routes: `info.index` (`/info`), six SEO articles, `info.faq`.
- `layouts/info.blade.php`: breadcrumb trail uses `info.index` as “Info & guides”; JSON-LD aligned; `@stack('structured_data')`; WebSite schema includes `publisher` → Ranyati Group; nav + footer internal links to group/motivations/storage.
- `SitemapController` lists all new info routes.
- `welcome.blade.php`: guides grid links to hub; footer ecosystem strip; “Resources” footer link renamed to Info & guides → `info.index`.

---

## B. Title tags and meta descriptions

### Apex (ranyati.co.za)

| URL path | Title | Meta description |
|----------|--------|------------------|
| `/` | *(unchanged)* Ranyati Group — Firearm Administration Specialists Since 2006 | *(unchanged)* |
| `/about` | About Ranyati Group \| Firearm Administration Since 2006 | Ranyati Group has supported South African firearm owners since 2006 with motivations, SAPS-accredited membership through NRAPA, and secure storage—one trusted ecosystem for compliance and administration. |
| `/services` | Firearm Services \| Motivations, NRAPA Membership & Storage \| Ranyati Group | Explore Ranyati Group services: professional firearm licence motivations, SAPS-accredited NRAPA membership for dedicated status, and secure firearm storage across South Africa. |
| `/contact` | Contact Ranyati Group \| Centurion, South Africa | Contact Ranyati Group for firearm administration support. Phone +27 87 151 0987, email info@firearmmotivations.co.za, registered address 241 Jean Avenue, Centurion, 0157. |
| `/faq` | FAQ \| Ranyati Group — Motivations, NRAPA & Storage | Frequently asked questions about Ranyati Group: which division to use for motivations, NRAPA membership, dedicated status, and firearm storage in South Africa. |
| `/guides` | Guides & Resources \| Firearm Licensing & Compliance \| Ranyati Group | Curated guides from Ranyati Group: motivations, NRAPA dedicated status, and secure storage. Links to in-depth articles on each division’s website. |

### Motivations (motivations.ranyati.co.za)

| Path | Title | Meta description |
|------|--------|------------------|
| `/` | *(unchanged)* | *(unchanged)* |
| `/firearm-licence-motivation-self-defence` | Firearm Licence Motivation for Self-Defence \| South Africa | Professional firearm licence motivation support for lawful self-defence in South Africa. Ranyati Motivations helps structure FCA-compliant motivation documents—part of Ranyati Group. |
| `/firearm-licence-motivation-sport-shooting` | Firearm Licence Motivation for Sport Shooting \| South Africa | Expert assistance with firearm licence motivations for dedicated and recreational sport shooting in South Africa. Ranyati Motivations, a Ranyati Group division. |
| `/firearm-licence-motivation-hunting` | Firearm Licence Motivation for Hunting \| South Africa | Professional motivations for hunting-purpose firearm licences in South Africa. Ranyati Motivations helps lawful hunters prepare FCA-aligned documentation under Ranyati Group. |
| `/firearm-licence-renewal-south-africa` | Firearm Licence Renewal South Africa \| Motivation & Support | Guidance and professional motivation support for firearm licence renewals in South Africa. Ranyati Motivations helps you prepare renewal submissions under the Firearms Control Act. |
| `/firearm-licence-appeal-south-africa` | Firearm Licence Appeal South Africa \| Motivation Support | If SAPS refused or failed to decide on your firearm licence, structured motivation and administrative support can help with appeals. Ranyati Motivations—Ranyati Group. |
| `/faq` | Firearm Motivation FAQ \| Ranyati Motivations South Africa | Answers about firearm licence motivations, timelines, SAPS submissions, and how Ranyati Motivations works with Ranyati Group, NRAPA, and Storage. |

### Storage (storage.ranyati.co.za)

| Path | Title | Meta description |
|------|--------|------------------|
| `/` | *(unchanged)* | *(unchanged)* |
| `/firearm-storage-pretoria` | Firearm Storage Pretoria \| Ranyati Storage \| FCA-Compliant | Secure firearm storage serving Pretoria and Gauteng through Ranyati Storage—safe custody aligned with the Firearms Control Act, part of Ranyati Group. |
| `/long-term-firearm-storage-south-africa` | Long-Term Firearm Storage South Africa \| Ranyati Storage | Long-term secure firearm storage in South Africa with FCA-aware custody records. Ranyati Storage, a division of Ranyati Group. |
| `/temporary-firearm-storage` | Temporary Firearm Storage South Africa \| Ranyati Storage | Short-term secure firearm storage for travel, moves, or transitions. Ranyati Storage under Ranyati Group—FCA-aligned safe custody. |
| `/secure-firearm-storage-faq` | Secure Firearm Storage FAQ \| Ranyati Storage South Africa | Frequently asked questions about secure firearm storage, safe custody, and Ranyati Storage within the Ranyati Group ecosystem. |

### NRAPA (nrapa.ranyati.co.za)

| Path | Title (browser; layout appends “\| NRAPA”) | Meta description |
|------|---------------------------------------------|------------------|
| `/` | *(unchanged)* | *(unchanged)* |
| `/info` | NRAPA Information & Guides | Official NRAPA guides: dedicated sport shooter and hunter status in South Africa, membership benefits, endorsements, and how dedicated status fits into the Firearms Control Act. |
| `/info/dedicated-sport-shooter-south-africa` | Dedicated Sport Shooter South Africa \| NRAPA | What dedicated sport shooter status means in South Africa, how SAPS-accredited associations like NRAPA fit in, and how to stay compliant with the Firearms Control Act. |
| `/info/dedicated-hunter-south-africa` | Dedicated Hunter South Africa \| NRAPA | Dedicated hunter status in South Africa: SAPS-accredited association membership through NRAPA, lawful hunting activity, and compliance with the Firearms Control Act. |
| `/info/how-to-get-dedicated-status-south-africa` | How to Get Dedicated Status in South Africa \| NRAPA | A practical overview of dedicated sport shooter or dedicated hunter status: accredited association membership, competency, SAPS applications, and ongoing compliance—NRAPA under Ranyati Group. |
| `/info/endorsements` | Firearm Endorsement Letters \| NRAPA South Africa | How endorsement letters work for NRAPA members: lawful purposes, SAPS requirements, and applying through the NRAPA member portal—not legal advice. |
| `/info/membership-benefits` | NRAPA Membership Benefits \| Dedicated Status & Compliance | Benefits of NRAPA membership: SAPS-accredited dedicated sport shooter and hunter administration, digital certificates, activities tracking, and integration with Ranyati Group services. |
| `/info/faq` | NRAPA FAQ \| Membership & Dedicated Status | Frequently asked questions about NRAPA membership, dedicated sport shooter and hunter status, and how the association works within Ranyati Group. |

---

## C. New SEO pages (full URLs)

- `https://ranyati.co.za/about`
- `https://ranyati.co.za/services`
- `https://ranyati.co.za/contact`
- `https://ranyati.co.za/faq`
- `https://ranyati.co.za/guides`
- `https://motivations.ranyati.co.za/firearm-licence-motivation-self-defence`
- `https://motivations.ranyati.co.za/firearm-licence-motivation-sport-shooting`
- `https://motivations.ranyati.co.za/firearm-licence-motivation-hunting`
- `https://motivations.ranyati.co.za/firearm-licence-renewal-south-africa`
- `https://motivations.ranyati.co.za/firearm-licence-appeal-south-africa`
- `https://motivations.ranyati.co.za/faq`
- `https://storage.ranyati.co.za/firearm-storage-pretoria`
- `https://storage.ranyati.co.za/long-term-firearm-storage-south-africa`
- `https://storage.ranyati.co.za/temporary-firearm-storage`
- `https://storage.ranyati.co.za/secure-firearm-storage-faq`
- `https://nrapa.ranyati.co.za/info`
- `https://nrapa.ranyati.co.za/info/dedicated-sport-shooter-south-africa`
- `https://nrapa.ranyati.co.za/info/dedicated-hunter-south-africa`
- `https://nrapa.ranyati.co.za/info/how-to-get-dedicated-status-south-africa`
- `https://nrapa.ranyati.co.za/info/endorsements`
- `https://nrapa.ranyati.co.za/info/membership-benefits`
- `https://nrapa.ranyati.co.za/info/faq`

---

## D. Schema types added or reinforced

| Property / host | Schema |
|-----------------|--------|
| ranyati.co.za apex support pages | `Organization` + `WebSite` (in `seo-apex` layout) |
| ranyati.co.za `/faq` | `FAQPage` |
| motivations new topics | `ProfessionalService` + `BreadcrumbList` (layout) |
| motivations `/faq` | `FAQPage` + `BreadcrumbList` |
| storage Pretoria | `LocalBusiness` + `BreadcrumbList` |
| storage long-term / temporary | `Service` + `BreadcrumbList` |
| storage `/secure-firearm-storage-faq` | `FAQPage` + `BreadcrumbList` |
| NRAPA info pages | `BreadcrumbList` + `WebPage` (layout); `WebSite.publisher` → Ranyati Group |
| NRAPA `/info/faq` | `FAQPage` (stack) |

Existing landing JSON-LD (e.g. Organization on welcome pages) was not removed.

---

## E. Manual follow-ups

1. **Deploy `APP_URL`** per environment host (apex vs motivations vs storage) so `url()`, canonicals, and sitemap `route()` output match public URLs.
2. **Google Search Console**: submit each property’s sitemap (`/sitemap.xml`); recrawl key new URLs.
3. **Absolute OG images**: apex layout uses `url(asset(...))` for Open Graph; align CDN/https if you front assets through a CDN.
4. **PHP mbstring**: local `php artisan` on NRAPA failed with `mb_split` undefined—enable `mbstring` in CLI PHP for tooling (production likely already OK).
5. **Storage contact email**: multiple addresses appear in the repo (`info@firearmstorage.co.za` in some storage resource CTAs vs `info@firearmmotivations.co.za` on landings). Align with the business’s single public contact if desired.
6. **Physical storage address**: Pretoria locality is used in schema/copy for storage; confirm against the actual licensed premises before treating as `LocalBusiness` with full street address.

---

## F. Suggested next content pieces

- Province-specific storage landing pages (Johannesburg, Cape Town) if operations expand.
- “Club secretary” or RO guide for NRAPA-affiliated clubs.
- Motivations: one page per major SAPS DFO myth/fact (non-legal, educational).
- Video schema / embedded explainer on `/info/how-to-get-dedicated-status` (if you produce video).
- Hreflang only if you add translated versions.

---

## Files touched (high level)

**Group app:** `routes/web.php`, `resources/views/layouts/resources.blade.php`, `resources/views/layouts/seo-apex.blade.php`, `resources/views/welcome.blade.php`, `resources/views/motivations.blade.php`, `resources/views/storage-landing.blade.php`, `resources/views/seo/**`, `resources/views/resources/motivations/index.blade.php`, `resources/views/resources/storage/index.blade.php`, `public/robots.txt` (deleted).

**NRAPA:** `routes/web.php`, `app/Http/Controllers/SitemapController.php`, `resources/views/layouts/info.blade.php`, `resources/views/welcome.blade.php`, `resources/views/pages/info/index.blade.php` + six new info blades.
