# testmetry.com audit (29 Oct 2026)

Source: Search Console exports (Pages, Queries, Crawled not indexed, Discovered not indexed, Not found 404).

* `testmetry-page-actions-2026-10-29.csv`: one row per URL with clicks, impressions, position, action, reason and redirect target.

## 404s caused by broken links inside posts (fix the link in the post)
* effective-platform-testing-strategies-ai-contributions-and-performance-engineering: links written as `www.testmetry.com/...` without https, so they resolve as relative paths
* preparing-for-manual-testing-interview-questions-for-4-years-experience-professionals: same problem (`testmetry.com/...`)
* the-indispensable-role-and-value-of-testplanners-in-qa-and-ai-era: placeholder links `internal-link1`, `external-link1`, `external-link2`
* automated-test-case-generators-amplifying-efficiency-in-software-testing: placeholder links `testmetryautomation.html`, `testmetryai.html`, `testmetrybestpractice.html`
* comprehensive-guide-to-ace-agile-testing-interview-understanding-agile-methodology: links ending in `(External link)` text
* Links to pages that were never written: ai-in-testing-*, performance-engineering-*, qa-best-practices*, test-automation-strategy*, blog/ai-in-testing
