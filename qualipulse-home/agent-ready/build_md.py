"""Build Markdown versions of qualipulse.tech pages for agents (Accept: text/markdown).
Output: agent-ready/md/<path>.md (homepage is md/index.md). Upload the md folder to public_html/md/."""
import re,glob,os,html
from markdownify import markdownify as md
SRC=os.path.dirname(os.path.dirname(os.path.abspath(__file__)))+'/'
OUT=os.path.dirname(os.path.abspath(__file__))+'/md/'
SKIP={'contact-steps.html','404.html','thank-you.html','thanks-for-join-us.html','author-george-ukkuru.html','privacy-policy.html','blog-post-b2b-saas-seo-agencies.html'}
n=0
for f in sorted(glob.glob(SRC+'*.html')):
    name=os.path.basename(f)
    if name in SKIP: continue
    s=open(f,encoding='utf-8').read()
    can=re.search(r'<link rel="canonical" href="https://qualipulse\.tech/([^"]*)"',s)
    if not can: continue
    path=can.group(1).strip('/') or 'index'
    title=html.unescape(re.search(r'<title>(.*?)</title>',s,re.S).group(1).strip())
    desc=re.search(r'<meta name="description" content="([^"]*)"',s); desc=html.unescape(desc.group(1)) if desc else ''
    main=re.search(r'(?s)<main[^>]*>(.*?)</main>',s)
    if not main: continue
    body=re.sub(r'(?is)<(script|style|svg|noscript|form|button|select)[^>]*>.*?</\1>','',main.group(1))
    body=re.sub(r'(?is)<nav[^>]*class="[^"]*crumbs[^"]*"[^>]*>.*?</nav>','',body)
    body=re.sub(r'(?is)<img[^>]*src="data:[^>]*>','',body)
    text=md(body,heading_style='ATX',bullets='-',strip=['span'])
    text=re.sub(r'\n{3,}','\n\n',text).strip()
    out=f"---\ntitle: {title}\nurl: https://qualipulse.tech/{'' if path=='index' else path+'/'}\ndescription: {desc}\n---\n\n{text}\n\n---\nQualiPulse Solutions LLP, Kochi, India. Contact: reachus@qualipulse.tech, https://qualipulse.tech/contact/\n"
    os.makedirs(os.path.dirname(OUT+path+'.md'),exist_ok=True)
    open(OUT+path+'.md','w',encoding='utf-8').write(out); n+=1
print('wrote',n)
