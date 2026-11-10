<?php
/**
 * Plugin Name: Testmetry Estimator
 * Description: Three point estimation calculator for testmetry.com, as the [tm_three_point] shortcode. PERT and triangular methods, standard deviation, confidence ranges, CSV export. Runs in the browser; nothing is sent to the server.
 * Version: 1.0.1
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function tme_css() {
	return <<<'CSS'
.tme{--n:#0b2a5b;--s:#334155;--m:#5b6780;--l:#dbe3ef;--bg:#f5f7fa;--y:#f6cf4b;background:var(--bg);border:1px solid var(--l);border-radius:16px;padding:22px;margin:24px 0;color:var(--s);font-family:Inter,system-ui,-apple-system,Segoe UI,sans-serif}
.tme *{box-sizing:border-box}
.tme h2.tme-t{font-size:20px!important;line-height:1.3!important;margin:0 0 4px!important;color:var(--n)!important}
.tme .tme-sub{font-size:14px;color:var(--m);margin:0 0 16px;line-height:1.5}
.tme .tme-opts{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin:0 0 16px}
.tme label{display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:600;color:var(--n);line-height:1.3}
.tme select,.tme input[type=text],.tme input[type=number]{width:100%;height:42px;padding:0 10px;border:1px solid #b8c4d6;border-radius:10px;background:#fff;color:var(--n);font-size:15px;margin:0}
.tme select:focus,.tme input:focus{outline:3px solid #9bb7e6;outline-offset:1px;border-color:var(--n)}
.tme .tme-wrap{overflow-x:auto;margin:0 0 12px;border:1px solid var(--l);border-radius:12px;background:#fff}
.tme table{width:100%;border-collapse:collapse;min-width:640px;margin:0}
.tme th{background:#eef2f8;color:var(--n);font-size:12px;letter-spacing:.04em;text-transform:uppercase;text-align:left;padding:10px 8px;border-bottom:1px solid var(--l)}
.tme td{padding:6px 8px;border-bottom:1px solid #eef2f8;vertical-align:middle;font-size:14px;color:var(--s)}
.tme td:first-child{min-width:170px}
.tme td.tme-num{font-variant-numeric:tabular-nums;font-weight:600;color:var(--n);white-space:nowrap}
.tme tr.tme-err td{background:#fef3f2}
.tme .tme-rm{width:36px!important;height:36px!important;padding:0!important;border:1px solid #b8c4d6!important;background:#fff!important;border-radius:8px!important;color:var(--n)!important;font-size:18px!important;line-height:1!important;cursor:pointer}
.tme .tme-rm:hover{background:#eef2f8!important}
.tme .tme-btn{background:var(--n)!important;color:#fff!important;border:2px solid var(--n)!important}
.tme .tme-btn.tme-alt{background:#fff!important;color:var(--n)!important}
.tme .tme-btn.tme-go{background:var(--y)!important;border-color:var(--y)!important;color:var(--n)!important}
.tme .tme-act{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 16px}
.tme .tme-btn{height:42px;padding:0 16px;border-radius:10px;border:2px solid var(--n);background:var(--n);color:#fff;font-weight:600;font-size:14px;cursor:pointer}
.tme .tme-btn:hover{background:#071d42}
.tme .tme-btn.tme-alt{background:#fff;color:var(--n)}
.tme .tme-btn.tme-alt:hover{background:#eef2f8}
.tme .tme-btn.tme-go{background:var(--y);border-color:var(--y);color:var(--n)}
.tme button:focus-visible{outline:3px solid #9bb7e6;outline-offset:2px}
.tme .tme-res{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px;margin:0 0 8px}
.tme .tme-card{background:#fff;border:1px solid var(--l);border-radius:12px;padding:14px}
.tme .tme-card b{display:block;font-size:12px;letter-spacing:.04em;text-transform:uppercase;color:var(--m);font-weight:600;margin:0 0 6px}
.tme .tme-card span{display:block;font-size:24px;font-weight:700;color:var(--n);font-variant-numeric:tabular-nums;line-height:1.2}
.tme .tme-card small{display:block;margin-top:4px;font-size:12px;color:var(--m);line-height:1.4}
.tme .tme-card.tme-hi{border:2px solid var(--y);background:#fffbea}
.tme .tme-msg{font-size:13px;color:var(--m);margin:10px 0 0;line-height:1.5}
.tme .tme-bad{color:#b42318;font-weight:600}
.tme .tme-sr{position:absolute;left:-9999px}
@media (max-width:600px){.tme{padding:16px}.tme .tme-btn{flex:1 1 45%}}
CSS;
}

function tme_page_css() {
	return 'main.site-main .page-content{max-width:860px;margin:0 auto;padding:24px 20px 48px}main.site-main .page-content table:not(.tme-x){display:block;overflow-x:auto;max-width:100%;border-collapse:collapse}main.site-main .page-content td,main.site-main .page-content th{border:1px solid #dbe3ef;padding:8px 10px;text-align:left}main.site-main .page-content th{background:#eef2f8;color:#0b2a5b}.tme table{display:table!important}.tme td,.tme th{border:0;border-bottom:1px solid #eef2f8}.tme th{border-bottom:1px solid #dbe3ef}';
}

add_action( 'wp_head', function () {
	if ( ! is_singular() ) { return; }
	$p = get_post();
	if ( ! $p || ! has_shortcode( $p->post_content, 'tm_three_point' ) ) { return; }
	echo '<style id="tme-css">' . tme_css() . tme_page_css() . '</style>'; // phpcs:ignore
	echo '<script nowprocket>(function(){if(document.getElementById("tme-css"))return;var s=document.createElement("style");s.id="tme-css";s.textContent=' . wp_json_encode( tme_css() ) . ';document.head.appendChild(s);})();</script>'; // phpcs:ignore
}, 98 );

add_shortcode( 'tm_three_point', function () {
	ob_start();
	?>
<div class="tme" id="tme-app">
<h2 class="tme-t">3 point estimation calculator</h2>
<p class="tme-sub">Enter an optimistic, most likely and pessimistic value for each task. The calculator gives the expected value, standard deviation and a confidence range for each task and for the total. Everything runs in your browser and nothing is saved.</p>
<div class="tme-opts">
<label>Method
<select id="tme-method"><option value="pert">PERT (weighted: O + 4M + P over 6)</option><option value="tri">Triangular (average: O + M + P over 3)</option></select></label>
<label>Unit
<select id="tme-unit"><option>hours</option><option>days</option><option>weeks</option><option>story points</option></select></label>
<label>Confidence for the planning estimate
<select id="tme-conf"><option value="0">50%</option><option value="0.524">70%</option><option value="0.842">80%</option><option value="1.036">85%</option><option value="1.282">90%</option><option value="1.645" selected>95%</option><option value="2.326">99%</option></select></label>
</div>
<div class="tme-wrap"><table id="tme-table"><thead><tr><th scope="col">Task</th><th scope="col">Optimistic (O)</th><th scope="col">Most likely (M)</th><th scope="col">Pessimistic (P)</th><th scope="col">Expected</th><th scope="col">Std dev</th><th scope="col"><span class="tme-sr">Remove</span></th></tr></thead><tbody></tbody></table></div>
<div class="tme-act">
<button type="button" class="tme-btn tme-go" id="tme-add">Add task</button>
<button type="button" class="tme-btn tme-alt" id="tme-ex">Load testing example</button>
<button type="button" class="tme-btn tme-alt" id="tme-clr">Clear</button>
<button type="button" class="tme-btn tme-alt" id="tme-copy">Copy results</button>
<button type="button" class="tme-btn tme-alt" id="tme-csv">Download CSV</button>
</div>
<div class="tme-res" id="tme-res" aria-live="polite"></div>
<p class="tme-msg" id="tme-msg"></p>
</div>
<script nowprocket>
(function(){
var root=document.getElementById('tme-app');if(!root)return;
var tb=root.querySelector('tbody'),$=function(id){return document.getElementById(id)};
var EX=[['Write test cases',8,12,24],['Execute test cycle',16,24,48],['Regression run',10,16,40],['Defect retest',6,10,26]];
function num(v){var x=parseFloat(v);return isFinite(x)?x:NaN}
function f(x){return (Math.round(x*100)/100).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}
function calc(o,m,p,method){
 var e,sd;
 if(method==='tri'){e=(o+m+p)/3;sd=Math.sqrt(Math.max(0,(o*o+m*m+p*p-o*m-o*p-m*p)/18));}
 else{e=(o+4*m+p)/6;sd=(p-o)/6;}
 return {e:e,sd:sd};
}
function row(name,o,m,p){
 var tr=document.createElement('tr'),i=tb.children.length+1;
 tr.innerHTML='<td><input type="text" aria-label="Task '+i+' name" value=""></td>'+
 ['Optimistic','Most likely','Pessimistic'].map(function(l){return '<td><input type="number" step="any" min="0" inputmode="decimal" aria-label="Task '+i+' '+l+'" value=""></td>'}).join('')+
 '<td class="tme-num" data-e>0.00</td><td class="tme-num" data-s>0.00</td><td><button type="button" class="tme-rm" aria-label="Remove task '+i+'">&times;</button></td>';
 var ins=tr.querySelectorAll('input');ins[0].value=name||'';ins[1].value=o==null?'':o;ins[2].value=m==null?'':m;ins[3].value=p==null?'':p;
 tb.appendChild(tr);
}
function rows(){return [].slice.call(tb.children)}
function render(){
 var method=$('tme-method').value,z=parseFloat($('tme-conf').value),unit=$('tme-unit').value;
 var E=0,V=0,n=0,bad=0;
 rows().forEach(function(tr){
  var ins=tr.querySelectorAll('input'),o=num(ins[1].value),m=num(ins[2].value),p=num(ins[3].value);
  var ce=tr.querySelector('[data-e]'),cs=tr.querySelector('[data-s]');
  var filled=ins[1].value!==''||ins[2].value!==''||ins[3].value!=='';
  if(!filled){ce.textContent='0.00';cs.textContent='0.00';tr.classList.remove('tme-err');return}
  if(isNaN(o)||isNaN(m)||isNaN(p)||o>m||m>p||o<0){tr.classList.add('tme-err');ce.textContent='check';cs.textContent='check';bad++;return}
  tr.classList.remove('tme-err');
  var r=calc(o,m,p,method);ce.textContent=f(r.e);cs.textContent=f(r.sd);E+=r.e;V+=r.sd*r.sd;n++;
 });
 var sd=Math.sqrt(V),plan=E+z*sd,res=$('tme-res');
 if(!n){res.innerHTML='';$('tme-msg').innerHTML=bad?'<span class="tme-bad">Each task needs 0 &le; O &le; M &le; P.</span>':'Add at least one task to see results.';return}
 var cont=E>0?((plan-E)/E*100):0;
 function card(t,v,s,hi){return '<div class="tme-card'+(hi?' tme-hi':'')+'"><b>'+t+'</b><span>'+v+'</span><small>'+s+'</small></div>'}
 res.innerHTML=
  card('Expected total',f(E)+' '+unit,n+' task'+(n>1?'s':'')+', '+(method==='tri'?'triangular':'PERT'))+
  card('Standard deviation',f(sd)+' '+unit,'Combined across tasks (square root of the summed variances)')+
  card('68% range',f(Math.max(0,E-sd))+' to '+f(E+sd)+' '+unit,'Expected total plus or minus 1 standard deviation')+
  card('95% range',f(Math.max(0,E-2*sd))+' to '+f(E+2*sd)+' '+unit,'Expected total plus or minus 2 standard deviations')+
  card('Planning estimate at '+$('tme-conf').options[$('tme-conf').selectedIndex].text,f(plan)+' '+unit,'Adds '+f(plan-E)+' '+unit+' ('+f(cont)+'%) of contingency to the expected total',true);
 $('tme-msg').innerHTML=bad?'<span class="tme-bad">'+bad+' row'+(bad>1?'s were':' was')+' skipped. Each task needs 0 &le; O &le; M &le; P.</span>':'The total assumes the tasks are independent. If one delay pushes the others, treat the range as optimistic.';
}
function load(list){tb.innerHTML='';list.forEach(function(r){row(r[0],r[1],r[2],r[3])});render()}
function text(){
 var out=['Task,Optimistic,Most likely,Pessimistic,Expected,Std dev'],method=$('tme-method').value;
 rows().forEach(function(tr){var i=tr.querySelectorAll('input'),o=num(i[1].value),m=num(i[2].value),p=num(i[3].value);
  if(isNaN(o)||isNaN(m)||isNaN(p)||o>m||m>p)return;var r=calc(o,m,p,method);
  out.push('"'+(i[0].value||'Task').replace(/"/g,'""')+'",'+o+','+m+','+p+','+r.e.toFixed(2)+','+r.sd.toFixed(2))});
 return out.join('\n');
}
root.addEventListener('input',render);root.addEventListener('change',render);
root.addEventListener('click',function(e){var t=e.target;
 if(t.classList.contains('tme-rm')){t.closest('tr').remove();render()}
});
$('tme-add').onclick=function(){row('');render();tb.lastChild.querySelector('input').focus()};
$('tme-ex').onclick=function(){load(EX)};
$('tme-clr').onclick=function(){load([['',null,null,null],['',null,null,null]])};
$('tme-copy').onclick=function(){var s=text();(navigator.clipboard?navigator.clipboard.writeText(s):Promise.reject()).then(function(){$('tme-msg').textContent='Results copied as CSV text.'},function(){$('tme-msg').textContent='Copy failed. Use Download CSV instead.'})};
$('tme-csv').onclick=function(){var b=new Blob([text()],{type:'text/csv'}),a=document.createElement('a');a.href=URL.createObjectURL(b);a.download='three-point-estimate.csv';document.body.appendChild(a);a.click();a.remove()};
load(EX);
})();
</script>
	<?php
	return ob_get_clean();
} );
