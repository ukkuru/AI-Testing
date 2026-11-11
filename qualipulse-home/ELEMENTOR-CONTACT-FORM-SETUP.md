# Updating the live contact form in Elementor

These steps make the live form at qualipulse.tech/contact/ match the new design. Take a backup first (Hostinger hPanel > Backups).

Open the page: WP Admin > Pages > Contact > Edit with Elementor, then click the form.

## 1. Add every service to the dropdown, with a blank first option

Content > Form Fields > "Service Interested In" (select field). Replace the Options box with exactly these lines (one per line). The first line is blank on purpose, so nothing is pre-selected:

```

Content Engine
LinkedIn Content
AI SEO Services
AI Email Marketing
Product Value Assurance
Career Positioning
Not sure yet
```


## 2. Rename the Company field ID from `message` to `company`

Form Fields > "Company Name" > Advanced tab > ID: change `message` to `company`.

Then check every place that used the old ID, or those emails will show an empty company:
- Actions After Submit > Email > Message: replace `[field id="message"]` with `[field id="company"]` (if you use `[all-fields]` nothing needs changing).
- Email 2, Webhook, Collect Submissions or any CRM integration that maps `message`.

Send yourself one test submission afterwards.

## 3. Enforce business emails

- Form Fields > "Business Email" > Placeholder: `you@yourcompany.com`
- Add the PHP snippet in `elementor-business-email.php` (instructions are at the top of that file). It blocks Gmail, Yahoo, Outlook, iCloud, Proton and other free addresses on every Elementor form, with the message "Please use your work email."
- Test it: submit with a gmail.com address (should be refused), then with your company address (should go through).

## 4. Add the booking option

The page title promises a content strategy call, so give people a direct way to book one:
- Add a Button widget above the form (and one in the right column) with the text "Book a 30-minute call", link `https://cal.com/qualipulse/30min`, and "Open in new window" turned on.
- Optional: in cal.com, set the event name to "Content strategy call (30 min)" so it matches the button.

## 5. Restyle the form

Paste `elementor-form-style.css` into Site Settings > Custom CSS (or the form widget's Advanced > Custom CSS). Keep "Label" turned on for every field and set Required Mark to on (Form widget > Content > Form Fields).

## 6. Publish and test

Update the page, purge the LiteSpeed cache, then test on desktop and phone:
- Empty submit shows required-field errors
- A gmail.com address is refused
- A valid submission arrives in your inbox with the company name and the chosen service
