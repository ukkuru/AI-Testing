<?php
/**
 * Plugin Name: Testmetry Tools
 * Description: Browser based test data tools for testmetry.com: [tm_ip_generator] and [tm_luhn_checker]. Also exposes Rank Math title, description and focus keyword to the REST API for editors. Nothing is sent to the server.
 * Version: 1.0.0
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

function tmt_styles() {
	static $done = false;
	if ( $done ) { return ''; }
	$done = true;
	return '<style>
.tmt-box{border:1px solid #d0d7de;border-radius:8px;padding:18px;margin:20px 0;background:#f6f8fa}
.tmt-row{display:flex;flex-wrap:wrap;gap:12px;margin-bottom:12px;align-items:flex-end}
.tmt-row label{display:flex;flex-direction:column;font-size:14px;font-weight:600;gap:4px}
.tmt-row select,.tmt-row input{padding:8px;border:1px solid #d0d7de;border-radius:6px;font-size:15px;min-width:140px}
.tmt-btn{background:#0b5cad;color:#fff;border:0;border-radius:6px;padding:9px 16px;font-size:15px;cursor:pointer}
.tmt-btn.tmt-alt{background:#fff;color:#0b5cad;border:1px solid #0b5cad}
.tmt-out{width:100%;min-height:200px;font-family:monospace;font-size:14px;padding:10px;border:1px solid #d0d7de;border-radius:6px;box-sizing:border-box}
.tmt-msg{font-size:14px;margin-top:8px}
.tmt-ok{color:#1a7f37;font-weight:600}.tmt-bad{color:#cf222e;font-weight:600}
</style>';
}

add_shortcode( 'tm_ip_generator', function () {
	ob_start();
	echo tmt_styles(); // phpcs:ignore
	?>
<div class="tmt-box" id="tmt-ip">
	<div class="tmt-row">
		<label>IP version<select id="tmt-ip-ver"><option value="4">IPv4</option><option value="6">IPv6</option></select></label>
		<label>Address type<select id="tmt-ip-type">
			<option value="public">Public (routable format)</option>
			<option value="private">Private (RFC 1918 / ULA)</option>
			<option value="doc">Documentation only (safe placeholders)</option>
			<option value="range">Custom IPv4 range (CIDR)</option>
		</select></label>
		<label id="tmt-ip-cidr-wrap" style="display:none">CIDR range<input id="tmt-ip-cidr" value="10.0.0.0/24" /></label>
		<label>How many (1 to 1000)<input id="tmt-ip-count" type="number" min="1" max="1000" value="10" /></label>
		<label><span><input id="tmt-ip-hex" type="checkbox" /> Show hex (IPv4)</span></label>
	</div>
	<div class="tmt-row">
		<button type="button" class="tmt-btn" id="tmt-ip-go">Generate IPs</button>
		<button type="button" class="tmt-btn tmt-alt" id="tmt-ip-copy">Copy</button>
		<button type="button" class="tmt-btn tmt-alt" data-fmt="txt">Download TXT</button>
		<button type="button" class="tmt-btn tmt-alt" data-fmt="csv">Download CSV</button>
		<button type="button" class="tmt-btn tmt-alt" data-fmt="json">Download JSON</button>
	</div>
	<textarea class="tmt-out" id="tmt-ip-out" readonly aria-label="Generated IP addresses"></textarea>
	<div class="tmt-msg" id="tmt-ip-msg">Runs in your browser. Nothing is sent to our server.</div>
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
	msg.innerHTML='<span class="tmt-ok">'+last.length+' addresses ready.</span> Runs in your browser. Nothing is sent to our server.';
}
function dl(fmt){
	if(!last.length)gen();
	var body,mime='text/plain';
	if(fmt==='json'){body=JSON.stringify(last,null,2);mime='application/json';}
	else if(fmt==='csv'){var h=last[0].hex!==undefined;body=(h?'ip,hex\n':'ip\n')+last.map(function(r){return h?r.ip+','+r.hex:r.ip;}).join('\n');mime='text/csv';}
	else{body=last.map(function(r){return r.ip;}).join('\n');}
	var a=document.createElement('a');a.href=URL.createObjectURL(new Blob([body],{type:mime}));a.download='random-ips.'+fmt;document.body.appendChild(a);a.click();a.remove();
}
$('tmt-ip-type').addEventListener('change',function(){$('tmt-ip-cidr-wrap').style.display=this.value==='range'?'flex':'none';});
$('tmt-ip-go').addEventListener('click',gen);
$('tmt-ip-copy').addEventListener('click',function(){if(!last.length)gen();var t=$('tmt-ip-out');t.select();try{navigator.clipboard.writeText(t.value);}catch(e){document.execCommand('copy');}$('tmt-ip-msg').innerHTML='<span class="tmt-ok">Copied.</span>';});
document.querySelectorAll('#tmt-ip [data-fmt]').forEach(function(b){b.addEventListener('click',function(){dl(b.getAttribute('data-fmt'));});});
gen();
})();
</script>
	<?php
	return ob_get_clean();
} );

add_shortcode( 'tm_luhn_checker', function () {
	ob_start();
	echo tmt_styles(); // phpcs:ignore
	?>
<div class="tmt-box" id="tmt-luhn">
	<div class="tmt-row">
		<label style="flex:1">Card number to check<input id="tmt-luhn-in" inputmode="numeric" autocomplete="off" placeholder="4242 4242 4242 4242" style="width:100%" /></label>
		<button type="button" class="tmt-btn" id="tmt-luhn-go">Check number</button>
	</div>
	<div class="tmt-msg" id="tmt-luhn-out">Checks the Luhn (mod 10) digit and detects the card brand. Runs in your browser; nothing is sent or stored. Only paste sandbox test numbers, never a real card.</div>
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
	if(!/^\d{12,19}$/.test(d)){out.innerHTML='<span class="tmt-bad">Enter 12 to 19 digits.</span>';return;}
	var ok=luhn(d);
	out.innerHTML=(ok?'<span class="tmt-ok">Passes the Luhn check.</span>':'<span class="tmt-bad">Fails the Luhn check.</span>')+' Brand: <strong>'+brand(d)+'</strong>. Length: '+d.length+' digits. A pass only means the number is well formed; it says nothing about whether a card exists.';
}
document.getElementById('tmt-luhn-go').addEventListener('click',check);
inp.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();check();}});
})();
</script>
	<?php
	return ob_get_clean();
} );
