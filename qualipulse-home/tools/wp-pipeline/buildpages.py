import json,sys,os,re
sys.path.insert(0,os.path.dirname(os.path.abspath(__file__)))
from minify import piece
W=os.path.dirname(os.path.abspath(__file__))+'/'
P=json.load(open(W+'pages.json')); ids=json.load(open(W+'pattern_ids.json')) if os.path.exists(W+'pattern_ids.json') else {}
os.makedirs(W+'payload',exist_ok=True); os.makedirs(W+'preview',exist_ok=True)
pat={k:open(W+f'patterns/{k}.html').read() for k in [f[:-5] for f in os.listdir(W+'patterns')]}
strip=lambda c:c.replace('<!-- wp:html -->\n','').replace('\n<!-- /wp:html -->','')
SRC='/home/user/AI-Testing/qualipulse-home/'
for f,p in P.items():
    blocks=[];buf=[];prev=[]
    hd=re.search(r'<head[^>]*>(.*?)</head>',open(SRC+f,encoding='utf-8').read(),re.S).group(1)
    for sc in re.findall(r'<script(?![^>]*ld\+json)[^>]*>.*?</script>',hd,re.S): buf.append(sc); prev.append(sc)
    for t,x in p['seq']:
        if x.startswith('ref:'):
            k=x[4:]
            if buf: blocks.append('<!-- wp:html -->\n'+'\n'.join(buf)+'\n<!-- /wp:html -->'); buf=[]
            blocks.append('<!-- wp:block {"ref":%s} /-->'%ids.get(k,'"'+k+'"')); prev.append(strip(pat[k]))
        else:
            y=piece(t,x); buf.append(y); prev.append(y)
    if buf: blocks.append('<!-- wp:html -->\n'+'\n'.join(buf)+'\n<!-- /wp:html -->')
    open(W+'payload/'+f,'w').write('\n'.join(blocks))
    open(W+'preview/'+f,'w').write('<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'+p['title']+'</title></head><body>'+'\n'.join(prev)+'</body></html>')
print('built',len(P))
