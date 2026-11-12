(function(){
  'use strict';
  function duration(seconds){var n=Math.max(0,Math.floor(Number(seconds)||0));return Math.floor(n/60)+'m '+(n%60)+'sec';}
  document.addEventListener('DOMContentLoaded',function(){
    var noticeUrl=new URL(location.href);['reset_done','settings_saved','history_deleted','saved'].forEach(function(key){noticeUrl.searchParams.delete(key);});if(noticeUrl.href!==location.href)history.replaceState(history.state,'',noticeUrl.href);
    var navigation=performance.getEntriesByType('navigation')[0];if(navigation&&navigation.type==='reload'){document.querySelectorAll('.hpg-alert').forEach(function(notice){notice.hidden=true;});document.querySelectorAll('[data-hpg-scan-results]').forEach(function(list){list.hidden=true;});}
    document.querySelectorAll('[data-hpg-copy]').forEach(function(button){
      button.addEventListener('click',async function(){
        var input=document.getElementById(button.dataset.hpgCopy),message=button.parentElement.querySelector('.hpg-copy-feedback');
        if(!input)return;try{
          if(navigator.clipboard&&window.isSecureContext){await navigator.clipboard.writeText(input.value);}
          else{input.focus();input.select();if(!document.execCommand('copy'))throw new Error('Copy unavailable');}
          message.textContent='Public key copied';message.classList.remove('error');
        }catch(e){input.focus();input.select();message.textContent='Select the key and press Ctrl+C to copy';message.classList.add('error');}
      });
    });
    document.querySelectorAll('[data-hpg-token-eye]').forEach(function(button){
      button.addEventListener('click',async function(){
        var box=button.closest('.hpg-token-eye'),view=box.querySelector('.hpg-stored-token-view'),message=box.querySelector('.hpg-token-message');
        if(!view.hidden){view.value='';view.type='password';view.hidden=true;button.textContent='◉ Show saved token';button.setAttribute('aria-pressed','false');message.textContent='Stored token hidden.';return;}
        if(!window.hpgProcess)return;button.disabled=true;message.textContent='Loading saved token…';
        try{var body=new FormData();body.set('action','hpg_reveal_token');body.set('profile',button.dataset.profile);body.set('nonce',button.dataset.nonce);var response=await fetch(hpgProcess.url,{method:'POST',body:body,credentials:'same-origin',cache:'no-store'});var result=await response.json();if(!response.ok||!result.success||!result.data||typeof result.data.token!=='string')throw new Error(result.data&&result.data.message?result.data.message:'Could not read the saved token');view.value=result.data.token;view.type='text';view.hidden=false;button.textContent='◉ Hide saved token';button.setAttribute('aria-pressed','true');message.textContent='Saved token shown for this profile.';}
        catch(e){view.value='';view.hidden=true;message.textContent=e.message;}
        finally{button.disabled=false;}
      });
    });
    document.querySelectorAll('[data-hpg-path-picker]').forEach(function(picker){
      var input=document.getElementById(picker.dataset.hpgPathPicker);if(!input)return;
      function lines(){return input.value.split(/\r?\n/).map(function(v){v=v.trim();if(input.id==='hpg-paths'&&v==='.')return '*';var clean=v.replace(/^\/+|\/+$/g,'');if(['plugins','themes','mu-plugins'].includes(clean))return 'wp-content/'+clean;return clean;}).filter(Boolean);}
      function sync(){var values=lines();Array.from(picker.options).forEach(function(o){o.selected=values.includes(o.value);});}
      input.addEventListener('input',sync);sync();
      picker.addEventListener('change',function(){
        var old=lines(),choices=Array.from(picker.options),known=choices.map(function(o){return o.value;}),selected=choices.filter(function(o){return o.selected;}).map(function(o){return o.value;});
        var extras=old.filter(function(v){return !known.includes(v);});
        if(input.id==='hpg-paths'){
          if(selected.includes('*')&&selected.length>1){if(old.includes('*'))selected=selected.filter(function(v){return v!=='*';});else selected=['*'];}
          if(selected.includes('*'))extras=[];
          var added=selected.filter(function(v){return !old.includes(v);});selected=selected.filter(function(parent){return !added.some(function(child){return child!==parent&&child.startsWith(parent+'/');});});
          var all=extras.concat(selected);all=all.filter(function(path){return !all.some(function(parent){return parent!==path&&(parent==='*'||parent==='.'||path.startsWith(parent+'/'));});});input.value=Array.from(new Set(all)).join('\n');
        }else{input.value=Array.from(new Set(extras.concat(selected))).join('\n');}sync();input.dispatchEvent(new Event('change',{bubbles:true}));
      });
      var search=document.querySelector('[data-hpg-picker-search="'+picker.dataset.hpgPathPicker+'"]');if(search)search.addEventListener('input',function(){var query=search.value.toLowerCase();Array.from(picker.options).forEach(function(o){o.hidden=!o.textContent.toLowerCase().includes(query);});});
    });
    var region=document.createElement('div');region.className='hpg-toast-region';region.setAttribute('aria-live','polite');
    var toast=document.createElement('div');toast.className='hpg-toast';toast.hidden=true;
    var toastText=document.createElement('span');toastText.className='hpg-toast-text';var close=document.createElement('button');close.type='button';close.className='hpg-toast-close';close.textContent='×';close.setAttribute('aria-label','Dismiss process notice');toast.append(toastText,close);region.append(toast);document.body.append(region);
    var hideTimer=null,completedText='';
    var dismissed=false;close.addEventListener('click',function(){dismissed=true;toast.hidden=true;});
    function showProcess(text,state){if(dismissed)return;if(state==='completed'&&toast.dataset.state==='completed'){if(!toast.hidden)toastText.textContent=text;return;}clearTimeout(hideTimer);toast.hidden=false;toast.dataset.state=state||'running';toastText.textContent=text;
      if(state==='completed'){completedText=text;hideTimer=setTimeout(function(){toast.hidden=true;},5000);}else{completedText='';}
    }
    try{var saved=JSON.parse(sessionStorage.getItem('hpg-success-toast')||'null');sessionStorage.removeItem('hpg-success-toast');if(saved&&Date.now()-saved.time<30000)showProcess(saved.text,'completed');}catch(e){}
    var successAlert=document.querySelector('.hpg-alert.success');if(toast.hidden&&successAlert&&(!navigation||navigation.type!=='reload'))showProcess(successAlert.textContent.trim().slice(0,600),'completed');
    var currentStatus=document.getElementById('hpg-process-text');if(currentStatus&&currentStatus.closest('.hpg-process').dataset.state==='running')showProcess(currentStatus.textContent,'running');
    var form=Array.from(document.querySelectorAll('.hpg form')).find(function(f){return f.querySelector('input[name=action][value=hpg_sync]');});
    if(!form||!window.hpgProcess)return;
    var profileInput=form.querySelector('input[name=profile]');
    async function watchExisting(){if(!profileInput||!profileInput.value||profileInput.value==='0'||busy)return;
      try{var url=new URL(hpgProcess.url,location.href);url.searchParams.set('action','hpg_process');url.searchParams.set('nonce',hpgProcess.nonce);url.searchParams.set('profile',profileInput.value);var response=await fetch(url,{credentials:'same-origin',cache:'no-store'});var result=await response.json();if(result.success&&result.data&&result.data.state){var p=result.data;var text=p.state+' · '+p.phase+' · '+duration(p.elapsed)+' elapsed';if(p.state==='running'){showProcess(text,'running');status.textContent=text;status.closest('.hpg-process').dataset.state='running';}else if(!toast.hidden){showProcess(text,p.state);status.textContent=text;status.closest('.hpg-process').dataset.state=p.state;}}}catch(e){}
    }
    setInterval(watchExisting,4000);
    var from=form.querySelector('select[name=compare_head]'),to=form.querySelector('select[name=compare_base]');
    function direction(changed){
      if(!from||!to)return;
      if(from.value&&from.value===to.value){var other=changed===to?from:to;var alternative=Array.from(other.options).find(function(o){return o.value!==(changed===to?to.value:from.value);});if(alternative)other.value=alternative.value;else other.selectedIndex=-1;}
      Array.from(from.options).forEach(function(o){o.disabled=!!o.value&&o.value===to.value;});Array.from(to.options).forEach(function(o){o.disabled=!!o.value&&o.value===from.value;});
      var a=document.getElementById('hpg-merge-from'),b=document.getElementById('hpg-merge-to');if(a)a.textContent=from.value||'Select branch';if(b)b.textContent=to.value||'Select branch';
      var title=form.querySelector('input[name=pr_title]');if(title)title.placeholder='Merge '+from.value+' into '+to.value;
      form.querySelectorAll('button[value=compare],button[value=create_pr],button[value=find_pr]').forEach(function(button){button.disabled=!from.value||!to.value||from.value===to.value;});
    }
    if(from&&to){from.addEventListener('change',function(){direction(from);});to.addEventListener('change',function(){direction(to);});direction(from);}
    var status=document.getElementById('hpg-process-text'),busy=false;
    form.addEventListener('submit',async function(event){
      if(busy){event.preventDefault();return;}
      var button=event.submitter;if(!button||!button.value)return;
      event.preventDefault();busy=true;dismissed=false;
      var body=new FormData(form);body.set('task',button.value);body.set('hpg_ajax','1');
      var id=body.get('profile'),started=Math.floor(Date.now()/1000),timer;
      var buttons=Array.from(form.querySelectorAll('button'));buttons.forEach(function(b){b.disabled=true;});
      status.textContent='Running · '+button.value+' · waiting for server';showProcess(status.textContent,'running');status.closest('.hpg-process').classList.add('running');status.closest('.hpg-process').dataset.state='running';
      async function poll(){
        if(!id||id==='0')return;
        try{var url=new URL(hpgProcess.url,location.href);url.searchParams.set('action','hpg_process');url.searchParams.set('nonce',hpgProcess.nonce);url.searchParams.set('profile',id);var response=await fetch(url,{credentials:'same-origin',cache:'no-store'});var result=await response.json();if(result.success&&result.data&&result.data.time>=started){status.closest('.hpg-process').dataset.state=result.data.state;status.textContent=result.data.state+' · '+result.data.phase+' · '+duration(typeof result.data.elapsed==='number'?result.data.elapsed:Date.now()/1000-started)+' elapsed';showProcess(status.textContent,result.data.state);}}catch(e){/* The action can continue even when polling is unavailable. */}
      }
      timer=setInterval(poll,2500);
      try{
        var response=await fetch(form.getAttribute('action'),{method:'POST',body:body,credentials:'same-origin'});
        var targetUrl;var type=response.headers.get('content-type')||'';
        if(type.includes('application/json')){var result=await response.json();if(!response.ok||!result.success||!result.data||!result.data.redirect)throw new Error(result.data&&result.data.message?result.data.message:'Server error (HTTP '+response.status+'). Check the PHP error log.');targetUrl=result.data.redirect;}
        else{if(!response.ok)throw new Error('Server error (HTTP '+response.status+'). Check the PHP error log.');if(!response.redirected)throw new Error('Unexpected server response. Check the PHP error log.');targetUrl=response.url;}
        if(typeof result!=='undefined'&&result.data&&result.data.result&&result.data.result.ok){var done=result.data.result;var text='Completed · '+done.action+' · '+done.message.slice(0,600);try{sessionStorage.setItem('hpg-success-toast',JSON.stringify({text:text,time:Date.now()}));}catch(e){showProcess(text,'completed');}}
        var target=new URL(targetUrl,location.href);if(target.origin!==location.origin)throw new Error('Unexpected redirect');
        if(button.value==='preview')target.hash='hpg-preview-panel';
        location.assign(target.href);
      }catch(e){status.closest('.hpg-process').dataset.state='failed';status.textContent='Connection interrupted · '+e.message.replace(/[.]+$/,'')+'. The server may still be working. Reload and check History before retrying.';showProcess(status.textContent,'failed');buttons.forEach(function(b){b.disabled=false;});busy=false;status.closest('.hpg-process').classList.remove('running');}
      finally{clearInterval(timer);}
    });
  });
})();
