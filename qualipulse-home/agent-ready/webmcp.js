(function(){
var mc=navigator.modelContext;if(!mc)return;
var SITE='https://qualipulse.tech';
function txt(o){return {content:[{type:'text',text:typeof o==='string'?o:JSON.stringify(o,null,2)}]};}
var SERVICES={company:'QualiPulse (QualiPulse Solutions LLP), Kochi, India',audience:'Software, SaaS and QA/testing companies',email:'reachus@qualipulse.tech',book_call:'https://cal.com/qualipulse/30min',pricing_page:SITE+'/pricing/',services:[
{name:'Content Engine (SEO content)',url:SITE+'/content-engine/',plans:[{plan:'Signal',price_usd_month:460,articles:4},{plan:'Momentum',price_usd_month:800,articles:8},{plan:'Authority',price_usd_month:1080,articles:12}]},
{name:'LinkedIn Content',url:SITE+'/linkedin-content-for-software-companies/',plans:[{plan:'Founder',price_usd_month:400,posts:8},{plan:'Leader',price_usd_month:720,posts:16},{plan:'Team',price_usd_month:972,posts:24}]},
{name:'AI SEO (AEO and GEO)',url:SITE+'/ai-seo-services/',plans:[{price_usd_month:400,pages:6,prompts_tracked:20},{price_usd_month:949,pages:12,prompts_tracked:30},{price_usd_month:1349,pages:20,prompts_tracked:40}]},
{name:'AI Email Marketing',url:SITE+'/ai-email-marketing/',plans:[{from_usd_month:700,emails:5000},{from_usd_month:1349,emails:10000},{from_usd_month:1999,emails:15000}],note:'Email tools billed at cost'},
{name:'Product Value Assurance',url:SITE+'/product-marketing-consultant/',price:'From $1,000, then $800 a month'},
{name:'Career Positioning',url:SITE+'/career-positioning/',plans:[{price_usd:49},{price_usd:99},{price_usd:149}]}],
terms:'30 day notice period on monthly plans'};
var tools=[
{name:'get_qualipulse_services',description:'List QualiPulse services, plans and USD prices, with links to each service page and how to book a call.',inputSchema:{type:'object',properties:{}},annotations:{readOnlyHint:true},execute:function(){return txt(SERVICES);}},
{name:'search_qualipulse_articles',description:'Search QualiPulse blog articles about SEO, AI search, content marketing, LinkedIn and QA marketing. Returns titles and URLs.',inputSchema:{type:'object',properties:{query:{type:'string',description:'Search words'},limit:{type:'integer',minimum:1,maximum:10,description:'Max results, default 5'}},required:['query']},annotations:{readOnlyHint:true},
 execute:function(a){var n=Math.min(Math.max(parseInt(a&&a.limit,10)||5,1),10);return fetch(SITE+'/wp-json/wp/v2/posts?_fields=title,link,date&per_page='+n+'&search='+encodeURIComponent((a&&a.query)||'')).then(function(r){return r.json();}).then(function(d){return txt(d.map(function(p){var e=document.createElement('textarea');e.innerHTML=p.title.rendered;return {title:e.value,url:p.link,date:p.date};}));});}},
{name:'get_qualipulse_contact_options',description:'How to contact QualiPulse or book a 30 minute call. Returns the booking link, email and contact page.',inputSchema:{type:'object',properties:{}},annotations:{readOnlyHint:true},execute:function(){return txt({book_call:SERVICES.book_call,email:SERVICES.email,contact_page:SITE+'/contact/',response_time:'Within one business day'});}}];
try{if(typeof mc.registerTool==='function'){tools.forEach(function(t){mc.registerTool(t);});}else if(typeof mc.provideContext==='function'){mc.provideContext({tools:tools});}}catch(e){}
})();
