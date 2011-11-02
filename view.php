<noscript>
	<div><?php echo __('Hello, you either have JavaScript turned off or an old version of Macromedia\'s Flash Player'); ?>.<br/> <?php echo __('Get the latest Flash player'); ?>.</div>
</noscript>
<div id="no-flash" style="display:none;"><?php echo __('Hello, you either have JavaScript turned off or an old version of Macromedia\'s Flash Player'); ?>.<br/> <?php echo __('Get the latest Flash player'); ?>.</div>
<div class="cfct-mod-content center"><?php echo $vidoejs_html; ?></div>
<script>
function getFlashVersionIE(){
  // ie
  try {
    try {
      // avoid fp6 minor version lookup issues
      // see: http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
      var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
      try { axo.AllowScriptAccess = 'always'; }
      catch(e) { return '6,0,0'; }
    } catch(e) {}
    return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];
  // other browsers
  } catch(e) {
    try {
      if(navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin){
        return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
      }
    } catch(e) {}
  }
  return false;
}

function getFlashVersion() { 
	var flashVersion = false; 
	var agent = navigator.userAgent.toLowerCase(); 
	flashVersion = 0; 
	if (agent.indexOf("mozilla/3") != -1 && agent.indexOf("msie") == -1) { 
		flashVersion = 0; 
	} // NS3+, Opera3+, IE5+ Mac (support plugin array): check for Flash plugin in plugin array 
	if (navigator.plugins != null && navigator.plugins.length > 0) {
		var flashPlugin = navigator.plugins['Shockwave Flash']; 
		if (typeof flashPlugin == 'object') { 
			for (i=25;i>0;i--) { 
				if (flashPlugin.description.indexOf(i+'.') != -1){ 
					flashVersion = i; 
				} 
			}	 
		} 
	}
	return 	flashVersion; 
	
} 


jQuery(document).ready(function(){
	jQuery('div.video-js-box').hide();
	jQuery('#no-flash').hide();

	
	var agent = navigator.userAgent.toLowerCase(); 
	var isIE  = (agent.indexOf("msie") != -1);
	var versionStr = false;
	if (isIE) { 
		versionStr = getFlashVersionIE(); 
	} else { 
		versionStr = getFlashVersion(); 
	} 
	if (versionStr) {
		jQuery('div.video-js-box').show();
	} else {
		jQuery('#no-flash').show();
	}
	
});
</script>


