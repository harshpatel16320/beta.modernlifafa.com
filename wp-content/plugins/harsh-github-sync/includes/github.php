<?php
if(!defined('ABSPATH')) { exit; }
final class HPG_GitHub {
    private $id,$config,$root,$task='',$deadline=0;
    private $details=['files'=>[],'branch'=>'','commit'=>''];
    public function details() { return $this->details; }
    private function manifest($files) {
        $out=[];foreach($files as $file) { $status=['added'=>'A','removed'=>'D','modified'=>'M','changed'=>'T','renamed'=>'A','copied'=>'A'][$file['status']]??'M';$out[]=['status'=>$status,'path'=>$file['filename']];if($file['status']==='renamed' && !empty($file['previous_filename'])) { $out[]=['status'=>'D','path'=>$file['previous_filename']]; } }return $out;
    }
    public function __construct($id) {
        $this->id=$id;$this->config=HPG_Sync::config($id);
        if(!preg_match('~^(?:https://github\.com/|git@github\.com:|ssh://git@github\.com/)([A-Za-z0-9_.-]+)/([A-Za-z0-9_.-]+)$~D',$this->config['url'],$m)) { throw new RuntimeException('Save a valid GitHub profile first.'); }
        $this->root='https://api.github.com/repos/'.$m[1].'/'.preg_replace('/\.git$/','',$m[2]);
    }
    public function create_branch_fast($source,$target) {
        if(!HPG_Access::admin()) { throw new RuntimeException('Only administrators may create branches.'); }
        $this->task='create_branch';
        $ref=$this->api('/git/ref/heads/'.rawurlencode($source));
        $sha=$ref['object']['sha']??'';
        if(!preg_match('/^[a-f0-9]{40}$/D',$sha)) { throw new RuntimeException('Source branch did not return a valid commit.'); }
        $this->api('/git/refs','POST',['ref'=>'refs/heads/'.$target,'sha'=>$sha]);
        $this->details=['files'=>[],'branch'=>$target,'commit'=>$sha];
        return 'Created branch '.$target.' from '.$source.' through GitHub API. No repository download was needed.';
    }
    private function api($path,$method='GET',$body=null) {
        $token=HPG_Engine::token($this->id);if(!$token) { throw new RuntimeException('No token is stored for this profile. Enter the token in Git Review and click Save token. SSH keys authorize Git push/fetch, but merge tools need a token.'); }
        update_post_meta($this->id,'_hpg_process',['state'=>'running','phase'=>$this->task.' · GitHub API · '.$method,'time'=>time()]);
        $args=['method'=>$method,'timeout'=>60,'redirection'=>0,'limit_response_size'=>8388608,'headers'=>['Authorization'=>'Bearer '.$token,'Accept'=>'application/vnd.github+json','User-Agent'=>'Harsh-GitHub-Sync','X-GitHub-Api-Version'=>'2026-03-10']];
        if($body!==null) { $args['headers']['Content-Type']='application/json';$args['body']=wp_json_encode($body); }
        $request_url=$this->root.$path;
        for($attempt=0;$attempt<($method==='GET'?2:1);$attempt++) {
            // Individual failed network requests stay bounded; the overall action has no deadline.
            $args['timeout']=60;
            $tune=function($handle,$parsed,$url) use($request_url,$args) {
                if($url===$request_url && function_exists('curl_setopt') && defined('CURLOPT_CONNECTTIMEOUT')) { curl_setopt($handle,CURLOPT_CONNECTTIMEOUT,min(30,$args['timeout'])); }
            };
            add_action('http_api_curl',$tune,PHP_INT_MAX,3);
            try { $r=wp_remote_request($request_url,$args); } finally { remove_action('http_api_curl',$tune,PHP_INT_MAX); }
            if(!is_wp_error($r)) { break; }
            $error=$r->get_error_message();
            if($method==='GET' && $attempt===0 && preg_match('/cURL error 28|Could not resolve host|Resolving timed out/i',$error)) {
                update_post_meta($this->id,'_hpg_process',['state'=>'running','phase'=>'Retrying GitHub read · connection timeout','time'=>time()]);continue;
            }
            $hint=preg_match('/Resolving timed out|Could not resolve host/i',$error)?'Cannot resolve api.github.com. Check server DNS, proxy or firewall. ':'';
            throw new RuntimeException(HPG_Engine::redact($hint.$error,$this->id));
        }
        $code=wp_remote_retrieve_response_code($r);$data=json_decode(wp_remote_retrieve_body($r),true);
        if($code<200 || $code>=300 || !is_array($data)) {
            $message=is_array($data) && is_scalar($data['message']??null)?(string)$data['message']:'Unexpected response';
            foreach(array_slice((array)($data['errors']??[]),0,10) as $error) {
                if(is_string($error)) { $message.="\n".$error; }
                elseif(is_array($error)) {
                    $parts=[];foreach(['message','resource','field','code'] as $key) { if(isset($error[$key]) && is_scalar($error[$key])) { $parts[]=$key.': '.(string)$error[$key]; } }
                    if($parts) { $message.="\n".implode(' · ',$parts); }
                }
            }
            if(in_array($code,[500,502,503,504],true)) {
                $message.="\nGitHub returned a server/gateway error. The result may be unknown. Check the repository's pull requests before retrying; a request may already have been created. Do not change token permissions based on this error alone.";
            }
            if($code===405 && $this->task==='accept_pr') { $message.="\nClick Load conflict editor in Git Review to check and resolve supported text conflicts. GitHub checks and branch rules can also prevent merging."; }
            if($code===422 && $this->task==='create_pr') {
                $message.="\nFrom: ".$body['head'].' → To: '.$body['base'];
                $message.="\nIf a request already exists, enter its number and click Review request. If there are no commits to propose, push new changes to From or check the branch direction.";
            }
            throw new RuntimeException('GitHub API '.$code.': '.HPG_Engine::redact($message,$this->id));
        }
        return $data;
    }
    public function run($task,$input) {
        $this->task=$task;$this->deadline=0;if(function_exists('set_time_limit')) { @set_time_limit(0); }
        if(in_array($task,['compare','find_pr','create_pr'],true)) {
            $from=trim(wp_unslash($input['compare_head']??''));$to=trim(wp_unslash($input['compare_base']??''));
            if($from==='' || $to==='') { throw new RuntimeException('Select a From branch and a To branch before continuing.'); }
            if($from===$to) { throw new RuntimeException('Choose two different branches.'); }
        }
        if($task==='test_api') {
            $this->api('');$this->api('/pulls?state=open&per_page=1');return 'Stored token can read this repository and its pull requests. Creating/merging still requires Contents and Pull requests write permissions. Repository: '.$this->config['url'];
        } elseif($task==='compare') {
            $base=trim(wp_unslash($input['compare_base']??'main'));$head=trim(wp_unslash($input['compare_head']??$this->config['branch']));
            foreach([$base,$head] as $branch) { $c=$this->config;$c['branch']=$branch;HPG_Engine::validate_config($c); }
            HPG_Access::protect_main([$base,$head]);$this->details['branch']=$head;
            $d=$this->api('/compare/'.rawurlencode($base).'...'.rawurlencode($head));
            $panel=['kind'=>'compare','base_ref'=>$base,'head_ref'=>$head,'time'=>time(),'title'=>$base.' → '.$head,'url'=>$d['html_url']??'','files'=>$d['files']??[],'message'=>'GitHub comparison: '.$d['status'].'. Up to 300 changed files are returned by GitHub; use the GitHub link for the full review. Missing patches can indicate binary or large files.'];
        } elseif($task==='find_pr') {
            $base=trim(wp_unslash($input['compare_base']??'main'));$head=trim(wp_unslash($input['compare_head']??$this->config['branch']));
            foreach([$base,$head] as $branch) { $c=$this->config;$c['branch']=$branch;HPG_Engine::validate_config($c); }
            HPG_Access::protect_main([$base,$head]);$owner=explode('/',substr($this->root,strlen('https://api.github.com/repos/')))[0];
            $list=$this->api('/pulls?state=open&head='.rawurlencode($owner.':'.$head).'&base='.rawurlencode($base).'&per_page=100');$found=[];
            foreach($list as $pr) { if(is_array($pr) && ($pr['head']['ref']??'')===$head && ($pr['base']['ref']??'')===$base && !empty($pr['number'])) { $found[]=['repo'=>$this->config['url'],'number'=>$pr['number'],'head'=>$head,'base'=>$base,'url'=>$pr['html_url']??'','title'=>$pr['title']??'']; } }
            update_post_meta($this->id,'_hpg_open_prs',$found);$this->details['branch']=$head;
            if($found) { update_post_meta($this->id,'_hpg_last_pr',$found[0]);return 'Open request #'.$found[0]['number'].' · '.$head.' → '.$base.'. Click Review request.'; }
            delete_post_meta($this->id,'_hpg_last_pr');return 'No open request found · '.$head.' → '.$base;
        } elseif($task==='create_pr') {
            $base=trim(wp_unslash($input['compare_base']??'main'));$head=trim(wp_unslash($input['compare_head']??$this->config['branch']));
            foreach([$base,$head] as $branch) { $c=$this->config;$c['branch']=$branch;HPG_Engine::validate_config($c); }
            HPG_Access::protect_main([$base,$head]);
            $title=sanitize_text_field(wp_unslash($input['pr_title']??''));if(!$title) { $title='Merge '.$head.' into '.$base; }if($base===$head) { throw new RuntimeException('Choose two different branches. From and To cannot be the same.'); }
            $this->details['branch']=$head;
            $pr=$this->api('/pulls','POST',['base'=>$base,'head'=>$head,'title'=>$title,'body'=>sanitize_textarea_field(wp_unslash($input['pr_body']??''))]);
            update_post_meta($this->id,'_hpg_last_pr',['repo'=>$this->config['url'],'number'=>$pr['number'],'head'=>$head,'base'=>$base,'url'=>$pr['html_url']]);
            return 'Merge request #'.$pr['number'].' created: '.$head.' → '.$base.'. '.$pr['html_url'].'. Click Review request, then Accept code to merge.';
        } elseif(in_array($task,['load_conflicts','resolve_conflicts'],true)) {
            if(!HPG_Access::admin()) { throw new RuntimeException('Only administrators may resolve conflicts.'); }
            $number=absint($input['pr_number']??0);if(!$number) { throw new RuntimeException('Enter a pull request number.'); }
            $pr=$this->api('/pulls/'.$number);
            if($pr['state']!=='open' || !empty($pr['draft'])) { throw new RuntimeException('Choose an open, ready pull request.'); }
            if(empty($pr['head']['repo']['full_name']) || strcasecmp($pr['head']['repo']['full_name'],$pr['base']['repo']['full_name']??'')!==0) { throw new RuntimeException('Fork requests must be resolved in their source repository using Git.'); }
            $saved=get_post_meta($this->id,'_hpg_conflicts',true);$values=null;
            if($task==='resolve_conflicts') {
                if(!is_array($saved) || $saved['number']!==$number || $saved['repo']!==$this->config['url'] || time()-$saved['time']>3600 || $saved['head']!==$pr['head']['sha'] || $saved['base']!==$pr['base']['sha']) { throw new HPG_Review_Required('Conflict review expired or branches changed. Load conflicts again.'); }
                $values=wp_unslash($input['conflict_text']??[]);if(!is_array($values) || count($values)!==count($saved['files'])) { throw new RuntimeException('Resolve every listed file.'); }
            } else { delete_post_meta($this->id,'_hpg_conflicts'); }
            $engine=new HPG_Engine($this->id,$this->config);$files=$engine->conflicts($pr,$values);$this->details=$engine->details();
            if($task==='resolve_conflicts') { delete_post_meta($this->id,'_hpg_conflicts');delete_post_meta($this->id,'_hpg_git_panel');return 'Resolutions pushed to '.$pr['head']['ref'].'. Review request again, then Accept code / merge. GitHub branch rules still apply.'; }
            if(!$files) { return 'No file conflicts found. Review request again; branch protection or checks may still block merging.'; }
            update_post_meta($this->id,'_hpg_conflicts',['number'=>$number,'repo'=>$this->config['url'],'head'=>$pr['head']['sha'],'base'=>$pr['base']['sha'],'head_ref'=>$pr['head']['ref'],'base_ref'=>$pr['base']['ref'],'time'=>time(),'files'=>$files]);
            return count($files).' conflicting text files loaded. Edit each file below and remove all conflict markers.';
        } elseif($task==='review_pr') {
            $number=absint($input['pr_number']??0);if(!$number) { throw new RuntimeException('Enter a pull request number.'); }
            $pr=$this->api('/pulls/'.$number);HPG_Access::protect_main([$pr['base']['ref'],$pr['head']['ref']]);$files=[];
            for($page=1;$page<=30;$page++) { $batch=$this->api('/pulls/'.$number.'/files?per_page=100&page='.$page);$files=array_merge($files,$batch);if(count($batch)<100) { break; } }
            $panel=['kind'=>'pr','base_ref'=>$pr['base']['ref'],'head_ref'=>$pr['head']['ref'],'number'=>$number,'head'=>$pr['head']['sha'],'base'=>$pr['base']['sha'],'repo'=>$this->config['url'],'time'=>time(),'title'=>'Merge '.$pr['head']['ref'].' into '.$pr['base']['ref'].' · #'.$number,'url'=>$pr['html_url'],'files'=>$files,'message'=>'State: '.$pr['state'].' · '.$pr['head']['ref'].' → '.$pr['base']['ref'].'. Review changes before accepting. GitHub returns at most 3,000 PR files; inspect GitHub for larger requests. Accept merges on GitHub; it does not overwrite WordPress files.'];
        } elseif($task==='accept_pr') {
            $number=absint($input['pr_number']??0);$panel=get_post_meta($this->id,'_hpg_git_panel',true);
            if(!is_array($panel) || $panel['kind']!=='pr' || $panel['number']!==$number || $panel['repo']!==$this->config['url'] || time()-$panel['time']>3600) { throw new HPG_Review_Required('Review this pull request first. Reviews expire after one hour.'); }
            $pr=$this->api('/pulls/'.$number);HPG_Access::protect_main([$pr['base']['ref'],$pr['head']['ref']]);
            if($pr['state']!=='open' || !empty($pr['draft']) || $pr['head']['sha']!==$panel['head'] || $pr['base']['sha']!==$panel['base']) { throw new HPG_Review_Required('Pull request changed, is closed, or is a draft. Review it again before accepting.'); }
            if(($pr['mergeable']??null)===false) { throw new HPG_Review_Required('This request has merge conflicts. Click Load conflict editor, resolve the files, then review again.'); }
            $defaults=get_option('hpg_ui_settings',[]);$method=$defaults['merge_method']??'merge';if(!in_array($method,['merge','squash','rebase'],true)) { $method='merge'; }
            $d=$this->api('/pulls/'.$number.'/merge','PUT',['sha'=>$panel['head'],'merge_method'=>$method]);
            if(empty($d['merged'])) { throw new RuntimeException($d['message']??'GitHub could not merge this request.'); }
            $this->details=['files'=>$this->manifest($panel['files']),'branch'=>$pr['base']['ref'],'commit'=>$d['sha']];
            delete_post_meta($this->id,'_hpg_git_panel');return 'Accepted pull request #'.$number.' on GitHub. Commit: '.$d['sha'].'. Local WordPress files were not changed.';
        } else { throw new RuntimeException('Invalid GitHub action.'); }
        $this->details['files']=$this->manifest($panel['files']);if($task==='review_pr') { $this->details['branch']=$pr['head']['ref']; }
        $budget=500000;foreach($panel['files'] as &$file) { $patch=(string)($file['patch']??'');$file['patch']=substr($patch,0,min(20000,max(0,$budget)));$budget-=strlen($file['patch']);if(strlen($patch)>strlen($file['patch'])) { $file['patch'].="\n[Patch shortened; inspect on GitHub.]"; } }unset($file);
        update_post_meta($this->id,'_hpg_git_panel',$panel);return $panel['message'];
    }
    public static function conflict_editor($id) {
        if(!HPG_Access::admin()) { return; }$p=get_post_meta($id,'_hpg_conflicts',true);
        if(!is_array($p) || ($p['repo']??'')!==HPG_Sync::config($id)['url'] || time()-$p['time']>3600) { return; }
        ?><section class="hpg-scope-box"><h3>Resolve #<?php echo esc_html($p['number']); ?> · <?php echo esc_html($p['head_ref'].' → '.$p['base_ref']); ?></h3><p>Keep the code you want; remove &lt;&lt;&lt;&lt;&lt;&lt;&lt;, ======= and &gt;&gt;&gt;&gt;&gt;&gt;&gt; marker lines. Saves a merge commit to the From branch.</p><div class="hpg-panel-scroll"><?php foreach($p['files'] as $i=>$file): ?><label><?php echo esc_html($file['path']); ?></label><textarea class="hpg-conflict-text" name="conflict_text[<?php echo esc_attr($i); ?>]" rows="16" spellcheck="false"><?php echo esc_textarea($file['text']); ?></textarea><?php endforeach; ?></div><button class="hpg-btn" name="task" value="resolve_conflicts" onclick="return confirm('Commit these resolutions and push to the From branch?');">Save resolutions to From branch</button></section><?php
    }
    public static function panel($id) {
        $p=get_post_meta($id,'_hpg_git_panel',true);if(!is_array($p)) { return; }
        if(!HPG_Access::admin() && (!isset($p['base_ref'],$p['head_ref']) || in_array('main',[$p['base_ref'],$p['head_ref']],true))) { return; }
        ?><section class="hpg-card hpg-preview"><h2><?php echo esc_html($p['title']); ?></h2><p><?php echo esc_html($p['message']); ?></p><p class="hpg-help">Reviewed <?php echo esc_html(wp_date('M j, Y · H:i:s',$p['time'])); ?></p><p><a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($p['url']); ?>">Open full review on GitHub ↗</a></p><div class="hpg-panel-scroll"><?php foreach($p['files'] as $file): ?><details><summary><?php echo esc_html($file['status'].' · '.$file['filename'].' · +'.$file['additions'].' / −'.$file['deletions']); ?></summary><pre><?php echo esc_html($file['patch']?:'No text patch. Inspect this file on GitHub.'); ?></pre></details><?php endforeach; ?></div></section><?php
    }
}
