<?php
/**
 * Plugin Name: Testmetry Tools
 * Description: Browser based test data tools for testmetry.com: [tm_ip_generator] and [tm_luhn_checker]. Also exposes Rank Math title, description and focus keyword to the REST API for editors. Nothing is sent to the server.
 * Version: 1.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'init', function () {
	foreach ( array( 'post', 'page' ) as $type ) {
		foreach ( array( 'rank_math_title', 'rank_math_description', 'rank_math_focus_keyword' ) as $key ) {
			register_post_meta( $type, $key, array(
				'type'          => 'string',
				'single'        => true,
				'show_in_rest'  => true,
				'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
			) );
		}
	}
} );

function tmt_css() {
	return '.tmt-box{background:#f7f9fc!important;border:1px solid #dbe3ef!important;border-radius:12px!important;padding:20px!important;margin:24px 0!important;box-shadow:0 1px 3px rgba(16,24,40,.06)!important;color:#101828!important}'
		. '.tmt-box *{box-sizing:border-box}'
		. '.tmt-box .tmt-hide{display:none!important}'
		. '.tmt-box .tmt-title{font-size:18px!important;font-weight:700!important;line-height:1.3!important;margin:0 0 4px!important;color:#101828!important}'
		. '.tmt-box .tmt-sub{font-size:13px!important;color:#475467!important;margin:0 0 16px!important;line-height:1.5!important}'
		. '.tmt-box .tmt-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin:0 0 16px}'
		. '.tmt-box label{display:flex!important;flex-direction:column!important;gap:6px!important;font-size:13px!important;font-weight:600!important;color:#344054!important;margin:0!important;line-height:1.3!important}'
		. '.tmt-box select,.tmt-box input[type=number],.tmt-box input[type=text]{display:block!important;width:100%!important;height:42px!important;padding:0 12px!important;margin:0!important;border:1px solid #cfd8e3!important;border-radius:8px!important;background:#fff!important;color:#101828!important;font-size:15px!important;line-height:40px!important;box-shadow:none!important}'
		. '.tmt-box select:focus,.tmt-box input:focus,.tmt-box textarea:focus{outline:none!important;border-color:#1f5fbf!important;box-shadow:0 0 0 3px rgba(31,95,191,.18)!important}'
		. '.tmt-box label.tmt-check{flex-direction:row!important;align-items:center!important;align-self:end!important;height:42px!important;gap:8px!important;font-weight:500!important;cursor:pointer}'
		. '.tmt-box .tmt-check input{width:18px!important;height:18px!important;margin:0!important;accent-color:#1f5fbf}'
		. '.tmt-box .tmt-actions{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 12px}'
		. '.tmt-box .tmt-btn{-webkit-appearance:none;appearance:none;display:inline-flex!important;align-items:center!important;justify-content:center!important;height:40px!important;padding:0 16px!important;margin:0!important;border:1px solid #1f5fbf!important;border-radius:8px!important;background:#1f5fbf!important;color:#fff!important;font-size:14px!important;font-weight:600!important;line-height:1!important;text-transform:none!important;letter-spacing:0!important;box-shadow:none!important;cursor:pointer;transition:background .15s,border-color .15s}'
		. '.tmt-box .tmt-btn:hover{background:#174a96!important;border-color:#174a96!important;color:#fff!important}'
		. '.tmt-box .tmt-btn.tmt-alt{background:#fff!important;color:#1f5fbf!important}'
		. '.tmt-box .tmt-btn.tmt-alt:hover{background:#eef4fd!important;color:#174a96!important}'
		. '.tmt-box .tmt-btn:focus-visible{outline:3px solid #9ec1f5!important;outline-offset:2px}'
		. '.tmt-box .tmt-out{display:block!important;width:100%!important;height:260px!important;min-height:160px!important;resize:vertical;margin:0!important;padding:12px 14px!important;border:1px solid #1e293b!important;border-radius:8px!important;background:#0f172a!important;color:#e2e8f0!important;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace!important;font-size:14px!important;line-height:1.6!important}'
		. '.tmt-box .tmt-msg{font-size:13px!important;color:#475467!important;margin:10px 0 0!important;line-height:1.5!important}'
		. '.tmt-box .tmt-ok{color:#067647!important;font-weight:600!important}.tmt-box .tmt-bad{color:#b42318!important;font-weight:600!important}'
		. '.tmt-box .tmt-inline{display:flex;gap:8px;align-items:flex-end}'
		. '.tmt-box .tmt-inline label{flex:1}'
		. '.tmt-box .tmt-result{margin:14px 0 0!important;padding:12px 14px!important;border:1px solid #dbe3ef!important;border-radius:8px!important;background:#fff!important;font-size:15px!important;line-height:1.5!important;color:#344054!important}'
		. '.tmt-box .tmt-result.is-ok{border-color:#abefc6!important;background:#ecfdf3!important}'
		. '.tmt-box .tmt-result.is-bad{border-color:#fecdca!important;background:#fef3f2!important}'
		. '@media (max-width:600px){.tmt-box{padding:16px!important}.tmt-box .tmt-actions .tmt-btn{flex:1 1 45%}.tmt-box .tmt-inline{flex-direction:column;align-items:stretch}.tmt-box .tmt-inline .tmt-btn{width:100%!important}}';
}

// Primary delivery: in the head, only on pages that use a tool.
add_action( 'wp_head', function () {
	if ( ! is_singular() ) { return; }
	$post = get_post();
	if ( ! $post || ( ! has_shortcode( $post->post_content, 'tm_ip_generator' ) && ! has_shortcode( $post->post_content, 'tm_luhn_checker' ) ) ) { return; }
	echo '<style id="tmt-css">' . tmt_css() . '</style>'; // phpcs:ignore
} );

// Fallback: optimisers sometimes strip inline styles, so inject them from a script if they are missing.
function tmt_css_fallback() {
	static $done = false;
	if ( $done ) { return ''; }
	$done = true;
	return '<script nowprocket>(function(){if(document.getElementById("tmt-css"))return;var s=document.createElement("style");s.id="tmt-css";s.textContent=' . wp_json_encode( tmt_css() ) . ';document.head.appendChild(s);})();</script>';
}

add_shortcode( 'tm_ip_generator', function () {
	ob_start();
	echo tmt_css_fallback(); // phpcs:ignore
	?>
<div class="tmt-box" id="tmt-ip">
	<p class="tmt-title">Random IP Address Generator</p>
	<p class="tmt-sub">Free, no sign up. Runs in your browser, so nothing is sent to our server.</p>
	<div class="tmt-grid">
		<label>IP version<select id="tmt-ip-ver"><option value="4">IPv4</option><option value="6">IPv6</option></select></label>
		<label>Address type<select id="tmt-ip-type">
			<option value="public">Public (routable format)</option>
			<option value="private">Private (RFC 1918 / ULA)</option>
			<option value="doc">Documentation only (safe placeholders)</option>
			<option value="range">Custom IPv4 range (CIDR)</option>
		</select></label>
		<label id="tmt-ip-cidr-wrap" class="tmt-hide">CIDR range<input id="tmt-ip-cidr" type="text" value="10.0.0.0/24" /></label>
		<label>How many (1 to 1000)<input id="tmt-ip-count" type="number" min="1" max="1000" value="10" /></label>
		<label class="tmt-check"><input id="tmt-ip-hex" type="checkbox" /> Show hex (IPv4)</label>
	</div>
	<div class="tmt-actions">
		<button type="button" class="tmt-btn" id="tmt-ip-go">Generate IPs</button>
		<button type="button" class="tmt-btn tmt-alt" id="tmt-ip-copy">Copy</button>
		<button type="button" class="tmt-btn tmt-alt" data-fmt="txt">TXT</button>
		<button type="button" class="tmt-btn tmt-alt" data-fmt="csv">CSV</button>
		<button type="button" class="tmt-btn tmt-alt" data-fmt="json">JSON</button>
	</div>
	<textarea class="tmt-out" id="tmt-ip-out" readonly aria-label="Generated IP addresses"></textarea>
	<div class="tmt-msg" id="tmt-ip-msg" aria-live="polite"></div>
</div>
<script nowprocket>
(function(){
var $=function(i){return document.getElementById(i)};
function r32(){var a=new Uint32Array(1);crypto.getRandomValues(a);return a[0]>>>0;}
function rint(n){return Math.floor(r32()/4294967296*n);}
function toIp(n){return [n>>>24,(n>>>16)&255,(n>>>8)&255,n&255].join('.');}
function toHex(n){return ('00000000'+(n>>>0).toString(16).toUpperCase()).slice(-8);}
var reserved=[['0.0.0.0',8],['10.0.0.0',8],['100.64.0.0',10],['127.0.0.0',8],['169.254.0.0',16],['172.16.0.0',12],['192.0.0.0',24],['192.0.2.0',24],['192.88.99.0',24],['192.168.0.0',16],['198.18.0.0',15],['198.51.100.0',24],['203.0.113.0',24],['224.0.0.0',3]];
function ipNum(s){var p=s.split('.');if(p.length!==4)return null;var n=0;for(var i=0;i<4;i++){var v=+p[i];if(!/^\d{1,3}$/.test(p[i])||v>255)return null;n=n*256+v;}return n>>>0;}
function inNet(n,base,bits){var m=bits===0?0:(0xFFFFFFFF<<(32-bits))>>>0;return ((n&m)>>>0)===((ipNum(base)&m)>>>0);}
function fromBlock(base,bits){var b=ipNum(base),size=Math.pow(2,32-bits);return (b+rint(size))>>>0;}
function v4(type,cidr){
	if(type==='private'){var nets=[['10.0.0.0',8],['172.16.0.0',12],['192.168.0.0',16]];var k=nets[rint(3)];return fromBlock(k[0],k[1]);}
	if(type==='doc'){var d=[['192.0.2.0',24],['198.51.100.0',24],['203.0.113.0',24]][rint(3)];return fromBlock(d[0],d[1]);}
	if(type==='range'){return fromBlock(cidr[0],cidr[1]);}
	for(;;){var n=r32();var bad=false;for(var i=0;i<reserved.length;i++){if(inNet(n,reserved[i][0],reserved[i][1])){bad=true;break;}}if(!bad)return n;}
}
function h16(){return rint(65536).toString(16);}
function v6(type){
	var g=[];for(var i=0;i<8;i++)g.push(h16());
	if(type==='private'){g[0]='fd'+('0'+rint(256).toString(16)).slice(-2);}
	else if(type==='doc'){g[0]='2001';g[1]='db8';}
	else{g[0]=(0x2000+rint(0x2000)).toString(16);}
	return g.join(':');
}
var last=[];
function gen(){
	var ver=$('tmt-ip-ver').value,type=$('tmt-ip-type').value,c=Math.max(1,Math.min(1000,parseInt($('tmt-ip-count').value,10)||1)),msg=$('tmt-ip-msg'),cidr=null;
	if(type==='range'){
		var m=/^\s*([\d.]+)\/(\d{1,2})\s*$/.exec($('tmt-ip-cidr').value);
		if(!m||ipNum(m[1])===null||+m[2]>32){msg.innerHTML='<span class="tmt-bad">Enter a range like 10.0.0.0/24</span>';return;}
		var bits=+m[2],mask=bits===0?0:(0xFFFFFFFF<<(32-bits))>>>0;cidr=[toIp((ipNum(m[1])&mask)>>>0),bits];
		if(ver==='6'){$('tmt-ip-ver').value='4';ver='4';}
	}
	last=[];
	for(var i=0;i<c;i++){
		if(ver==='6'){last.push({ip:v6(type)});}
		else{var n=v4(type,cidr);var row={ip:toIp(n)};if($('tmt-ip-hex').checked)row.hex=toHex(n);last.push(row);}
	}
	$('tmt-ip-out').value=last.map(function(r){return r.hex?r.ip+'  '+r.hex:r.ip;}).join('\n');
	msg.innerHTML='<span class="tmt-ok">'+last.length+' addresses ready.</span> Copy them or download as TXT, CSV or JSON.';
}
function dl(fmt){
	if(!last.length)gen();
	var body,mime='text/plain';
	if(fmt==='json'){body=JSON.stringify(last,null,2);mime='application/json';}
	else if(fmt==='csv'){var h=last[0].hex!==undefined;body=(h?'ip,hex\n':'ip\n')+last.map(function(r){return h?r.ip+','+r.hex:r.ip;}).join('\n');mime='text/csv';}
	else{body=last.map(function(r){return r.ip;}).join('\n');}
	var a=document.createElement('a');a.href=URL.createObjectURL(new Blob([body],{type:mime}));a.download='random-ips.'+fmt;document.body.appendChild(a);a.click();a.remove();
}
$('tmt-ip-type').addEventListener('change',function(){$('tmt-ip-cidr-wrap').classList.toggle('tmt-hide',this.value!=='range');});
$('tmt-ip-go').addEventListener('click',gen);
$('tmt-ip-copy').addEventListener('click',function(){if(!last.length)gen();var t=$('tmt-ip-out');t.select();try{navigator.clipboard.writeText(t.value);}catch(e){document.execCommand('copy');}$('tmt-ip-msg').innerHTML='<span class="tmt-ok">Copied to clipboard.</span>';});
document.querySelectorAll('#tmt-ip [data-fmt]').forEach(function(b){b.addEventListener('click',function(){dl(b.getAttribute('data-fmt'));});});
gen();
})();
</script>
	<?php
	return ob_get_clean();
} );

add_shortcode( 'tm_luhn_checker', function () {
	ob_start();
	echo tmt_css_fallback(); // phpcs:ignore
	?>
<div class="tmt-box" id="tmt-luhn">
	<p class="tmt-title">Luhn Checker for Test Card Numbers</p>
	<p class="tmt-sub">Checks the mod 10 digit and detects the brand. Runs in your browser; nothing is sent or stored. Only paste sandbox test numbers, never a real card.</p>
	<div class="tmt-inline">
		<label>Card number<input id="tmt-luhn-in" type="text" inputmode="numeric" autocomplete="off" placeholder="4242 4242 4242 4242" /></label>
		<button type="button" class="tmt-btn" id="tmt-luhn-go">Check number</button>
	</div>
	<div class="tmt-result" id="tmt-luhn-out" aria-live="polite">Enter a test number and press Check number.</div>
</div>
<script nowprocket>
(function(){
var inp=document.getElementById('tmt-luhn-in'),out=document.getElementById('tmt-luhn-out');
function luhn(d){var s=0,alt=false;for(var i=d.length-1;i>=0;i--){var n=+d[i];if(alt){n*=2;if(n>9)n-=9;}s+=n;alt=!alt;}return s%10===0;}
function brand(d){
	if(/^4/.test(d))return 'Visa';
	if(/^(5[1-5]|222[1-9]|22[3-9]\d|2[3-6]\d\d|27[01]\d|2720)/.test(d))return 'Mastercard';
	if(/^3[47]/.test(d))return 'American Express';
	if(/^(6011|64[4-9]|65)/.test(d))return 'Discover';
	if(/^35(2[89]|[3-8]\d)/.test(d))return 'JCB';
	if(/^3(0[0-5]|[68])/.test(d))return 'Diners Club';
	if(/^62/.test(d))return 'UnionPay';
	return 'Unknown brand';
}
function check(){
	var d=inp.value.replace(/[\s-]/g,'');
	if(!/^\d{12,19}$/.test(d)){out.className='tmt-result is-bad';out.innerHTML='<span class="tmt-bad">Enter 12 to 19 digits.</span>';return;}
	var ok=luhn(d);
	out.className='tmt-result '+(ok?'is-ok':'is-bad');
	out.innerHTML=(ok?'<span class="tmt-ok">Passes the Luhn check.</span>':'<span class="tmt-bad">Fails the Luhn check.</span>')+' Brand: <strong>'+brand(d)+'</strong>. Length: '+d.length+' digits. A pass only means the number is well formed; it says nothing about whether a card exists.';
}
document.getElementById('tmt-luhn-go').addEventListener('click',check);
inp.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();check();}});
})();
</script>
	<?php
	return ob_get_clean();
} );
