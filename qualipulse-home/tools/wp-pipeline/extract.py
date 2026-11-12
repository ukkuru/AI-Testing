import json,re,hashlib,sys
SRC='/home/user/AI-Testing/qualipulse-home/'
import os
W=os.path.dirname(os.path.abspath(__file__))+'/'
P=json.load(open(W+'pages.json')); S=json.load(open(W+'shared.json')); imgs=json.load(open(W+'images.json'))
h=lambda x:hashlib.md5(x.encode()).hexdigest()[:8]
bycontent={v['content']:k for k,v in S.items()}
for f in sys.argv[1:]:
    s=open(SRC+f,encoding='utf-8').read()
    for v in imgs.values(): s=s.replace(v['data'],'https://qualipulse.tech/wp-content/uploads/2026/11/'+v['file'])
    head=re.search(r'<head[^>]*>(.*?)</head>',s,re.S).group(1); body=re.search(r'<body[^>]*>(.*)</body>',s,re.S).group(1)
    ref=lambda t,x:[t,'ref:'+bycontent[x]] if x in bycontent else [t,x]
    seq=[ref('fonts',''.join(re.findall(r'<link[^>]+fonts\.(?:googleapis|gstatic)[^>]*>',head)))]+[ref('css',st) for st in re.findall(r'<style[^>]*>.*?</style>',head,re.S)]
    hm=re.search(r'<header.*?</header>',body,re.S)
    rest=body[hm.end():] if hm else body
    if hm:
        if body[:hm.start()].strip(): seq.append(['html',body[:hm.start()]])
        seq.append(ref('header',hm.group(0)))
    fms=list(re.finditer(r'<footer.*?</footer>',rest,re.S)); fm=fms[-1] if fms else None
    if fm and fm.group(0) in bycontent: seq+=[['html',rest[:fm.start()]],ref('footer',fm.group(0)),['html',rest[fm.end():]]]
    else: seq.append(['html',rest])
    ld=''.join(re.findall(r'<script type="application/ld\+json">.*?</script>',head,re.S))
    if ld: seq.append(['ld',ld])
    P.setdefault(f,{}); P[f]['seq']=[x for x in seq if x[1].strip()]
    P[f]['title']=re.search(r'<title>(.*?)</title>',s,re.S).group(1).strip()
    print(f,[(t,x[:12] if x.startswith('ref:') else len(x)) for t,x in P[f]['seq']])
json.dump(P,open(W+'pages.json','w'))
