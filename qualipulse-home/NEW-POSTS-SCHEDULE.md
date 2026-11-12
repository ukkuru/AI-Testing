# Scheduled New Posts (1 per day)

Each daily run takes the first row whose Status is `todo`, publishes it, then sets its Status to `done` with the date and live URL. Keywords were checked against all 45 existing posts and the service pages, and none of the primary keywords are targeted anywhere else on the site.

| # | Status | Slug | Working title (H1) | Primary keyword (vol, KD) | Secondary keywords | Must not overlap with | Link to |
|---|---|---|---|---|---|---|---|
| 1 | todo | enterprise-seo-services | Enterprise SEO Services for Software Companies: What You Should Get | enterprise seo services (4,400, KD 8) | enterprise seo experts, seo team for cross-department work | /seo-services-guide-2026/ (generic SEO services), /b2b-saas-seo-agency/ (consultant vs agency) | /ai-seo-services/, /technical-seo-case-study/ |
| 2 | todo | saas-content-marketing-agencies | Best SaaS Content Marketing Agencies in 2026: An Honest Comparison | saas content marketing agency (1,600, KD 27) | saas content marketing services, saas content marketing firm, saas content agencies | /saas-content-marketing/ (strategy), /content-writing-agency-guide/ | /content-engine/, /saas-content-marketing-case-study/ |
| 3 | todo | cold-email-subject-lines | Cold Email Subject Lines for B2B SaaS That Get Opened | cold email subject line (1,000, KD 13) | email subject lines for cold emails, highly personalized outbound examples | /ai-email-marketing/ (service page) | /ai-email-marketing/ |
| 4 | todo | bottom-of-the-funnel-content | Bottom of the Funnel Content for SaaS: Pages That Turn Readers Into Pipeline | bottom of the funnel content (420 combined, KD 19 to 22) | bottom of funnel keywords, high converting keywords | /software-content-marketing/, /seo-for-saas-startups/ | /content-engine/, /saas-content-marketing-case-study/ |
| 5 | todo | link-building-services | Link Building Services for SaaS: What a Monthly Retainer Should Include | monthly link building service (480, KD 6) | best link building companies, enterprise link building, enterprise link building services | /affordable-seo-services/, /seo-pricing/ | /ai-seo-services/, /pricing/ |
| 6 | todo | saas-technical-seo-checklist | SaaS Technical SEO Checklist: 25 Checks Before You Publish More Content | saas technical seo (480, KD 13) | technical seo for saas, saas site audit | /technical-seo-case-study/ (case study), /faq/technical-seo-geo/ | /find-broken-links/, /technical-seo-case-study/ |
| 7 | todo | competitor-comparison-pages | Competitor Comparison Pages for SaaS: How to Build Ones Buyers Trust | competitor comparison (590, KD 12) | comparison landing pages, alternatives pages | /draft-dev-alternatives-for-qa-companies/ (one specific comparison) | /content-engine/, /product-marketing-consultant/ |
| 8 | todo | geo-for-thought-leaders | GEO for Thought Leaders: How Founders Get Cited in AI Answers | geo for thought leaders (320, KD 1) | ai visibility for founders, linkedin and ai search | /why-aeo-brands-win-in/ (AEO and GEO KPIs), /build-thought-leadership-in-qa/ | /linkedin-content-for-software-companies/, /ai-seo-services/ |

## Rules for every post
- Before writing: grep all `qualipulse-home/*.html` titles, H1s and H2s for the primary keyword. If another page already targets it, stop and report instead of publishing.
- Copy the layout of an existing redesigned post (for example `blog-post-saas-seo-services.html`): same head snippets, header, article markup, author box, footer and JSON-LD (BlogPosting and BreadcrumbList).
- Put the primary keyword in the title (under 60 characters), the H1, the first 100 words, one H2 and the meta description (under 155 characters).
- 1,800 to 2,500 words, with at least one table or checklist and an FAQ section of 4 to 6 questions.
- Writing rules: no em dashes, en dashes or double hyphens; none of the banned words in the owner's list (foster, utilize, robust, leverage as a verb, holistic, optimize, implement, strategic, comprehensive and the rest); never invent statistics, clients or results. Use only real QualiPulse data: SaaS case study (+35% visitors in 90 days, from 800 to about 1,080; page-one keywords from 3 to 23 after 100 days; leads from 2 to about 2.4 a month) and the published prices.
- Listicles that include QualiPulse must say plainly that QualiPulse is the author's own agency, and must describe competitors fairly.
- Add the post as the first card on `blog.html`, and link to it from one related existing post.
- Publish through the WordPress.com MCP on site 254277522, using template `elementor_canvas`. Verify the live URL returns 200, the title and H1 are right, and the page has no console errors. Then commit and push to branch `claude/wizardly-einstein-23qwen`.
