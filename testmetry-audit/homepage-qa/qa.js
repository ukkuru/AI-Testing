const {chromium}=require('playwright');const fs=require('fs');
const url=process.argv[2], tag=process.argv[3];
const axeSrc=fs.readFileSync(__dirname+'/node_modules/axe-core/axe.min.js','utf8');
(async()=>{const b=await chromium.launch();const out={url};let state;
for (const [name,vp,mobile] of [['desktop',{width:1366,height:900},false],['mobile',{width:390,height:844},true]]){
 const ctx=await b.newContext({storageState:state,viewport:vp,isMobile:mobile,hasTouch:mobile,deviceScaleFactor:mobile?3:1});
 const p=await ctx.newPage();
 await p.addInitScript(()=>{window.__lcp=0;window.__cls=0;window.__lt=0;
  new PerformanceObserver(l=>{for(const e of l.getEntries())window.__lcp=e.startTime}).observe({type:'largest-contentful-paint',buffered:true});
  new PerformanceObserver(l=>{for(const e of l.getEntries())if(!e.hadRecentInput)window.__cls+=e.value}).observe({type:'layout-shift',buffered:true});
  new PerformanceObserver(l=>{for(const e of l.getEntries())window.__lt+=Math.max(0,e.duration-50)}).observe({type:'longtask',buffered:true});});
 const t0=Date.now(); await p.goto(url+(url.includes('?')?'&':'?')+'qa='+Date.now(),{waitUntil:'load',timeout:120000}); if((await p.title()).includes('Checking')){await p.waitForNavigation({timeout:90000}).catch(()=>{});await p.waitForLoadState('load');} const load=Date.now()-t0;
 await p.waitForTimeout(3500);
 const perf=await p.evaluate(()=>{const n=performance.getEntriesByType('navigation')[0];const r=performance.getEntriesByType('resource');const fcp=(performance.getEntriesByName('first-contentful-paint')[0]||{}).startTime;
  const kb=a=>Math.round(a.reduce((s,x)=>s+(x.transferSize||0),0)/1024);
  return {ttfb:Math.round(n.responseStart),fcp:Math.round(fcp||0),lcp:Math.round(window.__lcp),cls:+window.__cls.toFixed(3),blocking_ms:Math.round(window.__lt),requests:r.length+1,total_kb:kb(r)+Math.round((n.transferSize||0)/1024),js_kb:kb(r.filter(x=>x.initiatorType==='script')),css_kb:kb(r.filter(x=>x.initiatorType==='link'||x.name.includes('.css'))),img_kb:kb(r.filter(x=>x.initiatorType==='img'||/\.(png|jpe?g|webp|gif|svg)/.test(x.name))),overflow:document.documentElement.scrollWidth>window.innerWidth}});
 perf.load_ms=load;
 await p.addScriptTag({content:axeSrc});
 const axe=await p.evaluate(async()=>{const r=await axe.run(document,{runOnly:{type:'tag',values:['wcag2a','wcag2aa','wcag21a','wcag21aa','best-practice']}});return r.violations.map(v=>({id:v.id,impact:v.impact,n:v.nodes.length,inTmh:v.nodes.filter(x=>x.target.join(' ').includes('tmh')||x.html.includes('tmh')).length,sample:v.nodes.slice(0,2).map(x=>x.target.join(' ')+' :: '+x.html.slice(0,110))}))});
 const small=await p.evaluate(()=>[...document.querySelectorAll('.tmh a, .tmh button, .tmh input, .tmh summary')].filter(e=>{const r=e.getBoundingClientRect();return r.width>0&&(r.height<24||r.width<24)}).map(e=>e.textContent.trim().slice(0,30)+' '+Math.round(e.getBoundingClientRect().width)+'x'+Math.round(e.getBoundingClientRect().height)));
 await p.screenshot({path:`${__dirname}/${tag}-${name}.png`,fullPage:true});
 out[name]={perf,axe,small_targets:small};
 if(name==='desktop'){
  out.seo=await p.evaluate(()=>{const m=n=>{const e=document.querySelector(`meta[name="${n}"]`);return e&&e.content};const txt=(document.querySelector('.tmh')||document.body).innerText.toLowerCase();
   const kw=['ai software testing','ai test automation','ai in test automation','software testing','qa automation','ai qa','testmetry'];
   return {lang:document.documentElement.lang,title:document.title,title_len:document.title.length,desc:m('description'),desc_len:(m('description')||'').length,robots:m('robots'),canonical:(document.querySelector('link[rel=canonical]')||{}).href,viewport:m('viewport'),og_title:(document.querySelector('meta[property="og:title"]')||{}).content,
   h1:[...document.querySelectorAll('h1')].map(e=>e.innerText.trim()),h2:[...document.querySelectorAll('h2')].map(e=>e.innerText.trim()),
   imgs_no_alt:[...document.querySelectorAll('img')].filter(i=>!i.hasAttribute('alt')).map(i=>i.src.slice(0,80)).slice(0,10),
   schema:[...document.querySelectorAll('script[type="application/ld+json"]')].map(s=>{try{const j=JSON.parse(s.textContent);return (j['@graph']||[j]).map(x=>x['@type']).flat().join(',')}catch(e){return 'bad json'}}),
   words:txt.split(/\s+/).length,kw:Object.fromEntries(kw.map(k=>[k,txt.split(k).length-1])),
   links:[...new Set([...document.querySelectorAll('a[href]')].map(a=>a.getAttribute('href')))],
   anchors_missing:[...document.querySelectorAll('a[href^="#"]')].map(a=>a.getAttribute('href')).filter(h=>h.length>1&&!document.getElementById(h.slice(1)))}});
 }
 state=await ctx.storageState(); await ctx.close();}
fs.writeFileSync(`${__dirname}/${tag}.json`,JSON.stringify(out,null,1));await b.close();})();
