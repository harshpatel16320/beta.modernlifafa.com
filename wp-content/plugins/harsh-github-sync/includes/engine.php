<?php
if (!defined('ABSPATH')) { exit; }
class HPG_Review_Required extends RuntimeException {}
class HPG_Operation_Running extends RuntimeException {}
final class HPG_Engine {
    private $id, $config, $base, $repo, $env, $deadline;
    private $details=['files'=>[], 'commit'=>'', 'branch'=>''];
    private $large_files=[], $needs_lfs=false;
    private $base_branch='', $task='';
    
    public function details() { return $this->details; }
    public static function normalize_paths($input) {
        $paths=[]; foreach(self::lines($input) as $path) {
            $path=trim($path,'/');
            if($path==='*' || $path==='.') { return '.'; }
            if(in_array($path,['plugins','themes','mu-plugins'],true)) { $path='wp-content/'.$path; }
            $paths[]=$path;
        } return implode("\n",$paths);
    }
    public function __construct($id, $config) { $this->id=(int)$id; $config['paths']=self::normalize_paths($config['paths']??'');$this->config=$config; }
    public static function validate_config($c) {
        if (!preg_match('~^(?:https://github\.com/|git@github\.com:|ssh://git@github\.com/)[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+(?:\.git)?$~D', $c['url'])) {
            throw new RuntimeException('Enter a GitHub.com HTTPS or SSH repository URL without embedded credentials.');
        }
        if (!preg_match('~^[A-Za-z0-9][A-Za-z0-9._/-]*$~D',$c['branch']) || strpos($c['branch'],'..')!==false || strpos($c['branch'],'//')!==false || substr($c['branch'],-1)==='/' || substr($c['branch'],-5)==='.lock' || strpos($c['branch'],'@{')!==false) {
            throw new RuntimeException('Invalid branch name. Use a name such as main, staging, or feature/update.');
        }
        $paths=self::lines($c['paths']);
        if (!$paths) { throw new RuntimeException('Select at least one source path.'); }
        foreach($paths as $p) {
            if ($p!=='.' && (preg_match('/[\x00-\x1F\x7F]/',$p) || preg_match('~(^|/)\.\.?(/|$)~',$p) || $p[0]==='/' || substr($p,-1)==='/')) {
                throw new RuntimeException('Paths must be relative, without trailing slashes or .. components.');
            }
            $source=realpath(ABSPATH . $p); $root=realpath(ABSPATH);
            if (!$source || !self::inside($source,$root)) { throw new RuntimeException('Source path does not exist or is outside WordPress: '.$p); }
        }
        for($i=0;$i<count($paths);$i++) { for($j=$i+1;$j<count($paths);$j++) {
            if ($paths[$i]==='.' || $paths[$j]==='.' || strpos($paths[$i].'/', $paths[$j].'/')===0 || strpos($paths[$j].'/', $paths[$i].'/')===0) {
                throw new RuntimeException('Select either . or non-overlapping source paths, not both.');
            }
        } }
    }
    public static function lines($s) { return array_values(array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $s))))); }
    private static function inside($path,$root) { return $path===$root || strpos($path,$root.DIRECTORY_SEPARATOR)===0; }
    public static function seal($token) {
        if (!function_exists('openssl_encrypt')) { throw new RuntimeException('OpenSSL is required to securely store HTTPS tokens.'); }
        $iv=random_bytes(12); $tag='';
        $cipher=openssl_encrypt($token,'aes-256-gcm',hash('sha256',wp_salt('auth'),true),OPENSSL_RAW_DATA,$iv,$tag);
        if ($cipher===false) { throw new RuntimeException('Could not encrypt token.'); }
        return base64_encode($iv.$tag.$cipher);
    }
    public static function token($id) {
        $raw=get_post_meta($id,'_hpg_token',true);
        if (!$raw) { return ''; }
        if (!function_exists('openssl_decrypt')) { throw new RuntimeException('OpenSSL is required to read the stored token.'); }
        $raw=base64_decode($raw,true);
        if ($raw===false || strlen($raw)<29) { throw new RuntimeException('Stored token is invalid. Please enter it again.'); }
        $token=openssl_decrypt(substr($raw,28),'aes-256-gcm',hash('sha256',wp_salt('auth'),true),OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16));
        if ($token===false) { throw new RuntimeException('Cannot decrypt token. WordPress salts may have changed; enter the token again.'); }
        return $token;
    }
    public static function redact($text,$id) {
        try { $token=$id?self::token($id):''; if ($token) { $text=str_replace($token,'[redacted]',$text); } } catch(Throwable $e) {}
        $text=preg_replace('~(?:github_pat_[A-Za-z0-9_]+|gh[pousr]_[A-Za-z0-9_]+)~','[redacted]',$text);
        return substr($text,0,7000);
    }
    private static function base_path($id) {
        $tmp=realpath(sys_get_temp_dir());
        if (!$tmp) { throw new RuntimeException('System temporary directory is unavailable.'); }
        $root=realpath(ABSPATH); $content=realpath(WP_CONTENT_DIR);
        if (self::inside($tmp,$root) || ($content && self::inside($tmp,$content))) { throw new RuntimeException('Configure a system temporary directory outside the public WordPress folders.'); }
        return $tmp.DIRECTORY_SEPARATOR.'hpg-sync-'.substr(hash('sha256',ABSPATH.wp_salt('auth')),0,20).'-'.(int)$id;
    }
    public static function validate_branch_deletion($config,$branch,$default) {
        if(!HPG_Access::admin()) { throw new RuntimeException('Only administrators may delete branches.'); }
        if($branch===$default) { throw new RuntimeException('The repository default branch cannot be deleted.'); }
        if($branch===$config['branch'] || (!empty($config['date_branch']) && $branch===wp_date('Y-m-d'))) { throw new RuntimeException('This branch is an active upload target. Choose another target before deleting it.'); }
    }
    public static function is_running($id) {
        $path=self::base_path($id).'/lock';if(!is_file($path) || is_link($path)) { return false; }
        $lock=@fopen($path,'r');if(!$lock) { return false; }$free=flock($lock,LOCK_EX|LOCK_NB);if($free) { flock($lock,LOCK_UN); }fclose($lock);return !$free;
    }
    public static function running_message($id) {
        $p=get_post_meta($id,'_hpg_process',true);$phase=is_array($p)?($p['phase']??'Git operation'):'Git operation';
        return 'Already running: '.$phase.'. Wait for this operation to finish.';
    }
    private function workspace() {
        $this->base=self::base_path($this->id); $this->repo=$this->base.'/repository';
        if (is_link($this->base)) { throw new RuntimeException('Workspace cannot be a symlink.'); }
        if (!is_dir($this->base) && !mkdir($this->base,0700)) { throw new RuntimeException('Cannot create a private Git workspace; check PHP user permissions.'); }
        if (!chmod($this->base,0700) || !is_writable($this->base)) { throw new RuntimeException('Cannot secure the workspace. Check its ownership.'); }
        foreach(['repository','hooks'] as $dir) {
            $p=$this->base.'/'.$dir;
            if (is_link($p)) { throw new RuntimeException('Unexpected workspace symlink.'); }
            if (!is_dir($p) && !mkdir($p,0700)) { throw new RuntimeException('Cannot create workspace folder.'); }
        }
        $this->private_file($this->base.'/empty-config','');
        $env=getenv(); $this->env=is_array($env)?$env:[];
        foreach(array_keys($this->env) as $name) { if (strpos($name,'GIT_')===0) { unset($this->env[$name]); } }
        $this->env['GIT_TERMINAL_PROMPT']='0'; $this->env['GIT_LFS_SKIP_SMUDGE']='1'; $this->env['GIT_CONFIG_NOSYSTEM']='1';
        $this->env['GIT_CONFIG_GLOBAL']=$this->base.'/empty-config'; $this->env['LC_ALL']='C';
        $this->env['GIT_AUTHOR_NAME']='Harsh GitHub Sync'; $this->env['GIT_AUTHOR_EMAIL']='sync@localhost';
        $this->env['GIT_COMMITTER_NAME']='Harsh GitHub Sync'; $this->env['GIT_COMMITTER_EMAIL']='sync@localhost';
        if (strpos($this->config['url'],'https://')===0) {
            $token=self::token($this->id);
            $this->env['HPG_TOKEN']=$token;
            $this->private_file($this->base.'/askpass.sh',"#!/bin/sh\ncase \"\$1\" in *Username*) printf '%s\\n' 'x-access-token';; *) printf '%s\\n' \"\$HPG_TOKEN\";; esac\n",0700);
            $this->env['GIT_ASKPASS']=$this->base.'/askpass.sh';
        } else {
            $ssh='ssh -o BatchMode=yes -o ConnectTimeout=15 -o StrictHostKeyChecking=accept-new -o UserKnownHostsFile='.escapeshellarg($this->base.'/known_hosts');
            if (is_file($this->base.'/id_ed25519')) { $ssh.=' -o IdentitiesOnly=yes -i '.escapeshellarg($this->base.'/id_ed25519'); }
            $this->env['GIT_SSH_COMMAND']=$ssh;
        }
    }
    private function private_file($path,$contents,$mode=0600) {
        if (is_link($path)) { throw new RuntimeException('Unexpected workspace symlink.'); }
        if (file_put_contents($path,$contents)===false || !chmod($path,$mode)) { throw new RuntimeException('Cannot write private workspace file.'); }
    }
    private function command($args,$allow_fail=false,$git=true,$truncate=false,$consumer=null) {
        if (!function_exists('proc_open')) { throw new RuntimeException('PHP proc_open is disabled. Enable it in your local/server PHP configuration.'); }
        $operation=$args[0];
        if(function_exists('update_post_meta')) { update_post_meta($this->id,'_hpg_process',['state'=>'running','phase'=>($this->task?$this->task.' · ':'').'Git · '.$operation,'time'=>time()]); }
        if ($git) { $args=array_merge(['git','-c','core.hooksPath='.$this->base.'/hooks','-c','credential.helper=','-c','protocol.file.allow=never','-c','protocol.ext.allow=never'], $args); }
        $pipes=[];
        $proc=proc_open($args,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,$this->repo,$this->env);
        if (!is_resource($proc)) { throw new RuntimeException('Could not start Git. Make sure it is installed and accessible to the PHP service user.'); }
        fclose($pipes[0]); stream_set_blocking($pipes[1],false); stream_set_blocking($pipes[2],false);
        $out=''; $err=''; $status=null; $truncated=false; $limit=$truncate?6000:2097152;
        $drain=function() use(&$out,&$err,&$truncated,$limit,$consumer,$pipes) {
            $chunk=stream_get_contents($pipes[1]);
            if ($consumer!==null) { $consumer($chunk); }
            else {
                $room=max(0,$limit-strlen($out));
                $out.=substr($chunk,0,$room);
                if(strlen($chunk)>$room) { $truncated=true; }
            }
            // Drain all warning/progress output while retaining only a bounded tail.
            $err=substr($err.stream_get_contents($pipes[2]),-65536);
        };
        try {
            do {
                $drain();
                if($truncated && !$truncate) { throw new RuntimeException('Git '.$operation.' listing exceeds 2 MiB. This is a repository metadata limit, not a source-code limit.'); }
                $status=proc_get_status($proc);
                if (!$status['running']) { break; }
                usleep(20000);
            } while(true);
            $drain();
        } catch(Throwable $e) {
            proc_terminate($proc); fclose($pipes[1]); fclose($pipes[2]); proc_close($proc); throw $e;
        }
        fclose($pipes[1]); fclose($pipes[2]); $closed=proc_close($proc);
        $code=$status['exitcode']>=0?$status['exitcode']:$closed;
        if ($code!==0 && !$allow_fail) {
            throw new RuntimeException('Git '.$operation.' failed: '.trim($err?:$out)."\nCheck Git is in the PHP PATH, the repo exists, and your token/deploy key has access. SSH must trust GitHub and reach port 22.");
        }
        if($truncated && !$truncate) { throw new RuntimeException('Git '.$operation.' repository metadata listing exceeds 2 MiB.'); }
        if($truncated) { $out.="\n[File listing shortened for display. All selected files are still included in the upload.]"; }
        return ['code'=>$code,'out'=>trim($out),'err'=>trim($err)];
    }
    public function perform($task,$new_branch='',$manual=false) {
        $this->task=$task;
        if(!empty($this->config['date_branch']) && in_array($task,['preview','push'],true)) { $this->base_branch=$this->config['branch'];$this->config['branch']=wp_date('Y-m-d'); }
        self::validate_config($this->config);
        $this->details['branch']=$this->config['branch'];
        if (DIRECTORY_SEPARATOR==='\\') { throw new RuntimeException('Version 1.0 supports Linux/macOS PHP hosts. On Windows use WSL or a Linux container.'); }
        $this->deadline=0;
        if (function_exists('set_time_limit')) { @set_time_limit(0); }
        $this->workspace();
        if (is_link($this->base.'/lock')) { throw new RuntimeException('Unexpected lock symlink.'); }
        $lock=fopen($this->base.'/lock','c');
        if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) { if($lock) { fclose($lock); } throw new HPG_Operation_Running(self::running_message($this->id)); }
        chmod($this->base.'/lock',0600);
        update_post_meta($this->id,'_hpg_process',['state'=>'running','phase'=>'Preparing private workspace','time'=>time()]);
        try {
            if($task==='regenerate_key') { return $this->key(true).' Remove the old deploy key on GitHub and register this new key before pushing.'; }
            if($task==='delete_key') {
                foreach(['id_ed25519','id_ed25519.pub'] as $name) { $file=$this->base.'/'.$name;if(file_exists($file) && !unlink($file)) { throw new RuntimeException('Cannot delete local SSH key. Check ownership.'); } }
                delete_post_meta($this->id,'_hpg_public_key');
                if($task==='delete_key') { return 'Local SSH key pair deleted. Remove the old deploy key in GitHub repository settings. Automatic push is paused.'; }
                return $this->key().' Remove the old deploy key on GitHub and register this new key before pushing.';
            }
            if ($task==='key') { return $this->key(); }
            if($task==='scan_large') {
                $this->scan_large();
                return count($this->large_files).' selected files exceed '.(min(50*1048576,$this->threshold())/1048576).' MiB. Review the list below, choose Skip or Upload, then Preview or Push. No source files were changed or uploaded.';
            }
            if(in_array($task,['preview','push'],true)) { $this->review_large($manual); }
            if ($task==='permissions') { $this->repair($this->base); return 'Workspace permissions repaired. Folders are private (0700), files are private (0600), and the authentication helper is executable (0700). WordPress source files were not changed.'; }
            if($task==='create_branch' && ($this->config['branch_mode']??'empty')==='current' && self::token($this->id)) {
                $candidate=$this->config;$candidate['branch']=$new_branch;self::validate_config($candidate);
                $api=new HPG_GitHub($this->id);$message=$api->create_branch_fast($this->config['branch'],$new_branch);$this->details=$api->details();
                $branches=(array)get_post_meta($this->id,'_hpg_branches',true);$branches[]=$new_branch;update_post_meta($this->id,'_hpg_branches',array_values(array_unique($branches)));
                return $message;
            }
            $this->command(['--version']);
            $this->command(['check-ref-format','--branch',$this->config['branch']]);
            $heads=$this->command(['ls-remote','--heads',$this->config['url']]);
            $branches=[];
            foreach(explode("\n",$heads['out']) as $line) { if(preg_match('~\srefs/heads/(.+)$~',$line,$m)) { $branches[]=$m[1]; } }
            update_post_meta($this->id,'_hpg_branches',$branches);
            if ($task==='branches') { return 'Branches refreshed from GitHub: '.($branches?implode(', ',$branches):'(empty repository; first push creates a branch)'); }
            if ($task==='test') { return "Connection successful. Git can read this repository.\nBranches: ".($branches?implode(', ',$branches):'(empty repository; first push creates the branch)')."\nWrite permission is verified only by a successful push."; }
            if($task==='delete_branch') {
                if(!HPG_Access::admin()) { throw new RuntimeException('Only administrators may delete branches.'); }
                $candidate=$this->config;$candidate['branch']=$new_branch;self::validate_config($candidate);$this->command(['check-ref-format','--branch',$new_branch]);
                $sym=$this->command(['ls-remote','--symref',$this->config['url'],'HEAD']);
                if(!preg_match('~^ref: refs/heads/([^\s]+)\s+HEAD~m',$sym['out'],$m)) { throw new RuntimeException('Cannot identify the default branch. Refresh connection before deleting.'); }
                $default=$m[1];update_post_meta($this->id,'_hpg_default_branch',$default);
                self::validate_branch_deletion($this->config,$new_branch,$default);
                $sha='';foreach(explode("\n",$heads['out']) as $line) { $parts=preg_split('/\s+/',trim($line));if(($parts[1]??'')==='refs/heads/'.$new_branch && preg_match('/^[a-f0-9]{40,64}$/D',$parts[0])) { $sha=$parts[0];break; } }
                if(!$sha) { throw new RuntimeException('That branch no longer exists. Refresh branches.'); }
                $this->command(['init','--quiet']);
                $this->command(['push','--force-with-lease=refs/heads/'.$new_branch.':'.$sha,$this->config['url'],':refs/heads/'.$new_branch]);
                update_post_meta($this->id,'_hpg_branches',array_values(array_diff($branches,[$new_branch])));
                delete_post_meta($this->id,'_hpg_git_panel');delete_post_meta($this->id,'_hpg_conflicts');delete_post_meta($this->id,'_hpg_last_pr');delete_post_meta($this->id,'_hpg_open_prs');
                $this->details['branch']=$new_branch;$this->details['commit']=$sha;
                return 'Deleted GitHub branch '.$new_branch.'. Last commit: '.$sha.'. WordPress files were unchanged.';
            }
            $exists=in_array($this->config['branch'],$branches,true);
            if($task==='create_branch') {
                $candidate=$this->config; $candidate['branch']=$new_branch;
                self::validate_config($candidate); $this->command(['check-ref-format','--branch',$new_branch]);
                if(in_array($new_branch,$branches,true)) { throw new RuntimeException('That branch already exists. Select it from the available branches.'); }
                $mode=$this->config['branch_mode']??'empty';
                if(!in_array($mode,['current','empty','repository'],true)) { throw new RuntimeException('Choose a valid branch creation option.'); }
                if($mode==='empty') {
                    $this->prepare(false);
                    $this->command(['checkout','--quiet','--orphan',$new_branch]);
                    $this->command(['commit','--allow-empty','-m','Initialize empty branch '.$new_branch]);
                } elseif($mode==='repository') {
                    $source=$this->config;$source['url']=$this->config['branch_source_url'];$source['branch']=$this->config['branch_source_name'];self::validate_config($source);
                    $this->command(['check-ref-format','--branch',$source['branch']]);
                    $this->prepare(false);
                    $this->command(['fetch','--no-tags',$source['url'],'refs/heads/'.$source['branch']]);
                    $this->command(['checkout','--quiet','--detach','FETCH_HEAD']);
                } else {
                    if(!$exists) { throw new RuntimeException('The source branch does not exist. Choose Empty branch or push your first snapshot.'); }
                    $this->prepare(true);
                }
                // Empty expected ref prevents overwriting a concurrently created branch.
                $this->command(['push','--force-with-lease=refs/heads/'.$new_branch.':','origin','HEAD:refs/heads/'.$new_branch]);
                $branches[]=$new_branch; sort($branches); update_post_meta($this->id,'_hpg_branches',$branches);
                $this->details['branch']=$new_branch;
                $sha=$this->command(['rev-parse','--short','HEAD']);$this->details['commit']=$sha['out'];
                return 'Created GitHub branch '.$new_branch.' · '.$mode.'. Target branch selected. WordPress files were unchanged.';
            }
            if(!$exists && $this->base_branch && in_array($this->base_branch,$branches,true)) {
                $dated=$this->config['branch'];$this->config['branch']=$this->base_branch;
                try { $this->prepare(true); } finally { $this->config['branch']=$dated; }
                $this->command(['checkout','--quiet','-B',$dated]);
            } else { $this->prepare($exists); }
            if ($task==='fetch') {
                if (!$exists) { return 'Connected successfully. This branch does not yet exist. Preview and push to create it. No WordPress files were changed.'; }
                $log=$this->command(['log','-8','--format=%h %ad %s','--date=short']);
                return 'Fetched branch '.$this->config['branch'].". No WordPress files were changed.\n\nRecent GitHub commits:\n".$log['out'];
            }
            update_post_meta($this->id,'_hpg_process',['state'=>'running','phase'=>'Copying selected files','time'=>time()]);
            $this->snapshot();
            if(($this->config['large_mode']??'ask')==='skip') {
                foreach($this->large_files as $file) {
                    if($file['size']<=$this->threshold()) { continue; }
                    $tracked=$this->command(['ls-files','--error-unmatch','--',$file['path']],true);
                    if($tracked['code']===0) { $this->command(['restore','--source=HEAD','--worktree','--',$file['path']]); }
                }
            }
            if($this->needs_lfs) {
                if(is_file($this->repo.'/.lfsconfig')) { throw new RuntimeException('Custom .lfsconfig endpoints are not supported. Add .lfsconfig to exclusions before using large-file upload.'); }
                $this->command(['lfs','install','--local','--skip-smudge','--skip-repo']);
                foreach($this->large_files as $file) {
                    if($file['size']>100*1024*1024 && !(($this->config['large_mode']??'ask')==='skip' && $file['size']>$this->threshold())) { $this->command(['lfs','track','--filename','--',$file['path']]); }
                }
            }
            $this->command(['add','--force','--all','--','.']);
            $change=$this->command(['diff','--cached','--quiet'],true);
            if(!in_array($change['code'],[0,1],true)) { throw new RuntimeException('Could not inspect staged changes: '.$change['err']); }
            $changed=$change['code']===1;
            // Capture exact changed paths for history without a giant command output buffer.
            $pending=''; $status=null; $files=[];
            $consume=function($chunk) use(&$pending,&$status,&$files) {
                $pending.=$chunk;
                while(($end=strpos($pending,"\0"))!==false) {
                    $part=substr($pending,0,$end);$pending=substr($pending,$end+1);
                    if($status===null) { $status=$part; }
                    else { $files[]=['status'=>$status,'path'=>$part];$status=null; }
                    if(count($files)>200000) { throw new RuntimeException('Snapshot contains over 200,000 changed files; split the upload into profiles.'); }
                }
                if(strlen($pending)>1048576) { throw new RuntimeException('Malformed staged path.'); }
            };
            $this->command(['diff','--cached','--no-renames','--name-status','-z'],false,true,false,$consume);
            $only=trim($this->config['upload_only']??'');
            if($only!=='') {
                $keepAttributes=false;foreach($this->large_files as $large) { if($large['size']>100*1048576 && self::selected_change($large['path'],$only)) { $keepAttributes=true; } }
                $kept=[];$ignored=[];$hasHead=$this->command(['rev-parse','--verify','HEAD'],true)['code']===0;
                foreach($files as $file) {
                    if(($keepAttributes && $file['path']==='.gitattributes') || self::selected_change($file['path'],$only)) { $kept[]=$file; }else { $ignored[]=$file['path']; }
                }
                foreach(array_chunk($ignored,100) as $paths) {
                    $args=$hasHead?['reset','--quiet','HEAD','--']:['rm','--quiet','--cached','--force','--'];
                    $this->command(array_merge($args,$paths));
                }
                $files=$kept;$changed=count($files)>0;
            }
            $this->details['files']=$files;
            $listing=[];foreach(array_slice($files,0,100) as $file) { $listing[]=$file['status']."\t".$file['path']; }
            if(count($files)>100) { $listing[]='[Text summary: first 100 of '.count($files).' files. The full paginated file table appears below and in Sync History.]'; }
            $diff=['out'=>implode("\n",$listing)];
            if ($task==='preview') { return $diff['out']?"Pending upload to ".$this->config['branch']."\nA = added · M = modified · D = deleted\n\n".$diff['out']:'No code changes to upload. Your selected files match this GitHub branch.'; }
            if ($task!=='push') { throw new RuntimeException('Unknown operation.'); }
            if (strpos($this->config['url'],'https://')===0 && !self::token($this->id)) { throw new RuntimeException('Add a GitHub HTTPS token with Contents: Read and write before pushing.'); }
            if ($changed) { $this->command(['commit','--quiet','-m','WordPress code sync '.gmdate('Y-m-d H:i:s').' UTC']); }
            if (!$changed && !$exists) { throw new RuntimeException('No files to upload. Check source paths and exclusions.'); }
            if($this->needs_lfs && $changed) { $this->command(['lfs','push','origin',$this->config['branch']]); }
            $this->command(['push','origin','HEAD:refs/heads/'.$this->config['branch']]);
            $sha=$this->command(['rev-parse','--short','HEAD']);
            $this->details['commit']=$sha['out'];
            return 'Push successful. Branch: '.$this->config['branch'].' · Commit: '.$sha['out']."\n".($diff['out']?:'No file changes; remote write access verified.');
        } finally { flock($lock,LOCK_UN); fclose($lock); }
    }
    public function conflicts($pr,$resolutions=null) {
        $this->task=$resolutions===null?'load_conflicts':'resolve_conflicts';
        if(!HPG_Access::admin()) { throw new RuntimeException('Only administrators may resolve conflicts.'); }
        $this->config['branch']=$pr['head']['ref'];self::validate_config($this->config);
        $base=$pr['base']['ref'];$c=$this->config;$c['branch']=$base;self::validate_config($c);
        $this->deadline=0;$this->workspace();
        if(is_link($this->base.'/lock')) { throw new RuntimeException('Unexpected lock symlink.'); }
        $lock=fopen($this->base.'/lock','c');if(!$lock || !flock($lock,LOCK_EX|LOCK_NB)) { if($lock) { fclose($lock); }throw new HPG_Operation_Running(self::running_message($this->id)); }chmod($this->base.'/lock',0600);
        try {
            $this->prepare(true);$head=$this->command(['rev-parse','HEAD']);
            $this->command(['fetch','--no-tags','origin','refs/heads/'.$base]);$target=$this->command(['rev-parse','FETCH_HEAD']);
            if($head['out']!==$pr['head']['sha'] || $target['out']!==$pr['base']['sha']) { throw new HPG_Review_Required('Branches changed. Load conflicts again.'); }
            $tree=$this->command(['ls-tree','-r','FETCH_HEAD']);if(preg_match('/^(120000|160000) /m',$tree['out'])) { throw new RuntimeException('Resolve branches containing symlinks or submodules with local Git.'); }
            if(is_file($this->repo.'/.lfsconfig')) { throw new RuntimeException('Custom LFS configuration requires local Git resolution.'); }
            $merge=$this->command(['merge','--no-commit','--no-ff','FETCH_HEAD'],true);
            $raw='';$this->command(['diff','--name-only','--diff-filter=U','-z'],false,true,false,function($chunk) use(&$raw) { $raw.=$chunk;if(strlen($raw)>1048576) { throw new RuntimeException('Conflict listing too large.'); } });$paths=array_values(array_filter(explode("\0",$raw)));
            if(!$paths) { if($merge['code']!==0) { throw new RuntimeException('Git could not prepare the merge: '.$merge['err']); }return []; }
            if(count($paths)>20) { throw new RuntimeException('More than 20 conflicting files. Resolve this request with local Git.'); }
            $files=[];$total=0;
            foreach($paths as $i=>$path) {
                if(!preg_match('~^[A-Za-z0-9_./ -]+$~D',$path) || preg_match('~(^|/)\.\.?(/|$)~',$path) || $path[0]==='/' || strpos($path,'.git/')===0) { throw new RuntimeException('This conflict path requires local Git.'); }
                foreach([2,3] as $stage) { $blob=$this->command(['rev-parse',':'.$stage.':'.$path],true);if($blob['code']!==0) { throw new RuntimeException('Rename/delete conflicts require local Git: '.$path); } }
                $file=$this->repo.'/'.$path;$real=realpath($file);
                if(!$real || !self::inside($real,$this->repo) || is_link($file) || !is_file($file) || filesize($file)>262144) { throw new RuntimeException('Large or complex conflict requires local Git: '.$path); }
                $text=file_get_contents($file);$total+=strlen($text);
                if(strpos($text,"\0")!==false || !preg_match('//u',$text) || strpos($text,'<<<<<<< ')===false || $total>1048576) { throw new RuntimeException('Binary or complex conflict requires local Git: '.$path); }
                $files[]=['path'=>$path,'text'=>$text];
                if($resolutions!==null) {
                    if(!isset($resolutions[$i]) || !is_string($resolutions[$i])) { throw new RuntimeException('Resolve every listed file.'); }
                    $value=$resolutions[$i];
                    if(strlen($value)>262144 || strpos($value,"\0")!==false || !preg_match('//u',$value) || preg_match('/^(<<<<<<<|=======|>>>>>>>|\|\|\|\|\|\|\|)(?: |$)/m',$value)) { throw new RuntimeException('Remove conflict markers and keep valid text under 256 KiB: '.$path); }
                    $this->private_file($file,$value,fileperms($file)&0111?0700:0600);$this->command(['add','--',$path]);
                }
            }
            if($resolutions===null) { return $files; }
            $left=$this->command(['diff','--name-only','--diff-filter=U']);if($left['out']!=='') { throw new RuntimeException('Unresolved files remain.'); }
            $changes=$this->command(['diff','--cached','--name-status']);
            $manifest=[];foreach(explode("\n",$changes['out']) as $line) { $parts=explode("\t",$line);if(count($parts)>=2) { $manifest[]=['status'=>substr($parts[0],0,1),'path'=>end($parts)]; } }
            $this->command(['commit','-m','Resolve conflicts for pull request #'.$pr['number'].': '.$base.' into '.$this->config['branch']]);
            $this->command(['push','origin','HEAD:refs/heads/'.$this->config['branch']]);$sha=$this->command(['rev-parse','HEAD']);
            $this->details=['branch'=>$this->config['branch'],'commit'=>$sha['out'],'files'=>$manifest];
            return $files;
        } finally { flock($lock,LOCK_UN);fclose($lock); }
    }
    private function prepare($exists) {
        // This is a disposable checkout, never the user's WordPress Git repository.
        self::erase($this->repo);
        if (!mkdir($this->repo,0700)) { throw new RuntimeException('Cannot recreate isolated repository.'); }
        $this->command(['init','--quiet']);
        $this->command(['remote','add','origin',$this->config['url']]);
        if ($exists) {
            $this->command(['fetch','--no-tags','origin','refs/heads/'.$this->config['branch']]);
            $unsafe=false; $pending='';
            $consume=function($chunk) use(&$unsafe,&$pending) {
                $pending.=$chunk;
                while(($end=strpos($pending,"\0"))!==false) {
                    $entry=substr($pending,0,$end); $pending=substr($pending,$end+1);
                    if(preg_match('/^(120000|160000) /',$entry)) { $unsafe=true; }
                }
                if(strlen($pending)>1048576) { throw new RuntimeException('Malformed remote tree entry.'); }
            };
            $this->command(['ls-tree','-r','-z','FETCH_HEAD'],false,true,false,$consume);
            if ($unsafe) { throw new RuntimeException('This branch contains symlinks or submodules. Use a branch without them for source snapshots.'); }
            $this->command(['checkout','--quiet','-B',$this->config['branch'],'FETCH_HEAD']);
        } else { $this->command(['checkout','--quiet','--orphan',$this->config['branch']]); }
    }
    public static function selected_change($path,$patterns) {
        foreach(self::lines($patterns) as $p) { $p=trim($p,'/');if($p==='*' || $p==='.' || $path===$p || strpos($path,$p.'/')===0 || fnmatch($p,$path)) { return true; } }return false;
    }
    private function skip($relative) {
        if($relative==='wp-content/hpg-sync-private-log' || strpos($relative,'wp-content/hpg-sync-private-log/')===0) { return true; }
        if(strpos($relative,'wp-content/plugins/harsh-github-sync/log/')===0 || $relative==='wp-content/plugins/harsh-github-sync/log') { return true; }
        $parts=explode('/',$relative); $name=end($parts);
        // Git metadata is managed by the isolated repository, not copied from WordPress.
        foreach($parts as $part) { if(in_array(strtolower($part),['.git','.svn','.hg'],true)) { return true; } }
        if(empty($this->config['include_all'])) {
            foreach($parts as $part) {
                if(in_array(strtolower($part),['.ssh','.aws','.gnupg','uploads','cache','caches','backup','backups','ai1wm-backups','updraft','wflogs'],true)) { return true; }
            }
            if(preg_match('~^(?:wp-config\.php|\.env.*|\.htpasswd|\.gitconfig|\.gitmodules|\.netrc|\.npmrc|credentials.*|id_rsa.*|id_ed25519.*)$~i',$name)) { return true; }
            if(preg_match('~\.(?:sql(?:\.gz)?|sqlite\d*(?:-.+)?|db|pem|key|p12|pfx)$~i',$name)) { return true; }
        }
        foreach(self::lines($this->config['excludes']) as $pattern) {
            $pattern=trim($pattern,'/');
            if($pattern==='') { continue; }
            if(strpos($relative.'/', $pattern.'/')===0 || fnmatch($pattern,$relative) || fnmatch($pattern,$name)) { return true; }
            if (fnmatch($pattern,$relative) || fnmatch($pattern,$name)) { return true; }
            foreach($parts as $part) { if(fnmatch($pattern,$part)) { return true; } }
        }
        return false;
    }
    private function snapshot() {
        $paths=self::lines($this->config['paths']);
        foreach($paths as $path) {
            if ($path==='.') {
                foreach(scandir($this->repo) as $child) { if($child!=='.' && $child!=='..' && $child!=='.git') { self::erase($this->repo.'/'.$child); } }
                $this->copy_tree(rtrim(ABSPATH,'/'),$this->repo,'');
            } else {
                if ($this->skip($path)) { throw new RuntimeException('Selected source path is excluded: '.$path); }
                $target=$this->repo.'/'.$path;
                self::erase($target);
                $parent=dirname($target); if (!is_dir($parent) && !mkdir($parent,0700,true)) { throw new RuntimeException('Cannot create snapshot parent.'); }
                $this->copy_tree(rtrim(ABSPATH,'/').'/'.$path,$target,$path);
            }
        }
    }
    private function threshold() { $mb=(int)($this->config['size_mb']??50);return $mb===0?PHP_INT_MAX:max(10,$mb)*1048576; }
    private function copy_tree($src,$dst,$relative) {
        if(is_link($src) || ($relative!=='' && $this->skip($relative))) { return; }
        if(!is_readable($src)) { throw new RuntimeException('Cannot read source file/folder: '.$relative.'. Ask the OS owner to grant PHP read access.'); }
        if(is_dir($src)) {
            if(!is_dir($dst) && !mkdir($dst,0700)) { throw new RuntimeException('Cannot create snapshot folder: '.$relative); }
            $children=scandir($src); if($children===false) { throw new RuntimeException('Cannot list source folder: '.$relative); }
            foreach($children as $child) { if($child!=='.' && $child!=='..') { $this->copy_tree($src.'/'.$child,$dst.'/'.$child,ltrim($relative.'/'.$child,'/')); } }
        } elseif(is_file($src)) {
            if(filesize($src)>$this->threshold()) {
                $mode=$this->config['large_mode']??'ask';
                if($mode==='skip') { return; }
                if($mode!=='upload') { throw new RuntimeException('Large file requires review: '.$relative.'. Scan large files and choose Skip or Upload.'); }
                $reviewed=false;foreach($this->large_files as $file) { if($file['path']===$relative && $file['size']===filesize($src) && $file['mtime']===filemtime($src)) { $reviewed=true;break; } }
                if(!$reviewed) { throw new RuntimeException('Large file changed during snapshot: '.$relative.'. Scan and review again.'); }
            }
            if(filesize($src)>100*1048576) {
                $seen=false;foreach($this->large_files as $file) { if($file['path']===$relative && $file['size']===filesize($src) && $file['mtime']===filemtime($src)) { $seen=true;break; } }
                if(!$seen || !$this->needs_lfs) { throw new RuntimeException('LFS file changed during snapshot: '.$relative.'. Scan and retry.'); }
            }
            if(!copy($src,$dst) || !chmod($dst,is_executable($src)?0700:0600)) { throw new RuntimeException('Cannot snapshot file: '.$relative); }
        }
    }
    private function scan_large() {
        $this->large_files=[];
        foreach(self::lines($this->config['paths']) as $path) {
            $relative=$path==='.'?'':$path;
            $this->scan_path(rtrim(ABSPATH,'/').($relative!==''?'/'.$relative:''),$relative);
        }
        usort($this->large_files,function($a,$b){return strcmp($a['path'],$b['path']);});
        update_post_meta($this->id,'_hpg_large_files',$this->large_files);
        $sig=hash('sha256',json_encode([$this->config['size_mb']??50,$this->large_files]));
        update_post_meta($this->id,'_hpg_large_scan',$sig);
        update_post_meta($this->id,'_hpg_large_time',time());
        return $sig;
    }
    private function scan_path($src,$relative) {
        if(is_link($src) || ($relative!=='' && $this->skip($relative))) { return; }
        if(!is_readable($src)) { throw new RuntimeException('Cannot read selected path while scanning: '.$relative); }
        if(is_dir($src)) {
            $children=scandir($src);if($children===false) { throw new RuntimeException('Cannot scan selected folder: '.$relative); }
            foreach($children as $child) { if($child!=='.' && $child!=='..') { $this->scan_path($src.'/'.$child,ltrim($relative.'/'.$child,'/')); } }
        } elseif(is_file($src)) {
            $size=filesize($src);
            if($size>min(50*1048576,$this->threshold())) { $this->large_files[]=['path'=>$relative,'size'=>$size,'mtime'=>filemtime($src)]; }
        }
    }
    private function review_large($manual) {
        $previous=get_post_meta($this->id,'_hpg_large_scan',true);$sig=$this->scan_large();
        if(!$this->large_files) { return; }
        $review=array_filter($this->large_files,function($file){return $file['size']>$this->threshold();});
        $mode=$this->config['large_mode']??'ask';
        if($review && $mode==='ask') { throw new HPG_Review_Required(count($review).' files exceed your size threshold. Nothing was uploaded. Review the list below and choose No — skip or Yes — upload.'); }
        if($review && $mode!=='skip') {
        $approved=get_post_meta($this->id,'_hpg_large_approved',true);
        if($mode==='upload' && !empty($this->config['auto_large'])) { update_post_meta($this->id,'_hpg_large_approved',$sig);$approved=$sig; }
        if($manual && $previous===$sig) { update_post_meta($this->id,'_hpg_large_approved',$sig);$approved=$sig; }
        if($approved!==$sig) { throw new HPG_Review_Required('The large-file list is new or changed. Review the refreshed list below, select Yes — upload, then Preview or Push again.'); }
        }
        foreach($this->large_files as $file) { if($mode==='skip' && $file['size']>$this->threshold()) { continue; }if($file['size']>100*1024*1024) { $this->needs_lfs=true;break; } }
        if($this->needs_lfs) {
            $lfs=$this->command(['lfs','version'],true);
            if($lfs['code']!==0) { throw new RuntimeException('Your selection includes files over 100 MiB. GitHub requires Git LFS for them. Install git-lfs for the PHP/web-server account, then retry, or choose Skip.'); }
            // Extra time for LFS transfers; web-server limits may still be shorter.
            // Git and LFS operations have no plugin action deadline.
        }
    }
    private static function ssh_string($value) { return pack('N',strlen($value)).$value; }
    private static function ssh_integer($value) {
        $value=ltrim($value,"\0");
        if($value!=='' && (ord($value[0]) & 128)) { $value="\0".$value; }
        return self::ssh_string($value);
    }
    private function key($regenerate=false) {
        $private=$this->base.'/id_ed25519';
        if($regenerate || !is_file($private) || !is_file($private.'.pub')) {
            $comment='harsh-sync@localhost';
            if(function_exists('sodium_crypto_sign_keypair')) {
                $pair=sodium_crypto_sign_keypair();
                $public=sodium_crypto_sign_publickey($pair);
                $secret=sodium_crypto_sign_secretkey($pair);
                $blob=self::ssh_string('ssh-ed25519').self::ssh_string($public);
                $check=random_bytes(4);
                $inner=$check.$check.self::ssh_string('ssh-ed25519').self::ssh_string($public).self::ssh_string($secret).self::ssh_string($comment);
                $padding=8-(strlen($inner)%8);
                for($i=1;$i<=$padding;$i++) { $inner.=chr($i); }
                $raw="openssh-key-v1\0".self::ssh_string('none').self::ssh_string('none').self::ssh_string('').pack('N',1).self::ssh_string($blob).self::ssh_string($inner);
                $pem="-----BEGIN OPENSSH PRIVATE KEY-----\n".chunk_split(base64_encode($raw),70,"\n")."-----END OPENSSH PRIVATE KEY-----\n";
                $pub='ssh-ed25519 '.base64_encode($blob).' '.$comment;
                if(function_exists('sodium_memzero')) { sodium_memzero($secret);sodium_memzero($pair); }
            } elseif(function_exists('openssl_pkey_new')) {
                $pair=openssl_pkey_new(['private_key_bits'=>3072,'private_key_type'=>OPENSSL_KEYTYPE_RSA]);
                $pem='';
                if(!$pair || !openssl_pkey_export($pair,$pem)) { throw new RuntimeException('PHP OpenSSL could not generate an SSH key. Ask your host to enable key generation.'); }
                $details=openssl_pkey_get_details($pair);
                if(empty($details['rsa']['e']) || empty($details['rsa']['n'])) { throw new RuntimeException('PHP OpenSSL returned an invalid SSH key.'); }
                $blob=self::ssh_string('ssh-rsa').self::ssh_integer($details['rsa']['e']).self::ssh_integer($details['rsa']['n']);
                $pub='ssh-rsa '.base64_encode($blob).' '.$comment;
            } else { throw new RuntimeException('Enable PHP Sodium or OpenSSL to generate SSH keys. Git and ssh-keygen are not required for key generation.'); }
            // Build the complete pair before replacing an existing key.
            $old_private=is_file($private)?file_get_contents($private):null;
            $old_public=is_file($private.'.pub')?file_get_contents($private.'.pub'):null;
            try {
                $this->private_file($private,$pem,0600);
                $this->private_file($private.'.pub',$pub."\n",0600);
            } catch(Throwable $error) {
                if($old_private!==null && $old_private!==false) { $this->private_file($private,$old_private,0600); }
                if($old_public!==null && $old_public!==false) { $this->private_file($private.'.pub',$old_public,0600); }
                throw $error;
            }
        }
        if(is_link($private) || is_link($private.'.pub')) { throw new RuntimeException('Unexpected SSH key symlink.'); }
        chmod($private,0600);chmod($private.'.pub',0600);
        $public=file_get_contents($private.'.pub');
        if(!$public) { throw new RuntimeException('Cannot read generated SSH public key.'); }
        update_post_meta($this->id,'_hpg_public_key',trim($public));
        return 'SSH key is ready. Copy the public key into GitHub Deploy keys and enable Allow write access. Key generation uses PHP; push and fetch still require server Git and SSH.';
    }
    private function repair($path) {
        if(is_link($path)) { throw new RuntimeException('Workspace contains a symlink; permissions were not changed.'); }
        if(is_dir($path)) {
            if(!chmod($path,0700)) { throw new RuntimeException('Cannot change workspace permissions. Check ownership.'); }
            foreach(scandir($path) as $n) { if($n!=='.' && $n!=='..') { $this->repair($path.'/'.$n); } }
        } elseif(!chmod($path,basename($path)==='askpass.sh'?0700:0600)) { throw new RuntimeException('Cannot change workspace file permissions.'); }
    }
    public static function erase($path) {
        if (is_link($path) || is_file($path)) { if(!unlink($path)) { throw new RuntimeException('Cannot remove isolated workspace file.'); } return; }
        if (!is_dir($path)) { return; }
        $items=scandir($path); if($items===false) { throw new RuntimeException('Cannot read workspace directory.'); }
        foreach($items as $n) { if($n!=='.' && $n!=='..') { self::erase($path.'/'.$n); } }
        if(!rmdir($path)) { throw new RuntimeException('Cannot remove isolated workspace directory.'); }
    }
    public static function remove_profile($id) {
        $path=self::base_path($id);
        if (is_link($path)) { throw new RuntimeException('Unexpected workspace symlink.'); }
        if (!is_dir($path)) { return; }
        $lock=fopen($path.'/lock','c');
        if(!$lock || !flock($lock,LOCK_EX|LOCK_NB)) { if($lock) { fclose($lock); } throw new RuntimeException('Wait for the running sync to finish before deleting this profile.'); }
        try { self::erase($path); } finally { flock($lock,LOCK_UN); fclose($lock); }
    }
}
