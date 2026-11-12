import re
def css(x):
    x=re.sub(r'/\*.*?\*/','',x,flags=re.S)
    x=re.sub(r'\s+',' ',x)
    x=re.sub(r'\s*([{};,>])\s*',r'\1',x)
    x=re.sub(r':\s+',':',x)  # careful: selectors like a:hover fine
    return x.replace(';}','}').strip()
def html(x):
    out=[];keep=0
    for line in x.split('\n'):
        if re.search(r'<(pre|textarea)\b',line): keep+=1
        out.append(line if keep else line.lstrip())
        if re.search(r'</(pre|textarea)>',line): keep=max(0,keep-1)
    return '\n'.join(l for l in out if l.strip()!='' or keep)
def piece(t,x):
    if t=='css': return re.sub(r'(<style[^>]*>)(.*?)(</style>)',lambda m:m.group(1)+css(m.group(2))+m.group(3),x,flags=re.S)
    if t in('main','header','footer','html'):
        # minify inline <style> inside too
        x=re.sub(r'(<style[^>]*>)(.*?)(</style>)',lambda m:m.group(1)+css(m.group(2))+m.group(3),x,flags=re.S)
        return html(x)
    return x
